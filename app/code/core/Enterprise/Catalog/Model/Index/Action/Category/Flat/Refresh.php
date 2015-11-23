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
 * Full refresh category flat index
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @property Enterprise_Index_Model_Metadata $_metadata
 */
class Enterprise_Catalog_Model_Index_Action_Category_Flat_Refresh extends Enterprise_Index_Model_Action_Abstract
{
    /**
     * Suffix for table to show it is temporary
     */
    const TEMPORARY_TABLE_SUFFIX = '_tmp';

    /**
     * Suffix for table to show it is old
     */
    const OLD_TABLE_SUFFIX = '_old';

    /**
     * Loaded
     *
     * @var boolean
     */
    protected $_loaded = false;

    /**
     * Nodes
     *
     * @var array
     */
    protected $_nodes = array();

    /**
     * Columns
     *
     * @var array
     */
    protected $_columns = null;

    /**
     * Columns sql
     *
     * @var array
     */
    protected $_columnsSql = null;

    /**
     * Attribute codes
     *
     * @var array
     */
    protected $_attributeCodes = null;

    /**
     * Inactive categories ids
     *
     * @var array
     */
    protected $_inactiveCategoryIds = null;

    /**
     * Store flag which defines if Catalog Category Flat Data has been initialized
     *
     * @var bool|null
     */
    protected $_isBuilt = null;

    /**
     * Whether table changes are allowed
     *
     * @var bool
     */
    protected $_allowTableChanges = true;

    /**
     * Product helper, contains some useful functions for operations with attributes
     *
     * @var Enterprise_Catalog_Helper_Product
     */
    protected $_productHelper;

    /**
     * Constructor
     */
    public function __construct(array $argv)
    {
        parent::__construct($argv);
        $this->_productHelper = Mage::helper('enterprise_catalog/product');
        $this->_columns = array_merge($this->_getStaticColumns(), $this->_getEavColumns());
    }

    /**
     * Refresh all entities
     *
     * @return Enterprise_Catalog_Model_Index_Action_Category_Flat_Refresh
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        if (!$this->_isFlatIndexerEnabled()) {
            return $this;
        }
        $this->_validate();
        try {
            $this->_getCurrentVersionId();
            $this->_metadata->setInProgressStatus()->save();
            $this->_reindexAll();
            $this->_setChangelogValid();
            Mage::dispatchEvent('catalog_category_flat_full_reindex');
        } catch (Exception $e) {
            $this->_metadata->setInvalidStatus()->save();
            throw new Enterprise_Index_Model_Action_Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $this;
    }

    /**
     * Is category flat index enabled
     *
     * @return bool
     */
    protected function _isFlatIndexerEnabled()
    {
        return (bool)(int) Mage::getConfig()->getNode('default/catalog/frontend/flat_catalog_category');
    }

    /**
     * Return name of table for given $storeId.
     *
     * @param integer $storeId
     * @return string
     */
    protected function _getMainStoreTable($storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        if (is_string($storeId)) {
            $storeId = intval($storeId);
        }

        $suffix = sprintf('store_%d', $storeId);
        $table = $this->_productHelper->getTable(array('catalog/category_flat', $suffix));

        return $table;
    }

    /**
     * Retrieve inactive categories ids
     *
     * @return Enterprise_Catalog_Model_Index_Action_Category_Flat_Refresh
     */
    protected function _initInactiveCategoryIds()
    {
        $this->_inactiveCategoryIds = array();
        Mage::dispatchEvent(
            'enterprise_index_catalog_category_tree_init_inactive_category_ids',
            array('tree' => $this)
        );
        return $this;
    }

    /**
     * Retreive inactive categories ids
     *
     * @return array
     */
    protected function _getInactiveCategoryIds()
    {
        if (!is_array($this->_inactiveCategoryIds)) {
            $this->_initInactiveCategoryIds();
        }

        return $this->_inactiveCategoryIds;
    }

    /**
     * Load nodes by parent id
     *
     * @param Mage_Catalog_Model_Category|int $parentNode
     * @param integer $recursionLevel
     * @param integer $storeId
     * @return Enterprise_Catalog_Model_Index_Action_Category_Flat_Refresh
     */
    protected function _loadNodes($parentNode = null, $recursionLevel = 0, $storeId = 0)
    {
        $_conn = $this->_connection;
        $startLevel = 1;
        $parentPath = '';
        if ($parentNode instanceof Mage_Catalog_Model_Category) {
            $parentPath = $parentNode->getPath();
            $startLevel = $parentNode->getLevel();
        } elseif (is_numeric($parentNode)) {
            $selectParent = $_conn->select()
                ->from($this->_getMainStoreTable($storeId))
                ->where('entity_id = ?', $parentNode)
                ->where('store_id = ?', $storeId);
            $parentNode = $_conn->fetchRow($selectParent);
            if ($parentNode) {
                $parentPath = $parentNode['path'];
                $startLevel = $parentNode['level'];
            }
        }
        $select = $_conn->select()
            ->from(
                array('main_table' => $this->_getMainStoreTable($storeId)),
                array('entity_id',
                    new Zend_Db_Expr('main_table.' . $_conn->quoteIdentifier('name')),
                    new Zend_Db_Expr('main_table.' . $_conn->quoteIdentifier('path')),
                    'is_active',
                    'is_anchor')
            )
            ->joinLeft(
                array(
                    'url_rewrite'=>$this->_productHelper->getTable('core/url_rewrite')),
                    'url_rewrite.category_id=main_table.entity_id AND url_rewrite.is_system=1 AND ' .
                    $_conn->quoteInto('url_rewrite.product_id IS NULL AND url_rewrite.store_id=? AND ',
                $storeId) .
                $_conn->prepareSqlCondition('url_rewrite.id_path', array('like' => 'category/%')),
                array('request_path' => 'url_rewrite.request_path')
            )
            ->where('main_table.is_active = ?', '1')
            ->where('main_table.include_in_menu = ?', '1')
            ->order('main_table.position');

        if ($parentPath) {
            $select->where($_conn->quoteInto("main_table.path like ?", "$parentPath/%"));
        }
        if ($recursionLevel != 0) {
            $levelField = $_conn->quoteIdentifier('level');
            $select->where($levelField . ' <= ?', $startLevel + $recursionLevel);
        }

        $inactiveCategories = $this->_getInactiveCategoryIds();

        if (!empty($inactiveCategories)) {
            $select->where('main_table.entity_id NOT IN (?)', $inactiveCategories);
        }

        // Allow extensions to modify select (e.g. add custom category attributes to select)
        Mage::dispatchEvent('enterprise_catalog_category_flat_loadnodes_before', array('select' => $select));

        $arrNodes = $_conn->fetchAll($select);
        $nodes = array();
        foreach ($arrNodes as $node) {
            $node['id'] = $node['entity_id'];
            $nodes[$node['id']] = Mage::getModel('catalog/category')->setData($node);
        }

        return $nodes;
    }

    /**
     * Rebuild flat data from eav
     *
     * @return Enterprise_Catalog_Model_Index_Action_Category_Flat_Refresh
     */
    protected function _rebuild()
    {
        $stores = Mage::app()->getStores();
        $rootId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
        $categories = array();
        $categoriesIds = array();
        /* @var $store Mage_Core_Model_Store */
        foreach ($stores as $store) {
            if ($this->_allowTableChanges) {
                $this->_createTable($store->getId());
            }

            if (!isset($categories[$store->getRootCategoryId()])) {
                $select = $this->_connection->select()
                    ->from($this->_productHelper->getTable('catalog/category'))
                    ->where('path = ?', (string)$rootId)
                    ->orWhere('path = ?', "{$rootId}/{$store->getRootCategoryId()}")
                    ->orWhere('path LIKE ?', "{$rootId}/{$store->getRootCategoryId()}/%");
                $categories[$store->getRootCategoryId()] = $this->_connection->fetchAll($select);
                $categoriesIds[$store->getRootCategoryId()] = array();
                foreach ($categories[$store->getRootCategoryId()] as $category) {
                    $categoriesIds[$store->getRootCategoryId()][] = $category['entity_id'];
                }
            }

            $categoriesIdsChunks = array_chunk(
                $categoriesIds[$store->getRootCategoryId()],
                Mage::helper('enterprise_index')->getBatchSize()
            );
            foreach ($categoriesIdsChunks as $categoriesIdsChunk) {
                $attributesData = $this->_getAttributeValues($categoriesIdsChunk, $store->getId());
                $data = array();
                foreach ($categories[$store->getRootCategoryId()] as $category) {
                    if (!isset($attributesData[$category['entity_id']])) {
                        continue;
                    }
                    if (!empty($category['entity_id'])) {
                        $category['store_id'] = $store->getId();
                        $data[] = $this->_prepareValuesToInsert(
                            array_merge($category, $attributesData[$category['entity_id']])
                        );
                    }
                }
                $this->_connection->insertMultiple(
                    $this->_addTemporaryTableSuffix($this->_getMainStoreTable($store->getId())),
                    $data
                );
            }
        }
        return $this;
    }

    /**
     * Prepare array of column and columnValue pairs
     *
     * @param array $data
     * @return array
     */
    protected function _prepareValuesToInsert($data)
    {
        $values = array();
        foreach (array_keys($this->_columns) as $column) {
            if (isset($data[$column])) {
                $values[$column] = $data[$column];
            } else {
                $values[$column] = null;
            }
        }
        return $values;
    }

    /**
     * Add suffix to table name to show it is
     * temporary
     *
     * @param string $tableName
     * @return string
     */
    protected function _addTemporaryTableSuffix($tableName)
    {
        return $tableName . self::TEMPORARY_TABLE_SUFFIX;
    }

    /**
     * Add suffix to table name to show it is old
     *
     * @param string $tableName
     * @return string
     */
    protected function _addOldTableSuffix($tableName)
    {
        return $tableName . self::OLD_TABLE_SUFFIX;
    }

    /**
     * Drop foreign keys from current active table
     * to avoid keys name duplication during new table
     * creation
     *
     * @param string $tableName
     * @return Enterprise_Catalog_Model_Index_Action_Category_Flat_Refresh
     */
    protected function _dropOldForeignKeys($tableName)
    {
        $writeAdapter = $this->_connection;

        if ($writeAdapter->isTableExists($tableName)) {

            $writeAdapter->dropForeignKey(
                $tableName,
                $writeAdapter->getForeignKeyName(
                    $tableName, 'entity_id', $this->_productHelper->getTable('catalog/category'), 'entity_id'
                )
            );
        }

        return $this;
    }

    /**
     * Creating table and adding attributes as fields to table
     *
     * @param array|integer $store
     * @return Enterprise_Catalog_Model_Index_Action_Category_Flat_Refresh
     */
    protected function _createTable($store)
    {
        $temporaryTable = $this->_addTemporaryTableSuffix($this->_getMainStoreTable($store));
        $activeTable    = $this->_getMainStoreTable($store);
        $table          = $this->_getFlatTableStructure($temporaryTable, $activeTable);
        $this->_connection->dropTable($temporaryTable);
        $this->_dropOldForeignKeys($activeTable);
        $this->_connection->createTable($table);

        return $this;
    }

    /**
     * Return structure for flat catalof table
     *
     * @param string      $tableName
     * @param string|null $constraintsPrefix
     *
     * @return Varien_Db_Ddl_Table
     */
    protected function _getFlatTableStructure($tableName, $constraintsPrefix = null)
    {
        if (!$constraintsPrefix) {
            $constraintsPrefix = $tableName;
        }
        $table = $this->_connection
            ->newTable($tableName)
            ->setComment(sprintf('Can\'t create table (%s)', $tableName));

        //Adding columns
        if ($this->_columnsSql === null) {

            $this->_columns = array_merge($this->_getStaticColumns(), $this->_getEavColumns());
            foreach ($this->_columns as $fieldName => $fieldProp) {
                $default = $fieldProp['default'];
                if ($fieldProp['type'][0] == Varien_Db_Ddl_Table::TYPE_TIMESTAMP
                    && $default == 'CURRENT_TIMESTAMP') {
                    $default = Varien_Db_Ddl_Table::TIMESTAMP_INIT;
                }
                $table->addColumn(
                    $fieldName,
                    $fieldProp['type'][0],
                    $fieldProp['type'][1],
                    array(
                        'nullable' => $fieldProp['nullable'],
                        'unsigned' => $fieldProp['unsigned'],
                        'default'  => $default,
                        'primary'  => isset($fieldProp['primary']) ? $fieldProp['primary'] : false,
                    ),
                    ($fieldProp['comment'] != '') ? $fieldProp['comment'] : ucwords(str_replace('_', ' ', $fieldName))
                );
            }
        }

        // Adding indexes
        $table->addIndex(
            $this->_connection->getIndexName($tableName, array('entity_id')),
            array('entity_id'),
            array('type' => 'primary')
        );
        $table->addIndex(
            $this->_connection->getIndexName($tableName, array('store_id')),
            array('store_id'),
            array('type' => 'index')
        );
        $table->addIndex(
            $this->_connection->getIndexName($tableName, array('path')), array('path'), array('type' => 'index')
        );
        $table->addIndex(
            $this->_connection->getIndexName($tableName, array('level')), array('level'), array('type' => 'index')
        );

        // Adding foreign keys
        $table->addForeignKey(
            $this->_connection->getForeignKeyName(
                $constraintsPrefix, 'entity_id', $this->_productHelper->getTable('catalog/category'), 'entity_id'
            ),
            'entity_id', $this->_productHelper->getTable('catalog/category'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        );

        return $table;
    }

    /**
     * Return array of static columns
     *
     * @return array
     */
    protected function _getStaticColumns()
    {
        /** @var $helper Mage_Catalog_Model_Resource_Helper */
        $helper        = Mage::getResourceHelper('catalog');
        $columns       = array();
        $columnsToSkip = array('entity_type_id', 'attribute_set_id');
        $describe      = $this->_connection->describeTable($this->_productHelper->getTable('catalog/category'));
        $options       = null;

        foreach ($describe as $column) {
            if (in_array($column['COLUMN_NAME'], $columnsToSkip)) {
                continue;
            }
            $ddlType           = $helper->getDdlTypeByColumnType($column['DATA_TYPE']);
            $column['DEFAULT'] = trim($column['DEFAULT'], "' ");
            switch ($ddlType) {
                case Varien_Db_Ddl_Table::TYPE_SMALLINT:
                case Varien_Db_Ddl_Table::TYPE_INTEGER:
                case Varien_Db_Ddl_Table::TYPE_BIGINT:
                case Varien_Db_Ddl_Table::TYPE_DECIMAL:
                    $ddlColumn = $this->_prepareNumberColumnDdl($column, $ddlType, $options);
                    break;
                case Varien_Db_Ddl_Table::TYPE_TEXT:
                    $ddlColumn = $this->_prepareColumnDdl($column, $ddlType, $column['LENGTH']);
                    break;
                default:
                    $ddlColumn = $this->_prepareColumnDdl($column, $ddlType);
            }
            $columns[$column['COLUMN_NAME']] = $ddlColumn;
        }
        $columns['store_id'] = array(
            'type' => array(Varien_Db_Ddl_Table::TYPE_SMALLINT, 5),
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
            'comment' => 'Store Id'
        );
        return $columns;
    }

    /**
     * Sanitize number columns for future conversion into database types
     *
     * @param array  $column
     * @param string $ddlType
     *
     * @return array
     */
    protected function _prepareNumberColumnDdl(array $column, $ddlType)
    {
        if (!isset($column['DEFAULT']) || $column['DEFAULT'] === '') {
            $column['DEFAULT'] = null;
        }

        $typeOptions = null;
        if (isset($column['SCALE']) && $column['SCALE'] > 0) {
            $typeOptions = $column['PRECISION'] . ',' . $column['SCALE'];
            $ddlType = Varien_Db_Ddl_Table::TYPE_DECIMAL;
        }
        return $this->_prepareColumnDdl($column, $ddlType, $typeOptions, (bool)$column['UNSIGNED']);
    }

    /**
     * Format attribute declaration into database types
     *
     * @param array  $column
     * @param string $ddlType
     * @param bool|null   $typeOptions
     * @param string|null $isUnsigned
     *
     * @return array
     */
    protected function _prepareColumnDdl(array $column, $ddlType, $typeOptions = null, $isUnsigned = null)
    {
        return array(
            'type' => array($ddlType, $typeOptions),
            'unsigned' => $isUnsigned,
            'nullable' => $column['NULLABLE'],
            'default' => ($column['DEFAULT'] === null ? false : $column['DEFAULT']),
            'comment' => $column['COLUMN_NAME']
        );
    }

    /**
     * Return array of eav columns, skip attribute with static type
     *
     * @return array
     */
    protected function _getEavColumns()
    {
        $columns = array();
        foreach ($this->_getAttributes() as $attribute) {
            if ($attribute['backend_type'] == 'static') {
                continue;
            }
            $columns[$attribute['attribute_code']] = array();
            switch ($attribute['backend_type']) {
                case 'varchar':
                    $columns[$attribute['attribute_code']] = array(
                        'type' => array(Varien_Db_Ddl_Table::TYPE_TEXT, 255),
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label']
                    );
                    break;
                case 'int':
                    $columns[$attribute['attribute_code']] = array(
                        'type' => array(Varien_Db_Ddl_Table::TYPE_INTEGER, null),
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label']
                    );
                    break;
                case 'text':
                    $columns[$attribute['attribute_code']] = array(
                        'type' => array(Varien_Db_Ddl_Table::TYPE_TEXT, '64k'),
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label']
                    );
                    break;
                case 'datetime':
                    $columns[$attribute['attribute_code']] = array(
                        'type' => array(Varien_Db_Ddl_Table::TYPE_DATETIME, null),
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label']
                    );
                    break;
                case 'decimal':
                    $columns[$attribute['attribute_code']] = array(
                        'type' => array(Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4'),
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label']
                    );
                    break;
            }
        }
        return $columns;
    }

    /**
     * Return array of attribute codes for entity type 'catalog_category'
     *
     * @return array
     */
    protected function _getAttributes()
    {
        if ($this->_attributeCodes === null) {
            $select = $this->_connection->select()
                ->from($this->_productHelper->getTable('eav/entity_type'), array())
                ->join(
                    $this->_productHelper->getTable('eav/attribute'),
                    $this->_productHelper->getTable('eav/attribute')
                        . '.entity_type_id = ' . $this->_productHelper->getTable('eav/entity_type') . '.entity_type_id',
                    $this->_productHelper->getTable('eav/attribute').'.*'
                )
                ->where(
                    $this->_productHelper->getTable('eav/entity_type') . '.entity_type_code = ?',
                    Mage_Catalog_Model_Category::ENTITY
                );
            $this->_attributeCodes = array();
            foreach ($this->_connection->fetchAll($select) as $attribute) {
                $this->_attributeCodes[$attribute['attribute_id']] = $attribute;
            }
        }
        return $this->_attributeCodes;
    }

    /**
     * Return attribute values for given entities and store
     *
     * @param array $entityIds
     * @param integer $storeId
     * @return array
     */
    protected function _getAttributeValues($entityIds, $storeId)
    {
        if (!is_array($entityIds)) {
            $entityIds = array($entityIds);
        }
        $values = array();

        foreach ($entityIds as $entityId) {
            $values[$entityId] = array();
        }
        $attributes = $this->_getAttributes();
        $attributesType = array(
            'varchar',
            'int',
            'decimal',
            'text',
            'datetime'
        );
        foreach ($attributesType as $type) {
            foreach ($this->_getAttributeTypeValues($type, $entityIds, $storeId) as $row) {
                if (isset($row['entity_id']) && isset($row['attribute_id'])) {
                    $attributeId   = $row['attribute_id'];
                    if (isset($attributes[$attributeId])) {
                        $attributeCode = $attributes[$attributeId]['attribute_code'];
                        $values[$row['entity_id']][$attributeCode] = $row['value'];
                    }
                }
            }
        }
        return $values;
    }

    /**
     * Return attribute values for given entities and store of specific attribute type
     *
     * @param string $type
     * @param array $entityIds
     * @param integer $storeId
     * @return array
     */
    protected function _getAttributeTypeValues($type, $entityIds, $storeId)
    {
        $select = $this->_connection->select()
            ->from(
                array('def' => $this->_productHelper->getTable(array('catalog/category', $type))),
                array('entity_id', 'attribute_id')
            )
            ->joinLeft(
                array('store' => $this->_productHelper->getTable(array('catalog/category', $type))),
                'store.entity_id = def.entity_id AND store.attribute_id = def.attribute_id '
                    . 'AND store.store_id = ' . $storeId,
                array('value' => $this->_connection->getCheckSql(
                    'store.value_id > 0',
                    $this->_connection->quoteIdentifier('store.value'),
                    $this->_connection->quoteIdentifier('def.value')
                ))
            )
            ->where('def.entity_id IN (?)', $entityIds)
            ->where('def.store_id IN (?)', array(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, $storeId));
        return $this->_connection->fetchAll($select);
    }

    /**
     * Creating table and adding attributes as fields to table for all stores
     *
     * @return Enterprise_Catalog_Model_Index_Action_Category_Flat_Refresh
     */
    protected function _createTables()
    {
        if ($this->_allowTableChanges) {
            /** @var $store Mage_Core_Model_Store */
            foreach (Mage::app()->getStores() as $store) {
                $this->_createTable($store->getId());
            }
        }
        return $this;
    }

    /**
     * Switch table (temporary becomes active, old active will be dropped)
     *
     * @return Enterprise_Catalog_Model_Index_Action_Category_Flat_Refresh
     */
    protected function _switchTables()
    {
        $writeAdapter = $this->_connection;

        /** @var $store Mage_Core_Model_Store */
        foreach (Mage::app()->getStores() as $store) {
            $activeTableName = $this->_getMainStoreTable($store->getId());
            $temporaryTableName = $this->_addTemporaryTableSuffix($this->_getMainStoreTable($store->getId()));
            $oldTableName = $this->_addOldTableSuffix($this->_getMainStoreTable($store->getId()));

            //switch tables
            $tablesToRename = array();
            if ($writeAdapter->isTableExists($activeTableName)) {
                $tablesToRename[] = array(
                    'oldName' => $activeTableName,
                    'newName' => $oldTableName
                );
            }

            $tablesToRename[] = array(
                'oldName' => $temporaryTableName,
                'newName' => $activeTableName
            );

            $writeAdapter->renameTablesBatch($tablesToRename);

            //delete inactive table
            $tableToDelete = $oldTableName;

            if ($writeAdapter->isTableExists($tableToDelete)) {
                $writeAdapter->dropTable($tableToDelete);
            }
        }

        return $this;
    }

        /**
     * Transactional rebuild flat data from eav
     *
     * @return Enterprise_Catalog_Model_Index_Action_Category_Flat_Refresh
     */
    protected function _reindexAll()
    {
        $this->_createTables();

        $allowTableChanges = $this->_allowTableChanges;
        if ($allowTableChanges) {
            $this->_allowTableChanges = false;
        }

        $this->_rebuild();
        $this->_switchTables();

        $this->_allowTableChanges = true;

        return $this;
    }

    /**
     * Refresh entities index
     *
     * @param array $changedIds
     * @return Enterprise_Catalog_Model_Index_Action_Category_Flat_Refresh
     */
    protected function _reindex($changedIds = array())
    {
        $stores = Mage::app()->getStores();
        if (!is_array($stores)) {
            $stores = array($stores);
        }

        /* @var $category Mage_Catalog_Model_Category */
        $category = Mage::getModel('catalog/category');

        /* @var $store Mage_Core_Model_Store */
        foreach ($stores as $store) {
            if (!$this->_connection->isTableExists($this->_getMainStoreTable($store->getId()))) {
                $tableName = $this->_getMainStoreTable($store->getId());
                $table     = $this->_getFlatTableStructure($tableName);
                $this->_dropOldForeignKeys($tableName);
                $this->_connection->createTable($table);
            }
            $categoriesIdsChunks = array_chunk($changedIds, Mage::helper('enterprise_index')->getBatchSize());
            foreach ($categoriesIdsChunks as $categoriesIdsChunk) {

                $categoriesIdsChunk = $this->_filterIdsByStore($categoriesIdsChunk, $store);

                $attributesData = $this->_getAttributeValues($categoriesIdsChunk, $store->getId());
                $data = array();
                foreach ($categoriesIdsChunk as $categoryId) {
                    if (!isset($attributesData[$categoryId])) {
                        continue;
                    }
                    if ($category->load($categoryId)->getId()) {
                        $data[] = $this->_prepareValuesToInsert(
                            array_merge(
                                $category->getData(),
                                $attributesData[$categoryId],
                                array('store_id' => $store->getId())
                            )
                        );
                        $category->unsetData();
                    }
                }
                foreach ($data as $row) {
                    $updateFields = array();
                    foreach ($row as $key => $value) {
                        $updateFields[$key] = $key;
                    }
                    $this->_connection->insertOnDuplicate(
                        $this->_getMainStoreTable($store->getId()),
                        $row,
                        $updateFields
                    );
                }
            }
            $this->_deleteNonStoreCategories($store);
        }

        Mage::dispatchEvent('catalog_category_flat_partial_reindex', array('category_ids' => $changedIds));
        return $this;
    }

    /**
     * Filter category ids by store
     *
     * @param array $ids
     * @param Mage_Core_Model_Store $store
     * @return array
     */
    protected function _filterIdsByStore($ids, $store)
    {
        $rootId = Mage_Catalog_Model_Category::TREE_ROOT_ID;

        $rootIdExpr = $this->_connection->quote((string)$rootId);
        $rootCatIdExpr = $this->_connection->quote("{$rootId}/{$store->getRootCategoryId()}");
        $catIdExpr = $this->_connection->quote("{$rootId}/{$store->getRootCategoryId()}/%");

        $select = $this->_connection->select()
            ->from($this->_productHelper->getTable('catalog/category'), array('entity_id'))
            ->where("path = {$rootIdExpr} OR path = {$rootCatIdExpr} OR path like {$catIdExpr}")
            ->where('entity_id IN (?)', $ids);

        $resultIds = array();
        foreach ($this->_connection->fetchAll($select) as $category) {
            $resultIds[] = $category['entity_id'];
        }
        return $resultIds;
    }

    /**
     * Delete non stores categories
     *
     * @param Mage_Core_Model_Store $store
     * @return void
     */
    protected function _deleteNonStoreCategories($store)
    {
        $rootId = Mage_Catalog_Model_Category::TREE_ROOT_ID;

        $rootIdExpr = $this->_connection->quote((string)$rootId);
        $rootCatIdExpr = $this->_connection->quote("{$rootId}/{$store->getRootCategoryId()}");
        $catIdExpr = $this->_connection->quote("{$rootId}/{$store->getRootCategoryId()}/%");

        $select = $this->_connection->select()
            ->from(array('cf' => $this->_getMainStoreTable($store->getId())))
            ->joinLeft(
                array('ce' => $this->_productHelper->getTable('catalog/category')),
                'cf.path = ce.path',
                array()
            )
            ->where("cf.path = {$rootIdExpr} OR cf.path = {$rootCatIdExpr} OR cf.path like {$catIdExpr}")
            ->where('ce.entity_id IS NULL');

        $sql = $select->deleteFromSelect('cf');
        $this->_connection->query($sql);
    }
}
