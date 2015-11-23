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
 * @category    Mage
 * @package     Mage_Shell
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

if (strtolower(php_sapi_name()) != 'cli') {
    exit(0);
}

require dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit(0);
}

Mage::app('admin', 'store')->setUseSessionInUrl(false);

/**
 * Migration class
 */
class Mage_Migration {

    /**
     * Entity types
     */
    const ENTITY_TYPE_PRODUCT = 'product';
    const ENTITY_TYPE_CATEGORY = 'category';

    /**
     * Connection
     *
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * @var Mage_Core_Model_Resource
     */
    protected $_resource;

    /**
     * Entity tables
     *
     * @var array
     */
    protected $_entityMigrationTables = array (
        self::ENTITY_TYPE_PRODUCT  => 'migration_processed_products',
        self::ENTITY_TYPE_CATEGORY => 'migration_processed_categories'
    );

    /**
     * Constructor
     *
     * @param Mage_Core_Model_Resource $resource
     */
    public function __construct(Mage_Core_Model_Resource $resource = null)
    {
        if ($resource instanceof Mage_Core_Model_Resource) {
            $this->_resource = $resource;
        } else {
            $this->_resource = Mage::getModel('core/resource');
        }
        $this->_connection = $this->_resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
    }

    /**
     * Get entity migration log table
     *
     * @param string $type
     * @return string|mixed
     * @throws Exception
     */
    public function getEntityMigrationTable($type)
    {
        if (!isset($this->_entityMigrationTables[$type])) {
            throw new Exception("Unknown entity type: $type");
        }
        return $this->_entityMigrationTables[$type];
    }

    /**
     * Get migration connection
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Get resource model
     *
     * @return Mage_Core_Model_Resource
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     * Retrieve entity instance
     * It may be product or category instance, it depends on data in given parameter.
     *
     * @param array $row
     * @return Mage_Catalog_Model_Category|Mage_Catalog_Model_Product
     */
    function getOriginalEntity($row)
    {

        if (!empty($row['category_id'])) {
            $category = Mage::getModel('catalog/category', array('disable_flat' => true))
                ->setStoreId($row['store_id'])
                ->load($row['category_id']);
        }

        if (!empty($row['product_id'])) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId($row['store_id'])
                ->load($row['product_id']);

            if (isset($category)) {
                Mage::register('current_category', $category);
                $product->getRequestPath();
                Mage::unregister('current_category');
            }
            return $product;
        }
        return isset($category) ? $category : null;
    }

    /**
     * Retrieve product/category URL suffix
     *
     * @param array $row
     * @return null|string
     */
    function getSuffixForOriginalEntity($row)
    {
        if (!empty($row['product_id'])) {
            return Mage::app()->getStore($row['store_id'])
                ->getConfig(Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_SUFFIX);
        }

        return Mage::app()->getStore($row['store_id'])
            ->getConfig(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_URL_SUFFIX);
    }

    /**
     * Create redirect for given entity
     *
     * @param array $row
     *
     * @return bool
     */
    function createRedirectForOriginal($row)
    {
        $originalEntity = $this->getOriginalEntity($row);
        $suffix = $this->getSuffixForOriginalEntity($row);

        if (is_null($originalEntity)) {
            return false;
        }

        $entityUrl = (empty($suffix) || $suffix == '/') ? $originalEntity->getRequestPath()
            : $originalEntity->getRequestPath() . '.' . $suffix;

        if ($entityUrl == $row['request_path']) {
            return false;
        }

        try {
            $redirect = Mage::getSingleton('enterprise_urlrewrite/redirect')
                ->setRedirectId(null)
                ->setTargetPath($entityUrl)
                ->setIdentifier($row['request_path'])
                ->setStoreId($row['store_id'])
                ->setOptions('RP')
                ->setDescription('1.12.0.2-1.13.x migration redirect');

            if ($redirect->isCircular()) {
                $processorMethod = $originalEntity instanceof Mage_Catalog_Model_Product
                    ? 'processProduct'
                    : 'processCategory';
                $mock = array();
                $this->$processorMethod(
                    $originalEntity,
                    $originalEntity->getUrlKey(),
                    $originalEntity->getStoreId(),
                    $mock
                );
                $entityUrl = (empty($suffix) || $suffix == '/') ? $originalEntity->getRequestPath()
                    : $originalEntity->getRequestPath() . '.' . $suffix;
                $redirect->setTargetPath($entityUrl);
            }
            $redirect->save();
            $client = Mage::getModel('enterprise_mview/client');
            $client->init('enterprise_url_rewrite_redirect');
            $client->execute('enterprise_urlrewrite/index_action_url_rewrite_redirect_refresh_row',
                array('redirect_id' => $redirect->getId())
            );

        } catch (Exception $e) {}

        return true;
    }

    /**
     * Process url rewrite
     *
     * @param array $rewriteInfo
     * @param Enterprise_UrlRewrite_Model_Url_Rewrite_Request $rewriteRequest
     * @param Enterprise_UrlRewrite_Model_Url_Rewrite $rewrite
     * @param array $matchers
     * @param Mage_Core_Model_Resource $resource
     * @return bool
     */
    function processRewrite($rewriteInfo, $rewriteRequest, $rewrite, $matchers, $resource) {
        $result = false;
        Mage::app()->setCurrentStore(Mage::app()->getStore($rewriteInfo['store_id']));
        $requestPath = $rewriteInfo['request_path'];
        $paths = $rewriteRequest->getSystemPaths($requestPath);
        $rewriteRows = $rewrite->getResource()->getRewrites($paths);
        foreach ($matchers as $matcherIndex) {
            $matcher = Mage::getModel(Mage::getConfig()->getNode(
                sprintf(Enterprise_UrlRewrite_Model_Url_Rewrite::REWRITE_MATCHERS_MODEL_PATH, $matcherIndex), 'default'
            ));
            foreach ($rewriteRows as $row) {
                if ($matcher->match($row, $paths['request'])) {
                    switch ($row['entity_type']) {
                        case Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE:
                            $targetProductId = $this->getProductId($resource, $row);

                            if ($targetProductId != $rewriteInfo['product_id']) {
                                $targetProduct = Mage::getModel('catalog/product')
                                    ->setStoreId($rewriteInfo['store_id'])
                                    ->load($targetProductId);

                                $this->processProduct(
                                    $targetProduct,
                                    $targetProduct->getUrlKey(),
                                    $targetProduct->getStoreId()
                                );
                                $result = true;
                            }
                            break;

                        case Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE:
                            $categoryId = $this->getCategoryId($resource, $row);


                            if ($categoryId != $rewriteInfo['category_id']) {
                                $category = Mage::getModel('catalog/category', array('disable_flat' => true))
                                    ->setStoreId($rewriteInfo['store_id'])
                                    ->load($categoryId);

                                $this->processCategory($category, $category->getUrlKey(), $category->getStoreId());
                                $result = true;
                            }
                            break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Update product url key
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $newUrlKey
     * @param int $storeId
     */
    function processProduct($product, $newUrlKey, $storeId)
    {
        $store = Mage::app()->getStore();
        Mage::app()->setCurrentStore(Mage::app()->getStore(0));
        if (!$this->isEntityProcessed(self::ENTITY_TYPE_PRODUCT, $product->getId() . '-' . $storeId)
            && !preg_match('~-[a-f0-9]{32}$~i', $newUrlKey)
        ) {
            $product->setStoreId($storeId ? $storeId : null);
            $product->setUrlKey($newUrlKey . '-' . md5($product->getStoreId() . $product->getId()));
            $product->save();

            $this->_reindexProduct($product->getId());

            $product->unsetData('request_path');
            $product->unsetData('url');

            $this->markEntityProcessed(self::ENTITY_TYPE_PRODUCT, $product->getId() . '-' . $product->getStoreId());
        }
        Mage::app()->setCurrentStore($store);
    }

    /**
     * Update category url key
     *
     * @param Mage_Catalog_Model_Category $category
     * @param string $newUrlKey
     * @param int $storeId
     */
    function processCategory($category, $newUrlKey, $storeId)
    {
        $store = Mage::app()->getStore();
        Mage::app()->setCurrentStore(Mage::app()->getStore(0));
        if (!$this->isEntityProcessed(self::ENTITY_TYPE_CATEGORY, $category->getId() . '-' . $storeId)
            && !preg_match('~-[a-f0-9]{32}$~i', $newUrlKey)
        ) {
            $category->setStoreId($storeId);
            $category->setUrlKey($newUrlKey . '-' . md5($category->getStoreId() . $category->getId()));
            $category->save();

            $this->_reindexCategory($category->getId());

            $category->unsetData('request_path');
            $category->unsetData('url');

            $this->markEntityProcessed(self::ENTITY_TYPE_CATEGORY, $category->getId() . '-' . $category->getStoreId());
        }
        Mage::app()->setCurrentStore($store);
    }

    /**
     * Reindex category urls with dead locks handling
     *
     * @param $categoryId
     * @throws Exception
     */
    protected function _reindexCategory($categoryId)
    {
        try {
            $client = Mage::getModel('enterprise_mview/client')->init('enterprise_url_rewrite_category');
            $client->execute('enterprise_catalog/index_action_url_rewrite_category_refresh_row', array(
                'category_id' => $categoryId
            ));
        } catch (Exception $e) {
            if (false !== strpos($e->getMessage(), 'Deadlock found')) {
                sleep(mt_rand(1,3));
                $this->_reindexCategory($categoryId);
            } else {
                throw $e;
            }

        }
    }

    /**
     * Reindex product urls with dead locks handling
     *
     * @param $productId
     * @throws Exception
     */
    protected function _reindexProduct($productId)
    {
        try {
            $client = Mage::getModel('enterprise_mview/client')->init('enterprise_url_rewrite_product');
            $client->execute('enterprise_catalog/index_action_url_rewrite_product_refresh_row', array(
                'product_id' => $productId
            ));
        } catch (Exception $e) {
            if (false !== strpos($e->getMessage(), 'Deadlock found')) {
                sleep(mt_rand(1,3));
                $this->_reindexProduct($productId);
            } else {
                throw $e;
            }
        }
    }


    /**
     * Retrieve product instance by specified parameters
     *
     * @param Mage_Core_Model_Resource $resource
     * @param array $row
     * @return int
     */
    function getProductId($resource, $row)
    {
        $select = $this->_connection->select()
            ->from(array('nur' => $resource->getTableName('enterprise_url_rewrite')))
            ->join(
                array('e' => $resource->getTableName(array('catalog/product', 'url_key'))),
                'nur.value_id = e.value_id'
            )->where('nur.url_rewrite_id = ' . $row['url_rewrite_id']);

        $entityRow = $this->_connection->fetchRow($select);
        return $entityRow['entity_id'];
    }

    /**
     * Retrieve category instance by specified parameters
     *
     * @param Mage_Core_Model_Resource $resource
     * @param array $row
     * @return int
     */
    function getCategoryId($resource, $row)
    {
        $select = $this->_connection->select()
            ->from(array('nur' => $resource->getTableName('enterprise_url_rewrite')))
            ->join(
                array('e' => $resource->getTableName(array('catalog/category', 'url_key'))),
                'nur.value_id = e.value_id'
            )
            ->where('nur.url_rewrite_id = ' . $row['url_rewrite_id']);

        $entityRow = $this->_connection->fetchRow($select);
        return $entityRow['entity_id'];
    }

    /**
     * Check is entity processed
     *
     * @param $type string category|product
     * @param $id   string
     * @return bool
     */
    function isEntityProcessed($type, $id)
    {
        $select = $this->_connection->select()
            ->from($this->getEntityMigrationTable($type))
            ->where('id = ?', $id);
        return (bool)count($this->_connection->fetchAll($select));
    }

    /**
     * Mark entity as processed
     *
     * @param $type
     * @param $id
     * @return int
     */
    function markEntityProcessed($type, $id)
    {
        return $this->_connection->insert($this->getEntityMigrationTable($type), array('id' => $id));
    }
}

/**
 * Category redirect migration processor
 */
class Mage_Migration_Category_Redirect
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
     * Connection
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
     * Product rewrite type
     */
    const PRODUCT_URL_REWRITE_ENTITY_TYPE = 3;

    /**
     * Category rewrite type
     */
    const CATEGORY_URL_REWRITE_ENTITY_TYPE = 2;

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
        $this->_createRedirectTemporaryTable();
    }

    /**
     * Save custom redirects when url key was changed (for category, children and assigned products)
     *
     * @param Mage_Catalog_Model_Category $category
     * @param int $storeId
     */
    public function saveCustomRedirects($category, $storeId)
    {
        $this->_insertProductRedirectsToTemporaryTable($category, $storeId);
        $this->_insertCategoryRedirectsToTemporaryTable($category);
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
        $this->_connection->dropTemporaryTable(self::TMP_TABLE_NAME);

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
            ->addIndex(
                'UNQ_ENTERPRISE_URL_REWRITE_TMP_IDENTIFIER_STORE_ID',
                array('identifier'),
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
     * @return null|void
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
            return null;
        }

        // Select product redirects by stores (ignore redefined on store level values)
        $select = $this->_getProductsRedirectsSelect($category, $availableStores);
        $query = $select->insertFromSelect(
            self::TMP_TABLE_NAME,
            array('identifier', 'target_path'),
            Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE
        );
        $this->_connection->query($query);

        // Select product redirects redefined on store level
        $select = $this->_getProductsRedirectsSelect($category, $availableStores);
        $query = $select->insertFromSelect(
            self::TMP_TABLE_NAME,
            array('identifier', 'target_path'),
            Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE
        );
        $this->_connection->query($query);
    }

    /**
     * Get products redirects select
     *
     * @param Mage_Catalog_Model_Category $category
     * @param array $availableStores
     *
     * @return Varien_Db_Select
     */
    protected function _getProductsRedirectsSelect($category, $availableStores)
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
            )
        )
            ->joinInner(
                array('ce' => $this->_resource->getTableName('catalog/category')),
                'ce.entity_id = cp.category_id',
                array('')
            )
            ->joinInner(
                array('cr' => $this->_resource->getTableName('enterprise_catalog/category')),
                'cp.category_id = cr.category_id',
                array('')
            )
            ->joinInner(
                array('pr' => $this->_resource->getTableName('enterprise_catalog/product')),
                'cp.product_id = pr.product_id',
                array('')
            )
            ->joinInner(
                array('urp' => $this->_resource->getTableName('enterprise_urlrewrite/url_rewrite')),
                'pr.url_rewrite_id = urp.url_rewrite_id',
                array('')
            )
            ->joinInner(
                array('urc' => $this->_resource->getTableName('enterprise_urlrewrite/url_rewrite')),
                'cr.url_rewrite_id = urc.url_rewrite_id',
                array('')
            )
            ->where($categoryExpr)
            ->where('ce.level > 1');

        return $select;
    }

    /**
     * Fill temporary table with category (and children) redirects
     *
     * @param Mage_Catalog_Model_Category $category
     *
     * @return void
     */
    protected function _insertCategoryRedirectsToTemporaryTable($category)
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
            )
        )
            ->joinInner(
                array('ce' => $this->_resource->getTableName('catalog/category')),
                'ce.entity_id = cr.category_id',
                array('')
            )
            ->joinInner(
                array('urc' => $this->_resource->getTableName('enterprise_urlrewrite/url_rewrite')),
                'cr.url_rewrite_id = urc.url_rewrite_id',
                array('')
            )
            ->where($categoryExpr)
            ->where('ce.level > 1');

        $query = $select->insertFromSelect(
            self::TMP_TABLE_NAME,
            array('identifier', 'target_path'),
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
        $select->from(
            array('t' => self::TMP_TABLE_NAME),
            array(
                'identifier',
                'target_path',
                new Zend_Db_Expr('NULL'),
            )
        );
        $query = $select->insertFromSelect(
            $this->_resource->getTableName('enterprise_urlrewrite/redirect'),
            array('identifier', 'target_path', 'options'),
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
        $casesResults = array();
        /* @var $store Mage_Core_Model_Store */
        foreach ($this->_app->getStores(true) as $store) {
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
        $casesResults = array();
        /* @var $store Mage_Core_Model_Store */
        foreach ($this->_app->getStores(true) as $store) {
            $seoSuffix = $this->_categoryHelper->getCategoryUrlSuffix($store->getId());
            $casesResults[$store->getId()] = !empty($seoSuffix) ? '".' . $seoSuffix . '"' : '""';
        }

        return $this->_connection->getCaseSql($field, $casesResults);
    }
}
