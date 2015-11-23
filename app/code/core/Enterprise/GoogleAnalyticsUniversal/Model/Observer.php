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
 * @package     Enterprise_GoogleAnalyticsUniversal
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_GoogleAnalyticsUniversal_Model_Observer
{
    protected $_fpcBlockPositions = array();

    /** @var null Enterprise_GoogleAnalyticsUniversal_Block_List_Json */
    protected $_blockPromotions = null;

    /**
     * Add order information into GA block to render on checkout success pages
     * The method overwrites the GoogleAnalytics observer method by the system.xml event settings
     *
     * Fired by the checkout_onepage_controller_success_action and
     * checkout_multishipping_controller_success_action events
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function setGoogleAnalyticsOnOrderSuccessPageView(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_googleanalyticsuniversal')->isGoogleAnalyticsAvailable()) {
            return $this;
        }

        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return $this;
        }
        /** @var Enterprise_GoogleAnalyticsUniversal_Block_Ga $block */
        $block = Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('google_analyticsuniversal');
        if ($block) {
            $block->setOrderIds($orderIds);
        }
        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function setGoogleAnalyticsOnBanners(Varien_Event_Observer $observer)
    {

        if (!Mage::helper('enterprise_googleanalyticsuniversal')->isTagManagerAvailable()) {
            return $this;
        }

        $block = $observer->getEvent()->getBlock();
        if (!$block instanceof Enterprise_Banner_Block_Widget_Banner) {
            return $this;
        }

        /** @var Enterprise_GoogleAnalyticsUniversal_Block_List_Json $jsonBlock */
        $jsonBlock = $block->getLayout()->getBlock('banner_impression');
        if (is_object($jsonBlock)) {
            $jsonBlock->appendBannerBlock($block);
        }
        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function setGoogleAnalyticsOnMinishoppingCart(Varien_Event_Observer $observer)
    {

        if (!Mage::helper('enterprise_googleanalyticsuniversal')->isTagManagerAvailable()) {
            return $this;
        }

        $block = $observer->getEvent()->getBlock();
        if ($block && $block->getType() == 'checkout/cart_sidebar') {
            /** @var Enterprise_GoogleAnalyticsUniversal_Block_List_Json $jsonBlock */
                $jsonBlock = $block->getLayout()->getBlock('update_cart_analytics');
                $transport = $observer->getEvent()->getTransport();
                $html = $transport->getHtml();
                $html .= $jsonBlock->toHtml();
                $transport->setHtml($html);
        }
        return $this;
    }

    /**
     * Save previous cart quantities on add to cart action to find the delta on load page
     * Fired by sales_quote_load_after event
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function rememberCartQuantity(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_googleanalyticsuniversal')->isTagManagerAvailable()) {
            return $this;
        }

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        $session = Mage::getSingleton('checkout/session');
        $productQtys = array();
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            $parentQty = 1;
            switch ($quoteItem->getProductType()) {
                case 'bundle':
                case 'configurable':
                    break;
                case 'grouped':
                    $id = $quoteItem->getOptionByCode('product_type')->getProductId()
                        . '-' . $quoteItem->getProductId();
                    $productQtys[$id] = $quoteItem->getQty();
                    break;
                case 'giftcard':
                    $id = $quoteItem->getId() . '-' . $quoteItem->getProductId();
                    $productQtys[$id] = $quoteItem->getQty();
                    break;
                default:
                    if ($quoteItem->getParentItem()) {
                        $parentQty = $quoteItem->getParentItem()->getQty();
                        $id = $quoteItem->getId() . '-' .
                            $quoteItem->getParentItem()->getProductId() . '-' .
                            $quoteItem->getProductId();
                    } else {
                        $id = $quoteItem->getProductId();
                    }
                    $productQtys[$id] = $quoteItem->getQty() * $parentQty;
            }
        }
        /** prevent from overwriting on page load */
        if (!$session->hasData(
            Enterprise_GoogleAnalyticsUniversal_Helper_Data::PRODUCT_QUANTITIES_BEFORE_ADDTOCART
        )) {
            $session->setData(
                Enterprise_GoogleAnalyticsUniversal_Helper_Data::PRODUCT_QUANTITIES_BEFORE_ADDTOCART,
                $productQtys
            );
        }
        return $this;
    }

    /**
     * When shopping cart is cleaned the remembered quantities in a session needs also to be deleted
     *
     * Fired by controller_action_postdispatch_checkout_cart_updatePost event
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function clearSessionCartQuantity(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_googleanalyticsuniversal')->isTagManagerAvailable()) {
            return $this;
        }
        /** @var Mage_Core_Controller_Varien_Action $controllerAction */
        $controllerAction = $observer->getEvent()->getControllerAction();
        $updateAction = (string)$controllerAction->getRequest()->getParam('update_cart_action');
        if ($updateAction == 'empty_cart') {
            $session = Mage::getSingleton('checkout/session');
            $session->unsetData(Enterprise_GoogleAnalyticsUniversal_Helper_Data::PRODUCT_QUANTITIES_BEFORE_ADDTOCART);
        }
        return $this;
    }

    /**
     * Fired by sales_quote_product_add_after event
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function setGoogleAnalyticsOnCartAdd(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('enterprise_googleanalyticsuniversal');
        if (!$helper->isTagManagerAvailable()) {
            return $this;
        }
        $products = Mage::registry('googleanalyticsuniversal_products_addtocart');
        if (!$products) {
            $products = array();
        }
        $lastValues = array();
        $session = Mage::getSingleton('checkout/session');
        if ($session->hasData(
            Enterprise_GoogleAnalyticsUniversal_Helper_Data::PRODUCT_QUANTITIES_BEFORE_ADDTOCART
        )) {
            $lastValues = $session->getData(
                Enterprise_GoogleAnalyticsUniversal_Helper_Data::PRODUCT_QUANTITIES_BEFORE_ADDTOCART
            );
        }

        $items = $observer->getEvent()->getItems();
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        foreach ($items as $quoteItem) {
            $id = $quoteItem->getProductId();
            $parentQty = 1;
            $price = $quoteItem->getProduct()->getPrice();
            switch ($quoteItem->getProductType()) {
                case 'configurable':
                case 'bundle':
                    break ;
                case 'grouped':
                    $id = $quoteItem->getOptionByCode('product_type')->getProductId() . '-' .
                        $quoteItem->getProductId();
                    // no break;
                default:
                    if ($quoteItem->getParentItem()) {
                        $parentQty = $quoteItem->getParentItem()->getQty();
                        $id = $quoteItem->getId() . '-' .
                            $quoteItem->getParentItem()->getProductId() . '-' .
                            $quoteItem->getProductId();

                        if ($quoteItem->getParentItem()->getProductType() == 'configurable') {
                            $price = $quoteItem->getParentItem()->getProduct()->getPrice();
                        }
                    }
                    if ($quoteItem->getProductType() == 'giftcard') {
                        $price = $quoteItem->getProduct()->getFinalPrice();
                    }

                    $oldQty = (array_key_exists($id, $lastValues)) ? $lastValues[$id] : 0;
                    $finalQty = ($parentQty * $quoteItem->getQty()) - $oldQty;
                    if ($finalQty != 0) {
                        $products[] = array(
                            'sku'   => $quoteItem->getSku(),
                            'name'  => $quoteItem->getName(),
                            'price' => $price,
                            'qty'   => $finalQty
                        );
                    }
            }
        }
        Mage::unregister('googleanalyticsuniversal_products_addtocart');
        Mage::register('googleanalyticsuniversal_products_addtocart', $products);
        $session->unsetData(Enterprise_GoogleAnalyticsUniversal_Helper_Data::PRODUCT_QUANTITIES_BEFORE_ADDTOCART);
        return $this;
    }

    /**
     * Fired by sales_quote_remove_item event
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function setGoogleAnalyticsOnCartRemove(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('enterprise_googleanalyticsuniversal');
        if (!$helper->isTagManagerAvailable()) {
            return $this;
        }
        $products = Mage::registry('googleanalyticsuniversal_products_to_remove');
        if (!$products) {
            $products = array();
        }
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        $quoteItem = $observer->getEvent()->getQuoteItem();
        if($simples = $quoteItem->getChildren() and $quoteItem->getProductType() != 'configurable'){
            foreach($simples as $item){
                $products[] = array(
                    'sku'   => $item->getSku(),
                    'name'  => $item->getName(),
                    'price' => $item->getPrice(),
                    'qty' => $item->getQty()
                );
            }
        } else {
            $products[] = array(
                'sku' => $quoteItem->getSku(),
                'name' => $quoteItem->getName(),
                'price' => $quoteItem->getProduct()->getPrice(),
                'qty' => $quoteItem->getQty()
            );
        }
        Mage::unregister('googleanalyticsuniversal_products_to_remove');
        Mage::register('googleanalyticsuniversal_products_to_remove', $products);

        return $this;
    }

    /**
     * Send cookies after cart action
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function sendCookieOnCartActionComplete(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('enterprise_googleanalyticsuniversal');
        if (!$helper->isTagManagerAvailable()) {
            return $this;
        }
        $productsToAdd = Mage::registry('googleanalyticsuniversal_products_addtocart');
        if (!empty($productsToAdd)) {
            Mage::app()->getCookie()->set(Enterprise_GoogleAnalyticsUniversal_Helper_Data::GOOGLE_ANALYTICS_COOKIE_NAME,
                rawurlencode(json_encode($productsToAdd)), 0, '/', null, null, false);
        }
        $productsToRemove = Mage::registry('googleanalyticsuniversal_products_to_remove');
        if (!empty($productsToRemove)) {
            Mage::app()->getCookie()->set(
                Enterprise_GoogleAnalyticsUniversal_Helper_Data::GOOGLE_ANALYTICS_COOKIE_REMOVE_FROM_CART,
                rawurlencode(Mage::helper('core')->jsonEncode($productsToRemove)),  0, '/', null, null, false
            );
        }
        return $this;
    }

    /**
     * Adds to checkout shipping address step and review step GA block with related data
     * Fired by controller_action_postdispatch_checkout event
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function setGoogleAnalyticsOnCheckout(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_googleanalyticsuniversal')->isTagManagerAvailable()) {
            return $this;
        }
        /** @var Mage_Checkout_OnepageController $controllerAction */
        $controllerAction = $observer->getEvent()->getControllerAction();
        $action = $controllerAction->getRequest()->getRequestedActionName();
         switch ($action) {
            case 'saveBilling':
                $body = $controllerAction->getResponse()->getBody();
                $body = Mage::helper('core')->jsonDecode($body, true);
                if ($body['goto_section'] == 'shipping') {
                    $shippingBlock = $controllerAction->getLayout()
                        ->createBlock('enterprise_googleanalyticsuniversal/list_json')
                        ->setTemplate('googleanalyticsuniversal/checkout/step.phtml')
                        ->setStepName('shipping');
                    $body['update_section']['name'] = 'shipping';
                    $body['update_section']['html'] = '<div id="checkout-shipping-load"></div>'
                        . $shippingBlock->toHtml();
                    $controllerAction->getResponse()->setBody(Mage::helper('core')->jsonEncode($body));
                }
                break;
             case 'saveShippingMethod':
                 $shippingOption = Mage::getSingleton('checkout/session')->getQuote()
                     ->getShippingAddress()->getShippingDescription();
                 $blockShippingMethod = Mage::app()->getLayout()
                     ->createBlock('enterprise_googleanalyticsuniversal/list_json')
                     ->setTemplate('googleanalyticsuniversal/checkout/set_checkout_option.phtml')
                     ->setStepName('shipping_method')
                     ->setShippingOption($shippingOption);

                 $body = $controllerAction->getResponse()->getBody();
                 $body = Mage::helper('core')->jsonDecode($body, true);
                 if (!empty($body['update_section']['html'])) {
                     $body['update_section']['html'] = $blockShippingMethod->toHtml() . $body['update_section']['html'];
                 }
                 $controllerAction->getResponse()->setBody(Mage::helper('core')->jsonEncode($body));
                 break;
            case 'savePayment':
                $reviewBlock = $controllerAction->getLayout()
                    ->createBlock('enterprise_googleanalyticsuniversal/list_json')
                    ->setTemplate('googleanalyticsuniversal/checkout/step.phtml')
                    ->setStepName('review');

                $paymentMethod = Mage::getSingleton('checkout/session')->getQuote()
                    ->getPayment()->getMethod();
                $paymentOption = Mage::getStoreConfig('payment/' . $paymentMethod . '/title');
                $blockPaymentMethod = Mage::app()->getLayout()
                    ->createBlock('enterprise_googleanalyticsuniversal/list_json')
                    ->setTemplate('googleanalyticsuniversal/checkout/set_checkout_option.phtml')
                    ->setStepName('payment')
                    ->setShippingOption($paymentOption);

                $body = $controllerAction->getResponse()->getBody();
                $body = Mage::helper('core')->jsonDecode($body, true);
                if (!empty($body['update_section']['html'])) {
                    $body['update_section']['html'] = $blockPaymentMethod->toHtml()
                        . $body['update_section']['html'] . $reviewBlock->toHtml();
                } else {
                    $body['update_section']['html'] = $reviewBlock->toHtml();
                }
                $controllerAction->getResponse()->setBody(Mage::helper('core')->jsonEncode($body));
                break;
        }
        return $this;
    }

    /**
     * Save creditmemo id in session to fire GA action after
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function setGoogleAnalyticsOnCreditmemoSave(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_googleanalyticsuniversal')->isTagManagerAvailable()) {
            return $this;
        }

        $session = Mage::getSingleton('adminhtml/session');
        $creditmemo = $observer->getEvent()->getDataObject();
        if ($creditmemo) {
            $order = $creditmemo->getOrder();
            $session->setData('googleanalytics_creditmemo_order', $order->getIncrementId());
            $session->setData('googleanalytics_creditmemo_store_id', $creditmemo->getStoreId());
            if (abs((float)$creditmemo->getBaseGrandTotal() - (float)$order->getBaseGrandTotal()) > 0.009) {
                $session->setData('googleanalytics_creditmemo_revenue', $creditmemo->getBaseGrandTotal());
            }
            $products = array();
            foreach ($creditmemo->getItemsCollection() as $item) {
                $qty = $item->getQty();
                if ($qty < 1) {
                    continue;
                }
                $products[]= array(
                    'id' => $item->getSku(),
                    'quantity' => $qty,
                );
            }
            $session->setData('googleanalytics_creditmemo_products', $products);
        }
        return $this;
    }

    /**
     * Fires by the render_block event of the Enterprise_PageCache module only
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function updatePlaceholderInfo(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_googleanalyticsuniversal')->isTagManagerAvailable()) {
            return $this;
        }

        $block = $observer->getEvent()->getBlock();

        // Caching Banner Widget from FPC
        if ($block instanceof Enterprise_Banner_Block_Widget_Banner) {
            $this->_blockPromotions = Mage::getBlockSingleton('enterprise_googleanalyticsuniversal/list_json')
                ->appendBannerBlock($block);
        }

        return $this;
    }

    /**
     * Processing Related and Up-Sell product Items rendering via FPC
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function updateLinkedProductPlaceholderInfo(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_googleanalyticsuniversal')->isTagManagerAvailable()) {
            return $this;
        }
        $block = $observer->getEvent()->getBlock();
        $placeholder = $observer->getEvent()->getPlaceholder();
        $blockNames = array(
            'CATALOG_PRODUCT_ITEM_RELATED' => array('catalog.product.related', 'related'),
            'CATALOG_PRODUCT_ITEM_UPSELL'  => array('product.info.upsell', 'upsell')
        );

        $actualName = $blockNames[$placeholder->getName()];
        $blockImpressions = $block->getLayout()->createBlock('enterprise_googleanalyticsuniversal/list_json')
            ->setTemplate('googleanalyticsuniversal/fpc/impression.phtml')
            ->setBlockName($actualName[0])
            ->setListType($actualName[1])
            ->setPosition($this->_getFpcBlockPositions($actualName[0]))
            ->setShowCategory(true)
            ->setFpcBlock($block);

        $transport = $observer->getEvent()->getTransport();
        $html = $transport->getHtml();
        $html .= $blockImpressions->toHtml();
        $transport->setHtml($html);
        return $this;
    }

    /**
     * Add banner promotion code for Google Analytics
     * Fired by controller_action_postdispatch_enterprise_pagecache event
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function updateBannerPlaceholderInfo(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_googleanalyticsuniversal')->isTagManagerAvailable()) {
            return $this;
        }

        // No banners were found on the page
        if ($this->_blockPromotions == null) {
            return $this;
        }

        // No activated for GA tracking banners were found
        $bannerCollection = $this->_blockPromotions->getBannerCollection();
        if ($bannerCollection == null || !count($bannerCollection)) {
            return $this;
        }

        $this->_blockPromotions
            ->setVariableName('updatedPromotions')
            ->setTemplate('googleanalyticsuniversal/promotion.phtml');

        /** @var Enterprise_PageCache_RequestController $controllerAction */
        $controllerAction = $observer->getEvent()->getControllerAction();
        $body = $controllerAction->getResponse()->getBody();
        $count = 1;
        $body = str_replace('</body>', $this->_blockPromotions->toHtml() . '</body>', $body, $count);
        $controllerAction->getResponse()->setBody($body);
        return $this;
    }

    /**
     * @param $key
     * @return int
     */
    protected function _getFpcBlockPositions($key)
    {
        if (!array_key_exists($key, $this->_fpcBlockPositions)) {
            $this->_fpcBlockPositions[$key] = 1;
        } else {
            $this->_fpcBlockPositions[$key]++;
        }
        return $this->_fpcBlockPositions[$key];
    }

    /**
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function invalidateFpcCache(Varien_Event_Observer $observer)
    {
        if (Mage::helper('core')->isModuleEnabled('Enterprise_PageCache')) {
            Mage::app()->getCacheInstance()->invalidateType('full_page');
        }
        return $this;
    }
}
