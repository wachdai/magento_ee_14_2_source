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
 * @package     Enterprise_GiftWrapping
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Gift wrapping observer model
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftWrapping_Model_Observer
{
    /**
     * Prepare quote item info about gift wrapping
     *
     * @param mixed $entity
     * @param array $data
     * @return Enterprise_GiftWrapping_Model_Observer
     */
    protected function _saveItemInfo($entity, $data)
    {
        if (is_array($data)) {
            $wrapping = Mage::getModel('enterprise_giftwrapping/wrapping')->load($data['design']);
            $entity->setGwId($wrapping->getId())
                ->save();
        }
        return $this;
    }

    /**
     * Prepare entire order info about gift wrapping
     *
     * @param mixed $entity
     * @param array $data
     * @return Enterprise_GiftWrapping_Model_Observer
     */
    protected function _saveOrderInfo($entity, $data)
    {
        if (is_array($data)) {
            $wrappingInfo = array();
            if (isset($data['design'])) {
                $wrapping = Mage::getModel('enterprise_giftwrapping/wrapping')->load($data['design']);
                $wrappingInfo['gw_id'] = $wrapping->getId();
            }
            $wrappingInfo['gw_allow_gift_receipt'] = isset($data['allow_gift_receipt']);
            $wrappingInfo['gw_add_card'] = isset($data['add_printed_card']);
            if ($entity->getShippingAddress()) {
                $entity->getShippingAddress()->addData($wrappingInfo);
            }
            $entity->addData($wrappingInfo)->save();
        }
        return $this;
    }

    /**
     * Process gift wrapping options on checkout proccess
     *
     * @param Varien_Object $observer
     * @return Enterprise_GiftWrapping_Model_Observer
     */
    public function checkoutProcessWrappingInfo($observer)
    {
        $request = $observer->getEvent()->getRequest();
        $giftWrappingInfo = $request->getParam('giftwrapping');

        if (is_array($giftWrappingInfo)) {
            $quote = $observer->getEvent()->getQuote();
            $giftOptionsInfo = $request->getParam('giftoptions');
            foreach ($giftWrappingInfo as $entityId => $data) {
                $info = array();
                if (!is_array($giftOptionsInfo) || empty($giftOptionsInfo[$entityId]['type'])) {
                    continue;
                }
                switch ($giftOptionsInfo[$entityId]['type']) {
                    case 'quote':
                        $entity = $quote;
                        $this->_saveOrderInfo($entity, $data);
                        break;
                    case 'quote_item':
                        $entity = $quote->getItemById($entityId);
                        $this->_saveItemInfo($entity, $data);
                        break;
                    case 'quote_address':
                        $entity = $quote->getAddressById($entityId);
                        $this->_saveOrderInfo($entity, $data);
                        break;
                    case 'quote_address_item':
                        $entity = $quote
                            ->getAddressById($giftOptionsInfo[$entityId]['address'])
                            ->getItemById($entityId);
                        $this->_saveItemInfo($entity, $data);
                        break;
                }
            }
        }
        return $this;
    }

    /**
     * Process admin order creation
     *
     * @param Varien_Event_Observer $observer
     */
    public function processOrderCreationData($observer)
    {
        $quote = $observer->getEvent()->getOrderCreateModel()->getQuote();
        $request = $observer->getEvent()->getRequest();
        if (isset($request['giftwrapping'])) {
            $info = array();
            foreach ($request['giftwrapping'] as $entityId => $data) {
                if (isset($data['type'])) {
                    switch ($data['type']) {
                        case 'quote':
                            $entity = $quote;
                            $this->_saveOrderInfo($entity, $data);
                            break;
                        case 'quote_item':
                            $entity = $quote->getItemById($entityId);
                            $this->_saveItemInfo($entity, $data);
                            break;
                    }
                }
            }
        }
    }

    /**
     * Set the flag is it new collecting totals
     *
     * @param Varien_Event_Observer $observer
     */
    public function quoteCollectTotalsBefore(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quote->setIsNewGiftWrappingCollecting(true);
        $quote->setIsNewGiftWrappingTaxCollecting(true);
    }

    /**
     * Add gift wrapping items into PayPal checkout
     *
     * @param Varien_Event_Observer $observer
     */
    public function addPaypalGiftWrappingItem(Varien_Event_Observer $observer)
    {
        /** @var Mage_Paypal_Model_Cart $paypalCart */
        $paypalCart = $observer->getEvent()->getPaypalCart();
        $totalWrapping = 0;
        $totalCard = 0;
        if ($paypalCart) {
            $salesEntity = $paypalCart->getSalesEntity();
            if ($salesEntity instanceof Mage_Sales_Model_Order) {
                foreach ($salesEntity->getAllItems() as $_item) {
                    if (!$_item->getParentItem() && $_item->getGwId() && $_item->getGwBasePrice()) {
                        $totalWrapping += $_item->getGwBasePrice();
                    }
                }
                if ($salesEntity->getGwId() && $salesEntity->getGwBasePrice()) {
                    $totalWrapping += $salesEntity->getGwBasePrice();
                }
                if ($salesEntity->getGwAddCard() && $salesEntity->getGwCardBasePrice()) {
                    $totalCard += $salesEntity->getGwCardBasePrice();
                }
            } else {
                foreach ($salesEntity->getAllItems() as $_item) {
                    if (!$_item->getParentItem() && $_item->getGwId() && $_item->getGwBasePrice()) {
                        $totalWrapping += $_item->getGwBasePrice();
                    }
                }
                if ($salesEntity->getGwId() && $salesEntity->getGwBasePrice()) {
                    $totalWrapping += $salesEntity->getGwBasePrice();
                }
                if ($salesEntity->getGwAddCard() && $salesEntity->getGwCardBasePrice()) {
                    $totalCard += $salesEntity->getGwCardBasePrice();
                }
            }
            if ($totalWrapping) {
                $paypalCart->addItem(Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping'),1,$totalWrapping);
            }
            if ($totalCard) {
                $paypalCart->addItem(Mage::helper('enterprise_giftwrapping')->__('Printed Card'),1,$totalCard);
            }
        }
    }

    /**
     * Set gift options available flag for items
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftWrapping_Model_Observer
     */
    public function prepareGiftOptpionsItems(Varien_Event_Observer $observer)
    {
       $items = $observer->getEvent()->getItems();
       foreach ($items as $item) {
           $allowed = $item->getProduct()->getGiftWrappingAvailable();
           if (Mage::helper('enterprise_giftwrapping')->isGiftWrappingAvailableForProduct($allowed)
               && !$item->getIsVirtual()) {
               $item->setIsGiftOptionsAvailable(true);
           }
       }
       return $this;
    }

    /**
     * Import giftwrapping data from order to quote
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftWrapping_Model_Observer
     */
    public function salesEventOrderToQuote($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $storeId = $order->getStore()->getId();
        // Do not import giftwrapping data if order is reordered or GW is not available for order
        $giftWrappingHelper = Mage::helper('enterprise_giftwrapping');
        if ($order->getReordered() || !$giftWrappingHelper->isGiftWrappingAvailableForOrder($storeId)) {
            return $this;
        }
        $quote = $observer->getEvent()->getQuote();
        $quote->setGwId($order->getGwId())
            ->setGwAllowGiftReceipt($order->getGwAllowGiftReceipt())
            ->setGwAddCard($order->getGwAddCard());
        return $this;
    }

    /**
     * Import giftwrapping data from order item to quote item
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftWrapping_Model_Observer
     */
    public function salesEventOrderItemToQuoteItem($observer)
    {
        // @var $orderItem Mage_Sales_Model_Order_Item
        $orderItem = $observer->getEvent()->getOrderItem();
        // Do not import giftwrapping data if order is reordered or GW is not available for items
        $order = $orderItem->getOrder();
        $giftWrappingHelper = Mage::helper('enterprise_giftwrapping');
        if ($order && ($order->getReordered()
            || !$giftWrappingHelper->isGiftWrappingAvailableForItems($order->getStore()->getId()))
        ) {
            return $this;
        }
        $quoteItem = $observer->getEvent()->getQuoteItem();
        $quoteItem->setGwId($orderItem->getGwId())
            ->setGwBasePrice($orderItem->getGwBasePrice())
            ->setGwPrice($orderItem->getGwPrice())
            ->setGwBaseTaxAmount($orderItem->getGwBaseTaxAmount())
            ->setGwTaxAmount($orderItem->getGwTaxAmount());
        return $this;
    }

    /**
     * Add gift wrapping info for item to pdf (invoice, creditmemo)
     *
     * @param Varien_Event_Observer $observer
     */
    public function addGiftWrappingInfoForItemToPdf(Varien_Event_Observer $observer)
    {
        $entityItem = $observer->getEvent()->getEntityItem();
        $orderItem  = $entityItem->getOrderItem();
        if (!$orderItem->getGwPrice()) {
            return;
        }

        $transportObject = $observer->getEvent()->getTransportObject();
        $rendererTypeList = $transportObject->getRendererTypeList();
        $rendererTypeList['giftwrapping'] = 'giftwrapping';
        $transportObject->setRendererTypeList($rendererTypeList);
    }
}
