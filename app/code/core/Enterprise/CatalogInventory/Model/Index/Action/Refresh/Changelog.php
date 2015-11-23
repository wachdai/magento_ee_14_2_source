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
 * @package     Enterprise_CatalogInventory
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Refresh stock index by changelog action
 *
 * @category    Enterprise
 * @package     Enterprise_CatalogInventory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CatalogInventory_Model_Index_Action_Refresh_Changelog
    extends Enterprise_CatalogInventory_Model_Index_Action_Refresh
{
    /**
     * Refresh entities by changelog
     *
     * @return Enterprise_CatalogInventory_Model_Index_Action_Refresh_Changelog
     */
    public function execute()
    {
        $this->_validate();
        $changedIds = $this->_selectChangedIds();
        if (is_array($changedIds) && count($changedIds) > 0) {
            $idsBatches = array_chunk($changedIds, Mage::helper('enterprise_index')->getBatchSize());
            foreach ($idsBatches as $changedIds) {
                $this->_reindex($changedIds);
            }
            $this->_setChangelogValid();
        }
        return $this;
    }

    /**
     * Refresh entities index
     *
     * @param array $changedIds
     * @return Enterprise_CatalogInventory_Model_Index_Action_Refresh_Changelog
     */
    protected function _reindex($changedIds = array())
    {
        if (!is_array($changedIds)) {
            $changedIds = array($changedIds);
        }
        $select = $this->_connection->select()
            ->from($this->_getTable('catalog/product_relation'), 'parent_id')
            ->where('child_id IN(?)', $changedIds);
        $parentIds = $this->_connection->fetchCol($select);
        if ($parentIds) {
            $processIds = array_merge($parentIds, $changedIds);
        } else {
            $processIds = $changedIds;
        }

        // retrieve product types by processIds
        $select = $this->_connection->select()
            ->from($this->_getTable('catalog/product'), array('entity_id', 'type_id'))
            ->where('entity_id IN(?)', $processIds);
        $pairs  = $this->_connection->fetchPairs($select);

        $byType = array();
        foreach ($pairs as $productId => $typeId) {
            $byType[$typeId][$productId] = $productId;
        }


        $indexers = $this->_getTypeIndexers();
        foreach ($indexers as $indexer) {
            if (isset($byType[$indexer->getTypeId()])) {
                $indexer->reindexEntity($byType[$indexer->getTypeId()]);
            }
        }

        Mage::dispatchEvent('cataloginventory_stock_partial_reindex', array('product_ids' => $processIds));
        return $this;
    }
}
