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
 * Admin Checkout main form container
 *
 * @category    Enterprise
 * @package     Enterprise_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Block_Adminhtml_Manage extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('checkout_manage_container');

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create')) {
            $this->_updateButton('save', 'label', Mage::helper('sales')->__('Create Order'));
            $this->_updateButton('save', 'onclick', 'setLocation(\'' . $this->getCreateOrderUrl() . '\');');
        } else {
            $this->_removeButton('save');
        }
        $this->_removeButton('reset');
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getBackUrl() . '\');');
    }

    /**
     * Prepare layout, create buttons
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/enterprise_checkout/update')) {
            return $this;
        }

        $this->setChild('add_products_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('enterprise_checkout')->__('Add Products'),
                    'onclick' => 'checkoutObj.searchProducts()',
                    'class' => 'add',
                    'id' => 'add_products_btn'
                ))
        );

        $this->setChild('update_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('enterprise_checkout')->__('Update Items and Qty\'s'),
                    'onclick' => 'checkoutObj.updateItems()',
                    'style' => 'float:right; margin-left: 5px;'
                ))
        );
        $deleteAllConfirmString = Mage::helper('core')->jsQuoteEscape(
            Mage::helper('enterprise_checkout')->__('Are you sure you want to delete all items from shopping cart?')
        );
        $this->setChild('empty_customer_cart_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('enterprise_checkout')->__('Clear Shopping Cart'),
                    'onclick' => 'confirm(\'' . $deleteAllConfirmString . '\') '
                        . ' && checkoutObj.updateItems({\'empty_customer_cart\': 1})',
                    'style' => 'float:right;'
                ))
        );

        $this->setChild('addto_cart_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('enterprise_checkout')->__('Add Selected Product(s) to Shopping Cart'),
                    'onclick' => 'checkoutObj.addToCart()',
                    'class' => 'add button-to-cart'
                ))
        );

        $this->setChild('cancel_add_products_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('enterprise_checkout')->__('Cancel'),
                    'onclick' => 'checkoutObj.cancelSearch()',
                    'class' => 'cancel'
                ))
        );

        return $this;
    }

    /**
     * Rewrite for getFormHtml()
     *
     * @return string
     */
    public function getFormHtml()
    {
        return '';
    }

    /**
     * Return header title
     *
     * @return string
     */
    public function getHeaderText()
    {
        $customer = $this->escapeHtml($this->_getCustomer()->getName());
        $store = $this->escapeHtml($this->_getStore()->getName());
        return Mage::helper('enterprise_checkout')->__('Shopping Cart for %s in %s', $customer, $store);
    }

    /**
     * Return current customer from regisrty
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        return Mage::registry('checkout_current_customer');
    }

    /**
     * Return current store from regisrty
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        return Mage::registry('checkout_current_store');
    }

    /**
     * Return URL to customer edit page
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->_getCustomer()->getId()) {
            return $this->getUrl('*/customer/edit', array('id' => $this->_getCustomer()->getId()));
        } else {
            return $this->getUrl('*/customer');
        }
    }

    /**
     * Return URL to controller action
     *
     * @return string
     */
    public function getActionUrl($action)
    {
        return $this->getUrl('*/*/' . $action, array('_current' => true));
    }

    /**
     * Return URL to admin order creation
     *
     * @return string
     */
    public function getCreateOrderUrl()
    {
        return $this->getUrl('*/*/createOrder', array('_current' => true));
    }

    /**
     * Retrieve url for loading blocks
     *
     * @return string
     */
    public function getLoadBlockUrl()
    {
        return $this->getUrl('*/*/loadBlock');
    }

    public function getOrderDataJson()
    {
        $actionUrls = array(
            'cart' => $this->getActionUrl('cart'),
            'applyCoupon' => $this->getActionUrl('applyCoupon'),
            'coupon' => $this->getActionUrl('coupon')
        );

        $messages = array(
            'chooseProducts' => $this->__('Choose some products to add to shopping cart.')
        );

        $data = array(
            'action_urls' => $actionUrls,
            'messages' => $messages,
            'customer_id' => $this->_getCustomer()->getId(),
            'store_id' => $this->_getStore()->getId()
        );

        return Mage::helper('core')->jsonEncode($data);
    }

    /**
     * Retrieve curency name by code
     *
     * @param   string $code
     * @return  string
     */
    public function getCurrencySymbol($code)
    {
        $currency = Mage::app()->getLocale()->currency($code);
        return $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
    }

    /**
     * Retrieve current order currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_getStore()->getCurrentCurrencyCode();
    }
}
