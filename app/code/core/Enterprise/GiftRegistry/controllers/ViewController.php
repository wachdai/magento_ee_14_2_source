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
 * Gift registry frontend controller
 */
class Enterprise_GiftRegistry_ViewController extends Mage_Core_Controller_Front_Action
{
    /**
     * Check if gift registry is enabled on current store before all other actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::helper('enterprise_giftregistry')->isEnabled()) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return;
        }
    }

    /**
     * View giftregistry list in 'My Account' section
     */
    public function indexAction()
    {
        $entity = Mage::getModel('enterprise_giftregistry/entity');
        $entity->loadByUrlKey($this->getRequest()->getParam('id'));
        if (!$entity->getId() || !$entity->getCustomerId() || !$entity->getTypeId() || !$entity->getIsActive()) {
            $this->_forward('noroute');
            return;
        }

        /** @var Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer');
        $customer->load($entity->getCustomerId());
        $entity->setCustomer($customer);
        Mage::register('current_entity', $entity);

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(Mage::helper('enterprise_giftregistry')->__('Gift Registry Info'));
        }
        $this->renderLayout();
    }

    /**
     * Add specified gift registry items to quote
     */
    public function addToCartAction()
    {
        $items = $this->getRequest()->getParam('items');

        if (!$items || !$this->_validateFormKey()) {
            $this->_redirect('*/*', array('_current' => true));
            return;
        }
        /* @var Mage_Checkout_Model_Cart */
        $cart = Mage::getSingleton('checkout/cart');
        /* @var $session Mage_Wishlist_Model_Session */
        $session    = Mage::getSingleton('customer/session');
        $success = false;

        try {
            $count = 0;
            foreach ($items as $itemId => $itemInfo) {
                $item = Mage::getModel('enterprise_giftregistry/item')->load($itemId);
                $optionCollection = Mage::getModel('enterprise_giftregistry/item_option')->getCollection()
                    ->addItemFilter($itemId);
                $item->setOptions($optionCollection->getOptionsByItem($item));
                if (!$item->getId() || $itemInfo['qty'] < 1 || ($item->getQty() <= $item->getQtyFulfilled())) {
                    continue;
                }
                $item->addToCart($cart, $itemInfo['qty']);
                $count += $itemInfo['qty'];
            }
            $cart->save()->getQuote()->collectTotals();
            $success = true;
            if (!$count) {
                $success = false;
                $session->addError(
                    Mage::helper('enterprise_giftregistry')->__('Please specify the quantity of items that you want to add to cart.')
                );
            }
        } catch (Mage_Core_Exception $e) {
            $session->addError(Mage::helper('enterprise_giftregistry')->__($e->getMessage()));
        } catch (Exception $e) {
            $session->addException($e, Mage::helper('enterprise_giftregistry')->__('Cannot add item to shopping cart'));
            Mage::logException($e);
        }
        if (!$success) {
            $this->_redirect('*/*', array('_current' => true));
        } else {
            $this->_redirect('checkout/cart');
        }
    }
}
