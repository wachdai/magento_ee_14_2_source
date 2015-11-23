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
class Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh extends Enterprise_Index_Model_Action_Abstract
{
    /**
     * Path to maximum available amount of indexes for flat indexer
     */
    const XML_NODE_MAX_INDEX_COUNT  = 'global/catalog/product/flat/max_index_count';


    /**
     * Maximum size of attributes chunk
     */
    const ATTRIBUTES_CHUNK_SIZE = 59;

    /**
     * Suffix for value field on composite attributes
     *
     * @var string
     */
    protected $_valueFieldSuffix = '_value';

    /**
     * Suffix for drop table (uses on flat table rename)
     *
     * @var string
     */
    protected $_tableDropSuffix = '_drop_indexer';

    /**
     * Contains list of created "value" tables
     *
     * @var array
     */
    protected $_valueTables = array();

    /**
     * List of product types available in installation
     *
     * @var array
     */
    protected $_productTypes = array();

    /**
     * Current store number representation
     *
     * @var int
     */
    protected $_storeId = 0;

    /**
     * Calls amount during current session
     *
     * @var int
     */
    protected static $_calls = 0;

    /**
     * Product helper, contains some useful functions for operations with attributes
     *
     * @var Enterprise_Catalog_Helper_Product
     */
    protected $_productHelper;

    /**
     * Object initialization
     *
     * @param array $argv
     */
    public function __construct(array $argv)
    {
        parent::__construct($argv);
        $this->_productHelper = Mage::helper('enterprise_catalog/product');
    }

    /**
     * Is product flat index enabled
     *
     * @return bool
     */
    protected function _isFlatIndexerEnabled()
    {
        return (bool)(int)Mage::getConfig()->getNode('default/catalog/frontend/flat_catalog_product');
    }

    /**
     * Refresh entities
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        if (!$this->_isFlatIndexerEnabled()) {
            return $this;
        }
        try {
            $this->_getCurrentVersionId();
            $this->_metadata->setInProgressStatus()->save();
            $stores = Mage::app()->getStores();
            foreach ($stores as $store) {
                $this->_reindex($store->getId());
            }
            $this->_setChangelogValid();
            Mage::dispatchEvent('catalog_product_flat_full_reindex');
        } catch (Exception $e) {
            $this->_metadata->setInvalidStatus()->save();
            throw new Enterprise_Index_Model_Action_Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $this;
    }

    /**
     * Return temporary table name by regular table name
     *
     * @param string $tableName
     *
     * @return string
     */
    protected function _getTemporaryTableName($tableName)
    {
        return sprintf('%s_tmp_indexer', $tableName);
    }

    /**
     * Create empty temporary table with given columns list
     *
     * @param string $tableName  Table name
     * @param array $columns array('columnName' => Mage_Catalog_Model_Resource_Eav_Attribute, ...)
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
     */
    protected function _createTemporaryTable($tableName, array $columns)
    {
        if (!empty($columns)) {
            $valueTableName      = $tableName . $this->_valueFieldSuffix;
            $temporaryTable      = $this->_connection->newTable($tableName);
            $valueTemporaryTable = $this->_connection->newTable($valueTableName);
            $flatColumns         = $this->_productHelper->getFlatColumns();

            $temporaryTable->addColumn(
                'entity_id',
                Varien_Db_Ddl_Table::TYPE_INTEGER
            );

            $temporaryTable->addColumn(
                'type_id',
                Varien_Db_Ddl_Table::TYPE_VARCHAR
            );

            $temporaryTable->addColumn(
                'attribute_set_id',
                Varien_Db_Ddl_Table::TYPE_INTEGER
            );

            $valueTemporaryTable->addColumn(
                'entity_id',
                Varien_Db_Ddl_Table::TYPE_INTEGER
            );

            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            foreach ($columns as $columnName => $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                if (isset($flatColumns[$attributeCode])) {
                    $column = $flatColumns[$attributeCode];
                    if (Mage::helper('core')->useDbCompatibleMode()) {
                        /* Convert old format of flat columns to new MMDB format that uses DDL types and definitions */
                        $column = Mage::getResourceHelper('core')->convertOldColumnDefinition($column);
                    }
                } else {
                    $column = $attribute->_getFlatColumnsDdlDefinition();
                    $column = $column[$attributeCode];
                }

                $temporaryTable->addColumn(
                    $columnName,
                    $column['type'],
                    isset($column['length']) ? $column['length'] : null
                );

                $columnValueName = $attributeCode . $this->_valueFieldSuffix;
                if (isset($flatColumns[$columnValueName])) {
                    $columnValue = $flatColumns[$columnValueName];
                    if (Mage::helper('core')->useDbCompatibleMode()) {
                        /* Convert old format of flat columns to new MMDB format that uses DDL types and definitions */
                        $columnValue = Mage::getResourceHelper('core')->convertOldColumnDefinition($columnValue);
                    }
                    $valueTemporaryTable->addColumn(
                        $columnValueName,
                        $columnValue['type'],
                        isset($columnValue['length']) ? $columnValue['length'] : null
                    );
                }
            }
            $this->_connection->dropTemporaryTable($tableName);
            $this->_connection->createTemporaryTable($temporaryTable);

            if (count($valueTemporaryTable->getColumns()) > 1) {
                //If we have composite attributes we should process not only id but and value of attribute
                $this->_connection->dropTemporaryTable($valueTableName);
                $this->_connection->createTemporaryTable($valueTemporaryTable);
                $this->_valueTables[$valueTableName] = $valueTableName;
            }
        }
        return $this;
    }

    /**
     * Fill temporary entity table
     *
     * @param string $tableName
     * @param array  $columns
     * @param array  $changedIds
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
     */
    protected function _fillTemporaryEntityTable($tableName, array $columns, array $changedIds = array())
    {
        if (!empty($columns)) {
            $select = $this->_connection->select();
            $temporaryEntityTable = $this->_getTemporaryTableName($tableName);
            //List of attributes that aren't listed in EAV but need to be selected too
            $idsColumns = array(
                'entity_id',
                'type_id',
                'attribute_set_id',
            );

            $columns = array_merge($idsColumns, array_keys($columns));

            $select->from(array('e' => $tableName), $columns);
            $onDuplicate = false;
            if (!empty($changedIds)) {
                $select->where(
                    $this->_connection->quoteInto('e.entity_id IN (?)', $changedIds)
                );
                $onDuplicate = true;
            }
            $sql = $select->insertFromSelect($temporaryEntityTable, $columns, $onDuplicate);
            $this->_connection->query($sql);
        }

        return $this;
    }

    /**
     * Fill temporary table by data from products EAV attributes by type
     *
     * @param string $tableName
     * @param array  $tableColumns
     * @param array  $changedIds
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
     */
    protected function _fillTemporaryTable($tableName, array $tableColumns, array $changedIds)
    {
        if (!empty($tableColumns)) {

            $columnsChunks = array_chunk($tableColumns, self::ATTRIBUTES_CHUNK_SIZE, true);
            foreach ($columnsChunks as $columnsList) {
                $select                  = $this->_connection->select();
                $selectValue             = $this->_connection->select();
                $entityTableName         = $this->_getTemporaryTableName(
                    $this->_productHelper->getTable('catalog/product')
                );
                $temporaryTableName      = $this->_getTemporaryTableName($tableName);
                $temporaryValueTableName = $temporaryTableName . $this->_valueFieldSuffix;
                $keyColumn               = array('entity_id');
                $columns                 = array_merge($keyColumn, array_keys($columnsList));
                $valueColumns            = $keyColumn;
                $flatColumns             = $this->_productHelper->getFlatColumns();
                $iterationNum            = 1;

                $select->from(
                    array('e' => $entityTableName),
                    $keyColumn
                );

                $selectValue->from(
                    array('e' => $temporaryTableName),
                    $keyColumn
                );


                /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                foreach ($columnsList as $columnName => $attribute) {
                    $countTableName = 't' . $iterationNum++;
                    $joinCondition  = sprintf(
                        'e.entity_id = %1$s.entity_id AND %1$s.attribute_id = %2$d AND %1$s.store_id = 0',
                        $countTableName,
                        $attribute->getId()
                    );

                    $select->joinLeft(
                        array($countTableName => $tableName),
                        $joinCondition,
                        array($columnName => 'value')
                    );

                    //Skip possible attributes with source model without data in DB
                    if ($attribute->getFlatUpdateSelect($this->_storeId) instanceof Varien_Db_Select) {
                        $attributeCode   = $attribute->getAttributeCode();
                        $columnValueName = $attributeCode . $this->_valueFieldSuffix;
                        if (isset($flatColumns[$columnValueName])) {
                            $valueJoinCondition = sprintf(
                                'e.%1$s = %2$s.option_id AND %2$s.store_id = 0',
                                $attributeCode,
                                $countTableName
                            );
                            $selectValue->joinLeft(
                                array($countTableName => $this->_productHelper->getTable('eav/attribute_option_value')),
                                $valueJoinCondition,
                                array($columnValueName => $countTableName . '.value')
                            );
                            $valueColumns[] = $columnValueName;
                        }
                    }
                }

                if (!empty($changedIds)) {
                    $select->where(
                        $this->_connection->quoteInto('e.entity_id IN (?)', $changedIds)
                    );
                }
                $sql = $select->insertFromSelect($temporaryTableName, $columns, true);
                $this->_connection->query($sql);
                if (count($valueColumns) > 1) {
                    if (!empty($changedIds)) {
                        $selectValue->where(
                            $this->_connection->quoteInto('e.entity_id IN (?)', $changedIds)
                        );
                    }
                    $sql = $selectValue->insertFromSelect($temporaryValueTableName, $valueColumns, true);

                    $this->_connection->query($sql);
                }

            }

        }

        return $this;
    }

    /**
     * Add primary key to table by it name
     *
     * @param string $tableName
     * @param string $columnName
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
     */
    protected function _addPrimaryKeyToTable($tableName, $columnName = 'entity_id')
    {
        $this->_connection->addIndex(
            $tableName,
            'entity_id',
            array($columnName),
            Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY
        );

        return $this;
    }

    /**
     * Prepare flat table for store
     *
     * @throws Mage_Core_Exception
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
     */
    protected function _createTemporaryFlatTable()
    {
        // Extract columns we need to have in flat table
        $columns = $this->_productHelper->getFlatColumns();

        if (Mage::helper('core')->useDbCompatibleMode()) {
            /* Convert old format of flat columns to new MMDB format that uses DDL types and definitions */
            foreach ($columns as $key => $column) {
                $columns[$key] = Mage::getResourceHelper('core')->convertOldColumnDefinition($column);
            }
        }

        // Extract indexes we need to have in flat table
        $indexesNeed  = $this->_productHelper->getFlatIndexes();

        $maxIndex = Mage::getConfig()->getNode(self::XML_NODE_MAX_INDEX_COUNT);
        if (count($indexesNeed) > $maxIndex) {
            Mage::throwException(
                Mage::helper('enterprise_catalog')->__("The Flat Catalog module has a limit of %2\$d filterable and/or sortable attributes. Currently there are %1\$d of them. Please reduce the number of filterable/sortable attributes in order to use this module", count($indexesNeed), $maxIndex)
            );
        }

        // Process indexes to create names for them in MMDB-style and reformat to common index definition
        $indexKeys = array();
        $indexProps = array_values($indexesNeed);
        $upperPrimaryKey = strtoupper(Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY);
        foreach ($indexProps as $i => $indexProp) {
            $indexName = $this->_connection->getIndexName(
                $this->_getTemporaryTableName($this->_productHelper->getFlatTableName($this->_storeId)),
                $indexProp['fields'],
                $indexProp['type']
            );
            $indexProp['type'] = strtoupper($indexProp['type']);
            if ($indexProp['type'] == $upperPrimaryKey) {
                $indexKey = $upperPrimaryKey;
            } else {
                $indexKey = $indexName;
            }

            $indexProps[$i] = array(
                'KEY_NAME'     => $indexName,
                'COLUMNS_LIST' => $indexProp['fields'],
                'INDEX_TYPE'   => strtolower($indexProp['type'])
            );
            $indexKeys[$i] = $indexKey;
        }
        $indexesNeed = array_combine($indexKeys, $indexProps); // Array with index names as keys, except for primary

        // Create table or modify existing one
        /** @var $table Varien_Db_Ddl_Table */
        $table = $this->_connection->newTable(
            $this->_getTemporaryTableName($this->_productHelper->getFlatTableName($this->_storeId))
        );
        foreach ($columns as $fieldName => $fieldProp) {
            $columnLength = isset($fieldProp['length']) ? $fieldProp['length'] : null;

            $columnDefinition = array(
                'nullable' => isset($fieldProp['nullable']) ? (bool)$fieldProp['nullable'] : false,
                'unsigned' => isset($fieldProp['unsigned']) ? (bool)$fieldProp['unsigned'] : false,
                'default'  => isset($fieldProp['default']) ? $fieldProp['default'] : false,
                'primary'  => false,
            );

            $columnComment = isset($fieldProp['comment']) ? $fieldProp['comment'] : $fieldName;

            $table->addColumn(
                $fieldName,
                $fieldProp['type'],
                $columnLength,
                $columnDefinition,
                $columnComment
            );
        }

        foreach ($indexesNeed as $indexProp) {
            $table->addIndex(
                $indexProp['KEY_NAME'], $indexProp['COLUMNS_LIST'],
                array('type' => $indexProp['INDEX_TYPE'])
            );
        }

        $tableName = $this->_productHelper->getFlatTableName($this->_storeId);
        $foreignEntityKey = $this->_connection->getForeignKeyName(
            $tableName, 'entity_id', $this->_productHelper->getTable('catalog/product'), 'entity_id'
        );
        $foreignChildKey  = $this->_connection->getForeignKeyName(
            $tableName, 'child_id', $this->_productHelper->getTable('catalog/product'), 'entity_id'
        );

        $table->addForeignKey($foreignEntityKey,
            'entity_id', $this->_productHelper->getTable('catalog/product'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

        if ($this->_productHelper->getFlatHelper()->isAddChildData()) {
            $table->addForeignKey($foreignChildKey,
                'child_id', $this->_productHelper->getTable('catalog/product'), 'entity_id',
                Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
        }

        $table->setComment("Catalog Product Flat (Store {$this->_storeId})");

        $this->_dropOldForeignKeys($tableName);
        $this->_connection->dropTable(
            $this->_getTemporaryTableName($this->_productHelper->getFlatTableName($this->_storeId))
        );
        $this->_connection->createTable($table);

        return $this;
    }

    /**
     * Drop foreign keys from current active table
     * to avoid keys name duplication during new table
     * creation
     *
     * @param string $tableName
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
     */
    protected function _dropOldForeignKeys($tableName)
    {
        $writeAdapter = $this->_connection;

        if ($writeAdapter->isTableExists($tableName)) {

            $writeAdapter->dropForeignKey(
                $tableName,
                $writeAdapter->getForeignKeyName(
                    $tableName, 'entity_id', $this->_productHelper->getTable('catalog/product'), 'entity_id'
                )
            );

            if ($this->_productHelper->getFlatHelper()->isAddChildData()) {
                $writeAdapter->dropForeignKey(
                    $tableName,
                    $writeAdapter->getForeignKeyName(
                        $tableName, 'child_id', $this->_productHelper->getTable('catalog/product'), 'entity_id'
                    )
                );
            }
        }

        return $this;
    }

    /**
     * Fill temporary flat table by data from temporary flat table parts
     *
     * @param array $tables
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
     */
    protected function _fillTemporaryFlatTable(array $tables)
    {
        $select                   = $this->_connection->select();
        $temporaryFlatTableName   = $this->_getTemporaryTableName(
            $this->_productHelper->getFlatTableName($this->_storeId)
        );
        $flatColumns              = $this->_productHelper->getFlatColumns();
        $entityTableName          = $this->_productHelper->getTable('catalog/product');
        $entityTemporaryTableName = $this->_getTemporaryTableName($entityTableName);
        $columnsList              = array_keys($tables[$entityTableName]);
        $websiteId                = (int)Mage::app()->getStore($this->_storeId)->getWebsite()->getId();

        unset($tables[$entityTableName]);

        $allColumns = array_merge(
            array(
                'entity_id',
                'type_id',
                'attribute_set_id',
            ),
            $columnsList
        );

        /* @var $status Mage_Eav_Model_Entity_Attribute */
        $status = $this->_productHelper->getAttribute('status');
        $statusTable = $this->_getTemporaryTableName($status->getBackendTable());
        $statusConditions = array('e.entity_id = dstatus.entity_id',
            'dstatus.entity_type_id = ' . (int)$status->getEntityTypeId(), 'dstatus.store_id = ' . (int)$this->_storeId,
            'dstatus.attribute_id = ' . (int)$status->getId());

        $select->from(
            array('e' => $entityTemporaryTableName),
            $allColumns
        )->joinInner(
            array('wp' => $this->_productHelper->getTable('catalog/product_website')),
            'wp.product_id = e.entity_id AND wp.website_id = ' . $websiteId,
            array()
        )->joinLeft(
            array('dstatus' => $status->getBackend()->getTable()),
            implode(' AND ', $statusConditions),
            array()
        );

        foreach ($tables as $tableName => $columns) {
            $columnValueNames        = array();
            $temporaryTableName      = $this->_getTemporaryTableName($tableName);
            $temporaryValueTableName = $temporaryTableName . $this->_valueFieldSuffix;
            $columnsNames            = array_keys($columns);

            $select->joinLeft(
                $temporaryTableName,
                'e.entity_id = ' . $temporaryTableName. '.entity_id',
                $columnsNames
            );
            $allColumns = array_merge($allColumns, $columnsNames);

            foreach ($columnsNames as $name ) {
                $columnValueName = $name . $this->_valueFieldSuffix;
                if (isset($flatColumns[$columnValueName])) {
                    $columnValueNames[] = $columnValueName;
                }
            }
            if (!empty($columnValueNames)) {
                $select->joinLeft(
                    $temporaryValueTableName,
                    'e.entity_id = ' . $temporaryValueTableName. '.entity_id',
                    $columnValueNames
                );
                $allColumns = array_merge($allColumns, $columnValueNames);
            }
        }
        $sql = $select->insertFromSelect($temporaryFlatTableName, $allColumns, false);
        $this->_connection->query($sql);

        return $this;
    }

    /**
     * Apply diff. between 0 store and current store to temporary flat table
     *
     * @param array $tables
     * @param array $changedIds
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
     */
    protected function _updateTemporaryTableByStoreValues(array $tables, array $changedIds)
    {
        $flatColumns = $this->_productHelper->getFlatColumns();
        $temporaryFlatTableName = $this->_getTemporaryTableName(
            $this->_productHelper->getFlatTableName($this->_storeId)
        );

        foreach ($tables as $tableName => $columns) {
            foreach ($columns as $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                if ($attribute->getBackend()->getType() != 'static') {
                    $joinCondition = 't.entity_id = e.entity_id'
                        . ' AND t.entity_type_id = ' . $attribute->getEntityTypeId()
                        . ' AND t.attribute_id=' . $attribute->getId()
                        . ' AND t.store_id = ' . $this->_storeId
                        . ' AND t.value IS NOT NULL';

                    $select = $this->_connection->select()
                        ->joinInner(
                            array('t' => $tableName),
                            $joinCondition,
                            array($attributeCode => 't.value')
                        );
                    if (!empty($changedIds)) {
                        $select->where(
                            $this->_connection->quoteInto('e.entity_id IN (?)', $changedIds)
                        );
                    }
                    $sql = $select->crossUpdateFromSelect(array('e' => $temporaryFlatTableName));
                    $this->_connection->query($sql);
                }

                //Update not simple attributes (eg. dropdown)
                if (isset($flatColumns[$attributeCode . $this->_valueFieldSuffix])) {
                    $select = $this->_connection->select()
                        ->joinInner(
                        array('t' => $this->_productHelper->getTable('eav/attribute_option_value')),
                        't.option_id = e.' . $attributeCode . ' AND t.store_id=' . $this->_storeId,
                        array($attributeCode . $this->_valueFieldSuffix => 't.value')
                    );
                    if (!empty($changedIds)) {
                        $select->where(
                            $this->_connection->quoteInto('e.entity_id IN (?)', $changedIds)
                        );
                    }
                    $sql = $select->crossUpdateFromSelect(array('e' => $temporaryFlatTableName));
                    $this->_connection->query($sql);
                }
            }
        }

        return $this;
    }

    /**
     * Swap flat product table and temporary flat table and drop old one
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
     */
    protected function _moveDataToFlatTable()
    {
        $flatTable              = $this->_productHelper->getFlatTableName($this->_storeId);
        $flatDropName           = $flatTable . $this->_tableDropSuffix;
        $temporaryFlatTableName = $this->_getTemporaryTableName(
            $this->_productHelper->getFlatTableName($this->_storeId)
        );
        $renameTables           = array();

        if ($this->_connection->isTableExists($flatTable)) {
            $renameTables[] = array(
                'oldName' => $flatTable,
                'newName' => $flatDropName,
            );
        }
        $renameTables[] = array(
            'oldName' => $temporaryFlatTableName,
            'newName' => $flatTable,
        );

        $this->_connection->dropTable($flatDropName);
        $this->_connection->renameTablesBatch($renameTables);
        $this->_connection->dropTable($flatDropName);

        return $this;
    }

    /**
     * Drop temporary tables created by reindex process
     *
     * @param array $tablesList
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
     */
    protected function _cleanOnFailure(array $tablesList)
    {
        foreach ($tablesList as $table => $columns) {
            $this->_connection->dropTemporaryTable($table);
        }
        $tableName = $this->_getTemporaryTableName($this->_productHelper->getFlatTableName($this->_storeId));
        $this->_connection->dropTable($tableName);
        return $this;
    }

    /**
     * Rebuild catalog flat index from scratch
     *
     * @param int $storeId
     * @param array $changedIds
     * @param bool $resetFlag
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
     * @throws Exception
     */
    protected function _reindex($storeId, array $changedIds = array(), $resetFlag = false)
    {
        $this->_storeId     = $storeId;
        $entityTableName    = $this->_productHelper->getTable('catalog/product');
        $attributes         = $this->_productHelper->getAttributes();
        $eavAttributes      = $this->_productHelper->getTablesStructure($attributes);
        $flag               = Mage::helper('catalog/product_flat')->getFlag();
        $entityTableColumns = $eavAttributes[$entityTableName];

        try {
            //We should prepare temp. tables only for first call of reindex all
            if (!self::$_calls && !$resetFlag) {
                $temporaryEavAttributes = $eavAttributes;

                //add status global value to the base table
                /* @var $status Mage_Eav_Model_Entity_Attribute */
                $status = $this->_productHelper->getAttribute('status');
                $temporaryEavAttributes[$status->getBackendTable()]['status'] = $status;
                //Create list of temporary tables based on available attributes attributes
                foreach ($temporaryEavAttributes as $tableName => $columns) {
                    $this->_createTemporaryTable($this->_getTemporaryTableName($tableName), $columns);
                }

                //Fill "base" table which contains all available products
                $this->_fillTemporaryEntityTable($entityTableName, $entityTableColumns, $changedIds);

                //Add primary key to "base" temporary table for increase speed of joins in future
                $this->_addPrimaryKeyToTable($this->_getTemporaryTableName($entityTableName));
                unset($temporaryEavAttributes[$entityTableName]);

                foreach ($temporaryEavAttributes as $tableName => $columns) {
                    $temporaryTableName = $this->_getTemporaryTableName($tableName);

                    //Add primary key to temporary table for increase speed of joins in future
                    $this->_addPrimaryKeyToTable($temporaryTableName);

                    //Create temporary table for composite attributes
                    if (isset($this->_valueTables[$temporaryTableName . $this->_valueFieldSuffix])) {
                        $this->_addPrimaryKeyToTable($temporaryTableName . $this->_valueFieldSuffix);
                    }

                    //Fill temporary tables with attributes grouped by it type
                    $this->_fillTemporaryTable($tableName, $columns, $changedIds);
                }
            }
            //Create and fill flat temporary table
            $this->_createTemporaryFlatTable();
            $this->_fillTemporaryFlatTable($eavAttributes);
            //Update zero based attributes by values from current store
            $this->_updateTemporaryTableByStoreValues($eavAttributes, $changedIds);

            //Rename current flat table to "drop", rename temporary flat to flat and drop "drop" table
            $this->_moveDataToFlatTable();
            $this->_updateEventAttributes($this->_storeId);
            $this->_updateRelationProducts($this->_storeId, $changedIds);
            $this->_cleanRelationProducts($this->_storeId);
            self::$_calls++;
            $flag->setIsBuilt(true)->setStoreBuilt($this->_storeId, true)->save();
        } catch (Exception $e) {
            $flag->setIsBuilt(false)->setStoreBuilt($this->_storeId, false)->save();
            $this->_cleanOnFailure($eavAttributes);
            throw $e;
        }

        return $this;
    }

    /**
     * Update events observer attributes
     *
     * @param int $storeId
     */
    protected function _updateEventAttributes($storeId = null)
    {
        Mage::dispatchEvent(
            'enterprise_catalog_product_flat_rebuild',
            array(
                'store_id' => $storeId,
                'table'    => $this->_productHelper->getFlatTableName($storeId)
            )
        );
    }

    /**
     * Retrieve Product Type Instances
     * as key - type code, value - instance model
     *
     * @return array
     */
    protected function _getProductTypeInstances()
    {
        if ($this->_productTypes === null) {
            $this->_productTypes = array();
            $productEmulator     = new Varien_Object();

            foreach (array_keys(Mage_Catalog_Model_Product_Type::getTypes()) as $typeId) {
                $productEmulator->setTypeId($typeId);
                $this->_productTypes[$typeId] = Mage::getSingleton('catalog/product_type')
                    ->factory($productEmulator);
            }
        }
        return $this->_productTypes;
    }

    /**
     * Update relation products
     *
     * @param int $storeId
     * @param int|array $productIds Update child product(s) only
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
     */
    protected function _updateRelationProducts($storeId, $productIds = null)
    {
        if (!$this->_productHelper->getFlatHelper()->isAddChildData() || !$this->_isFlatTableExists($storeId)) {
            return $this;
        }

        foreach ($this->_getProductTypeInstances() as $typeInstance) {
            if (!$typeInstance->isComposite()) {
                continue;
            }
            $relation = $typeInstance->getRelationInfo();
            if ($relation
                && $relation->getTable()
                && $relation->getParentFieldName()
                && $relation->getChildFieldName()
            ) {
                $columns   = $this->_productHelper->getFlatColumns();
                $fieldList = array_keys($columns);
                unset($columns['entity_id']);
                unset($columns['child_id']);
                unset($columns['is_child']);

                $select = $this->_connection->select()
                    ->from(
                        array('t' => $this->_productHelper->getTable($relation->getTable())),
                        array($relation->getParentFieldName(), $relation->getChildFieldName(), new Zend_Db_Expr('1')))
                    ->join(
                        array('e' => $this->_productHelper->getFlatTableName($storeId)),
                        "e.entity_id = t.{$relation->getChildFieldName()}",
                        array_keys($columns)
                    );
                if ($relation->getWhere() !== null) {
                    $select->where($relation->getWhere());
                }
                if ($productIds !== null) {
                    $cond = array(
                        $this->_connection->quoteInto("{$relation->getChildFieldName()} IN(?)", $productIds),
                        $this->_connection->quoteInto("{$relation->getParentFieldName()} IN(?)", $productIds)
                    );

                    $select->where(implode(' OR ', $cond));
                }
                $sql = $select->insertFromSelect($this->_productHelper->getFlatTableName($storeId), $fieldList);
                $this->_connection->query($sql);
            }
        }

        return $this;
    }

    /**
     * Clean unused relation products
     *
     * @param int $storeId
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
     */
    protected function _cleanRelationProducts($storeId)
    {
        if (!$this->_productHelper->getFlatHelper()->isAddChildData()) {
            return $this;
        }

        foreach ($this->_getProductTypeInstances() as $typeInstance) {
            if (!$typeInstance->isComposite()) {
                continue;
            }
            $relation = $typeInstance->getRelationInfo();
            if ($relation
                && $relation->getTable()
                && $relation->getParentFieldName()
                && $relation->getChildFieldName()
            ) {
                $select = $this->_connection->select()
                    ->distinct(true)
                    ->from(
                        $this->_productHelper->getTable($relation->getTable()),
                        "{$relation->getParentFieldName()}"
                    );
                $joinLeftCond = array(
                    "e.entity_id = t.{$relation->getParentFieldName()}",
                    "e.child_id = t.{$relation->getChildFieldName()}"
                );
                if ($relation->getWhere() !== null) {
                    $select->where($relation->getWhere());
                    $joinLeftCond[] = $relation->getWhere();
                }

                $entitySelect = new Zend_Db_Expr($select->__toString());

                $select = $this->_connection->select()
                    ->from(array('e' => $this->_productHelper->getFlatTableName($storeId)), null)
                    ->joinLeft(
                        array('t' => $this->_productHelper->getTable($relation->getTable())),
                        implode(' AND ', $joinLeftCond),
                        array()
                    )
                    ->where('e.is_child = ?', 1)
                    ->where('e.entity_id IN(?)', $entitySelect)
                    ->where("t.{$relation->getChildFieldName()} IS NULL");

                $sql = $select->deleteFromSelect('e');
                $this->_connection->query($sql);
            }
        }

        return $this;
    }
}
