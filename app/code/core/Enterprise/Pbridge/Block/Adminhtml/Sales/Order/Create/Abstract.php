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
 * @package     Enterprise_Pbridge
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Paypal Direct payment block
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pbridge_Block_Adminhtml_Sales_Order_Create_Abstract
    extends Enterprise_Pbridge_Block_Payment_Form_Abstract
{
    /**
     * Return url model class for adminhtml
     *
     *  @return string
     */
    protected function _getUrlModelClass()
    {
        return 'adminhtml/url';
    }

    /**
     * Adminhtml template for payment form block
     *
     * @var string
     */
    protected $_template = 'enterprise/pbridge/sales/order/create/pbridge.phtml';

    /**
     * Adminhtml Iframe block type
     *
     * @var string
     */
    protected $_iframeBlockType = 'adminhtml/template';

    /**
     * Adminhtml iframe template
     *
     * @var string
     */
    protected $_iframeTemplate = 'enterprise/pbridge/iframe.phtml';

    /**
     * Return 3D validation flag
     *
     * @return bool
     */
    public function is3dSecureEnabled()
    {
        if ($this->hasMethod() && $this->getMethod()->is3dSecureEnabled()) {
            return true;
        }
        return parent::is3dSecureEnabled();
    }

    /**
     * Return redirect url for Payment Bridge application
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('*/pbridge/result',
            array('store' => $this->getQuote()->getStoreId())
        );
    }

    /**
     * Getter
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('adminhtml/session_quote')->getQuote();
    }

    /**
     * Generate and return variation code
     *
     * @return string
     */
    protected function _getVariation()
    {
        return Mage::app()->getConfig()->getNode('default/payment/pbridge/merchantcode')
            . '_' . $this->getQuote()->getStore()->getWebsite()->getCode();
    }

    /**
     * Disable external CSS in admin order creation
     * @return null
     */
    public function getCssUrl()
    {
        return null;
    }

    /**
     * Get current customer object
     *
     * @return null|Mage_Customer_Model_Customer
     */
    protected function _getCurrentCustomer()
    {
        if (Mage::getSingleton('adminhtml/session_quote')->getCustomer() instanceof Mage_Customer_Model_Customer) {
            return Mage::getSingleton('adminhtml/session_quote')->getCustomer();
        }

        return null;
    }

    /**
     * Return store for current context
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getCurrentStore()
    {
        return $this->getQuote()->getStore();
    }
}
