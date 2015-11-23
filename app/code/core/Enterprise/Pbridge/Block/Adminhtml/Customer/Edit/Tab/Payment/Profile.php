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
 * Customer Account Payment Profiles form block
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pbridge_Block_Adminhtml_Customer_Edit_Tab_Payment_Profile
    extends Enterprise_Pbridge_Block_Iframe_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Block template
     * @var string
     */
    protected $_template = 'enterprise/pbridge/customer/edit/tab/payment/profile.phtml';

    /**
     * Default iframe template
     *
     * @var string
     */
    protected $_iframeTemplate = 'enterprise/pbridge/iframe.phtml';

    /**
     * Default iframe height
     *
     * @var string
     */
    protected $_iframeHeight = '600';

    /**
     * Getter for label
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('enterprise_pbridge')->__('Credit Cards');
    }

    /**
     * Getter for title
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('enterprise_pbridge')->__('Credit Cards');
    }

    /**
     * Whether tab can be shown
     * @return bool
     */
    public function canShowTab()
    {
        if (Mage::registry('current_customer')->getId() && $this->_isProfileEnable()) {
            return true;
        }
        return false;
    }

    /**
     * Whether tab is hidden
     * @return bool
     */
    public function isHidden()
    {
        if (Mage::registry('current_customer')->getId() && $this->_isProfileEnable()) {
            return false;
        }
        return true;
    }

    /**
     * Check if payment profiles enabled
     * @return bool
     */
    protected function _isProfileEnable()
    {
        return Mage::getStoreConfigFlag('payment/pbridge/profilestatus', $this->_getCurrentStore());
    }

    /**
     * Precessor tab ID getter
     *
     * @return string
     */
    public function getAfter()
    {
        return 'tags';
    }

    /**
     * Return iframe source URL
     * @return string
     */
    public function getSourceUrl()
    {
        $helper = Mage::helper('enterprise_pbridge');
        $helper->setStoreId($this->_getCurrentStore()->getId());
        return $helper->getPaymentProfileUrl(
            array(
                'billing_address' => $this->_getAddressInfo(),
                'css_url'         => null,
                'customer_id'     => $this->getCustomerIdentifier(),
                'customer_name'   => $this->getCustomerName(),
                'customer_email'  => $this->getCustomerEmail()
            )
        );
    }

    /**
     * Get current customer object
     *
     * @return null|Mage_Customer_Model_Customer
     */
    protected function _getCurrentCustomer()
    {
        if (Mage::registry('current_customer') instanceof Mage_Customer_Model_Customer) {
            return Mage::registry('current_customer');
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
        return $this->_getCurrentCustomer()->getStore();
    }
}
