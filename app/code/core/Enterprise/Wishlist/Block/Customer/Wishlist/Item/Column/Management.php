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
 * Wishlist item management column (copy, move, etc.)
 *
 * @category    Enterprise
 * @package     Enterprise_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Wishlist_Block_Customer_Wishlist_Item_Column_Management
    extends Mage_Wishlist_Block_Customer_Wishlist_Item_Column
{
    /**
     * Render block
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::helper('enterprise_wishlist')->isMultipleEnabled();
    }

    /**
     * Retrieve current customer wishlist collection
     *
     * @return Mage_Wishlist_Model_Resource_Wishlist_Collection
     */
    public function getWishlists()
    {
        return Mage::helper('enterprise_wishlist')->getCustomerWishlists();
    }

    /**
     * Retrieve default wishlist for current customer
     *
     * @return Mage_Wishlist_Model_Wishlist
     */
    public function getDefaultWishlist()
    {
        return Mage::helper('enterprise_wishlist')->getDefaultWishlist();
    }

    /**
     * Retrieve current wishlist
     *
     * @return Mage_Wishlist_Model_Wishlist
     */
    public function getCurrentWishlist()
    {
        return Mage::helper('wishlist')->getWishlist();
    }

    /**
     * Check whether user multiple wishlist limit reached
     *
     * @param Mage_Wishlist_Model_Resource_Wishlist_Collection $wishlists
     * @return bool
     */
    public function canCreateWishlists(Mage_Wishlist_Model_Resource_Wishlist_Collection $wishlists)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        return !Mage::helper('enterprise_wishlist')->isWishlistLimitReached($wishlists) && $customerId;
    }

    /**
     * Get wishlist item copy url
     *
     * @return string
     */
    public function getCopyItemUrl()
    {
        return $this->getUrl('wishlist/index/copyitem');
    }
}
