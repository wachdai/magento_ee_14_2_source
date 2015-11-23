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
 * Wishlist sidebar block
 *
 * @category    Enterprise
 * @package     Enterprise_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Wishlist_Block_Customer_Wishlist_Management extends Mage_Core_Block_Template
{
    /**
     * Id of current customer
     *
     * @var int|null
     */
    protected $_customerId = null;

    /**
     * Wishlist Collection
     *
     * @var Mage_Wishlist_Model_Resource_Wishlist_Collection
     */
    protected $_collection;

    /**
     * Render block
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::helper('enterprise_wishlist')->isMultipleEnabled()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Retrieve customer Id
     *
     * @return int|null
     */
    protected function _getCustomerId()
    {
        if (is_null($this->_customerId)) {
            $this->_customerId = Mage::getSingleton('customer/session')->getCustomerId();
        }
        return $this->_customerId;
    }

    /**
     * Retrieve wishlist collection
     *
     * @return Mage_Wishlist_Model_Resource_Wishlist_Collection
     */
    public function getWishlists()
    {
        return Mage::helper('enterprise_wishlist')->getCustomerWishlists($this->_getCustomerId());
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
     * Retrieve currently selected wishlist
     *
     * @return Mage_Wishlist_Model_Wishlist
     */
    public function getCurrentWishlist()
    {
        if (!$this->_current) {
            $wishlistId = $this->getRequest()->getParam('wishlist_id');
            if ($wishlistId) {
                $this->_current = $this->getWishlists()->getItemById($wishlistId);
            } else {
                $this->_current = $this->getDefaultWishlist();
            }
        }
        return $this->_current;
    }

    /**
     * Build string that displays the number of items in wishlist
     *
     * @param Mage_Wishlist_Model_Wishlist $wishlist
     * @return string
     */
    public function getItemCount(Mage_Wishlist_Model_Wishlist $wishlist)
    {
        $count = Mage::helper('enterprise_wishlist')->getWishlistItemCount($wishlist);
        if ($count == 1) {
            return Mage::helper('enterprise_wishlist')->__('1 item');
        } else {
            return Mage::helper('enterprise_wishlist')->__('%d items', $count);
        }
    }

    /**
     * Build wishlist management page url
     *
     * @param Mage_Wishlist_Model_Wishlist $wishlist
     * @return string
     */
    public function getWishlistManagementUrl(Mage_Wishlist_Model_Wishlist $wishlist)
    {
        return $this->getUrl('wishlist/*/*', array('wishlist_id' => $wishlist->getId()));
    }

    /**
     * Retrieve Wishlist creation url
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('wishlist/index/createwishlist');
    }

    /**
     * Build wishlist edit url
     *
     * @param int $wishlistId
     * @return string
     */
    public function getEditUrl($wishlistId)
    {
        return $this->getUrl('wishlist/index/editwishlist', array('wishlist_id' => $wishlistId));
    }

    /**
     * Build wishlist items copy url
     *
     * @return string
     */
    public function getCopySelectedUrl()
    {
        return $this->getUrl('wishlist/index/copyitems', array('wishlist_id' => '%wishlist_id%'));
    }

    /**
     * Build wishlist items move url
     *
     * @return string
     */
    public function getMoveSelectedUrl()
    {
        return $this->getUrl('wishlist/index/moveitems', array('wishlist_id' => '%wishlist_id%'));
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

    /**
     * Get wishlist item move url
     *
     * @return string
     */
    public function getMoveItemUrl()
    {
        return $this->getUrl('wishlist/index/moveitem');
    }

    /**
     * Check whether user multiple wishlist limit reached
     *
     * @param $wishlists
     * @return bool
     */
    public function canCreateWishlists($wishlists)
    {
        return !Mage::helper('enterprise_wishlist')->isWishlistLimitReached($wishlists);
    }
}
