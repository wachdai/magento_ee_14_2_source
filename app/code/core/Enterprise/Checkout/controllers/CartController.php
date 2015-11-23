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
 * Enterprise checkout cart controller
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_CartController extends Mage_Core_Controller_Front_Action
{
    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get customer session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Retrieve helper instance
     *
     * @return Enterprise_Checkout_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('enterprise_checkout');
    }

    /**
     * Get cart model instance
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get failed items cart model instance
     *
     * @return Enterprise_Checkout_Model_Cart
     */
    protected function _getFailedItemsCart()
    {
        return Mage::getSingleton('enterprise_checkout/cart')
            ->setContext(Enterprise_Checkout_Model_Cart::CONTEXT_FRONTEND);
    }

    /**
     * Add to cart products, which SKU specified in request
     *
     * @return void
     */
    public function advancedAddAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*');
        }
        // check empty data
        /** @var $helper Enterprise_Checkout_Helper_Data */
        $helper = Mage::helper('enterprise_checkout');
        $items = $this->getRequest()->getParam('items');
        foreach ($items as $k => $item) {
            if (empty($item['sku'])) {
                unset($items[$k]);
            }
        }
        if (empty($items) && !$helper->isSkuFileUploaded($this->getRequest())) {
            $this->_getSession()->addError($helper->getSkuEmptyDataMessageText());
            $this->_redirect('checkout/cart');
            return;
        }

        try {
            // perform data
            $cart = $this->_getFailedItemsCart()
                ->prepareAddProductsBySku($items)
                ->saveAffectedProducts();

            $this->_getSession()->addMessages($cart->getMessages());

            if ($cart->hasErrorMessage()) {
                Mage::throwException($cart->getErrorMessage());
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
        }

        $this->_redirect('checkout/cart');
    }

    /**
     * Add failed items to cart
     *
     * @return null
     */
    public function addFailedItemsAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*');
        }
        $failedItemsCart = $this->_getFailedItemsCart()->removeAllAffectedItems();
        $failedItems = $this->getRequest()->getParam('failed', array());
        foreach ($failedItems as $data) {
            $data += array('sku' => '', 'qty' => '');
            $failedItemsCart->prepareAddProductBySku($data['sku'], $data['qty']);
        }
        $failedItemsCart->saveAffectedProducts();
        $this->_redirect('checkout/cart');
    }

    /**
     * Remove failed items from storage
     *
     * @return void
     */
    public function removeFailedAction()
    {
        $removed = $this->_getFailedItemsCart()->removeAffectedItem(
            Mage::helper('core/url')->urlDecode($this->getRequest()->getParam('sku'))
        );

        if ($removed) {
            $this->_getSession()->addSuccess(
                $this->__('Item was successfully removed.')
            );
        }

        $this->_redirect('checkout/cart');
    }

    /**
     * Remove all failed items from storage
     *
     * @return void
     */
    public function removeAllFailedAction()
    {
        $this->_getFailedItemsCart()->removeAllAffectedItems();
        $this->_getSession()->addSuccess(
            $this->__('Items were successfully removed.')
        );
        $this->_redirect('checkout/cart');
    }

    /**
     * Configure failed item options
     *
     * @return void
     */
    public function configureFailedAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $qty = $this->getRequest()->getParam('qty', 1);

        try {
            $params = new Varien_Object();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);

            $buyRequest = new Varien_Object(array(
                'product'   => $id,
                'qty'       => $qty
            ));

            $params->setBuyRequest($buyRequest);

            Mage::helper('catalog/product_view')->prepareAndRender($id, $this, $params);
        } catch (Mage_Core_Exception $e) {
            $this->_getCustomerSession()->addError($e->getMessage());
            $this->_redirect('*');
            return;
        } catch (Exception $e) {
            $this->_getCustomerSession()->addError($this->__('Cannot configure product'));
            Mage::logException($e);
            $this->_redirect('*');
            return;
        }
    }

    /**
     * Update failed items options data and add it to cart
     *
     * @return void
     */
    public function updateFailedItemOptionsAction()
    {
        $hasError = false;
        $id = (int)$this->getRequest()->getParam('id');
        $buyRequest = new Varien_Object($this->getRequest()->getParams());
        try {
            $cart = $this->_getCart();

            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($id);

            $cart->addProduct($product, $buyRequest)->save();

            $this->_getFailedItemsCart()->removeAffectedItem($this->getRequest()->getParam('sku'));

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $productName = Mage::helper('core')->escapeHtml($product->getName());
                    $message = $this->__('%s was added to your shopping cart.', $productName);
                    $this->_getSession()->addSuccess($message);
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $hasError = true;
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot add product'));
            Mage::logException($e);
            $hasError = true;
        }

        if ($hasError) {
            $this->_redirect('checkout/cart/configureFailed', array('id' => $id, 'sku' => $buyRequest->getSku()));
        } else {
            $this->_redirect('checkout/cart');
        }
    }
}
