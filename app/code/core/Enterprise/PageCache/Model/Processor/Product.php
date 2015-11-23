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

/**
 * Catalog product view processor
 */
class Enterprise_PageCache_Model_Processor_Product extends Enterprise_PageCache_Model_Processor_Default
{
    /**
     * Key for saving product id in metadata
     */
    const METADATA_PRODUCT_ID = 'current_product_id';

    /**
     * Prepare response body before caching
     *
     * @param Zend_Controller_Response_Http $response
     * @return string
     */
    public function prepareContent(Zend_Controller_Response_Http $response)
    {
        $cacheInstance = Enterprise_PageCache_Model_Cache::getCacheInstance();

        /** @var Enterprise_PageCache_Model_Processor */
        $processor = Mage::getSingleton('enterprise_pagecache/processor');
        $countLimit = Mage::getStoreConfig(Mage_Reports_Block_Product_Viewed::XML_PATH_RECENTLY_VIEWED_COUNT);
        // save recently viewed product count limit
        $cacheId = $processor->getRecentlyViewedCountCacheId();
        if (!$cacheInstance->getFrontend()->test($cacheId)) {
            $cacheInstance->save($countLimit, $cacheId, array(Enterprise_PageCache_Model_Processor::CACHE_TAG));
        }
        // save current product id
        $product = Mage::registry('current_product');
        if ($product) {
            $cacheId = $processor->getRequestCacheId() . '_current_product_id';
            $cacheInstance->save($product->getId(), $cacheId, array(Enterprise_PageCache_Model_Processor::CACHE_TAG));
            $processor->setMetadata(self::METADATA_PRODUCT_ID, $product->getId());
            Enterprise_PageCache_Model_Cookie::registerViewedProducts($product->getId(), $countLimit);
        }

        return parent::prepareContent($response);
    }

    /**
     * Return cache page id without application. Depends on GET super global array.
     *
     * @param Enterprise_PageCache_Model_Processor $processor
     * @return string
     */
    public function getPageIdWithoutApp(Enterprise_PageCache_Model_Processor $processor)
    {
        $pageId = parent::getPageIdWithoutApp($processor);
        return $this->_appendCustomerRatesToPageId($pageId);
    }
}
