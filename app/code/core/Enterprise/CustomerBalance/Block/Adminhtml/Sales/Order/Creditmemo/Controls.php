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
 * Refund to customer balance functionality block
 *
 */
class Enterprise_CustomerBalance_Block_Adminhtml_Sales_Order_Creditmemo_Controls
 extends Mage_Core_Block_Template
{
    /**
     * Check whether refund to customerbalance is available
     *
     * @return bool
     */
    public function canRefundToCustomerBalance()
    {
        if ($this->_getCreditmemo()->getOrder()->getCustomerIsGuest()) {
            return false;
        }
        return true;
    }

    /**
     * Check whether real amount can be refunded to customer balance
     *
     * @return bool
     */
    public function canRefundMoneyToCustomerBalance()
    {
        if ($this->_getCreditmemo()->getGrandTotal()) {
            return false;
        }

        if ($this->_getCreditmemo()->getOrder()->getCustomerIsGuest()) {
            return false;
        }
        return true;
    }

    /**
     * Pre Populate amount to be refunded to customerbalance
     *
     * @return float
     */
    public function getReturnValue()
    {
        $max = $this->_getCreditmemo()->getCustomerBalanceReturnMax();

        //We want to subtract the reward points when returning to the customer
        $rewardCurrencyBalance = $this->_getCreditmemo()->getRewardCurrencyAmount();

        if ($rewardCurrencyBalance > 0 && $rewardCurrencyBalance < $max) {
            $max = $max - $rewardCurrencyBalance;
        }

        if ($max) {
            return $max;
        }
        return 0;
    }

    /**
     * Fetches the Credit Memo Object
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    protected function _getCreditmemo()
    {
        return Mage::registry('current_creditmemo');
    }
}
