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

class Enterprise_PageCache_Model_Config extends Varien_Simplexml_Config
{
    protected $_placeholders = null;

    /**
     * Class constructor
     * load cache configuration
     *
     * @param $data
     */
    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->setCacheId('cache_config');
        $this->_cacheChecksum   = null;
        $this->_cache = Mage::app()->getCache();

        $canUsaCache = Mage::app()->useCache('config');
        if ($canUsaCache) {
            if ($this->loadCache()) {
                return $this;
            }
        }

        $config = Mage::getConfig()->loadModulesConfiguration('cache.xml');
        $this->setXml($config->getNode());

        if ($canUsaCache) {
            $this->saveCache(array(Mage_Core_Model_Config::CACHE_TAG));
        }
        return $this;
    }

    /**
     * Initialize all declared placeholders as array
     * @return Enterprise_PageCache_Model_Config
     */
    protected function _initPlaceholders()
    {
        if ($this->_placeholders === null) {
            $this->_placeholders = array();
            foreach ($this->getNode('placeholders')->children() as $placeholder) {
                $this->_placeholders[(string)$placeholder->block][] = array(
                    'container'     => (string)$placeholder->container,
                    'code'          => (string)$placeholder->placeholder,
                    'cache_lifetime'=> (int) $placeholder->cache_lifetime,
                    'name'          => (string) $placeholder->name
                );
            }
        }
        return $this;
    }

    /**
     * Retrieve all declared placeholders as array
     *
     * @return array
     */
    public function getDeclaredPlaceholders()
    {
        $this->_initPlaceholders();
        return $this->_placeholders;
    }

    /**
     * Create placeholder object based on block information
     *
     * @param Mage_Core_Block_Abstract $block
     * @return Enterprise_PageCache_Model_Container_Placeholder
     */
    public function getBlockPlaceholder($block)
    {
        $this->_initPlaceholders();
        $type = $block->getType();

        if (isset($this->_placeholders[$type])) {
            $placeholderData = false;
            foreach ($this->_placeholders[$type] as $placeholderInfo) {
                if (!empty($placeholderInfo['name'])) {
                    if ($placeholderInfo['name'] == $block->getNameInLayout()) {
                        $placeholderData = $placeholderInfo;
                    }
                } else {
                    $placeholderData = $placeholderInfo;
                }
            }

            if (!$placeholderData) {
                return false;
            }

            $placeholder = $placeholderData['code']
                . ' container="' . $placeholderData['container'] . '"'
                . ' block="' . get_class($block) . '"';
            $placeholder.= ' cache_id="' . $block->getCacheKey() . '"';

            if (!empty($placeholderData['cache_lifetime'])) {
                $placeholder .= ' cache_lifetime="' . $placeholderData['cache_lifetime'] . '"';
            }

            foreach ($block->getCacheKeyInfo() as $k => $v) {
                if (is_string($k) && !empty($k)) {
                    $placeholder .= ' ' . $k . '="' . $v . '"';
                }
            }
            $placeholder = Mage::getModel('enterprise_pagecache/container_placeholder', $placeholder);
            return $placeholder;
        }
        return false;
    }
}
