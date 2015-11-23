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

class Enterprise_PageCache_Model_Processor_Default
{
    /**
     * @var Enterprise_PageCache_Model_Container_Placeholder
     */
    private $_placeholder;

    /**
     * Disable cache for url with next GET params
     *
     * @var array
     */
    protected $_noCacheGetParams = array('___store', '___from_store');

    /**
     * Is cache allowed
     *
     * @var null|boll
     */
    protected $_allowCache = null;

    /**
     * Check if request can be cached
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function allowCache(Zend_Controller_Request_Http $request)
    {
        if (is_null($this->_allowCache)) {
            foreach ($this->_noCacheGetParams as $param) {
                if (!is_null($request->getParam($param, null))) {
                    $this->_allowCache = false;
                    return $this->_allowCache;
                }
            }
            if (Mage::getSingleton('core/session')->getNoCacheFlag()) {
                $this->_allowCache = false;
                return $this->_allowCache;
            }
            $this->_allowCache = true;
            return $this->_allowCache;
        }
        return $this->_allowCache;
    }


    /**
     * Replace block content to placeholder replacer
     *
     * @param string $content
     * @return string
     */
    public function replaceContentToPlaceholderReplacer($content)
    {
        $placeholders = array();
        preg_match_all(
            Enterprise_PageCache_Model_Container_Placeholder::HTML_NAME_PATTERN,
            $content,
            $placeholders,
            PREG_PATTERN_ORDER
        );
        $placeholders = array_unique($placeholders[1]);
        try {
            foreach ($placeholders as $definition) {
                $this->_placeholder = Mage::getModel('enterprise_pagecache/container_placeholder', $definition);
                $content = preg_replace_callback($this->_placeholder->getPattern(),
                    array($this, '_getPlaceholderReplacer'), $content);
            }
            $this->_placeholder = null;
        } catch (Exception $e) {
            $this->_placeholder = null;
            throw $e;
        }
        return $content;
    }

    /**
     * Prepare response body before caching
     *
     * @param Zend_Controller_Response_Http $response
     * @return string
     */
    public function prepareContent(Zend_Controller_Response_Http $response)
    {
        return $this->replaceContentToPlaceholderReplacer($response->getBody());
    }

    /**
     * Retrieve placeholder replacer
     *
     * @param array $matches Matches by preg_replace_callback
     * @return string
     */
    protected function _getPlaceholderReplacer($matches)
    {
        return $this->_placeholder->getReplacer();
    }


    /**
     * Return cache page id with application. Depends on GET super global array.
     *
     * @param Enterprise_PageCache_Model_Processor $processor
     * @param Zend_Controller_Request_Http $request
     * @return string
     */
    public function getPageIdInApp(Enterprise_PageCache_Model_Processor $processor)
    {
        return $this->getPageIdWithoutApp($processor);
    }

    /**
     * Return cache page id without application. Depends on GET super global array.
     *
     * @param Enterprise_PageCache_Model_Processor $processor
     * @return string
     */
    public function getPageIdWithoutApp(Enterprise_PageCache_Model_Processor $processor)
    {
        $queryParams = $_GET;
        ksort($queryParams);
        $queryParamsHash = md5(serialize($queryParams));
        return $processor->getRequestId() . '_' . $queryParamsHash;
    }

    /**
     * Append customer rates cookie to page id
     *
     * @param string $pageId
     * @return string
     */
    protected function _appendCustomerRatesToPageId($pageId)
    {
        if (isset($_COOKIE[Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER_RATES])) {
            $pageId .= '_' . $_COOKIE[Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER_RATES];
        }
        return $pageId;
    }

    /**
     * Get request uri based on HTTP request uri and visitor session state
     *
     * @deprecated after 1.8
     * @param Enterprise_PageCache_Model_Processor $processor
     * @param Zend_Controller_Request_Http $request
     * @return string
     */
    public function getRequestUri(Enterprise_PageCache_Model_Processor $processor,
        Zend_Controller_Request_Http $request
    ) {
    }
}
