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
 * Class for HTTP request rewrite
 *
 * @category    Enterprise
 * @package     Enterprise_UrlRewrite
 * @author      Magento Core Team <core@magentocommerce.com>
 * @property Enterprise_UrlRewrite_Model_Url_Rewrite _rewrite
 */
class Enterprise_UrlRewrite_Model_Url_Rewrite_Request extends Mage_Core_Model_Url_Rewrite_Request
{

    /**
     * Set URL rewrite instance
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        if (empty($args['rewrite'])) {
            $args['rewrite'] = Mage::getModel('enterprise_urlrewrite/url_rewrite');
        }
        parent::__construct($args);
    }

    /**
     * Implement logic of custom rewrites
     *
     * @return bool
     */
    protected function _rewriteDb()
    {
        $this->_loadRewrite();

        if (!$this->_rewrite->getId()) {
            return false;
        }

        $this->_setRequestPathAlias()
            ->_processRedirectOptions();

        return true;
    }

    /**
     * Set request path alias to request model
     *
     * @return Enterprise_UrlRewrite_Model_Url_Rewrite_Request
     */
    protected function _setRequestPathAlias()
    {
        $requestAlias = $this->_rewrite->getRequestPath();
        switch ($this->_rewrite->getEntityType()) {
            case Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE:
                $seoSuffix = (string) Mage::app()->getStore()->getConfig(
                    Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_SUFFIX
                );
                if (!empty($seoSuffix)) {
                    $requestAlias .= '.' . $seoSuffix;
                }
                break;
            case Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE:
                $seoSuffix = (string) Mage::app()->getStore()->getConfig(
                    Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_URL_SUFFIX
                );
                if (!empty($seoSuffix)) {
                    $requestAlias .= '.' . $seoSuffix;
                }
                break;
        }
        $this->_request->setAlias(
            Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
            $requestAlias
        );
        return $this;
    }

    /**
     * Load rewrite model
     *
     * @return Enterprise_UrlRewrite_Model_Url_Rewrite_Request
     */
    protected function _loadRewrite()
    {
        $requestPath = $this->_getRequestPath();

        $paths = $this->_getSystemPaths($requestPath);
        if (count($paths)) {
            $this->_rewrite->loadByRequestPath($paths);
        }

        if ($this->_rewrite->getId() && !$this->_rewrite->getIsSystem()) {
            /**
             * Try to load data by request path from redirect model
             */
            $this->_rewrite->setData(
                $this->_getRedirect($requestPath, $this->_rewrite->getStoreId())->getData()
            );
        }

        return $this;
    }

    /**
     * Get redirect model with load data by request path
     *
     * @param string $requestPath
     * @param int $storeId
     * @return Enterprise_UrlRewrite_Model_Redirect
     */
    protected function _getRedirect($requestPath, $storeId)
    {
        /** @var $redirect Enterprise_UrlRewrite_Model_Redirect */
        $redirect = $this->_factory->getModel('enterprise_urlrewrite/redirect');
        $redirect->loadByRequestPath($requestPath, $storeId);

        return $redirect;
    }

    /**
     * Return request path pieces
     *
     * @param string $requestPath
     * @return array
     */
    public function getSystemPaths($requestPath)
    {
        return $this->_getSystemPaths($requestPath);
    }


    /**
     * Get system path from request path
     *
     * @param string $requestPath
     * @return array
     */
    protected function _getSystemPaths($requestPath)
    {
        $parts = explode('/', $requestPath);
        $suffix = array_pop($parts);
        if (false !== strrpos($suffix, '.')) {
            $suffix = substr($suffix, 0, strrpos($suffix, '.'));
        }
        $paths = array('request' => $requestPath, 'suffix' => $suffix);
        if (count($parts)) {
            $paths['whole'] = implode('/', $parts) . '/' . $suffix;
        }
        return $paths;
    }

    /**
     * Get request path from requested path info
     *
     * @return string
     */
    protected function _getRequestPath()
    {
        $pathInfo    = $this->_request->getPathInfo();
        $requestPath = trim($pathInfo, '/');

        return $requestPath;
    }
}
