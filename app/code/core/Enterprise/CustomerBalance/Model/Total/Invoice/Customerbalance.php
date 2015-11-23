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

class Enterprise_CustomerBalance_Model_Total_Invoice_Customerbalance extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Collect customer balance totals for invoice
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Enterprise_CustomerBalance_Model_Total_Invoice_Customerbalance
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        if (!Mage::helper('enterprise_customerbalance')->isEnabled()) {
            return $this;
        }
        $order = $invoice->getOrder();
        if ($order->getBaseCustomerBalanceAmount() && $order->getBaseCustomerBalanceInvoiced() != $order->getBaseCustomerBalanceAmount()) {
            $gcaLeft = $order->getBaseCustomerBalanceAmount() - $order->getBaseCustomerBalanceInvoiced();
            $used = 0;
            $baseUsed = 0;
            if ($gcaLeft >= $invoice->getBaseGrandTotal()) {
                $baseUsed = $invoice->getBaseGrandTotal();
                $used = $invoice->getGrandTotal();

                $invoice->setBaseGrandTotal(0);
                $invoice->setGrandTotal(0);
            } else {
                $baseUsed = $order->getBaseCustomerBalanceAmount() - $order->getBaseCustomerBalanceInvoiced();
                $used = $order->getCustomerBalanceAmount() - $order->getCustomerBalanceInvoiced();

                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()-$baseUsed);
                $invoice->setGrandTotal($invoice->getGrandTotal()-$used);
            }

            $invoice->setBaseCustomerBalanceAmount($baseUsed);
            $invoice->setCustomerBalanceAmount($used);
        }
        return $this;
    }
}
