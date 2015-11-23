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
 *
 */
class Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh_Changelog
    extends Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
{
    /**
     * Move data from temporary flat table into regular flat table.
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Refresh_Changelog
     */
    protected function _moveDataToFlatTable()
    {
        $flatTable = $this->_productHelper->getFlatTableName($this->_storeId);

        if (!$this->_connection->isTableExists($flatTable)) {
            parent::_moveDataToFlatTable();
        } else {
            $describe = $this->_connection->describeTable($this->_productHelper->getFlatTableName($this->_storeId));
            $columns  = $this->_productHelper->getFlatColumns();
            $columns  = array_keys(array_intersect_key($describe, $columns));
            $select   = $this->_connection->select();

            $select->from(
                array(
                    'tf' => $this->_getTemporaryTableName($this->_productHelper->getFlatTableName($this->_storeId)),
                ),
                $columns
            );
            $sql = $select->insertFromSelect($flatTable, $columns);
            $this->_connection->query($sql);

            //drop "temporary" table after reindex
            $this->_connection->dropTable(
                $this->_getTemporaryTableName($this->_productHelper->getFlatTableName($this->_storeId))
            );
        }

        return $this;
    }

    /**
     * Refresh entities
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Refresh_Changelog
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        if (!$this->_isFlatIndexerEnabled()) {
            return $this;
        }
        $this->_validate();

        $changedIds = $this->_selectChangedIds();
        if (!empty($changedIds)) {
            $stores = Mage::app()->getStores();
            $resetFlag = true;
            foreach ($stores as $store) {
                $idsBatches = array_chunk($changedIds, Mage::helper('enterprise_index')->getBatchSize());
                foreach ($idsBatches as $ids) {
                    $this->_reindex($store->getId(), $ids, $resetFlag);
                }
                $resetFlag = false;
            }
            $this->_setChangelogValid();
            Mage::dispatchEvent('catalog_product_flat_partial_reindex', array('product_ids' => $changedIds));
        }

        return $this;
    }
}
