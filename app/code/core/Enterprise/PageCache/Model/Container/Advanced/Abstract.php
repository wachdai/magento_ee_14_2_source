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
 * Abstract advanced placeholder container
 */
abstract class Enterprise_PageCache_Model_Container_Advanced_Abstract
    extends Enterprise_PageCache_Model_Container_Abstract
{

    /**
     * Get container individual additional cache id
     *
     * @return string | false
     */
    abstract protected function _getAdditionalCacheId();

    /**
     * Load cached data by cache id
     *
     * @param string $id
     * @return string | false
     */
    protected function _loadCache($id)
    {
        $cacheRecord = parent::_loadCache($id);
        if (!$cacheRecord) {
            return false;
        }

        $cacheRecord = json_decode($cacheRecord, true);
        if (!$cacheRecord) {
            return false;
        }

        return isset($cacheRecord[$this->_getAdditionalCacheId()])
            ? $cacheRecord[$this->_getAdditionalCacheId()] : false;
    }

    /**
     * Save data to cache storage. Store many block instances in one cache record depending on additional cache ids.
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param null|int $lifetime
     * @return Enterprise_PageCache_Model_Container_Advanced_Abstract
     */
    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        $additionalCacheId = $this->_getAdditionalCacheId();
        if (!$additionalCacheId) {
            Mage::throwException(Mage::helper('enterprise_pagecache')->__('Additional id should not be empty'));
        }

        $tags[] = Enterprise_PageCache_Model_Processor::CACHE_TAG;
        $tags = array_merge($tags, $this->_getPlaceHolderBlock()->getCacheTags());
        if (is_null($lifetime)) {
            $lifetime = $this->_placeholder->getAttribute('cache_lifetime') ?
                $this->_placeholder->getAttribute('cache_lifetime') : false;
        }

        Enterprise_PageCache_Helper_Data::prepareContentPlaceholders($data);

        $result = array();

        $cacheRecord = parent::_loadCache($id);
        if ($cacheRecord) {
            $cacheRecord = json_decode($cacheRecord, true);
            if ($cacheRecord) {
                $result = $cacheRecord;
            }
        }

        $result[$additionalCacheId] = $data;

        Enterprise_PageCache_Model_Cache::getCacheInstance()->save(json_encode($result), $id, $tags, $lifetime);
        return $this;
    }
}
