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
 * @package     Enterprise_CustomerBalance
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Customer balance as an additional payment option during checkout
 *
 * @category   Enterprise
 * @package    Enterprise_CustomerBalance
 */
class Enterprise_CustomerBalance_Block_Checkout_Onepage_Payment_Additional extends Mage_Core_Block_Template
{
    /**
     * Customer balance instance
     *
     * @var Enterprise_CustomerBalance_Model_Balance
     */
    protected $_balanceModel = null;

    /**
     * Get quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Getter
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->_getQuote();
    }

    /**
     * Get balance instance
     *
     * @return Enterprise_CustomerBalance_Model_Balance
     */
    protected function _getBalanceModel()
    {
        if (is_null($this->_balanceModel)) {
            $this->_balanceModel = Mage::getModel('enterprise_customerbalance/balance')
                ->setCustomer($this->_getCustomer())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

            //load customer balance for customer in case we have
            //registered customer and this is not guest checkout
            if ($this->_getCustomer()->getId()) {
                $this->_balanceModel->loadByCustomer();
            }
        }
        return $this->_balanceModel;
    }

    /**
     * Get customer instance
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Can display customer balance container
     *
     * @return bool
     */
    public function isDisplayContainer()
    {
        if (!$this->_getCustomer()->getId()) {
            return false;
        }

        if (!$this->getBalance()) {
            return false;
        }

        return true;
    }

    /**
     * Check whether customer balance is allowed as additional payment option
     *
     * @return bool
     */
    public function isAllowed()
    {
        if (!$this->isDisplayContainer()) {
            return false;
        }

        if (!$this->getAmountToCharge()) {
            return false;
        }

        return true;
    }

    /**
     * Get balance amount
     *
     * @return float
     */
    public function getBalance()
    {
        if (!$this->_getCustomer()->getId()) {
            return 0;
        }
        return $this->_getBalanceModel()->getAmount();
    }

    /**
     * Get balance amount to be charged
     *
     * @return float
     */
    public function getAmountToCharge()
    {
        if ($this->isCustomerBalanceUsed()) {
            return $this->_getQuote()->getCustomerBalanceAmountUsed();
        }

        return min($this->getBalance(), $this->_getQuote()->getBaseGrandTotal());
    }

    /**
     * Check whether customer balance is used in current quote
     *
     * @return bool
     */
    public function isCustomerBalanceUsed() {
        return $this->_getQuote()->getUseCustomerBalance();
    }

    /**
     * Check whether customer balance fully covers quote
     *
     * @return bool
     */
    public function isFullyPaidAfterApplication()
    {
        return $this->_getBalanceModel()->isFullAmountCovered($this->_getQuote(), true);
    }
}
