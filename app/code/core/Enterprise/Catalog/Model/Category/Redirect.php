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
 * Catalog category resource model
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Category_Redirect
{
    /**
     * Temporary redirects table name
     */
    const TMP_TABLE_NAME = 'enterprise_url_rewrite_redirect_tmp';

    /**
     * Resource model
     *
     * @var Mage_Core_Model_Resource
     */
    protected $_resource;

    /**
     * connection
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Categories paths
     *
     * @var array $_paths;
     */
    protected $_paths = array();

    /**
     * Config model
     *
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * @var Mage_Catalog_Model_Category
     */
    protected $_categoryModel;

    /**
     * Product helper
     *
     * @var $helper Mage_Catalog_Helper_Product
     */
    protected $_productHelper;

    /**
     * Category helper
     *
     * @var $helper Mage_Catalog_Helper_Category
     */
    protected $_categoryHelper;

    /**
     * App model
     *
     * @var $_app Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_resource = isset($args['resource']) ? $args['resource'] : Mage::getSingleton('core/resource');
        $this->_connection = isset($args['connection'])
            ? $args['connection']
            : $this->_resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        $this->_config = isset($args['config'])
            ? $args['config']
            : Mage::getConfig();
        $this->_categoryModel = isset($args['categoryModel'])
            ? $args['categoryModel']
            : Mage::getModel('catalog/category');
        $this->_productHelper = isset($args['productHelper'])
            ? $args['productHelper']
            : Mage::helper('catalog/product');
        $this->_categoryHelper = isset($args['categoryHelper'])
            ? $args['categoryHelper']
            : Mage::helper('catalog/category');
        $this->_app = isset($args['app'])
            ? $args['app']
            : Mage::app();
    }

    /**
     * Save custom redirects when url key was changed (for category, children and assigned products)
     *
     * @param Mage_Catalog_Model_Category $category
     * @param int $storeId
     */
    public function saveCustomRedirects($category, $storeId)
    {
        $this->_createRedirectTemporaryTable();

        $categoryPriority = (int) $this->_config->getNode(
            sprintf(Enterprise_UrlRewrite_Model_Url_Rewrite::REWRITE_MATCHERS_PRIORITY_PATH, 'category'), 'default'
        );
        $productsPriority = (int) $this->_config->getNode(
            sprintf(Enterprise_UrlRewrite_Model_Url_Rewrite::REWRITE_MATCHERS_PRIORITY_PATH, 'product'), 'default'
        );

        if ($categoryPriority < $productsPriority) {
            $this->_insertProductRedirectsToTemporaryTable($category, $storeId);
            $this->_insertCategoryRedirectsToTemporaryTable($category, $storeId);
        } else {
            $this->_insertCategoryRedirectsToTemporaryTable($category, $storeId);
            $this->_insertProductRedirectsToTemporaryTable($category, $storeId);
        }
        $this->_copyRedirectsToMainTable();
    }

    /**
     * Create redirects temporary table
     *
     * @return void
     */
    protected function _createRedirectTemporaryTable()
    {
        $temporaryTable = $this->_connection->newTable(self::TMP_TABLE_NAME);

        $temporaryTable->addColumn(
            'identifier',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            255
        )
        ->addColumn(
            'target_path',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            255
        )
        ->addColumn(
            'store_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER
        )
        ->addColumn(
            'category_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array('unsigned' => true)
        )
        ->addColumn(
            'product_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array('unsigned' => true)
        )
        ->addIndex(
            'UNQ_ENTERPRISE_URL_REWRITE_TMP_IDENTIFIER_STORE_ID',
            array('identifier', 'store_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        );
        $this->_connection->createTemporaryTable($temporaryTable);
    }

    /**
     * Fill temporary table with products redirects for default store
     *
     * @param Mage_Catalog_Model_Category $category
     * @param int $storeId
     *
     * @return void
     */
    protected function _insertProductRedirectsToTemporaryTable($category, $storeId)
    {
        $availableStores = array();
        /* @var $store Mage_Core_Model_Store */
        foreach ($this->_app->getStores() as $store) {
            if ((bool) $store->getConfig(Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_USE_CATEGORY)) {
                $availableStores[] = $store->getId();
            }
        }
        if (empty($availableStores)) {
            return;
        }

        // Select product redirects by stores (ignore redefined on store level values)
        $select = $this->_getProductsRedirectsSelect($category, 0, $storeId, $availableStores);
        $query = $select->insertFromSelect(
            self::TMP_TABLE_NAME,
            array('identifier', 'target_path', 'store_id', 'category_id', 'product_id'),
            Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE
        );
        $this->_connection->query($query);

        // Select product redirects redefined on store level
        $select = $this->_getProductsRedirectsSelect($category, 'cp.store_id', $storeId, $availableStores);
        $query = $select->insertFromSelect(
            self::TMP_TABLE_NAME,
            array('identifier', 'target_path', 'store_id', 'category_id', 'product_id'),
            Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE
        );
        $this->_connection->query($query);
    }

    /**
     * Get products redirects select
     *
     * @param Mage_Catalog_Model_Category
     * @param int|string $joinStore
     * @param int $storeId
     * @param array $availableStores
     *
     * @return Varien_Db_Select
     */
    protected function _getProductsRedirectsSelect($category, $joinStore, $storeId, $availableStores)
    {
        $select = $this->_connection->select();
        $concatSql = $this->_connection->getConcatSql(
            array(
                'urc.request_path',
                '"/"',
                'urp.request_path',
                $this->_getProductSeoSuffixCaseSql('cp.store_id', $category)
            )
        );
        $categoryExpr = sprintf(
            "cp.category_id = %d OR ce.path LIKE '%s'",
            (int) $category->getId(),
            $this->_getCategoryPath($category->getId()) . '/%'
        );
        $select->from(
            array('cp' => $this->_resource->getTableName('catalog/category_product_index')),
            array(
                $concatSql,
                'urp.target_path',
                'cp.store_id',
                'cp.category_id',
                'cp.product_id',
            )
        )
        ->joinInner(
            array('ce' => $this->_resource->getTableName('catalog/category')),
            'ce.entity_id = cp.category_id',
            array('')
        )
        ->joinInner(
            array('cr' => $this->_resource->getTableName('enterprise_catalog/category')),
            'cp.category_id = cr.category_id AND cp.store_id = cr.store_id',
            array('')
        )
        ->joinInner(
            array('pr' => $this->_resource->getTableName('enterprise_catalog/product')),
            'cp.product_id = pr.product_id AND pr.store_id = ' . $joinStore,
            array('')
        )
        ->joinInner(
            array('urp' => $this->_resource->getTableName('enterprise_urlrewrite/url_rewrite')),
            'pr.url_rewrite_id = urp.url_rewrite_id AND pr.store_id = urp.store_id
                AND urp.entity_type = ' . Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE,
            array('')
        )
        ->joinInner(
            array('urc' => $this->_resource->getTableName('enterprise_urlrewrite/url_rewrite')),
            'cr.url_rewrite_id = urc.url_rewrite_id AND cr.store_id = urc.store_id
                AND urc.entity_type = ' . Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE,
            array('')
        )
        ->where($categoryExpr)
        ->where('cp.store_id IN(?)', $availableStores);

        if (!empty($storeId)) {
            $select->where('cp.store_id = ?', (int) $storeId);
        }
        return $select;
    }

    /**
     * Fill temporary table with category (and children) redirects
     *
     * @param Mage_Catalog_Model_Category $category
     * @param int $storeId
     *
     * @return void
     */
    protected function _insertCategoryRedirectsToTemporaryTable($category, $storeId)
    {
        $concatSql = $this->_connection->getConcatSql(
            array('urc.request_path', $this->_getCategorySeoSuffixCaseSql('cr.store_id', $category))
        );
        $select = $this->_connection->select();
        $categoryExpr = sprintf(
            "cr.category_id = %d OR ce.path LIKE '%s'",
            (int) $category->getId(),
            $this->_getCategoryPath($category->getId()) . '/%'
        );
        $select->from(
            array('cr' => $this->_resource->getTableName('enterprise_catalog/category')),
            array(
                $concatSql,
                'urc.target_path',
                'cr.store_id',
                'ce.entity_id',
                new Zend_Db_Expr('NULL'),
            )
        )
        ->joinInner(
            array('ce' => $this->_resource->getTableName('catalog/category')),
            'ce.entity_id = cr.category_id',
            array('')
        )
        ->joinInner(
            array('urc' => $this->_resource->getTableName('enterprise_urlrewrite/url_rewrite')),
            'cr.url_rewrite_id = urc.url_rewrite_id AND cr.store_id = urc.store_id
                AND urc.entity_type = ' . Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE,
            array('')
        )
        ->where($categoryExpr);

        if (!empty($storeId)) {
            $select->where('urc.store_id = ?', (int) $storeId);
        }
        $query = $select->insertFromSelect(
            self::TMP_TABLE_NAME,
            array('identifier', 'target_path', 'store_id', 'category_id', 'product_id'),
            Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE
        );
        $this->_connection->query($query);
    }

    /**
     * Copy data from temporary to main redirects table
     *
     * @return void
     */
    protected function _copyRedirectsToMainTable()
    {
        $select = $this->_connection->select();
        $select->from(array('t' => self::TMP_TABLE_NAME), array('*'));
        $query = $select->insertFromSelect(
            $this->_resource->getTableName('enterprise_urlrewrite/redirect'),
            array('identifier', 'target_path', 'store_id', 'category_id', 'product_id'),
            Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE
        );
        $this->_connection->query($query);
    }

    /**
     * Get category ids path by id
     *
     * @param int $categoryId
     * @return string
     *
     * @return void
     */
    protected function _getCategoryPath($categoryId)
    {
        if (!isset($this->_paths[$categoryId])) {
            $this->_paths[$categoryId] = $this->_categoryModel->load($categoryId)->getPath();
        }
        return $this->_paths[$categoryId];
    }

    /**
     * Get case sql for define product seo suffix by store
     *
     * @param string $field
     *
     * @return Zend_Db_Expr
     */
    protected function _getProductSeoSuffixCaseSql($field)
    {
        /* @var $store Mage_Core_Model_Store */
        foreach ($this->_app->getStores() as $store) {
            $seoSuffix = $this->_productHelper->getProductUrlSuffix($store->getId());
            $casesResults[$store->getId()] = !empty($seoSuffix) ? '".' . $seoSuffix . '"' : '""';
        }

        return $this->_connection->getCaseSql($field, $casesResults);
    }

    /**
     * Get case sql for define category seo suffix by store
     *
     * @param string $field
     *
     * @return Zend_Db_Expr
     */
    protected function _getCategorySeoSuffixCaseSql($field)
    {
        /* @var $store Mage_Core_Model_Store */
        foreach ($this->_app->getStores() as $store) {
            $seoSuffix = $this->_categoryHelper->getCategoryUrlSuffix($store->getId());
            $casesResults[$store->getId()] = !empty($seoSuffix) ? '".' . $seoSuffix . '"' : '""';
        }

        return $this->_connection->getCaseSql($field, $casesResults);
    }
}
