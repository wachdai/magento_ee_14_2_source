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


class Enterprise_CustomerBalance_Model_Total_Quote_Customerbalance extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Init total model, set total code
     */
    public function __construct()
    {
        $this->setCode('customerbalance');
    }

    /**
     * Collect customer balance totals for specified address
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Enterprise_CustomerBalance_Model_Total_Quote_Customerbalance
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        if (!Mage::helper('enterprise_customerbalance')->isEnabled()) {
            return $this;
        }
        $quote = $address->getQuote();
        if (!$quote->getCustomerBalanceCollected()) {
            $quote->setBaseCustomerBalAmountUsed(0);
            $quote->setCustomerBalanceAmountUsed(0);

            $quote->setCustomerBalanceCollected(true);
        }

        $baseBalance = $balance = 0;
        if ($quote->getCustomer()->getId() && $quote->getUseCustomerBalance()) {
            $baseBalance = Mage::helper('enterprise_customerbalance')->getCustomerBalanceModelFromSalesEntity($quote)
                ->getAmount();
            $balance = $quote->getStore()->convertPrice($baseBalance);
        }

        $baseAmountLeft = $baseBalance - $quote->getBaseCustomerBalAmountUsed();
        $amountLeft = $balance - $quote->getCustomerBalanceAmountUsed();

        if ($baseAmountLeft >= $address->getBaseGrandTotal()) {
            $baseUsed = $address->getBaseGrandTotal();
            $used = $address->getGrandTotal();

            $address->setBaseGrandTotal(0);
            $address->setGrandTotal(0);
        } else {
            $baseUsed = $baseAmountLeft;
            $used = $amountLeft;

            $address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseAmountLeft);
            $address->setGrandTotal($address->getGrandTotal() - $amountLeft);
        }

        $baseTotalUsed = $quote->getBaseCustomerBalAmountUsed() + $baseUsed;
        $totalUsed = $quote->getCustomerBalanceAmountUsed() + $used;

        $quote->setBaseCustomerBalAmountUsed($baseTotalUsed);
        $quote->setCustomerBalanceAmountUsed($totalUsed);

        $address->setBaseCustomerBalanceAmount($baseUsed);
        $address->setCustomerBalanceAmount($used);

        return $this;
    }

    /**
     * Return shopping cart total row items
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Enterprise_CustomerBalance_Model_Total_Quote_Customerbalance
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if (!Mage::helper('enterprise_customerbalance')->isEnabled()) {
            return $this;
        }
        if ($address->getCustomerBalanceAmount()) {
            $address->addTotal(array(
                'code'=>$this->getCode(),
                'title'=>Mage::helper('enterprise_customerbalance')->__('Store Credit'),
                'value'=>-$address->getCustomerBalanceAmount(),
            ));
        }
        return $this;
    }
}
