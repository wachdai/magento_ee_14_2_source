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
 * Category rewrite matcher
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Urlrewrite_Matcher_Category
{
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
     * Config model
     *
     * @var Mage_Core_Model_Config $_config
     */
    protected $_config;

    /**
     * Seo suffix
     *
     * @var string $_seoSuffix
     */
    protected $_seoSuffix;

    /**
     * New store seo suffix (for redirect when store was switched)
     *
     * @var string $_newStoreSeoSuffix
     */
    protected $_newStoreSeoSuffix;

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
        $this->_categoryResource = isset($args['resource'])
            ? $args['resource']
            : Mage::getResourceSingleton('enterprise_catalog/category');
        $this->_request = !empty($args['request'])
            ? $args['request']
            : Mage::app()->getFrontController()->getRequest();
        $this->_response = !empty($args['response'])
            ? $args['response']
            : Mage::app()->getFrontController()->getResponse();

        $this->_storeId = isset($args['storeId']) ? $args['storeId'] : Mage::app()->getStore()->getId();

        $fromStore = $this->_request->getQuery('___from_store');
        $this->_prevStoreId = isset($args['prevStoreId'])
            ? $args['prevStoreId']
            : (!empty($fromStore) ? Mage::app()->getStore($fromStore)->getId() : $this->_storeId);

        $this->_config = isset($args['config']) ? $args['config'] : Mage::app()->getConfig();

        $this->_seoSuffix = (string) $this->_config->getNode(
            Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_URL_SUFFIX, 'store', (int) $this->_prevStoreId
        );
        $this->_newStoreSeoSuffix = (string) $this->_config->getNode(
            Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_URL_SUFFIX, 'store', (int) $this->_storeId
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
     * Match category rewrite
     *
     * @param array $rewriteRow
     * @param string $requestPath
     * @return bool
     */
    public function match(array $rewriteRow, $requestPath)
    {
        if (Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE != $rewriteRow['entity_type']) {
            return false;
        }

        if ($rewriteRow['store_id'] != $this->_prevStoreId) {
            return false;
        }

        $rewritePath = $rewriteRow['request_path'];
        if (!empty($this->_seoSuffix)) {
            $rewritePath .= '.' . $this->_seoSuffix;
        }
        if ($rewritePath == $requestPath) {
            $this->_checkStoreRedirect($rewriteRow['url_rewrite_id']);
            return true;
        }
        return false;
    }

    /**
     * Redirect to category from another store if custom url key defined
     *
     * @param int $rewriteId
     */
    protected function _checkStoreRedirect($rewriteId)
    {
        if ($this->_prevStoreId != $this->_storeId) {
            $categoryId = $this->_categoryResource->getCategoryIdByRewriteId($rewriteId);
            if (!empty($categoryId)) {
                $rewrite = $this->_categoryResource->getRewriteByCategoryId($categoryId, $this->_storeId);
                if (!empty($rewrite)) {
                    $requestPath = $rewrite['request_path'];
                    if (!empty($this->_newStoreSeoSuffix)) {
                        $requestPath .= '.' . $this->_newStoreSeoSuffix;
                    }

                    $requestPath = $this->_getBaseUrl() . $requestPath;
                    $this->_response->setRedirect($requestPath, 301);
                    $this->_request->setDispatched(true);
                }
            }
        }
    }
}
