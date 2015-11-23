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
 * Form for adding products by SKU
 *
 * @category    Enterprise
 * @package     Enterprise_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Block_Adminhtml_Sales_Order_Create_Sku_Errors
    extends Enterprise_Checkout_Block_Adminhtml_Sku_Errors_Abstract
{
    /**
     * Returns url to configure item
     *
     * @return string
     */
    public function getConfigureUrl()
    {
        return $this->getUrl('*/sales_order_create/configureProductToAdd');
    }

    /**
     * Returns enterprise cart model with custom session for order create page
     *
     * @return Enterprise_Checkout_Model_Cart
     */
    public function getCart()
    {
        if (!$this->_cart) {
            $session = Mage::getSingleton('adminhtml/session_quote');
            $this->_cart = parent::getCart()->setSession($session);
        }
        return $this->_cart;
    }

    /**
     * Returns current store model
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        $storeId = $this->getCart()->getSession()->getStoreId();
        return Mage::app()->getStore($storeId);
    }

    /**
     * Get title of button, that adds products to order
     *
     * @return string
     */
    public function getAddButtonTitle()
    {
        return $this->__('Add Products to Order');
    }
}
