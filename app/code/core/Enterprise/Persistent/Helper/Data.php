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
 * @package     Enterprise_Persistent
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Persistent helper
 */
class Enterprise_Persistent_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_PERSIST_WISHLIST             = 'persistent/options/wishlist';
    const XML_PATH_PERSIST_ORDERED_ITEMS        = 'persistent/options/recently_ordered';
    const XML_PATH_PERSIST_COMPARE_PRODUCTS     = 'persistent/options/compare_current';
    const XML_PATH_PERSIST_COMPARED_PRODUCTS    = 'persistent/options/compare_history';
    const XML_PATH_PERSIST_VIEWED_PRODUCTS      = 'persistent/options/recently_viewed';
    const XML_PATH_PERSIST_CUSTOMER_AND_SEGM    = 'persistent/options/customer';

    /**
     * Name of config file
     *
     * @var string
     */
    protected $_configFileName = 'persistent.xml';

    /**
     * Retrieve path for config file
     *
     * @return string
     */
    public function getPersistentConfigFilePath()
    {
        return Mage::getConfig()->getModuleDir('etc', $this->_getModuleName()) . DS . $this->_configFileName;
    }

    /**
     * Check whether wishlist is persist
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return bool
     */
    public function isWishlistPersist($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_PERSIST_WISHLIST, $store);
    }

    /**
     * Check whether ordered items is persist
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return bool
     */
    public function isOrderedItemsPersist($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_PERSIST_ORDERED_ITEMS, $store);
    }

    /**
     * Check whether compare products is persist
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return bool
     */
    public function isCompareProductsPersist($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_PERSIST_COMPARE_PRODUCTS, $store);
    }

    /**
     * Check whether compared products is persist
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return bool
     */
    public function isComparedProductsPersist($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_PERSIST_COMPARED_PRODUCTS, $store);
    }

    /**
     * Check whether viewed products is persist
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return bool
     */
    public function isViewedProductsPersist($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_PERSIST_VIEWED_PRODUCTS, $store);
    }

    /**
     * Check whether customer and segments is persist
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return bool
     */
    public function isCustomerAndSegmentsPersist($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_PERSIST_CUSTOMER_AND_SEGM, $store);
    }
}
