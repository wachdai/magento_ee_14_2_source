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
 * Customer balance model
 *
 * @method Enterprise_CustomerBalance_Model_Resource_Balance _getResource()
 * @method Enterprise_CustomerBalance_Model_Resource_Balance getResource()
 * @method int getCustomerId()
 * @method Enterprise_CustomerBalance_Model_Balance setCustomerId(int $value)
 * @method int getWebsiteId()
 * @method Enterprise_CustomerBalance_Model_Balance setWebsiteId(int $value)
 * @method Enterprise_CustomerBalance_Model_Balance setAmount(float $value)
 * @method string getBaseCurrencyCode()
 * @method Enterprise_CustomerBalance_Model_Balance setBaseCurrencyCode(string $value)
 *
 * @category    Enterprise
 * @package     Enterprise_CustomerBalance
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CustomerBalance_Model_Balance extends Mage_Core_Model_Abstract
{
    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;

    protected $_eventPrefix = 'customer_balance';
    protected $_eventObject = 'balance';

    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_customerbalance/balance');
    }

    /**
     * @deprecated after 1.3.2.3
     * @param Mage_Customer_Model_Customer $customer
     * @return bool
     */
    public function shouldCustomerHaveOneBalance($customer)
    {
        return false;
    }

    /**
     * Get balance amount
     *
     * @return float
     */
    public function getAmount()
    {
        return (float)$this->getData('amount');
    }

    /**
     * Load balance by customer
     * Website id should either be set or not admin
     *
     * @return Enterprise_CustomerBalance_Model_Balance
     * @throws Mage_Core_Exception
     */
    public function loadByCustomer()
    {
        $this->_ensureCustomer();
        if ($this->hasWebsiteId()) {
            $websiteId = $this->getWebsiteId();
        }
        else {
            if (Mage::app()->getStore()->isAdmin()) {
                Mage::throwException(Mage::helper('enterprise_customerbalance')->__('Website ID must be set.'));
            }
            $websiteId = Mage::app()->getStore()->getWebsiteId();
        }
        $this->getResource()->loadByCustomerAndWebsiteIds($this, $this->getCustomerId(), $websiteId);
        return $this;
    }

    /**
     * Specify whether email notification should be sent
     *
     * @param bool $shouldNotify
     * @param int $storeId
     * @return Enterprise_CustomerBalance_Model_Balance
     * @throws Mage_Core_Exception
     */
    public function setNotifyByEmail($shouldNotify, $storeId = null)
    {
        $this->setData('notify_by_email', $shouldNotify);
        if ($shouldNotify) {
            if (null === $storeId) {
                Mage::throwException(Mage::helper('enterprise_customerbalance')->__('Please set store ID as well.'));
            }
            $this->setStoreId($storeId);
        }
        return $this;

    }

    /**
     * Validate before saving
     *
     * @return Enterprise_CustomerBalance_Model_Balance
     */
    protected function _beforeSave()
    {
        $this->_ensureCustomer();

        // make sure appropriate website was set. Admin website is disallowed
        if ((!$this->hasWebsiteId()) && $this->shouldCustomerHaveOneBalance($this->getCustomer())) {
            $this->setWebsiteId($this->getCustomer()->getWebsiteId());
        }
        if (0 == $this->getWebsiteId()) {
            Mage::throwException(Mage::helper('enterprise_customerbalance')->__('Website ID must be set.'));
        }

        // check history action
        if (!$this->getId()) {
            $this->loadByCustomer();
            if (!$this->getId()) {
                $this->setHistoryAction(Enterprise_CustomerBalance_Model_Balance_History::ACTION_CREATED);
            }
        }
        if (!$this->hasHistoryAction()) {
            $this->setHistoryAction(Enterprise_CustomerBalance_Model_Balance_History::ACTION_UPDATED);
        }

        // check balance delta and email notification settings
        $delta = $this->_prepareAmountDelta();
        if (0 == $delta) {
            $this->setNotifyByEmail(false);
        }
        if ($this->getNotifyByEmail() && !$this->hasStoreId()) {
            Mage::throwException(Mage::helper('enterprise_customerbalance')->__('In order to send email notification, the Store ID must be set.'));
        }

        return parent::_beforeSave();
    }

    /**
     * Update history after saving
     *
     * @return Enterprise_CustomerBalance_Model_Balance
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        // save history action
        if (abs($this->getAmountDelta())) {
            $history = Mage::getModel('enterprise_customerbalance/balance_history')
                ->setBalanceModel($this)
                ->save();
        }

        return $this;
    }

    /**
     * Make sure proper customer information is set. Load customer if required
     *
     * @throws Mage_Core_Exception
     */
    protected function _ensureCustomer()
    {
        if ($this->getCustomer() && $this->getCustomer()->getId()) {
            $this->setCustomerId($this->getCustomer()->getId());
        }
        if (!$this->getCustomerId()) {
            Mage::throwException(Mage::helper('enterprise_customerbalance')->__('Customer ID must be specified.'));
        }
        if (!$this->getCustomer()) {
            $this->setCustomer(Mage::getModel('customer/customer')->load($this->getCustomerId()));
        }
        if (!$this->getCustomer()->getId()) {
            Mage::throwException(Mage::helper('enterprise_customerbalance')->__('Customer is not set or does not exist.'));
        }
    }

    /**
     * Validate & adjust amount change
     *
     * @return float
     */
    protected function _prepareAmountDelta()
    {
        $result = 0;
        if ($this->hasAmountDelta()) {
            $result = (float)$this->getAmountDelta();
            if ($this->getId()) {
                if (($result < 0) && (($this->getAmount() + $result) < 0)) {
                    $result = -1 * $this->getAmount();
                }
            }
            elseif ($result <= 0) {
                $result = 0;
            }
        }
        $this->setAmountDelta($result);
        if (!$this->getId()) {
            $this->setAmount($result);
        }
        else {
            $this->setAmount($this->getAmount() + $result);
        }
        return $result;
    }

    /**
     * Check whether balance completely covers specified quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isFullAmountCovered(Mage_Sales_Model_Quote $quote, $isEstimation = false)
    {
        if (!$isEstimation && !$quote->getUseCustomerBalance()) {
            return false;
        }
        return $this->getAmount() >=
            ((float)$quote->getBaseGrandTotal() + (float)$quote->getBaseCustomerBalAmountUsed());
    }

    /**
     * @deprecated after 1.3.2.3
     */
    public function isFulAmountCovered(Mage_Sales_Model_Quote $quote)
    {
        return $this->isFullAmountCovered($quote);
    }

    /**
     * Update customers balance currency code per website id
     *
     * @param int $websiteId
     * @param string $currencyCode
     * @return Enterprise_CustomerBalance_Model_Balance
     */
    public function setCustomersBalanceCurrencyTo($websiteId, $currencyCode)
    {
        $this->getResource()->setCustomersBalanceCurrencyTo($websiteId, $currencyCode);
        return $this;
    }

    /**
     * Delete customer orphan balances
     *
     * @param int $customerId
     * @return Enterprise_CustomerBalance_Model_Balance
     */
    public function deleteBalancesByCustomerId($customerId)
    {
        $this->getResource()->deleteBalancesByCustomerId($customerId);
        return $this;
    }

    /**
     * Get customer orphan balances count
     *
     * @return Enterprise_CustomerBalance_Model_Balance
     */
    public function getOrphanBalancesCount($customerId)
    {
        return $this->getResource()->getOrphanBalancesCount($customerId);
    }

    /**
     * Public version of afterLoad
     *
     * @return Mage_Core_Model_Abstract
     */
    public function afterLoad()
    {
        return $this->_afterLoad();
    }
}
