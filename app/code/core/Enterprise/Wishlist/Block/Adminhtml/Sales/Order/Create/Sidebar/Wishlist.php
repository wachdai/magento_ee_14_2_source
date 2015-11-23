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
 * Adminhtml customer orders grid block
 *
 * @category    Enterprise
 * @package     Enterprise_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Wishlist_Block_Adminhtml_Sales_Order_Create_Sidebar_Wishlist
    extends Mage_Adminhtml_Block_Sales_Order_Create_Sidebar_Wishlist
{
    /**
     * Retrieve item collection
     *
     * @return Enterprise_Wishlist_Model_Resource_Item_Collection
     */
    public function getItemCollection()
    {
        $collection = $this->getData('item_collection');
        $storeIds = $this->getCreateOrderModel()->getSession()->getStore()->getWebsite()->getStoreIds();
        if (is_null($collection)) {
            $collection = Mage::getModel('enterprise_wishlist/item')->getCollection()
                ->addCustomerIdFilter($this->getCustomerId())
                ->addStoreFilter($storeIds)
                ->setVisibilityFilter();
            if ($collection) {
                $collection = $collection->load();
            }
            $this->setData('item_collection', $collection);
        }
        return $collection;
    }

    /**
     * Retrieve list of customer wishlists with items
     *
     * @return array
     */
    public function getWishlists()
    {
        $wishlists = array();
        /* @var $item Mage_Wishlist_Model_Item */
        foreach ($this->getItemCollection() as $item) {
            $wishlists[$item->getWishlistId()] = $item->getWishlistName();
        }
        ksort($wishlists);
        return $wishlists;
    }
}

