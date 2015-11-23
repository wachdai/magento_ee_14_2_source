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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Adapter item default resource
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Resource_Adapter_Item_Default extends Enterprise_Staging_Model_Resource_Adapter_Abstract
{
    /**
     * Processed tables
     *
     * @var array
     */
    protected $_processedTables        = array();

    /**
     * in backend mode only backend tables will be processed
     *
     * @var boolean
     */
    protected $_isBackendProcessing    = false;

    /**
     * Check backend Staging Tables Creates
     *
     * @param object Enterprise_Staging_Model_Staging $staging
     * @param Enterprise_Staging_Model_Staging_Event $event
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    public function checkfrontendRun(Enterprise_Staging_Model_Staging $staging, $event = null)
    {
        parent::checkfrontendRun($staging, $event);
        $this->_processItemMethodCallback('_checkBackendTables');
        return $this;
    }

    /**
     * Staging Create (Staging Item handle part)
     *
     * @param object Enterprise_Staging_Model_Staging $staging
     * @param object Enterprise_Staging_Model_Staging_Event $event
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    public function createRun(Enterprise_Staging_Model_Staging $staging, $event = null)
    {
        parent::checkfrontendRun($staging, $event);
        $this->_processItemMethodCallback('_createItem');
        return $this;
    }

    /**
     * Staging Backup (Staging Item handle part)
     *
     * @param object Enterprise_Staging_Model_Staging $staging
     * @param object Enterprise_Staging_Model_Staging_Event $event
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    public function backupRun(Enterprise_Staging_Model_Staging $staging, $event = null)
    {
        parent::backupRun($staging, $event);
        $this->_processItemMethodCallback('_backupItem');
        return $this;
    }

    /**
     * Staging Merge (Staging Item handle part)
     *
     * @param object Enterprise_Staging_Model_Staging $staging
     * @param object Enterprise_Staging_Model_Staging_Event $event
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    public function mergeRun(Enterprise_Staging_Model_Staging $staging, $event = null)
    {
        parent::mergeRun($staging, $event);
        $this->_processItemMethodCallback('_mergeItem');
        return $this;
    }

    /**
     * Staging Rollback (Staging Item handle part)
     *
     * @param object Enterprise_Staging_Model_Staging $staging
     * @param object Enterprise_Staging_Model_Staging_Event $event
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    public function rollbackRun(Enterprise_Staging_Model_Staging $staging, $event = null)
    {
        parent::rollbackRun($staging, $event);
        $this->_processItemMethodCallback('_rollbackItem');
        return $this;
    }

    /**
     * Get all backup tables
     *
     * @param  Enterprise_Staging_Model_Staging $staging
     * @param  Enterprise_Staging_Model_Staging_Event|null $event
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    public function getBackupTablesRun(Enterprise_Staging_Model_Staging $staging, $event = null)
    {
        parent::getBackupTablesRun($staging, $event);
        $this->_processItemMethodCallback('_getBackupTables');
        return $this;
    }

    /**
     * Validate and run callback method for flat item
     *
     * @param string $entityName
     * @param string $callbackMethod
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _itemFlatRun($entityName, $callbackMethod)
    {
        $helper   = Mage::helper($entityName);
        $resource = Mage::getResourceModel($entityName);

        /** @var $helper Mage_Catalog_Helper_Flat_Abstract */
        if ($helper->isAvailable() && $helper->isBuilt()) {
            $staging    = $this->getStaging();
            $websites   = $staging->getMapperInstance()->getWebsiteObjects();
            $callback   = $callbackMethod . 'Flat';

            if (!empty($websites)) {
                foreach ($websites as $website) {
                    $stores = $website->getStores();
                    foreach ($stores as $store) {
                        $masterStoreId  = (int)$store->getMasterStoreId();
                        $stagingStoreId = (int)$store->getStagingStoreId();
                        if (!$masterStoreId || !$stagingStoreId) {
                            continue;
                        }

                        $this->$callback($store, $resource);
                    }
                }
            }
        }

        // set processed tables flag
        $this->_processedTables[$entityName] = $entityName;

        return $this;
    }

    /**
     * Check Staging backend tables to exist
     *
     * @param string $entityName
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _checkBackendTables($entityName)
    {
        $stagingTablePrefix = Mage::getSingleton('enterprise_staging/staging_config')->getTablePrefix();
        $targetTable = $this->_getWriteAdapter()->getTableName($stagingTablePrefix . $this->getTable($entityName));

        if (!$this->tableExists($targetTable)) {
            $this->createTable($targetTable, $entityName);
        }

        $this->_processedTables[$entityName] = $targetTable;

        return $this;
    }

    /**
     * Create item table and records, run processes in website and store scopes
     *
     * @param string $entityName
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _createItem($entityName)
    {
        $srcTableDesc = $this->getTableProperties($entityName);
        if (!$srcTableDesc) {
            return $this;
        }

        $fields = $srcTableDesc['fields'];
        foreach ($fields as $id => $field) {
            if ($field['IDENTITY']) {
                unset($fields[$id]);
            }
        }
        $fields = array_keys($fields);

        if ($this->allowToProceedInWebsiteScope($fields)) {
            $this->_createWebsiteScopeItemTableData($entityName, $fields);
        }
        if ($this->allowToProceedInStoreScope($fields)) {
            $this->_createStoreScopeItemTableData($entityName, $fields);
        }

        $this->_processedTables[$entityName] = $this->_getStagingTableName($entityName);
        return $this;
    }

    /**
     * Create item table and records for flat tables
     *
     * @throws Enterprise_Staging_Exception
     *
     * @param Varien_Object $store
     * @param Mage_Core_Model_Resource_Db_Abstract $resource
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _createItemFlat(Varien_Object $store, $resource)
    {
        $sourceStoreId  = (int)$store->getMasterStoreId();
        $targetStoreId  = (int)$store->getStagingStoreId();

        $sourceTable    = $resource->setStoreId($sourceStoreId)->getMainTable();
        $targetTable    = $resource->setStoreId($targetStoreId)->getMainTable();

        $this->_copyFlatTable($sourceTable, $targetTable, true);

        return $this;
    }

    /**
     * Copy table and records process
     *
     * @throws Enterprise_Staging_Exception
     *
     * @param string $sourceTableName
     * @param string $targetTableName
     * @param bool $create              create table if does not exists flag
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _copyFlatTable($sourceTableName, $targetTableName, $create = false)
    {
        $sourceTableDesc = $this->getTableProperties($sourceTableName);
        if (!$sourceTableDesc) {
            return $this;
        }

        if ($create) {
            $this->createTable($targetTableName, $sourceTableName, true);
        }
        $this->cloneTable($sourceTableName, $targetTableName);

        return $this;
    }

    /**
     * Backup process (empty function)
     *
     * @param Varien_Object $store
     * @param Mage_Core_Model_Resource_Db_Abstract $resource
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _backupItemFlat(Varien_Object $store, $resource)
    {
        return $this;
    }

    /**
     * Merge item records for flat tables
     *
     * @throws Enterprise_Staging_Exception
     *
     * @param Varien_Object $store
     * @param Mage_Core_Model_Resource_Db_Abstract $resource
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _mergeItemFlat(Varien_Object $store, $resource)
    {
        $sourceStoreId  = (int)$store->getStagingStoreId();
        $targetStoreId  = (int)$store->getMasterStoreId();

        $sourceTable    = $resource->setStoreId($sourceStoreId)->getMainTable();
        $targetTable    = $resource->setStoreId($targetStoreId)->getMainTable();

        $this->_copyFlatTable($sourceTable, $targetTable, false);

        return $this;
    }

    /**
     * Rollback process (empty function)
     *
     * @param Varien_Object $store
     * @param Mage_Core_Model_Resource_Db_Abstract $resource
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _rollbackItemFlat(Varien_Object $store, $resource)
    {
        return $this;
    }

    /**
     * Create item table, run website and item table structure
     *
     * @param string $entityName
     * @param mixed $fields
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _createWebsiteScopeItemTableData($entityName, $fields)
    {
        $staging            = $this->getStaging();
        $readAdapter        = $this->_getReadAdapter();
        $writeAdapter       = $this->_getWriteAdapter();

        $masterWebsiteId    = (int) $staging->getMasterWebsiteId();
        $stagingWebsiteId   = (int) $staging->getStagingWebsiteId();
        if (!$masterWebsiteId || !$stagingWebsiteId) {
            return $this;
        }

        $srcTable    = $this->getTable($entityName);
        $targetTable = $this->_getStagingTableName($srcTable);

        if (in_array('website_ids', $fields)) {
            $stagingCond = $readAdapter->prepareSqlCondition('website_ids', array('finset'=>$stagingWebsiteId));
            $masterCond  = $readAdapter->prepareSqlCondition('website_ids', array('finset'=>$masterWebsiteId));
            $concatVal   = $readAdapter->getConcatSql(array('website_ids', $readAdapter->quote(','.$stagingWebsiteId)));

            $writeAdapter->update(
                $targetTable,
                array('website_ids' => $concatVal),
                array($masterCond, ' NOT '. $stagingCond)
            );
        } else {
            $selectFields = $fields;
            $_websiteFieldNameSql = 'website_id';
            foreach ($selectFields as $id => $field) {
                if ($field == 'website_id') {
                    $selectFields[$id] = new Zend_Db_Expr($stagingWebsiteId);
                    $_websiteFieldNameSql = $readAdapter->quoteIdentifier($field)
                        . $readAdapter->quoteInto(' = ?', $masterWebsiteId);
                } elseif ($field == 'scope_id') {
                    $selectFields[$id] = new Zend_Db_Expr($stagingWebsiteId);
                    $_websiteFieldNameSql = $readAdapter->quoteIdentifier('scope')
                        . $readAdapter->quoteInto(' = ?', 'websites')
                        . ' AND '.$readAdapter->quoteIdentifier($field)
                        . $readAdapter->quoteInto(' = ?', $masterWebsiteId);
                } elseif ($field == 'website_ids') {
                    $selectFields[$id]    = $concatVal;
                    $_websiteFieldNameSql = $readAdapter->prepareSqlCondition(
                        'website_ids',
                        array('finset'=>$masterWebsiteId));
                }
            }
            $srcSelectSql  = $this->_getSimpleSelect($srcTable, $selectFields, $_websiteFieldNameSql);
            $sql = $readAdapter->insertFromSelect(
                $srcSelectSql,
                $targetTable,
                $fields);

            $writeAdapter->query($sql);
        }

        return $this;
    }

    /**
     * Create item table, run website and item table structure
     *
     * @param string $entityName
     * @param mixed $fields
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _createStoreScopeItemTableData($entityName, $fields)
    {
        $staging      = $this->getStaging();
        $readAdapter  = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();
        $websites     = $staging->getMapperInstance()->getWebsiteObjects();

        if (!empty($websites)) {
            $srcTable    = $this->getTable($entityName);
            $targetTable = $this->_getStagingTableName($srcTable);
            foreach ($websites as $website) {
                $stores = $website->getStores();
                foreach ($stores as $store) {
                    $masterStoreId  = (int) $store->getMasterStoreId();
                    $stagingStoreId = (int) $store->getStagingStoreId();
                    if (!$masterStoreId || !$stagingStoreId) {
                        return $this;
                    }

                    $_storeFieldNameSql = 'store_id';

                    $selectFields = $fields;
                    foreach ($selectFields as $id => $field) {
                        if ($field == 'store_id') {
                            $selectFields[$id] = new Zend_Db_Expr($stagingStoreId);
                            $_storeFieldNameSql = $readAdapter->quoteIdentifier($field)
                                . $readAdapter->quoteInto(' = ?',$masterStoreId);
                        } elseif ($field == 'scope_id') {
                            $selectFields[$id] = new Zend_Db_Expr($stagingStoreId);
                            $_storeFieldNameSql = $readAdapter->quoteIdentifier('scope')
                                . $readAdapter->quoteInto(' = ?','stores')
                                . ' AND ' .  $readAdapter->quoteIdentifier($field)
                                . $readAdapter->quoteInto(' = ?',$masterStoreId);
                        }
                    }
                    $srcSelectSql  = $this->_getSimpleSelect($srcTable, $selectFields, $_storeFieldNameSql);
                    $sql = $readAdapter->insertFromSelect(
                        $srcSelectSql,
                        $targetTable,
                        $fields);

                    $writeAdapter->query($sql);

                }
            }
        }
        return $this;
    }

    /**
     * Prepare data for merging
     *
     * @param string $entityName
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _backupItem($entityName)
    {
        $srcTable     = $this->getTable($entityName);
        $backupPrefix = $this->getBackupTablePrefix($this->getEvent()->getId());
        $targetTable  = $this->_getWriteAdapter()->getTableName($backupPrefix . $srcTable);

        if ($srcTable != $targetTable) {
            if ($this->tableExists($srcTable)) {
                $this->_checkCreateTable($targetTable, $srcTable, $backupPrefix);
                $this->_backupItemData($srcTable, $targetTable);
            }
        }

        $this->_processedTables[$entityName] = $targetTable;
        return $this;
    }

    /**
     * Get backup table prefix
     *
     * @param string $addOnPrefix
     * @return string
     */
    public function getBackupTablePrefix($addOnPrefix = '')
    {
        $config = Mage::getSingleton('enterprise_staging/staging_config');

        $backupPrefix = $config->getTablePrefix($this->getStaging()) . $config->getStagingBackupTablePrefix();
        if (!empty($addOnPrefix)) {
            $backupPrefix .= $addOnPrefix;
        }
        return $backupPrefix . "_";;
    }

    /**
     * Process backup item
     *
     * @param string $srcTable
     * @param string $targetTable
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _backupItemData($srcTable, $targetTable)
    {
        $adapter = $this->_getWriteAdapter();
        $resourceHelper = Mage::getResourceHelper('enterprise_staging');
        $targetTableDesc = $this->getTableProperties($targetTable);
        $adapter->disableTableKeys($targetTableDesc['table_name']);

        try {
            $fields = array_keys($targetTableDesc['fields']);
            $srcSelectSql  = $this->_getSimpleSelect($srcTable, $fields);
            $sql = $adapter->insertFromSelect($srcSelectSql, $targetTable, $fields);
            $sql = $resourceHelper->wrapEnableIdentityDataInsert($sql, $targetTableDesc);
            $adapter->query($sql);
        } catch (Exception $e) {
            try {
                $adapter->enableTableKeys($targetTableDesc['table_name']);
                $resourceHelper->disableIdentityItemDataInsert($targetTableDesc);
            } catch (Exception $eTemp) {
            }
            throw $e;
        }

        $adapter->enableTableKeys($targetTableDesc['table_name']);

        return $this;
    }

    /**
     * Prepare data to merge as Website Scope and as Store scope
     *
     * @param string $entityName
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _mergeItem($entityName)
    {
        $srcTableDesc = $this->getTableProperties($entityName);
        if (!$srcTableDesc) {
            return $this;
        }

        $fields = $srcTableDesc['fields'];
        foreach ($fields as $id => $field) {
            if ($field['IDENTITY']) {
                unset($fields[$id]);
            }
        }
        $fields = array_keys($fields);

        if ($this->allowToProceedInWebsiteScope($fields)) {
            $this->_mergeTableDataInWebsiteScope($entityName, $fields);
        }
        if ($this->allowToProceedInStoreScope($fields)) {
            $this->_mergeTableDataInStoreScope($entityName, $fields);
        }

        $this->_processedTables[$entityName] = $this->_getStagingTableName($entityName);
        return $this;
    }

    /**
     * Process website scope
     *
     * @param string $entityName
     * @param mixed $fields
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _mergeTableDataInWebsiteScope($entityName, $fields)
    {
        $staging        = $this->getStaging();
        $mappedWebsites = $staging->getMapperInstance()->getWebsites();
        if (in_array('website_ids', $fields)) {
            $this->_mergeTableDataInWebsiteScopeUpdate($mappedWebsites, null, $entityName);
        } else {
            $this->_mergeTableDataInWebsiteScopeInsert($mappedWebsites, null, $entityName, $fields);
        }
        return $this;
    }

    /**
     * Insert New data on merge
     *
     * @param array $mappedWebsites
     * @param object|null $connection
     * @param string $entityName
     * @param array $fields
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _mergeTableDataInWebsiteScopeInsert($mappedWebsites, $connection, $entityName, $fields)
    {
        $readAdapter  = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();
        $srcTable     = $this->getTable($entityName);
        $targetTable  = $this->_getStagingTableName($srcTable);

        foreach ($mappedWebsites as $stagingWebsiteId => $masterWebsiteIds) {
            if (empty($stagingWebsiteId) || empty($masterWebsiteIds)) {
                continue;
            }
            $stagingWebsiteId     = intval($stagingWebsiteId);
            $_websiteFieldNameSql = 'website_id';

            foreach ($masterWebsiteIds as $masterWebsiteId) {
                if (empty($masterWebsiteId)) {
                    continue;
                }
                $masterWebsiteId = intval($masterWebsiteId);

                $selectFields = $fields;
                foreach ($selectFields as $id => $field) {
                    if ($field == 'website_id') {
                        $selectFields[$id] = new Zend_Db_Expr($masterWebsiteId);
                        $_websiteFieldNameSql = $readAdapter->quoteIdentifier($field)
                            . $readAdapter->quoteInto(' = ?', $stagingWebsiteId);
                    } elseif ($field == 'scope_id') {
                        $selectFields[$id] = new Zend_Db_Expr($masterWebsiteId);
                        $_websiteFieldNameSql = $readAdapter->quoteIdentifier('scope')
                            . $readAdapter->quoteInto(' = ?', 'websites')
                            . ' AND '.$readAdapter->quoteIdentifier($field)
                            . $readAdapter->quoteInto(' = ?', $stagingWebsiteId);
                    }
                }

                $srcSelectSql = $this->_getSimpleSelect($srcTable, $selectFields, $_websiteFieldNameSql);


                $sql = Mage::getResourceHelper('enterprise_staging')->getInsertFromSelect(
                    $srcSelectSql,
                    $targetTable,
                    $fields
                );
                $writeAdapter->query($sql);

                if ($entityName == 'catalog/product_website') {
                    $unlinkedTable = $this->getTable('enterprise_staging/staging_product_unlinked');

                    $select = $writeAdapter->select()
                        ->from($unlinkedTable, array('product_id'))
                        ->where('website_id = ?', $stagingWebsiteId);

                    $writeAdapter->delete(
                        $targetTable,
                        array(
                            'website_id = ?'    => $masterWebsiteId,
                            'product_id IN (?)' => $select
                        )
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Update data on merge
     *
     * @param array $mappedWebsites
     * @param object|null $connection
     * @param string $entityName
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _mergeTableDataInWebsiteScopeUpdate($mappedWebsites, $connection, $entityName)
    {
        $readAdapter  = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();
        $targetTable  = $this->_getStagingTableName($entityName);

        foreach ($mappedWebsites as $stagingWebsiteId => $masterWebsiteIds) {
            if (empty($stagingWebsiteId) || empty($masterWebsiteIds)) {
                continue;
            }
            $stagingWebsiteId = intval($stagingWebsiteId);

            foreach ($masterWebsiteIds as $masterWebsiteId) {
                if (empty($masterWebsiteId)) {
                    continue;
                }
                $masterWebsiteId = intval($masterWebsiteId);

                $stagingCond = $readAdapter->prepareSqlCondition('website_ids', array('finset'=>$stagingWebsiteId));
                $masterCond  = $readAdapter->prepareSqlCondition('website_ids', array('finset'=>$masterWebsiteId));
                $concatVal   = $readAdapter->getConcatSql(array(
                    'website_ids',
                    $readAdapter->quote(','.$masterWebsiteId)));

                $writeAdapter->update(
                    $targetTable,
                    array('website_ids' => $concatVal),
                    array($stagingCond, ' NOT '. $masterCond)
                );
            }
        }

        return $this;
    }

    /**
     * Process Store scope
     *
     * @param string $entityName
     * @param mixed $fields
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _mergeTableDataInStoreScope($entityName, $fields)
    {
        $staging      = $this->getStaging();
        $readAdapter  = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();
        $storesMap    = $staging->getMapperInstance()->getStores();

        if (!empty($storesMap)) {
            $srcTable    = $this->getTable($entityName);
            $targetTable = $this->_getStagingTableName($srcTable);

            foreach ($storesMap as $stagingStoreId => $masterStoreIds) {
                $stagingStoreId = intval($stagingStoreId);

                foreach ($masterStoreIds as $masterStoreId) {
                    $masterStoreId = intval($masterStoreId);

                    $this->_beforeStoreMerge($entityName, $fields, $masterStoreId, $stagingStoreId);

                    $_storeFieldNameSql = $readAdapter->quoteInto('store_id = ?', $stagingStoreId);
                    $selectFields = $fields;
                    foreach ($fields as $id => $field) {
                        if ($field == 'store_id') {
                            $selectFields[$id] = new Zend_DB_Expr($masterStoreId);
                        } elseif ($field == 'scope_id') {
                            $selectFields[$id] = new Zend_DB_Expr($masterStoreId);
                            $_storeFieldNameSql = $readAdapter->quoteIdentifier('scope')
                            . $readAdapter->quoteInto(' = ?', 'stores')
                            . ' AND '.$readAdapter->quoteIdentifier($field)
                            . $readAdapter->quoteInto(' = ?', $stagingStoreId);
                        }
                    }
                    $srcSelectSql = $this->_getSimpleSelect($srcTable, $selectFields, $_storeFieldNameSql);

                    $sql = Mage::getResourceHelper('enterprise_staging')->getInsertFromSelect(
                        $srcSelectSql,
                        $targetTable,
                        $fields
                    );
                    $writeAdapter->query($sql);

                    $this->_afterStoreMerge($entityName, $fields, $masterStoreId, $stagingStoreId);
                }
            }
        }

        return $this;
    }

    /**
     * Executed before merging staging store to master store
     *
     * @param string $entityName
     * @param mixed $fields
     * @param int $masterStoreId
     * @param int $stagingStoreId
     *
     * @return Enterprise_Staging_Model_Mysql4_Adapter_Item_Default
     */
    protected function _beforeStoreMerge($entityName, $fields, $masterStoreId, $stagingStoreId)
    {
        return $this;
    }

    /**
     * Executed after merging staging store to master store
     *
     * @param string $entityName
     * @param mixed $fields
     * @param int $stagingStoreId
     * @param int $masterStoreId
     *
     * @return Enterprise_Staging_Model_Mysql4_Adapter_Item_Default
     */
    protected function _afterStoreMerge($entityName, $fields, $masterStoreId, $stagingStoreId)
    {
        return $this;
    }

    /**
     * Prepare table data to rollback
     *
     * @param string $entityName
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _rollbackItem($entityName)
    {
        $targetTableDesc = $this->getTableProperties($entityName);
        if (!$targetTableDesc) {
            return $this;
        }

        $resourceHelper = Mage::getResourceHelper('enterprise_staging');
        $fields     = $targetTableDesc['fields'];
        $fields     = array_keys($fields);

        $backupPrefix = $this->getStaging()->getMapperInstance()->getBackupTablePrefix();
        $backupTable  = $this->_getWriteAdapter()->getTableName($backupPrefix . $this->getTable($entityName));


        $resourceHelper->beforeIdentityItemDataInsert($targetTableDesc);

        if ($this->tableExists($backupTable)) {
            if ($this->allowToProceedInWebsiteScope($fields)) {
                $this->_rollbackTableDataInWebsiteScope($backupTable, $entityName, null, $fields);
            }
            if ($this->allowToProceedInStoreScope($fields)) {
                $this->_rollbackTableDataInStoreScope($backupTable, $entityName, null, $fields);
            }
        }

        $resourceHelper->afterIdentityItemDataInsert($targetTableDesc);

        $this->_processedTables[$entityName] = $backupTable;
        return $this;
    }

    /**
     * Get all backup tables
     *
     * @param  $entityName
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _getBackupTables($entityName)
    {
        $staging = $this->getStaging();

        $backupPrefix = $staging->getMapperInstance()->getBackupTablePrefix();
        $backupTable  = $this->_getWriteAdapter()->getTableName($backupPrefix . $this->getTable($entityName));
        $staging->addBackupTable($backupTable);

        return $this;
    }

    /**
     * process website rollback
     *
     * @param string $srcTable
     * @param string $targetTable
     * @param object $connection
     * @param mixed $fields
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _rollbackTableDataInWebsiteScope($srcTable, $targetTable, $connection, $fields)
    {
        $staging        = $this->getStaging();
        $mergedWebsites = $staging->getMapperInstance()->getWebsites();

        if (!empty($mergedWebsites)) {
            $srcTable     = $this->getTable($srcTable);
            $targetTable  = $this->getTable($targetTable);

            $readAdapter  = $this->_getReadAdapter();
            $writeAdapter = $this->_getWriteAdapter();
            foreach ($mergedWebsites as $stagingWebsiteId => $masterWebsiteIds) {
                if (!empty($masterWebsiteIds)) {
                    if (in_array('website_id', $fields)) {
                        $_websiteCondition = array('website_id IN (?)' => $masterWebsiteIds);
                    } elseif (in_array('scope_id', $fields)) {
                        $_websiteCondition = array(
                            'scope = ?' => 'websites',
                            'scope_id IN (?)' => $masterWebsiteIds);
                    } elseif (in_array('website_ids', $fields)) {
                        $whereFields = array();
                        foreach ($masterWebsiteIds AS $webId) {
                            $whereFields[] = $readAdapter->prepareSqlCondition(
                                'website_ids',
                                array('finset'=>$webId));
                        }
                        $whereFields[] = $readAdapter->prepareSqlCondition(
                                'website_ids',
                                array('finset'=>$stagingWebsiteId));
                        $_websiteCondition = array(implode(" OR " , $whereFields));
                    }

                    $writeAdapter->delete($targetTable, $_websiteCondition);
                    $srcSelectSql = $this->_getSimpleSelect($srcTable, $fields, $_websiteCondition);
                    $sql = Mage::getResourceHelper('enterprise_staging')->getInsertFromSelect(
                        $srcSelectSql,
                        $targetTable,
                        $fields
                    );
                    $writeAdapter->query($sql);
                }
            }
        }

        return $this;
    }

    /**
     * process store scope rollback
     *
     * @param string $srcTable
     * @param string $targetTable
     * @param Varien_Db_Adapter_Interface $connection
     * @param mixed $fields
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _rollbackTableDataInStoreScope($srcTable, $targetTable, $connection, $fields)
    {
        $staging        = $this->getStaging();
        $mergedStores   = $staging->getMapperInstance()->getStores();
        $connection     = $this->_getWriteAdapter();
        $resourceHelper = Mage::getResourceHelper('enterprise_staging');


        if (!empty($mergedStores)) {
            $origSrcTable = $srcTable;
            $srcTable    = $this->getTable($srcTable);
            $origTargetTable = $targetTable;
            $targetTable = $this->getTable($targetTable);
            $targetDesc = $this->getTableProperties($origTargetTable);
            foreach ($mergedStores as $stagingStoreId => $masterStoreIds) {
                if (empty($stagingStoreId) || empty($masterStoreIds)) {
                    continue;
                }
                $stagingStoreId = intval($stagingStoreId);

                foreach ($masterStoreIds as $masterStoreId) {
                    if (empty($masterStoreId)) {
                        continue;
                    }
                    $masterStoreId = intval($masterStoreId);

                    $this->_beforeStoreRollback($origSrcTable, $origTargetTable, $connection, $fields, $masterStoreId,
                        $stagingStoreId);

                    $resourceHelper->beforeIdentityItemDataInsert($targetDesc);

                    if (in_array('store_id', $fields)) {
                        $whereCond = array('store_id = ?' => $masterStoreId);
                    } elseif (in_array('scope_id', $fields)) {
                        $whereCond = array(
                            $connection->quoteIdentifier('scope') .'= ?' => 'stores',
                            'scope_id = ?' => $masterStoreId
                        );
                    }
                    // 1 -- delete all rows
                    $connection->delete($targetTable, $whereCond);
                    // 2 -- insert Rows from backup
                    $select = $this->_getSimpleSelect($srcTable, $fields, $whereCond);

                    $query = $resourceHelper->getInsertFromSelect($select, $targetTable, $fields);
                    $connection->query($query);

                    $this->_afterStoreRollback($origSrcTable, $origTargetTable, $connection, $fields, $masterStoreId,
                        $stagingStoreId);
                }
            }
        }
        return $this;
    }

    /**
     * Executed before rolling back backup to master store
     *
     * @param string $srcTable
     * @param string $targetTable
     * @param object $connection
     * @param mixed $fields
     * @param int $masterStoreId
     * @param int $stagingStoreId
     *
     * @return Enterprise_Staging_Model_Mysql4_Adapter_Item_Default
     */
    protected function _beforeStoreRollback($srcTable, $targetTable, $connection, $fields, $masterStoreId,
        $stagingStoreId)
    {
        return $this;
    }

    /**
     * Executed after rolling back backup to master store
     *
     * @param string $srcTable
     * @param string $targetTable
     * @param object $connection
     * @param mixed $fields
     * @param int $masterStoreId
     * @param int $stagingStoreId
     *
     * @return Enterprise_Staging_Model_Mysql4_Adapter_Item_Default
     */
    protected function _afterStoreRollback($srcTable, $targetTable, $connection, $fields, $masterStoreId,
        $stagingStoreId)
    {
        return $this;
    }

    /**
     * Return Staging table name with all prefixes
     *
     * @param string $entityName
     * @param string $internalPrefix
     * @return string
     */
    public function getStagingTableName($entityName, $internalPrefix = '')
    {
        $table = $this->getTable($entityName);

        if (isset($this->_processedTables[$table])) {
            return $this->_processedTables[$table];
        }
        return parent::getStagingTableName($table, $internalPrefix);
    }

    /**
     * Get Staging Table Name
     *
     * @param string $entityName
     * @return string
     */
    protected function _getStagingTableName($entityName)
    {
        if ($this->_isBackendProcessing) {
            $targetTable = $this->getStagingTableName($entityName);
            if (!$this->tableExists($targetTable)) {
                $targetTable = $this->getTable($entityName);
            }
        } else {
            $targetTable = $this->getTable($entityName);
        }

        return $targetTable;
    }

    /**
     * Check is table matchs to current staging item
     *
     * @param string $table
     * @param string $code
     * @param string $model
     * @return bollean
     */
    protected function _matchTable($table, $code, $model)
    {
        if ($model == 'catalog') {
            if (strpos($table, $code) !== 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Prepares data for action and makes callback
     *
     * @param string $callbackMethod
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _processItemMethodCallback($callbackMethod)
    {
        $itemConfig = $this->getConfig();

        $this->_isBackendProcessing = ((string)$itemConfig->is_backend === '1');

        $code = (string) $itemConfig->getName();
        if ($itemConfig->model) {
            $model = (string) $itemConfig->model;
        } else {
            $model = $code;
        }

        $tables       = (array) $itemConfig->entities;
        $ignoreTables = (array) $itemConfig->ignore_tables;

        $resourceName = (string) Mage::getConfig()->getNode("global/models/{$model}/resourceModel");
        $entityTables = (array) Mage::getConfig()->getNode("global/models/{$resourceName}/entities");

        foreach ($entityTables as $entityTableConfig) {
            $table = $entityTableConfig->getName();
            if (!empty($tables)) {
                if (!array_key_exists($table, $tables)) {
                    continue;
                }
            }
            if (!empty($ignoreTables)) {
                if (array_key_exists($table, $ignoreTables)) {
                    continue;
                }
            }
            if (!$this->_matchTable($table, $code, $model)) {
                continue;
            }
            $entityName = "{$model}/{$table}";
            if (isset($this->_processedTables[$entityName])) {
                continue;
            }

            if (isset($this->_eavModels[$entityName])) {
                if ($this->_isBackendProcessing) {
                    $this->{$callbackMethod}($entityName);
                }
                foreach ($this->_eavTableTypes as $type) {
                    $_srcTable = $this->getTable(array($entityName, $type));
                    $this->{$callbackMethod}($_srcTable);
                }
                continue;
            } else if (isset($this->_flatTables[$entityName])) {
                $this->_itemFlatRun($entityName, $callbackMethod);
                continue;
            }
            $this->{$callbackMethod}($entityName);
        }

        return $this;
    }

    /**
     * Stub method
     *
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Default
     */
    protected function _getBackupTablesFlat()
    {
        return $this;
    }
}
