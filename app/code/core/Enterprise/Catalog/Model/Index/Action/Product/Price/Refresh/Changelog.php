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
 * @package     Enterprise_Catalog
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Refresh category flat index by changelog action
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Action_Product_Price_Refresh_Changelog
    extends Enterprise_Catalog_Model_Index_Action_Product_Price_Refresh
{
    /**
     * Refresh entities by changelog
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Price_Refresh_Changelog
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        $this->_validate();
        $changedIds = $this->_selectChangedIds();
        if (is_array($changedIds) && count($changedIds) > 0) {
            $idsBatches = array_chunk($changedIds, Mage::helper('enterprise_index')->getBatchSize());
            foreach ($idsBatches as $changedIds) {
                $affectedIds = $this->_reindex($changedIds);
                Mage::dispatchEvent('catalog_product_price_partial_reindex', array('product_ids' => $affectedIds));
            }
            $this->_setChangelogValid();
        }
        return $this;
    }

    /**
     * Refresh entities index
     *
     * @param array $changedIds
     * @return array Affected ids
     */
    protected function _reindex($changedIds = array())
    {
        $this->_emptyTable($this->_getIdxTable());
        $this->_prepareWebsiteDateTable();
        // retrieve products types
        $select = $this->_connection->select()
            ->from($this->_getTable('catalog/product'), array('entity_id', 'type_id'))
            ->where('entity_id IN(?)', $changedIds);
        $pairs  = $this->_connection->fetchPairs($select);
        $byType = array();
        foreach ($pairs as $productId => $productType) {
            $byType[$productType][$productId] = $productId;
        }

        $compositeIds    = array();
        $notCompositeIds = array();

        foreach ($byType as $productType => $entityIds) {
            $indexer = $this->_getIndexer($productType);
            if ($indexer->getIsComposite()) {
                $compositeIds += $entityIds;
            } else {
                $notCompositeIds += $entityIds;
            }
        }

        if (!empty($notCompositeIds)) {
            $select = $this->_connection->select()
                ->from(
                    array('l' => $this->_getTable('catalog/product_relation')),
                    'parent_id'
                )
                ->join(
                    array('e' => $this->_getTable('catalog/product')),
                    'e.entity_id = l.parent_id',
                    array('type_id')
                )
                ->where('l.child_id IN(?)', $notCompositeIds);
            $pairs  = $this->_connection->fetchPairs($select);
            foreach ($pairs as $productId => $productType) {
                if (!in_array($productId, $changedIds)) {
                    $changedIds[] = $productId;
                    $byType[$productType][$productId] = $productId;
                    $compositeIds[$productId] = $productId;
                }
            }
        }

        if (!empty($compositeIds)) {
            $this->_copyRelationIndexData($compositeIds, $notCompositeIds);
        }
        $this->_prepareTierPriceIndex($compositeIds + $notCompositeIds);
        $this->_prepareGroupPriceIndex($compositeIds + $notCompositeIds);

        $indexers = $this->_getTypeIndexers();
        foreach ($indexers as $indexer) {
            if (!empty($byType[$indexer->getTypeId()])) {
                $indexer->reindexEntity($byType[$indexer->getTypeId()]);
            }
        }

        $this->_syncData($changedIds);
        return $compositeIds + $notCompositeIds;
    }

    /**
     * Copy relations product index from primary index to temporary index table by parent entity
     *
     * @param array $parentIds
     * @param array $excludeIds
     * @return Enterprise_Catalog_Model_Index_Action_Product_Price_Refresh_Changelog
     */
    protected function _copyRelationIndexData($parentIds, $excludeIds = null)
    {
        $write  = $this->_connection;
        $select = $write->select()
            ->from($this->_getTable('catalog/product_relation'), array('child_id'))
            ->where('parent_id IN(?)', $parentIds);
        if (!empty($excludeIds)) {
            $select->where('child_id NOT IN(?)', $excludeIds);
        }

        $children = $write->fetchCol($select);

        if ($children) {
            $select = $write->select()
                ->from($this->_getTable('catalog/product_index_price'))
                ->where('entity_id IN(?)', $children);
            $query  = $select->insertFromSelect($this->_getIdxTable(), array(), false);
            $write->query($query);
        }

        return $this;
    }
}
