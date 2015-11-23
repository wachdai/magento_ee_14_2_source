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
 * Category/Product index refresh all action
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Action_Catalog_Category_Product_Refresh
    implements Enterprise_Mview_Model_Action_Interface
{
    /**
     * Chunk size
     */
    const RANGE_CATEGORY_STEP = 500;

    /**
     * Chunk size for product
     */
    const RANGE_PRODUCT_STEP = 1000000;

    /**
     * Last version ID
     *
     * @var int
     */
    protected $_lastVersionId;

    /**
     * Connection instance
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Mview metadata instance
     *
     * @var Enterprise_Mview_Model_Metadata
     */
    protected $_metadata;

    /**
     * Mview factory instance
     *
     * @var Enterprise_Mview_Model_Factory
     */
    protected $_factory;

    /**
     * Cached non anchor categories select by store id
     *
     * @var array
     */
    protected $_nonAnchorCategoriesSelect = array();

    /**
     * Cached anchor categories select by store id
     *
     * @var array
     */
    protected $_anchorCategoriesSelect = array();

    /**
     * Cached all product select by store id
     *
     * @var array
     */
    protected $_allProductsSelect = array();

    /**
     * Category path by id
     *
     * @var array
     */
    protected  $_categoryPath = array();

    /**
     * Application instance
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'metadata' Enterprise_Mview_Model_Metadata
     *  - 'connection' Varien_Db_Adapter_Interface
     *  - 'factory' Enterprise_Mview_Model_Factory
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->_setConnection($args['connection']);
        $this->_setMetadata($args['metadata']);
        $this->_setFactory($args['factory']);
        $this->_app = !empty($args['app']) ? $args['app'] : Mage::app();
    }

    /**
     * Set connection
     *
     * @param Varien_Db_Adapter_Interface $connection
     */
    protected function _setConnection(Varien_Db_Adapter_Interface $connection)
    {
        $this->_connection = $connection;
    }

    /**
     * Set metadata
     *
     * @param Enterprise_Mview_Model_Metadata $metadata
     */
    protected function _setMetadata(Enterprise_Mview_Model_Metadata $metadata)
    {
        $this->_metadata = $metadata;
    }

    /**
     * Set factory
     *
     * @param Enterprise_Mview_Model_Factory $factory
     */
    protected function _setFactory(Enterprise_Mview_Model_Factory $factory)
    {
        $this->_factory = $factory;
    }

    /**
     * Run full reindex
     *
     * @return Enterprise_Catalog_Model_Index_Action_Catalog_Category_Product_Refresh
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        if (!$this->_metadata->isValid()) {
            throw new Enterprise_Index_Model_Action_Exception("Can't perform operation, incomplete metadata!");
        }

        try {
            $this->_dispatchNotification();
            $this->_reindex();
            $this->_app->dispatchEvent('enterprise_after_reindex_process_catalog_category_product', array());
            $this->_dispatchNotification();
        } catch (Exception $e) {
            $this->_metadata->setInvalidStatus()->save();
            throw new Enterprise_Index_Model_Action_Exception($e->getMessage(), $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * Update metadata
     *
     * @return Enterprise_Catalog_Model_Index_Action_Catalog_Category_Product_Refresh
     */
    protected function _updateMetadata()
    {
        if ($this->_metadata->getStatus() == Enterprise_Mview_Model_Metadata::STATUS_IN_PROGRESS) {
            $this->_metadata->setValidStatus();
        }
        $this->_metadata->setVersionId($this->_getLastVersionId())
            ->save();

        return $this;
    }

    /**
     * Return last version ID
     *
     * @param string|null $metadata
     * @return int
     */
    protected function _getLastVersionId($metadata = null)
    {
        $changelogName = is_object($metadata) ? $metadata->getChangelogName() : $this->_metadata->getChangelogName();
        if (empty($changelogName)) {
            return 0;
        }

        if (!$this->_lastVersionId) {
            $select = $this->_connection->select()
                ->from($changelogName, array('version_id'))
                ->order('version_id DESC')
                ->limit(1);

            $this->_lastVersionId = (int)$this->_connection->fetchOne($select);
        }
        return $this->_lastVersionId;
    }

    /**
     * Return table name by path
     *
     * @param string|array $table
     * @return string
     */
    protected function _getTable($table)
    {
        return $this->_metadata->getResource()->getTable($table);
    }

    /**
     * Return main index table name
     *
     * @return string
     */
    protected function _getMainTable()
    {
        return $this->_getTable('catalog/category_product_index');
    }

    /**
     * Return tmp table
     *
     * @return string
     */
    protected function _getMainTmpTable()
    {
        return $this->_metadata->getTableName() . '_tmp';
    }

    /**
     * Create tmp table
     */
    protected function _createTmpTable()
    {
        $table = $this->_connection->newTable($this->_getMainTmpTable())
            ->addColumn(
                'category_id',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                null,
                array(
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'default'   => '0',
                ),
                'Category ID'
            )
            ->addColumn(
                'product_id',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                null,
                array(
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'default'   => '0',
                ),
                'Product ID'
            )
            ->addColumn(
                'position',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                null,
                array(
                    'unsigned'  => false,
                    'nullable'  => true,
                    'default'   => null,
                ),
                'Position'
            )
            ->addColumn(
                'is_parent',
                Varien_Db_Ddl_Table::TYPE_SMALLINT,
                null,
                array(
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                ),
                'Is Parent'
            )
            ->addColumn(
                'store_id',
                Varien_Db_Ddl_Table::TYPE_SMALLINT,
                null,
                array(
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'default'   => '0',
                ),
                'Store ID'
            )
            ->addColumn(
                'visibility',
                Varien_Db_Ddl_Table::TYPE_SMALLINT,
                null,
                array(
                    'unsigned'  => true,
                    'nullable'  => false,
                ),
                'Visibility'
            )
            ->setComment('Catalog Category Product Index Tmp');

        $this->_connection->dropTemporaryTable($this->_getMainTmpTable());
        $this->_connection->createTemporaryTable($table);
    }

    /**
     * Retrieve code for the paired indexer
     *
     * @return string
     */
    protected function _getPairedIndexerCode()
    {
        return $this->_metadata->getTableName() == 'catalog_category_product_index'
            ? 'catalog_category_product_cat' : 'catalog_category_product_index';
    }

    /**
     * Execute additional operations before reindex
     *
     * @return Enterprise_Catalog_Model_Index_Action_Catalog_Category_Product_Refresh
     */
    protected function _beforeReindex()
    {
        $this->_metadata->setInProgressStatus()->save();

        /** @var $client Enterprise_Mview_Model_Client */
        $client = $this->_factory->getModel('enterprise_mview/client');
        $client->init($this->_getPairedIndexerCode())->getMetadata()
            ->setInProgressStatus()
            ->save();

        return $this;
    }

    /**
     * Execute additional operations after reindex
     *
     * @return Enterprise_Catalog_Model_Index_Action_Catalog_Category_Product_Refresh
     */
    protected function _afterReindex()
    {
        $this->_updateMetadata();

        /** @var $client Enterprise_Mview_Model_Client */
        $client = $this->_factory->getModel('enterprise_mview/client');
        $client->init($this->_getPairedIndexerCode())->getMetadata()
            ->setValidStatus()
            ->save();

        return $this;
    }

    /**
     * Reindex category/product index by store
     *
     * @return Enterprise_Catalog_Model_Index_Action_Catalog_Category_Product_Refresh
     */
    protected function _reindex()
    {
        $this->_beforeReindex();

        $this->_createTmpTable();

        $rootCatIds = array();
        foreach ($this->_app->getStores() as $store) {
            /** @var $store Mage_Core_Model_Store */
            $rootCatIds[] = $store->getRootCategoryId();
            if ($this->_getPathFromCategoryId($store->getRootCategoryId())) {
                $this->_reindexNonAnchorCategories($store);
                $this->_reindexAnchorCategories($store);
                $this->_reindexRootCategory($store);
            }
        }
        $this->_publishData();
        $this->_removeUnnecessaryData($rootCatIds);
        $this->_clearTmpData();

        $this->_afterReindex();

        return $this;
    }

    /**
     * Return select for remove unnecessary data
     *
     * @param array $rootCatIds
     * @return Varien_Db_Select
     */
    protected function _getSelectUnnecessaryData($rootCatIds)
    {
        return $this->_connection->select()
            ->from($this->_getMainTable(), array())
            ->joinLeft(
                array('t' => $this->_getMainTmpTable()),
                $this->_getMainTable() . '.category_id = t.category_id AND '
                    . $this->_getMainTable() . '.store_id = t.store_id AND '
                    . $this->_getMainTable() . '.product_id = t.product_id',
                array()
            )
            ->where('t.category_id IS NULL');
    }

    /**
     * Remove unnecessary data
     *
     * @param array $rootCatIds
     */
    protected function _removeUnnecessaryData($rootCatIds)
    {
        $this->_connection->query(
            $this->_connection->deleteFromSelect($this->_getSelectUnnecessaryData($rootCatIds), $this->_getMainTable())
        );
    }

    /**
     * Publish data from tmp to index
     */
    protected function _publishData()
    {
        $select = $this->_connection->select()
            ->from($this->_getMainTmpTable());

        $queries = $this->_prepareSelectsByRange($select, 'category_id');

        foreach ($queries as $query) {
            $this->_connection->query(
                $this->_connection->insertFromSelect(
                    $query,
                    $this->_getMainTable(),
                    array('category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'),
                    Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE
                )
            );
        }
    }

    /**
     * Clear all index data
     */
    protected function _clearTmpData()
    {
        $this->_connection->dropTemporaryTable($this->_getMainTmpTable());
    }

    /**
     * Return category path by id
     *
     * @param $categoryId
     * @return mixed
     */
    protected function _getPathFromCategoryId($categoryId)
    {
        if (!isset($this->_categoryPath[$categoryId])) {
            $this->_categoryPath[$categoryId] = $this->_connection->fetchOne(
                $this->_connection->select()
                    ->from($this->_getTable('catalog/category'), array('path'))
                    ->where('entity_id = ?', $categoryId)
            );
        }
        return $this->_categoryPath[$categoryId];
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param Mage_Core_Model_Store $store
     * @return Varien_Db_Select
     */
    protected function _getNonAnchorCategoriesSelect(Mage_Core_Model_Store $store)
    {
        if (!isset($this->_nonAnchorCategoriesSelect[$store->getId()])) {
            /** @var $eavConfig Mage_Eav_Model_Config */
            $eavConfig = $this->_factory->getSingleton('eav/config');
            $statusAttributeId = $eavConfig->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'status')->getId();
            $visibilityAttributeId = $eavConfig->getAttribute(
                Mage_Catalog_Model_Product::ENTITY, 'visibility'
            )->getId();

            $rootPath = $this->_getPathFromCategoryId($store->getRootCategoryId());

            $select = $this->_connection->select()
                ->from(array('cc' => $this->_getTable('catalog/category')), array())
                ->joinInner(
                    array('ccp' => $this->_getTable('catalog/category_product')),
                    'ccp.category_id = cc.entity_id',
                    array()
                )
                ->joinInner(
                    array('cpw' => $this->_getTable('catalog/product_website')),
                    'cpw.product_id = ccp.product_id',
                    array()
                )
                ->joinInner(
                    array('cpsd' => $this->_getTable(array('catalog/product', 'int'))),
                    'cpsd.entity_id = ccp.product_id AND cpsd.store_id = 0 AND cpsd.attribute_id = '
                        . $statusAttributeId,
                    array()
                )
                ->joinLeft(
                    array('cpss' => $this->_getTable(array('catalog/product', 'int'))),
                    'cpss.entity_id = ccp.product_id AND cpss.attribute_id = cpsd.attribute_id'
                        . ' AND cpss.store_id = ' . $store->getId(),
                    array()
                )
                ->joinInner(
                    array('cpvd' => $this->_getTable(array('catalog/product', 'int'))),
                    'cpvd.entity_id = ccp.product_id AND cpvd.store_id = 0'
                        . ' AND cpvd.attribute_id = ' . $visibilityAttributeId,
                    array()
                )
                ->joinLeft(
                    array('cpvs' => $this->_getTable(array('catalog/product', 'int'))),
                    'cpvs.entity_id = ccp.product_id AND cpvs.attribute_id = cpvd.attribute_id '
                        . 'AND cpvs.store_id = ' . $store->getId(),
                    array()
                )
                ->where(
                    'cc.path LIKE '
                        . $this->_connection->getConcatSql(
                            array(
                                $this->_connection->quote($rootPath),
                                $this->_connection->quote('/%')
                            )
                    )
                )
                ->where('cpw.website_id = ?', $store->getWebsiteId())
                ->where(
                    $this->_connection->getIfNullSql('cpss.value', 'cpsd.value') . ' = ?',
                    Mage_Catalog_Model_Product_Status::STATUS_ENABLED
                )
                ->where(
                    $this->_connection->getIfNullSql('cpvs.value', 'cpvd.value') . ' IN (?)',
                    array(
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
                    )
                )
                ->columns(
                    array(
                        'category_id'   => 'cc.entity_id',
                        'product_id'    => 'ccp.product_id',
                        'position'      => 'ccp.position',
                        'is_parent'     => new Zend_Db_Expr('1'),
                        'store_id'      => new Zend_Db_Expr($store->getId()),
                        'visibility'    => new Zend_Db_Expr(
                            $this->_connection->getIfNullSql('cpvs.value', 'cpvd.value')
                        )
                    )
                );

            $this->_nonAnchorCategoriesSelect[$store->getId()] = $select;
        }

        return $this->_nonAnchorCategoriesSelect[$store->getId()];
    }

    /**
     * Return selects cut by min and max
     *
     * @param Varien_Db_Select $select
     * @param string $field
     * @param int $range
     * @return array
     */
    protected function _prepareSelectsByRange(Varien_Db_Select $select, $field, $range = self::RANGE_CATEGORY_STEP)
    {
        return $this->_connection->selectsByRange($field, $select, $range);
    }

    /**
     * Reindex products of non anchor categories
     *
     * @param Mage_Core_Model_Store $store
     */
    protected function _reindexNonAnchorCategories(Mage_Core_Model_Store $store)
    {
        $selects = $this->_prepareSelectsByRange($this->_getNonAnchorCategoriesSelect($store), 'entity_id');
        foreach ($selects as $select) {
            $this->_connection->query(
                $this->_connection->insertFromSelect(
                    $select,
                    $this->_getMainTmpTable(),
                    array('category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility')
                )
            );
        }
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param Mage_Core_Model_Store $store
     * @return Varien_Db_Select
     */
    protected function _getAnchorCategoriesSelect(Mage_Core_Model_Store $store)
    {
        if (!isset($this->_anchorCategoriesSelect[$store->getId()])) {
            /** @var $eavConfig Mage_Eav_Model_Config */
            $eavConfig = $this->_factory->getSingleton('eav/config');
            $isAnchorAttributeId = $eavConfig->getAttribute(Mage_Catalog_Model_Category::ENTITY, 'is_anchor')->getId();
            $statusAttributeId = $eavConfig->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'status')->getId();
            $visibilityAttributeId = $eavConfig->getAttribute(
                Mage_Catalog_Model_Product::ENTITY, 'visibility'
            )->getId();

            $rootCatIds = explode('/', $this->_getPathFromCategoryId($store->getRootCategoryId()));
            array_pop($rootCatIds);

            $select = $this->_connection->select()
                ->from(array('cc' => $this->_getTable('catalog/category')), array())
                ->joinInner(
                    array('cc2' => $this->_getTable('catalog/category')),
                    'cc2.path LIKE '
                        . $this->_connection->getConcatSql(
                            array(
                                $this->_connection->quoteIdentifier('cc.path'),
                                $this->_connection->quote('/%')
                            )
                        )
                        . ' AND cc.entity_id NOT IN (' . implode(',', $rootCatIds) . ')',
                    array()
                )
                ->joinInner(
                    array('ccp' => $this->_getTable('catalog/category_product')),
                    'ccp.category_id = cc2.entity_id',
                    array()
                )
                ->joinInner(
                    array('cpw' => $this->_getTable('catalog/product_website')),
                    'cpw.product_id = ccp.product_id',
                    array()
                )
                ->joinInner(
                    array('cpsd' => $this->_getTable(array('catalog/product', 'int'))),
                    'cpsd.entity_id = ccp.product_id AND cpsd.store_id = 0 AND cpsd.attribute_id = '
                        . $statusAttributeId,
                    array()
                )
                ->joinLeft(
                    array('cpss' => $this->_getTable(array('catalog/product', 'int'))),
                    'cpss.entity_id = ccp.product_id AND cpss.attribute_id = cpsd.attribute_id'
                        . ' AND cpss.store_id = ' . $store->getId(),
                    array()
                )
                ->joinInner(
                    array('cpvd' => $this->_getTable(array('catalog/product', 'int'))),
                    'cpvd.entity_id = ccp.product_id AND cpvd.store_id = 0'
                        . ' AND cpvd.attribute_id = ' . $visibilityAttributeId,
                    array()
                )
                ->joinLeft(
                    array('cpvs' => $this->_getTable(array('catalog/product', 'int'))),
                    'cpvs.entity_id = ccp.product_id AND cpvs.attribute_id = cpvd.attribute_id '
                        . 'AND cpvs.store_id = ' . $store->getId(),
                    array()
                )
                ->joinInner(
                    array('ccad' => $this->_getTable(array('catalog/category', 'int'))),
                    'ccad.entity_id = cc.entity_id AND ccad.store_id = 0'
                        . ' AND ccad.attribute_id = ' . $isAnchorAttributeId,
                    array()
                )
                ->joinLeft(
                    array('ccas' => $this->_getTable(array('catalog/category', 'int'))),
                    'ccas.entity_id = cc.entity_id AND ccas.attribute_id = ccad.attribute_id'
                        . ' AND ccas.store_id = ' . $store->getId(),
                    array()
                )
                ->where('cpw.website_id = ?', $store->getWebsiteId())
                ->where(
                    $this->_connection->getIfNullSql('cpss.value', 'cpsd.value') . ' = ?',
                    Mage_Catalog_Model_Product_Status::STATUS_ENABLED
                )
                ->where(
                    $this->_connection->getIfNullSql('cpvs.value', 'cpvd.value') . ' IN (?)',
                    array(
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
                    )
                )
                ->where(
                    $this->_connection->getIfNullSql('ccas.value', 'ccad.value') . ' = ?',
                    1
                )
                ->columns(
                    array(
                        'category_id'   => 'cc.entity_id',
                        'product_id'    => 'ccp.product_id',
                        'position'      => new Zend_Db_Expr('ccp.position + 10000'),
                        'is_parent'     => new Zend_Db_Expr('0'),
                        'store_id'      => new Zend_Db_Expr($store->getId()),
                        'visibility'    => new Zend_Db_Expr(
                            $this->_connection->getIfNullSql('cpvs.value', 'cpvd.value')
                        )
                    )
                );

            $this->_anchorCategoriesSelect[$store->getId()] = $select;
        }

        return $this->_anchorCategoriesSelect[$store->getId()];
    }

    /**
     * Reindex products of anchor categories
     *
     * @param Mage_Core_Model_Store $store
     */
    protected function _reindexAnchorCategories(Mage_Core_Model_Store $store)
    {
        $selects = $this->_prepareSelectsByRange($this->_getAnchorCategoriesSelect($store), 'entity_id');

        foreach ($selects as $select) {
            $this->_connection->query(
                $this->_connection->insertFromSelect(
                    $select,
                    $this->_getMainTmpTable(),
                    array('category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'),
                    Varien_Db_Adapter_Interface::INSERT_IGNORE
                )
            );
        }
    }

    /**
     * Get select for all products
     *
     * @param $store
     * @return Varien_Db_Select
     */
    protected function _getAllProducts(Mage_Core_Model_Store $store)
    {
        if (!isset($this->_allProductsSelect[$store->getId()])) {
            /** @var $eavConfig Mage_Eav_Model_Config */
            $eavConfig = $this->_factory->getSingleton('eav/config');
            $statusAttributeId = $eavConfig->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'status')->getId();
            $visibilityAttributeId = $eavConfig->getAttribute(
                Mage_Catalog_Model_Product::ENTITY, 'visibility'
            )->getId();

            $select = $this->_connection->select()
                ->from(array('cp' => $this->_getTable('catalog/product')), array())
                ->joinInner(
                    array('cpw' => $this->_getTable('catalog/product_website')),
                    'cpw.product_id = cp.entity_id',
                    array()
                )
                ->joinInner(
                    array('cpsd' => $this->_getTable(array('catalog/product', 'int'))),
                    'cpsd.entity_id = cp.entity_id AND cpsd.store_id = 0 AND cpsd.attribute_id = '
                        . $statusAttributeId,
                    array()
                )
                ->joinLeft(
                    array('cpss' => $this->_getTable(array('catalog/product', 'int'))),
                    'cpss.entity_id = cp.entity_id AND cpss.attribute_id = cpsd.attribute_id'
                        . ' AND cpss.store_id = ' . $store->getId(),
                    array()
                )
                ->joinInner(
                    array('cpvd' => $this->_getTable(array('catalog/product', 'int'))),
                    'cpvd.entity_id = cp.entity_id AND cpvd.store_id = 0'
                        . ' AND cpvd.attribute_id = ' . $visibilityAttributeId,
                    array()
                )
                ->joinLeft(
                    array('cpvs' => $this->_getTable(array('catalog/product', 'int'))),
                    'cpvs.entity_id = cp.entity_id AND cpvs.attribute_id = cpvd.attribute_id '
                        . 'AND cpvs.store_id = ' . $store->getId(),
                    array()
                )
                ->joinLeft(
                    array('ccp' => $this->_getTable('catalog/category_product')),
                    'ccp.product_id = cp.entity_id',
                    array()
                )
                ->where('cpw.website_id = ?', $store->getWebsiteId())
                ->where(
                    $this->_connection->getIfNullSql('cpss.value', 'cpsd.value') . ' = ?',
                    Mage_Catalog_Model_Product_Status::STATUS_ENABLED
                )
                ->where(
                    $this->_connection->getIfNullSql('cpvs.value', 'cpvd.value') . ' IN (?)',
                    array(
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
                    )
                )
                ->group('cp.entity_id')
                ->columns(
                    array(
                        'category_id'   => new Zend_Db_Expr($store->getRootCategoryId()),
                        'product_id'    => 'cp.entity_id',
                        'position'      => new Zend_Db_Expr(
                            $this->_connection->getCheckSql('ccp.product_id IS NOT NULL', 'ccp.position', '0')
                        ),
                        'is_parent'     => new Zend_Db_Expr(
                            $this->_connection->getCheckSql('ccp.product_id IS NOT NULL', '0', '1')
                        ),
                        'store_id'      => new Zend_Db_Expr($store->getId()),
                        'visibility'    => new Zend_Db_Expr(
                            $this->_connection->getIfNullSql('cpvs.value', 'cpvd.value')
                        )
                    )
                );

            $this->_allProductsSelect[$store->getId()] = $select;
        }

        return $this->_allProductsSelect[$store->getId()];
    }

    /**
     * Reindex all products to root category
     *
     * @param Mage_Core_Model_Store $store
     */
    protected function _reindexRootCategory(Mage_Core_Model_Store $store)
    {
        $selects = $this->_prepareSelectsByRange($this->_getAllProducts($store), 'entity_id', self::RANGE_PRODUCT_STEP);

        foreach ($selects as $select) {
            $this->_connection->query(
                $this->_connection->insertFromSelect(
                    $select,
                    $this->_getMainTmpTable(),
                    array('category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'),
                    Varien_Db_Adapter_Interface::INSERT_IGNORE
                )
            );
        }
    }

    /**
     * Dispatches an event after reindex
     * @return Enterprise_Catalog_Model_Index_Action_Catalog_Category_Product_Refresh
     */
    protected function _dispatchNotification()
    {
        $this->_app->dispatchEvent('catalog_category_product_full_reindex', array());
        return $this;
    }
}
