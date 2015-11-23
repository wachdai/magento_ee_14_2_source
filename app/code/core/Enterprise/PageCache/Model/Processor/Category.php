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
 * @package     Enterprise_PageCache
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_PageCache_Model_Processor_Category extends Enterprise_PageCache_Model_Processor_Default
{
    /**
     * Key for saving category id in metadata
     */
    const METADATA_CATEGORY_ID = 'catalog_category_id';

    protected $_paramsMap = array(
        'display_mode'  => 'mode',
        'limit_page'    => 'limit',
        'sort_order'    => 'order',
        'sort_direction'=> 'dir',
    );

    /**
     * Query params
     *
     * @var string
     */
    protected $_queryParams;

    /**
     * Return cache page id with application. Depends on catalog session and GET super global array.
     *
     * @param Enterprise_PageCache_Model_Processor $processor
     * @return string
     */
    public function getPageIdInApp(Enterprise_PageCache_Model_Processor $processor)
    {
        $queryParams = $this->_getQueryParams();

        Enterprise_PageCache_Model_Cookie::setCategoryCookieValue($queryParams);
        $this->_prepareCatalogSession();

        $category = Mage::helper('catalog')->getCategory();
        if ($category) {
            $processor->setMetadata(self::METADATA_CATEGORY_ID, $category->getId());
            $this->_updateCategoryViewedCookie($processor);
        }
        $pageId = $processor->getRequestId() . '_' . md5($queryParams);

        return $this->_appendCustomerRatesToPageId($pageId);
    }

    /**
     * Return cache page id without application. Depends on GET super global array.
     *
     * @param Enterprise_PageCache_Model_Processor $processor
     * @return string
     */
    public function getPageIdWithoutApp(Enterprise_PageCache_Model_Processor $processor)
    {
        $this->_updateCategoryViewedCookie($processor);
        $queryParams = $_GET;

        $sessionParams = Enterprise_PageCache_Model_Cookie::getCategoryCookieValue();
        if ($sessionParams) {
            $sessionParams = (array)json_decode($sessionParams);
            foreach ($sessionParams as $key => $value) {
                if (in_array($key, $this->_paramsMap) && !isset($queryParams[$key])) {
                    $queryParams[$key] = $value;
                }
            }
        }
        ksort($queryParams);
        $queryParams = json_encode($queryParams);

        Enterprise_PageCache_Model_Cookie::setCategoryCookieValue($queryParams);

        $pageId = $processor->getRequestId() . '_' . md5($queryParams);

        return $this->_appendCustomerRatesToPageId($pageId);
    }

    /**
     * Check if request can be cached
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function allowCache(Zend_Controller_Request_Http $request)
    {
        $res = parent::allowCache($request);
        if ($res) {
            $params = $this->_getSessionParams();
            $queryParams = $request->getQuery();
            $queryParams = array_merge($queryParams, $params);
            $maxDepth = Mage::getStoreConfig(Enterprise_PageCache_Model_Processor::XML_PATH_ALLOWED_DEPTH);
            $res = count($queryParams)<=$maxDepth;
        }
        return $res;
    }

    /**
     * Get page view related parameters from session mapped to wuery parametes
     * @return array
     */
    protected function _getSessionParams()
    {
        $params = array();
        $data   = Mage::getSingleton('catalog/session')->getData();
        foreach ($this->_paramsMap as $sessionParam => $queryParam) {
            if (isset($data[$sessionParam])) {
                $params[$queryParam] = $data[$sessionParam];
            }
        }
        return $params;
    }

    /**
     * Update catalog session from GET or cookies
     *
     * @param string $queryParams
     */
    protected function _prepareCatalogSession()
    {
        $queryParams = json_decode($this->_getQueryParams(), true);
        if (empty($queryParams)) {
            $queryParams = Enterprise_PageCache_Model_Cookie::getCategoryCookieValue();
            $queryParams = json_decode($queryParams, true);
        }

        if (is_array($queryParams) && !empty($queryParams)) {
            $session = Mage::getSingleton('catalog/session');
            $flipParamsMap = array_flip($this->_paramsMap);
            foreach ($queryParams as $key => $value) {
                if (in_array($key, $this->_paramsMap)) {
                    $session->setData($flipParamsMap[$key], $value);
                }
            }
        }
    }

    /**
     * Return merged session and GET params
     *
     * @return string
     */
    protected function _getQueryParams()
    {
        if (is_null($this->_queryParams)) {
            $queryParams = array_merge($this->_getSessionParams(), $_GET);
            ksort($queryParams);
            $this->_queryParams = json_encode($queryParams);
        }

        return $this->_queryParams;
    }

    /**
     * Update last visited category id cookie
     *
     * @param Enterprise_PageCache_Model_Processor $processor
     * @return Enterprise_PageCache_Model_Processor_Category
     */
    protected function _updateCategoryViewedCookie(Enterprise_PageCache_Model_Processor $processor)
    {
        Enterprise_PageCache_Model_Cookie::setCategoryViewedCookieValue(
            $processor->getMetadata(self::METADATA_CATEGORY_ID)
        );
        return $this;
    }
}
