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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Customer gift registry checkout abstract block
 */
class Enterprise_GiftRegistry_Block_Customer_Checkout extends Mage_Core_Block_Template
{
    /**
     * Get current checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Check whether module is available
     *
     * @return bool
     */
    public function getEnabled()
    {
        return  Mage::helper('enterprise_giftregistry')->isEnabled();
    }

    /**
     * Get customer quote gift registry items
     *
     * @return array
     */
    protected function _getGiftRegistryQuoteItems()
    {
        $items = array();
        if ($this->_getCheckoutSession()->getQuoteId()) {
            $quote = $this->_getCheckoutSession()->getQuote();
            $model = Mage::getModel('enterprise_giftregistry/entity');
            foreach ($quote->getItemsCollection() as $quoteItem) {
                $item = array();
                if ($registryItemId = $quoteItem->getGiftregistryItemId()) {
                    $model->loadByEntityItem($registryItemId);
                    $item['entity_id'] = $model->getId();
                    $item['item_id'] = $registryItemId;
                    $item['is_address'] = ($model->getShippingAddress()) ? 1 : 0;
                    $items[$quoteItem->getId()] = $item;
                }
            }
        }
        return $items;
    }

   /**
     * Get quote gift registry items for multishipping checkout
     *
     * @return array
     */
    public function getItems()
    {
        $items = array();
        foreach ($this->_getGiftRegistryQuoteItems() as $quoteItemId => $item) {
            if ($item['is_address']) {
                $items[$quoteItemId] = $item;
            }
        }
        return $items;
    }

    /**
     * Get quote unique gift registry item for onepage checkout
     *
     * @return false|int
     */
    public function getItem()
    {
        $items = array();
        foreach ($this->_getGiftRegistryQuoteItems() as $registryItem) {
            $items[$registryItem['entity_id']] = $registryItem;
        }
        if (count($items) == 1) {
            $item = array_shift($items);
            if ($item['is_address']) {
                return $item['item_id'];
            }
        }
        return false;
    }

    /**
     * Get select shipping address id prefix
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getAddressIdPrefix()
    {
        return Mage::helper('enterprise_giftregistry')->getAddressIdPrefix();
    }

    /**
     * Retrieve giftregistry selected addresses indexes
     *
     * @return array
     */
    public function getGiftregistrySelectedAddressesIndexes()
    {
        $result = array();
        $registryQuoteItemIds = array_keys($this->getItems());
        $quoteAddressItems = Mage::getSingleton('checkout/type_multishipping')->getQuoteShippingAddressesItems();
        foreach ($quoteAddressItems as $index => $quoteAddressItem) {
            $quoteItemId = $quoteAddressItem->getQuoteItem()->getId();
            if (!$quoteAddressItem->getCustomerAddressId() && in_array($quoteItemId, $registryQuoteItemIds)) {
                $result[] = $index;
            }
        }
        return $result;
    }
}
