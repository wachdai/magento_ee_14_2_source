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
 * @package     Enterprise_TargetRule
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * TargetRule observer
 *
 */
class Enterprise_TargetRule_Model_Observer
{
    /**
     * Prepare target rule data
     *
     * @param Varien_Event_Observer $observer
     */
    public function prepareTargetRuleSave(Varien_Event_Observer $observer)
    {
        $_vars = array('targetrule_rule_based_positions', 'tgtr_position_behavior');
        $_varPrefix = array('related_', 'upsell_', 'crosssell_');
        if ($product = $observer->getEvent()->getProduct()) {
            foreach ($_vars as $var) {
                foreach ($_varPrefix as $pref) {
                    $v = $pref . $var;
                    if ($product->getData($v.'_default') == 1) {
                        $product->setData($v, null);
                    }
                }
            }
        }
    }

    /**
     * Process event on 'save_commit_after' event. Rebuild product index by rule conditions
     *
     * @param Varien_Event_Observer $observer
     */
    public function catalogProductSaveCommitAfter(Varien_Event_Observer $observer)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();

        /** @var \Mage_Index_Model_Indexer $indexer */
        $indexer = Mage::getSingleton('index/indexer');
        $indexer->processEntityAction(
            new Varien_Object(
                array(
                    'id' => $product->getId(),
                    'store_id' => $product->getStoreId(),
                    'rule' => $product->getData('rule'),
                    'from_date' => $product->getData('from_date'),
                    'to_date' => $product->getData('to_date')
                )
            ),
            Enterprise_TargetRule_Model_Index::ENTITY_PRODUCT,
            Enterprise_TargetRule_Model_Index::EVENT_TYPE_REINDEX_PRODUCTS
        );

        // check for upsell(s) products if any
        $this->refreshUpSells($product);

    }

    /**
     * ReIndex UpSells for the product
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function refreshUpSells(Mage_Catalog_Model_Product $product)
    {
        $upSellCollection = $product->getUpSellProductCollection();

        if($upSellCollection->count() > 0){
            /** @var \Mage_Index_Model_Indexer $indexer */
            $indexer = Mage::getSingleton('index/indexer');

            foreach($upSellCollection as $product){
                $indexer->processEntityAction(
                    new Varien_Object(
                        array(
                            'id' => $product->getId(),
                            'store_id' => $product->getStoreId(),
                            'rule' => $product->getData('rule'),
                            'from_date' => $product->getData('from_date'),
                            'to_date' => $product->getData('to_date')
                        )
                    ),
                    Enterprise_TargetRule_Model_Index::ENTITY_PRODUCT,
                    Enterprise_TargetRule_Model_Index::EVENT_TYPE_REINDEX_PRODUCTS
                );
            }
        }
    }

    /**
     * Clear customer segment indexer if customer segment is on|off on backend
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_TargetRule_Model_Observer
     */
    public function coreConfigSaveCommitAfter(Varien_Event_Observer $observer)
    {
        if ($observer->getDataObject()->getPath() == 'customer/enterprise_customersegment/is_enabled'
            && $observer->getDataObject()->isValueChanged()) {
            Mage::getSingleton('index/indexer')->logEvent(
                new Varien_Object(array('type_id' => null, 'store' => null)),
                Enterprise_TargetRule_Model_Index::ENTITY_TARGETRULE,
                Enterprise_TargetRule_Model_Index::EVENT_TYPE_CLEAN_TARGETRULES
            );
            Mage::getSingleton('index/indexer')->indexEvents(
                Enterprise_TargetRule_Model_Index::ENTITY_TARGETRULE,
                Enterprise_TargetRule_Model_Index::EVENT_TYPE_CLEAN_TARGETRULES
            );
        }
        return $this;
    }
}
