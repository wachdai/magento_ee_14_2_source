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
 * Full refresh stock index
 *
 * @category    Enterprise
 * @package     Enterprise_CatalogInventory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CatalogInventory_Model_Index_Action_Refresh extends Enterprise_Index_Model_Action_Abstract
{
    /**
     * Stock Indexer models per product type
     * Sorted by priority
     *
     * @var array
     */
    protected $_indexers;

    /**
     * Default Stock Indexer resource model name
     *
     * @var string
     */
    protected $_defaultIndexer   = 'enterprise_cataloginventory/indexer_stock_default';

    /**
     * Refresh all entities
     *
     * @return Enterprise_CatalogInventory_Model_Index_Action_Refresh
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        $this->_validate();
        try {
            $this->_metadata->setInProgressStatus()->save();
            $this->_reindexAll();
            $this->_setChangelogValid();
            Mage::dispatchEvent('cataloginventory_stock_full_reindex');
        } catch (Exception $e) {
            $this->_metadata
                ->setInvalidStatus()
                ->save();
            throw new Enterprise_Index_Model_Action_Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $this;
    }

    /**
     * Reindex all
     *
     * @return Enterprise_CatalogInventory_Model_Index_Action_Refresh
     */
    protected function _reindexAll()
    {
        $this->_clearTemporaryIndexTable();
        foreach ($this->_getTypeIndexers() as $indexer) {
            $indexer->reindexAll();
        }
        $this->_syncData();
        return $this;
    }

    /**
     * Retrieve Stock Indexer Models per Product Type
     *
     * @return array
     */
    protected function _getTypeIndexers()
    {
        if (is_null($this->_indexers)) {
            $this->_indexers = array();
            $types = Mage::getSingleton('catalog/product_type')->getTypesByPriority();
            foreach ($types as $typeId => $typeInfo) {
                if (isset($typeInfo['stock_indexer'])) {
                    $modelName = $typeInfo['stock_indexer'];
                } else {
                    $modelName = $this->_defaultIndexer;
                }
                $isComposite = !empty($typeInfo['composite']);
                $indexer = Mage::getResourceModel($modelName)
                    ->setTypeId($typeId)
                    ->setIsComposite($isComposite);

                $this->_indexers[$typeId] = $indexer;
            }
        }
        return $this->_indexers;
    }

    /**
     * Synchronize data between index storage and original storage
     *
     * @return Enterprise_CatalogInventory_Model_Index_Action_Refresh
     */
    protected function _syncData()
    {
        $idxTableName = $this->_getIdxTable();
        $tableName    = $this->_getTable('cataloginventory/stock_status');

        $this->_deleteOldRelations($tableName);

        $columns = array_keys($this->_connection->describeTable($idxTableName));
        $select = $this->_connection->select()->from($idxTableName, $columns);
        $query = $select->insertFromSelect($tableName, $columns);
        $this->_connection->query($query);
        return $this;
    }

    /**
     * Retrieve temporary index table name
     *
     * @return string
     */
    protected function _getIdxTable()
    {
        return $this->_getTable('cataloginventory/stock_status_indexer_idx');
    }

    /**
     * Clean up temporary index table
     *
     * @return void
     */
    protected function _clearTemporaryIndexTable()
    {
        $this->_connection->truncateTable($this->_getIdxTable());
    }

    /**
     * Returns table name for given entity
     * @param $entityName
     * @return string
     */
    protected function _getTable($entityName)
    {
        return $this->_metadata->getResource()->getTable($entityName);
    }

    /**
     * Delete old relations
     *
     * @var string $tableName
     */
    protected function _deleteOldRelations($tableName)
    {
        $select = $this->_connection->select()
            ->from(array('s' => $tableName))
            ->joinLeft(
                array('w' => $this->_getTable('catalog/product_website')),
                's.product_id = w.product_id AND s.website_id = w.website_id',
                array()
            )
            ->where('w.product_id IS NULL');

        $sql = $select->deleteFromSelect('s');
        $this->_connection->query($sql);
    }
}
