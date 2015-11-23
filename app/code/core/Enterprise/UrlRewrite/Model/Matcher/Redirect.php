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
 * @package     Enterprise_UrlRewrite
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Redirect matcher
 *
 * @category    Enterprise
 * @package     Enterprise_UrlRewrite
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_UrlRewrite_Model_Matcher_Redirect
{
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
     * Store id
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
     * Category resource model
     *
     * @var Enterprise_Catalog_Model_Resource_Category
     */
    protected $_categoryResource;

    /**
     * Product resource model
     *
     * @var Enterprise_Catalog_Model_Resource_Product
     */
    protected $_productResource;


    /**
     * Redirect resource model
     *
     * @var Enterprise_Urlrewrite_Model_Resource_Redirect
     */
    protected $_redirectResource;

    /**
     * Config model
     *
     * @var Mage_Core_Model_Config $_config
     */
    protected $_config;

    /**
     * New product store seo suffix (for redirect when store was switched)
     *
     * @var string $_newStoreSeoSuffix
     */
    protected $_newProductStoreSeoSuffix;

    /**
     * New category store seo suffix (for redirect when store was switched)
     *
     * @var string $_newStoreSeoSuffix
     */
    protected $_newCategoryStoreSeoSuffix;

    /**
     * Base store url
     *
     * @var string $_baseUrl
     */
    protected $_baseUrl = null;

    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_storeId = !empty($args['storeId']) ? $args['storeId'] : Mage::app()->getStore()->getId();
        $this->_request = !empty($args['request'])
            ? $args['request']
            : Mage::app()->getFrontController()->getRequest();
        $this->_response = !empty($args['response'])
            ? $args['response']
            : Mage::app()->getFrontController()->getResponse();
        $fromStore = $this->_request->getQuery('___from_store');
        $this->_prevStoreId = isset($args['prevStoreId'])
            ? $args['prevStoreId']
            : (!empty($fromStore) ? Mage::app()->getStore($fromStore)->getId() : $this->_storeId);

        $this->_categoryResource = isset($args['categoryResource'])
            ? $args['categoryResource']
            : Mage::getResourceSingleton('enterprise_catalog/category');
        $this->_productResource = isset($args['productResource'])
            ? $args['productResource']
            : Mage::getResourceSingleton('enterprise_catalog/product');
        $this->_redirectResource = isset($args['redirectResource'])
            ? $args['redirectResource']
            : Mage::getResourceSingleton('enterprise_urlrewrite/redirect');

        $this->_config = isset($args['config']) ? $args['config'] : Mage::app()->getConfig();

        $this->_newProductStoreSeoSuffix = (string) $this->_config->getNode(
            Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_SUFFIX, 'store', (int) $this->_storeId
        );
        $this->_newCategoryStoreSeoSuffix = (string) $this->_config->getNode(
            Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_URL_SUFFIX, 'store', (int) $this->_storeId
        );
        if (isset($args['baseUrl'])) {
            $this->_baseUrl =  $args['baseUrl'];
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
     * Match redirect rewrite
     *
     * @param array $rewriteRow
     * @param string $requestPath
     * @return bool
     */
    public function match(array $rewriteRow, $requestPath)
    {
        if (Enterprise_UrlRewrite_Model_Redirect::URL_REWRITE_ENTITY_TYPE != $rewriteRow['entity_type']) {
            return false;
        }

        if ($rewriteRow['store_id'] != $this->_prevStoreId
            && $rewriteRow['store_id'] != Mage_Core_Model_App::ADMIN_STORE_ID)
        {
            return false;
        }

        if ($rewriteRow['request_path'] == $requestPath) {
            $this->_checkStoreRedirect($rewriteRow['url_rewrite_id']);
            return true;
        }
        return false;
    }

    /**
     * Redirect to entity (category or product) in new store
     *
     * @param int $rewriteId
     *
     * @return null|void
     */
    public function _checkStoreRedirect($rewriteId)
    {
        if ($this->_prevStoreId == $this->_storeId) {
            return;
        }

        $redirect = $this->_redirectResource->getRedirectByRewriteId($rewriteId);
        if (!empty($redirect['product_id'])) {
            $requestPath = $this->_getProductRequestPath($redirect, $redirect['category_id']);
        } elseif (!empty($redirect['category_id'])) {
            $requestPath = $this->_getCategoryRequestPath($redirect);
        }

        if (!empty($requestPath)) {
            $this->_response->setRedirect($requestPath, 301);
            $this->_request->setDispatched(true);
        }
    }

    /**
     * Get product rewrite path in new store
     *
     * @param array $redirect
     * @param int $categoryId
     * @return string
     */
    protected function _getProductRequestPath($redirect, $categoryId)
    {
        $requestPath = '';
        $rewrite = $this->_productResource->getRewriteByProductId($redirect['product_id'], $this->_storeId);
        if ($rewrite) {
            $requestPath = $rewrite['request_path'];
            if (!empty($this->_newProductStoreSeoSuffix)) {
                $requestPath .= '.' . $this->_newProductStoreSeoSuffix;
            }
            if (!empty($categoryId)) {
                $requestPath = $this->_getNewStoreCategoryPath($categoryId) . '/' . $requestPath;
            }
            $requestPath = $this->_getBaseUrl() . $requestPath;
        }
        return $requestPath;
    }

    /**
     * Get category rewrite path in new store
     *
     * @param array $redirect
     * @return string
     */
    protected function _getCategoryRequestPath($redirect)
    {
        $requestPath = '';
        $rewrite = $this->_categoryResource->getRewriteByCategoryId($redirect['category_id'], $this->_storeId);
        if (!empty($rewrite)) {
            $requestPath = $rewrite['request_path'];
            if (!empty($this->_newCategoryStoreSeoSuffix)) {
                $requestPath .= '.' . $this->_newCategoryStoreSeoSuffix;
            }
            $requestPath = $this->_getBaseUrl() . $requestPath;
        }
        return $requestPath;
    }

    /**
     * Get new store category path
     *
     * @param int $categoryId
     * @return string
     */
    protected function _getNewStoreCategoryPath($categoryId)
    {
        $categoryPath = '';
        if (!empty($categoryId)) {
            $rewrite = $this->_categoryResource->getRewriteByCategoryId($categoryId, $this->_storeId);
            if (!empty($rewrite)) {
                $categoryPath = $rewrite['request_path'];
            }
        }
        return $categoryPath;
    }
}
