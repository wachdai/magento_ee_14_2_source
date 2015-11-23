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
 * Product rewrite matcher
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Urlrewrite_Matcher_Product
{
    /**
     * Product resource model
     *
     * @var Enterprise_Catalog_Model_Resource_Product
     */
    protected $_productResource;

    /**
     * Category resource model
     *
     * @var Enterprise_Catalog_Model_Resource_Category
     */
    protected $_categoryResource;

    /**
     * Instance of request
     *
     * @var Mage_Core_Controller_Request_Http
     */
    protected $_request;

    /**
     * Instance of response
     *
     * @var Mage_Core_Controller_Response_Http
     */
    protected $_response;

    /**
     * Store id (current)
     *
     * @var int $_storeId
     */
    protected $_storeId;

    /**
     * Previous store id (or current if store wasn't switched)
     *
     * @var int
     */
    protected $_prevStoreId;

    /**
     * Seo suffix
     *
     * @var string
     */
    protected $_seoSuffix;

    /**
     * New store seo suffix (for redirect when store was switched)
     *
     * @var string $_newStoreSeoSuffix
     */
    protected $_newStoreSeoSuffix;

    /**
     * Config model
     *
     * @var Mage_Core_Model_Config $_config
     */
    protected $_config;

    /**
     * Base store url
     *
     * @var string $_baseUrl
     */
    protected $_baseUrl;

    /**
     * Magento application object
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;


    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_categoryResource = isset($args['categoryResource'])
            ? $args['categoryResource']
            : Mage::getResourceSingleton('enterprise_catalog/category');
        $this->_productResource = isset($args['resource'])
            ? $args['resource']
            : Mage::getResourceSingleton('enterprise_catalog/product');
        $this->_request = !empty($args['request'])
            ? $args['request']
            : Mage::app()->getFrontController()->getRequest();
        $this->_response = !empty($args['response'])
            ? $args['response']
            : Mage::app()->getFrontController()->getResponse();
        $this->_app = !empty($args['app'])
            ? $args['app']
            : Mage::app();

        $this->_storeId = !empty($args['storeId']) ? $args['storeId'] : Mage::app()->getStore()->getId();

        $fromStore = $this->_request->getQuery('___from_store');
        $this->_prevStoreId = isset($args['prevStoreId'])
            ? $args['prevStoreId']
            : (!empty($fromStore) ? Mage::app()->getStore($fromStore)->getId() : $this->_storeId);

        $this->_config = isset($args['config']) ? $args['config'] : Mage::app()->getConfig();

        $this->_seoSuffix = (string) $this->_config->getNode(
            Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_SUFFIX, 'store', (int) $this->_prevStoreId
        );
        $this->_newStoreSeoSuffix = (string) $this->_config->getNode(
            Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_SUFFIX, 'store', (int) $this->_storeId
        );

        if (isset($args['baseUrl'])) {
            $this->_baseUrl = $args['baseUrl'];
        }
    }

    /**
     * Return current base url
     *
     * @return string
     */
    protected function _getBaseUrl()
    {
        if (is_null($this->_baseUrl)) {
            $this->_baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK,
                Mage::app()->getStore()->isCurrentlySecure()
            );
        }
        return $this->_baseUrl;
    }

    /**
     * Match product rewrite
     *
     * @param array $rewriteRow
     * @param string $requestPath
     * @return bool
     */
    public function match(array $rewriteRow, $requestPath)
    {
        if (Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE != $rewriteRow['entity_type']) {
            return false;
        }

        $rewriteParts = explode('/', $rewriteRow['request_path']);
        $rewriteTail = array_pop($rewriteParts);

        if (!empty($this->_seoSuffix)) {
            $rewriteTail .= '.' . $this->_seoSuffix;
        }

        $requestParts = explode('/', $requestPath);
        $requestTail = array_pop($requestParts);

        if (strcmp($rewriteTail, $requestTail) === 0) {

            $categoryPath = implode('/', $requestParts);

            $productId = $this->_productResource->getProductIdByRewrite(
                $rewriteRow['url_rewrite_id'],
                $this->_prevStoreId
            );

            $isMatched = !empty($productId)
                && $this->_isRewriteRedefinedInStore($productId, $rewriteRow['request_path'])
                && $this->_isProductAssignedToCategory($productId, $categoryPath);

            if ($isMatched) {
                $this->_checkStoreRedirect($productId, $categoryPath);
                if (!empty($categoryPath)) {
                    $categoryId = $this->_categoryResource->getCategoryIdByRequestPath(
                        $categoryPath,
                        $this->_storeId
                    );
                    $this->_app->dispatchEvent(
                        'catalog_category_product_fix_category_id',
                        array('category_id' => $categoryId)
                    );
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Check is product assigned to category
     *
     * @param int $productId
     * @param string $categoryPath
     * @return bool
     */
    protected function _isProductAssignedToCategory($productId, $categoryPath)
    {
        return empty($categoryPath) || $this->_productResource->getCountProductCategoriesByRewrite(
            $productId,
            $categoryPath,
            $this->_prevStoreId
        );
    }

    /**
     * Is rewrite redefined on store level
     *
     * @param $productId
     * @param $requestPath
     * @return bool
     */
    protected function _isRewriteRedefinedInStore($productId, $requestPath)
    {
        // Check that url key isn't redefined on store level
        $storeRewriteRow = $this->_productResource->getRewriteByStoreId($this->_prevStoreId, $productId);
        if (!empty($storeRewriteRow) && $storeRewriteRow['request_path'] != $requestPath) {
            return false;
        }
        return true;
    }

    /**
     * Redirect to product from another store if custom url key defined
     *
     * @param int $productId
     * @param string $categoryPath
     */
    protected function _checkStoreRedirect($productId, $categoryPath)
    {
        if ($this->_prevStoreId != $this->_storeId) {
            $rewrite = $this->_productResource->getRewriteByProductId($productId, $this->_storeId);
            if (!empty($rewrite)) {
                $requestPath = $rewrite['request_path'];
                if (!empty($this->_newStoreSeoSuffix)) {
                    $requestPath .= '.' . $this->_newStoreSeoSuffix;
                }
                if (!empty($categoryPath)) {
                    $requestPath = $this->_getNewStoreCategoryPath($categoryPath) . '/' . $requestPath;
                }

                $requestPath = $this->_getBaseUrl() . $requestPath;
                $this->_response->setRedirect($requestPath, 301);
                $this->_request->setDispatched(true);
            }
        }
    }

    /**
     * Get new store category path
     *
     * @param $categoryPath
     * @return string
     */
    protected function _getNewStoreCategoryPath($categoryPath)
    {
        $categoryId = $this->_categoryResource->getCategoryIdByRequestPath($categoryPath, $this->_prevStoreId);
        if (!empty($categoryId)) {
            $rewrite = $this->_categoryResource->getRewriteByCategoryId($categoryId, $this->_storeId);
            if (!empty($rewrite)) {
                $categoryPath = $rewrite['request_path'];
            }
        }
        return $categoryPath;
    }
}
