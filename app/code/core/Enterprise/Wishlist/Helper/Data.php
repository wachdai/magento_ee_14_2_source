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
 * @package     Enterprise_Wishlist
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Multiple wishlist helper
 *
 * @category    Enterprise
 * @package     Enterprise_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Wishlist_Helper_Data extends Mage_Wishlist_Helper_Data
{
    /**
     * The list of default wishlists grouped by customer id
     *
     * @var array
     */
    protected $_defaultWishlistsByCustomer = array();

    /**
     * Create wishlist item collection
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    protected function _createWishlistItemCollection()
    {
        if ($this->isMultipleEnabled() && $this->getCustomer()) {
            return Mage::getModel('wishlist/item')->getCollection()
                ->addCustomerIdFilter($this->getCustomer()->getId())
                ->addStoreFilter(Mage::app()->getStore()->getWebsite()->getStoreIds())
                ->setVisibilityFilter();
        } else {
            return parent::_createWishlistItemCollection();
        }
    }

    /**
     * Retrieve current customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::helper('wishlist')->getCustomer();
    }

    /**
     * Check whether multiple wishlist is enabled
     *
     * @return bool
     */
    public function isMultipleEnabled()
    {
        return $this->isModuleOutputEnabled()
            && Mage::getStoreConfig('wishlist/general/active')
            && Mage::getStoreConfig('wishlist/general/multiple_enabled');
    }

    /**
     * Check whether given wishlist is default for it's customer
     *
     * @param Mage_Wishlist_Model_Wishlist $wishlist
     * @return bool
     */
    public function isWishlistDefault(Mage_Wishlist_Model_Wishlist $wishlist)
    {
        return $this->getDefaultWishlist($wishlist->getCustomerId())->getId() == $wishlist->getId();
    }

    /**
     * Retrieve customer's default wishlist
     *
     * @param int $customerId
     * @return Mage_Wishlist_Model_Wishlist
     */
    public function getDefaultWishlist($customerId = null)
    {
        if (!$customerId && $this->getCustomer()) {
            $customerId = $this->getCustomer()->getId();
        }
        if (!isset($this->_defaultWishlistsByCustomer[$customerId])) {
            $this->_defaultWishlistsByCustomer[$customerId] = Mage::getModel('wishlist/wishlist');
            $this->_defaultWishlistsByCustomer[$customerId]->loadByCustomer($customerId, false);
        }
        return $this->_defaultWishlistsByCustomer[$customerId];
    }

    /**
     * Get max allowed number of wishlists per customers
     *
     * @return int
     */
    public function getWishlistLimit()
    {
        return Mage::getStoreConfig('wishlist/general/multiple_wishlist_number');
    }

    /**
     * Check whether given wishlist collection size exceeds wishlist limit
     *
     * @param Mage_Wishlist_Model_Resource_Wishlist_Collection $wishlistList
     * @return bool
     */
    public function isWishlistLimitReached(Mage_Wishlist_Model_Resource_Wishlist_Collection $wishlistList)
    {
        return count($wishlistList) >= $this->getWishlistLimit();
    }

    /**
     * Retrieve Wishlist collection by customer id
     *
     * @param int $customerId
     * @return Mage_Wishlist_Model_Resource_Wishlist_Collection
     */
    public function getCustomerWishlists($customerId = null)
    {
        if (!$customerId && $this->getCustomer()) {
            $customerId = $this->getCustomer()->getId();
        }
        $wishlistsByCustomer = Mage::registry('wishlists_by_customer');
        if (!isset($wishlistsByCustomer[$customerId])) {
            $collection = Mage::getModel('wishlist/wishlist')->getCollection();
            $collection->filterByCustomerId($customerId);
            $wishlistsByCustomer[$customerId] = $collection;
            Mage::register('wishlists_by_customer', $wishlistsByCustomer);
        }
        return $wishlistsByCustomer[$customerId];
    }

    /**
     * Retrieve number of wishlist items in given wishlist
     *
     * @param Mage_Wishlist_Model_Wishlist $wishlist
     * @return int
     */
    public function getWishlistItemCount(Mage_Wishlist_Model_Wishlist $wishlist)
    {
        $collection = $wishlist->getItemCollection();
        if (Mage::getStoreConfig(self::XML_PATH_WISHLIST_LINK_USE_QTY)) {
            $count = $collection->getItemsQty();
        } else {
            $count = $collection->getSize();
        }
        return $count;
    }
}
