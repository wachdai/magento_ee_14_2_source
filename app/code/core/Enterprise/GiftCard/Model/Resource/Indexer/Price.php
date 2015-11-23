<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_GiftCard
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * GiftCard product price indexer resource model
 *
 * @category    Enterprise
 * @package     Enterprise_GiftCard
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftCard_Model_Resource_Indexer_Price extends Mage_Catalog_Model_Resource_Product_Indexer_Price_Default
{
    /**
     * Register data required by product type process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    public function registerEvent(Mage_Index_Model_Event $event)
    {
        $attributes = array(
            'allow_open_amount',
            'open_amount_min',
            'open_amount_max',
        );

        $entity = $event->getEntity();
        if ($entity == Mage_Catalog_Model_Product::ENTITY) {
            switch ($event->getType()) {
                case Mage_Index_Model_Event::TYPE_SAVE:
                    /* @var $product Mage_Catalog_Model_Product */
                    $product      = $event->getDataObject();
                    $reindexPrice = $product->getAmountsHasChanged();
                    foreach ($attributes as $code) {
                        if ($product->dataHasChangedFor($code)) {
                            $reindexPrice = true;
                            break;
                        }
                    }

                    if ($reindexPrice) {
                        $event->addNewData('product_type_id', $product->getTypeId());
                        $event->addNewData('reindex_price', 1);
                    }

                    break;

                case Mage_Index_Model_Event::TYPE_MASS_ACTION:
                    /* @var $actionObject Varien_Object */
                    $actionObject = $event->getDataObject();
                    $reindexPrice = false;

                    // check if attributes changed
                    $attrData = $actionObject->getAttributesData();
                    if (is_array($attrData)) {
                        foreach ($attributes as $code) {
                            if (array_key_exists($code, $attrData)) {
                                $reindexPrice = true;
                                break;
                            }
                        }
                    }

                    if ($reindexPrice) {
                        $event->addNewData('reindex_price_product_ids', $actionObject->getProductIds());
                    }

                    break;
            }
        }
    }

    /**
     * Prepare giftCard products prices in temporary index table
     *
     * @param int|array $entityIds  the entity ids limitation
     * @return Enterprise_GiftCard_Model_Resource_Indexer_Price
     */
    protected function _prepareFinalPriceData($entityIds = null)
    {
        $this->_prepareDefaultFinalPriceTable();

        $write  = $this->_getWriteAdapter();
        $select = $write->select()
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'))
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                '',
                array('customer_group_id')
            );
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        $select->columns(array('website_id'), 'cw')
            ->columns(array('tax_class_id'  => new Zend_Db_Expr('0')))
            ->where('e.type_id = ?', $this->getTypeId());

        // add enable products limitation
        $statusCond = $write->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $statusCond, true);

        $allowOpenAmount = $this->_addAttributeToSelect($select, 'allow_open_amount', 'e.entity_id', 'cs.store_id');
        $openAmountMin    = $this->_addAttributeToSelect($select, 'open_amount_min', 'e.entity_id', 'cs.store_id');
//        $openAmounMax    = $this->_addAttributeToSelect($select, 'open_amount_max', 'e.entity_id', 'cs.store_id');



        $attrAmounts = $this->_getAttribute('giftcard_amounts');
        // join giftCard amounts table
        $select->joinLeft(
            array('gca' => $this->getTable('enterprise_giftcard/amount')),
            'gca.entity_id = e.entity_id AND gca.attribute_id = '
            . $attrAmounts->getAttributeId()
            . ' AND (gca.website_id = cw.website_id OR gca.website_id = 0)',
            array()
        );

        $amountsExpr    = 'MIN(' . $write->getCheckSql('gca.value_id IS NULL', 'NULL', 'gca.value') . ')';

        $openAmountExpr = 'MIN(' . $write->getCheckSql(
                $allowOpenAmount . ' = 1',
                $write->getCheckSql($openAmountMin . ' > 0', $openAmountMin, '0'),
                'NULL'
            ) . ')';

        $priceExpr = new Zend_Db_Expr(
            'ROUND(' . $write->getCheckSql(
                $openAmountExpr . ' IS NULL',
                $write->getCheckSql($amountsExpr . ' IS NULL', '0', $amountsExpr),
                $write->getCheckSql(
                    $amountsExpr . ' IS NULL',
                    $openAmountExpr,
                    $write->getCheckSql(
                        $openAmountExpr . ' > ' . $amountsExpr,
                        $amountsExpr,
                        $openAmountExpr
                    )
                )
            ) . ', 4)'
        );

        $select->group(array('e.entity_id', 'cg.customer_group_id', 'cw.website_id'))
            ->columns(array(
                'price'            => new Zend_Db_Expr('NULL'),
                'final_price'      => $priceExpr,
                'min_price'        => $priceExpr,
                'max_price'        => new Zend_Db_Expr('NULL'),
                'tier_price'       => new Zend_Db_Expr('NULL'),
                'base_tier'        => new Zend_Db_Expr('NULL'),
                'group_price'      => new Zend_Db_Expr('NULL'),
                'base_group_price' => new Zend_Db_Expr('NULL'),
            ));

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('prepare_catalog_product_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('e.entity_id'),
            'website_field' => new Zend_Db_Expr('cw.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id')
        ));

        $query = $select->insertFromSelect($this->_getDefaultFinalPriceTable());
        $write->query($query);

        return $this;
    }
}
