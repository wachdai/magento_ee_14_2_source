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
 * @package     Enterprise_Checkout
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Admin Checkout processing model
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 */
class Enterprise_Checkout_Model_Observer
{
    /**
     * Get cart model instance
     *
     * @return Enterprise_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('enterprise_checkout/cart');
    }

    /**
     * Returns cart model for backend
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Checkout_Model_Cart
     */
    protected function _getBackendCart(Varien_Event_Observer $observer)
    {
        $storeId = $observer->getRequestModel()->getParam('storeId');
        if (is_null($storeId)) {
            $storeId = $observer->getRequestModel()->getParam('store_id');
        }
        return $this->_getCart()
            ->setSession($observer->getSession())
            ->setContext(Enterprise_Checkout_Model_Cart::CONTEXT_ADMIN_ORDER)
            ->setCurrentStore((int)$storeId);
    }

    /**
     * Check submitted SKU's form the form or from error grid
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function addBySku(Varien_Event_Observer $observer)
    {
        /* @var $request Mage_Core_Controller_Request_Http */
        $request = $observer->getRequestModel();
        $cart = $this->_getBackendCart($observer);

        if (empty($request) || empty($cart)) {
            return;
        }

        $removeFailed = $request->getPost('sku_remove_failed');

        if ($removeFailed || $request->getPost('from_error_grid')) {
            $cart->removeAllAffectedItems();
            if ($removeFailed) {
                return;
            }
        }

        $sku = $observer->getRequestModel()->getPost('remove_sku', false);

        if ($sku) {
            $this->_getBackendCart($observer)->removeAffectedItem($sku);
            return;
        }

        $addBySkuItems = $request->getPost(Enterprise_Checkout_Block_Adminhtml_Sku_Abstract::LIST_TYPE, array());
        $items = $request->getPost('item', array());
        if (!$addBySkuItems) {
            return;
        }
        foreach ($addBySkuItems as $id => $params) {
            $sku = isset($params['sku']) ? $params['sku'] : $id;
            $cart->prepareAddProductBySku($sku, $params['qty'], isset($items[$id]) ? $items[$id] : array());
        }
        /* @var $orderCreateModel Mage_Adminhtml_Model_Sales_Order_Create */
        $orderCreateModel = $observer->getOrderCreateModel();
        $cart->saveAffectedProducts($orderCreateModel, false);
        // We have already saved succeeded add by SKU items in saveAffectedItems(). This prevents from duplicate saving.
        $request->setPost('item', array());
    }

    /**
     * Upload and parse CSV file with SKUs
     *
     * @param Varien_Event_Observer $observer
     * @return null
     */
    public function uploadSkuCsv(Varien_Event_Observer $observer)
    {
        /** @var $helper Enterprise_Checkout_Helper_Data */
        $helper = Mage::helper('enterprise_checkout');
        $rows = $helper->isSkuFileUploaded($observer->getRequestModel())
            ? $helper->processSkuFileUploading($observer->getSession())
            : array();
        if (empty($rows)) {
            return;
        }

        /* @var $orderCreateModel Mage_Adminhtml_Model_Sales_Order_Create */
        $orderCreateModel = $observer->getOrderCreateModel();
        $cart = $this->_getBackendCart($observer);
        $cart->prepareAddProductsBySku($rows);
        $cart->saveAffectedProducts($orderCreateModel, false);
    }

    /**
     * Create handle for sku failed products
     *
     * @param Varien_Event_Observer $observer
     */
    public function createSkuErrorHandleLayout(Varien_Event_Observer $observer)
    {
        /** @var $layout Mage_Core_Model_Layout */
        $layout = $observer->getEvent()->getLayout();

        $affectedItems = $this->_getCart()->getFailedItems();
        if (!empty($affectedItems)) {
            $layout->getUpdate()->addHandle(Enterprise_Checkout_Helper_Data::SKU_FAILED_PRODUCTS_HANDLE);
        }
    }

    /**
     * Copy real address to the quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Quote_Address $realAddress
     * @return Mage_Sales_Model_Quote_Address
     */
    protected function _copyAddress($quote, $realAddress)
    {
        $address = Mage::getModel('sales/quote_address');
        $address->setData($realAddress->getData());
        $address
            ->setId(null)
            ->unsEntityId()
            ->unsetData('cached_items_nominal')
            ->unsetData('cached_items_nonnominal')
            ->unsetData('cached_items_all')
            ->setQuote($quote);
        return $address;
    }

    /**
     * Calculate failed items quote-related data
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function collectTotalsFailedItems($observer)
    {
        if ($observer->getEvent()->getAction()->getFullActionName() != 'checkout_cart_index') {
            return;
        }

        /** @var $realQuote Mage_Sales_Model_Quote */
        $realQuote = Mage::getSingleton('sales/quote');
        $affectedItems = $this->_getCart()->getFailedItems();
        if (empty($affectedItems)) {
            return;
        }

        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel('sales/quote');
        $collection = new Varien_Data_Collection();

        foreach (Mage::helper('enterprise_checkout')->getFailedItems(false) as $item) {
            /** @var $item Mage_Sales_Model_Quote_Item */
            if ((float)$item->getQty() <= 0) {
                $item->setSkuRequestedQty($item->getQty());
                $item->setData('qty', 1);
            }
            $item->setQuote($quote);
            $collection->addItem($item);
        }

        $quote->preventSaving()->setItemsCollection($collection);

        $quote->setShippingAddress($this->_copyAddress($quote, $realQuote->getShippingAddress()));
        $quote->setBillingAddress($this->_copyAddress($quote, $realQuote->getBillingAddress()));
        $quote->setTotalsCollectedFlag(false)->collectTotals();

        foreach ($quote->getAllItems() as $item) {
            /** @var $item Mage_Sales_Model_Quote_Item */
            if ($item->hasSkuRequestedQty()) {
                $item->setData('qty', $item->getSkuRequestedQty());
            }
        }
    }

    /**
     * Add link to cart in cart sidebar to view grid with failed products
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function addCartLink($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (!$block instanceof Mage_Checkout_Block_Cart_Sidebar) {
            return;
        }

        $failedItemsCount = count(Mage::getSingleton('enterprise_checkout/cart')->getFailedItems());
        if ($failedItemsCount > 0) {
            $block->setAllowCartLink(true);
            $block->setCartEmptyMessage(
                Mage::helper('enterprise_checkout')->__('You have %d item(s) requiring attention.', $failedItemsCount)
            );
        }
    }
}
