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
 * Customer balance observer
 *
 */
class Enterprise_CustomerBalance_Model_Observer
{
    /**
     * Prepare customer balance POST data
     *
     * @param Varien_Event_Observer $observer
     */
    public function prepareCustomerBalanceSave($observer)
    {
        if (!Mage::helper('enterprise_customerbalance')->isEnabled()) {
            return;
        }
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = $observer->getCustomer();
        /* @var $request Mage_Core_Controller_Request_Http */
        $request = $observer->getRequest();
        if ($data = $request->getPost('customerbalance')) {
            $customer->setCustomerBalanceData($data);
        }
    }

    /**
     * Customer balance update after save
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerSaveAfter($observer)
    {
        if (!Mage::helper('enterprise_customerbalance')->isEnabled()) {
            return;
        }
        if ($data = $observer->getCustomer()->getCustomerBalanceData()) {
            if (!empty($data['amount_delta'])) {
                $balance = Mage::getModel('enterprise_customerbalance/balance')
                    ->setCustomer($observer->getCustomer())
                    ->setWebsiteId(
                        isset($data['website_id']) ? $data['website_id'] : $observer->getCustomer()->getWebsiteId()
                    )
                    ->setAmountDelta($data['amount_delta'])
                    ->setComment($data['comment'])
                ;
                if (isset($data['notify_by_email']) && isset($data['store_id'])) {
                    $balance->setNotifyByEmail(true, $data['store_id']);
                }
                $balance->save();
            }
        }
    }

    /**
     * Check for customer balance use switch & update payment info
     *
     * @param Varien_Event_Observer $observer
     */
    public function paymentDataImport(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_customerbalance')->isEnabled()) {
            return;
        }

        $input = $observer->getEvent()->getInput();
        $payment = $observer->getEvent()->getPayment();
        $this->_importPaymentData($payment->getQuote(), $input, $input->getUseCustomerBalance());
    }

    /**
     * Check store credit balance
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  Enterprise_CustomerBalance_Model_Observer
     */
    protected function _checkStoreCreditBalance(Mage_Sales_Model_Order $order)
    {
        if ($order->getBaseCustomerBalanceAmount() > 0) {
            $baseBalance = Mage::helper('enterprise_customerbalance')->getCustomerBalanceModelFromSalesEntity($order)
                ->getAmount();

            if (($order->getBaseCustomerBalanceAmount() - $baseBalance) >= Mage_Sales_Helper_Data::PRECISION_VALUE) {
                Mage::getSingleton('checkout/type_onepage')
                    ->getCheckout()
                    ->setUpdateSection('payment-method')
                    ->setGotoSection('payment');

                Mage::throwException(
                    Mage::helper('enterprise_customerbalance')->__('Not enough Store Credit Amount to complete this Order.')
                );
            }
        }

        return $this;
    }

    /**
     * Validate balance just before placing an order
     *
     * @param   Varien_Event_Observer $observer
     * @return  Enterprise_CustomerBalance_Model_Observer
     */
    public function processBeforeOrderPlace(Varien_Event_Observer $observer)
    {
        if (Mage::helper('enterprise_customerbalance')->isEnabled()) {
            $order = $observer->getEvent()->getOrder();
            $this->_checkStoreCreditBalance($order);
        }

        return $this;
    }

    /**
     * Check if customer balance was used in quote and reduce balance if so
     *
     * @param   Varien_Event_Observer $observer
     * @return  Enterprise_CustomerBalance_Model_Observer
     */
    public function processOrderPlace(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_customerbalance')->isEnabled()) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        if ($order->getBaseCustomerBalanceAmount() > 0) {
            $this->_checkStoreCreditBalance($order);

            $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();
            Mage::getModel('enterprise_customerbalance/balance')
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId($websiteId)
                ->setAmountDelta(-$order->getBaseCustomerBalanceAmount())
                ->setHistoryAction(Enterprise_CustomerBalance_Model_Balance_History::ACTION_USED)
                ->setOrder($order)
                ->save();
        }

        return $this;
    }

    /**
     * Revert authorized store credit amount for order
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  Enterprise_CustomerBalance_Model_Observer
     */
    protected function _revertStoreCreditForOrder(Mage_Sales_Model_Order $order)
    {
        if (!$order->getCustomerId() || !$order->getBaseCustomerBalanceAmount()) {
            return $this;
        }

        Mage::getModel('enterprise_customerbalance/balance')
            ->setCustomerId($order->getCustomerId())
            ->setWebsiteId(Mage::app()->getStore($order->getStoreId())->getWebsiteId())
            ->setAmountDelta($order->getBaseCustomerBalanceAmount())
            ->setHistoryAction(Enterprise_CustomerBalance_Model_Balance_History::ACTION_REVERTED)
            ->setOrder($order)
            ->save();

        return $this;
    }

    /**
     * Revert store credit if order was not placed
     *
     * @param   Varien_Event_Observer $observer
     * @return  Enterprise_CustomerBalance_Model_Observer
     */
    public function revertStoreCredit(Varien_Event_Observer $observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();
        if ($order) {
            $this->_revertStoreCreditForOrder($order);
        }

        return $this;
    }

    /**
     * Revert authorized store credit amounts for all orders
     *
     * @param   Varien_Event_Observer $observer
     * @return  Enterprise_CustomerBalance_Model_Observer
     */
    public function revertStoreCreditForAllOrders(Varien_Event_Observer $observer)
    {
        $orders = $observer->getEvent()->getOrders();

        foreach ($orders as $order) {
            $this->_revertStoreCreditForOrder($order);
        }

        return $this;
    }

    /**
     * Disable entire customerbalance layout
     *
     * @param Varien_Event_Observer $observer
     */
    public function disableLayout($observer)
    {
        if (!Mage::helper('enterprise_customerbalance')->isEnabled()) {
            unset($observer->getUpdates()->enterprise_customerbalance);
        }
    }

    /**
     * The same as paymentDataImport(), but for admin checkout
     *
     * @param Varien_Event_Observer $observer
     */
    public function processOrderCreationData(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_customerbalance')->isEnabled()) {
            return $this;
        }
        $quote = $observer->getEvent()->getOrderCreateModel()->getQuote();
        $request = $observer->getEvent()->getRequest();
        if (isset($request['payment']) && isset($request['payment']['use_customer_balance'])) {
            $this->_importPaymentData($quote, $quote->getPayment(),
                (bool)(int)$request['payment']['use_customer_balance']);
        }
    }

    /**
     * Analyze payment data for quote and set free shipping if grand total is covered by balance
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Varien_Object|Mage_Sales_Model_Quote_Payment $payment
     * @param bool $shouldUseBalance
     */
    protected function _importPaymentData($quote, $payment, $shouldUseBalance)
    {
        $store = Mage::app()->getStore($quote->getStoreId());
        if (!$quote || !$quote->getCustomerId()) {
            return;
        }
        $quote->setUseCustomerBalance($shouldUseBalance);
        if ($shouldUseBalance) {
            $balance = Mage::getModel('enterprise_customerbalance/balance')
                ->setCustomerId($quote->getCustomerId())
                ->setWebsiteId($store->getWebsiteId())
                ->loadByCustomer();
            if ($balance) {
                $quote->setCustomerBalanceInstance($balance);
                if (!$payment->getMethod()) {
                    $payment->setMethod('free');
                }
            }
            else {
                $quote->setUseCustomerBalance(false);
            }
        }
    }

    /**
     * Make only Zero Subtotal Checkout enabled if SC covers entire balance
     *
     * The Customerbalance instance must already be in the quote
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function togglePaymentMethods($observer)
    {
        if (!Mage::helper('enterprise_customerbalance')->isEnabled()) {
            return;
        }

        $quote = $observer->getEvent()->getQuote();
        if (!$quote) {
            return;
        }

        $balance = $quote->getCustomerBalanceInstance();
        if (!$balance) {
            return;
        }

        // disable all payment methods and enable only Zero Subtotal Checkout
        if ($balance->isFullAmountCovered($quote)) {
            $paymentMethod = $observer->getEvent()->getMethodInstance()->getCode();
            $result = $observer->getEvent()->getResult();
            $result->isAvailable = $paymentMethod === 'free' && empty($result->isDeniedInConfig);
        }
    }

    /**
     * Set the flag that we need to collect overall totals
     *
     * @param Varien_Event_Observer $observer
     */
    public function quoteCollectTotalsBefore(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quote->setCustomerBalanceCollected(false);
    }

    /**
     * Set the source customer balance usage flag into new quote
     *
     * @param Varien_Event_Observer $observer
     */
    public function quoteMergeAfter(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $source = $observer->getEvent()->getSource();

        if ($source->getUseCustomerBalance()) {
            $quote->setUseCustomerBalance($source->getUseCustomerBalance());
        }
    }


    /**
     * Increase order customer_balance_invoiced attribute based on created invoice
     * used for event: sales_order_invoice_save_after
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CustomerBalance_Model_Observer
     */
    public function increaseOrderInvoicedAmount(Varien_Event_Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();

        /**
         * Update customer balance only if invoice is just created
         */
        if ($invoice->getOrigData() === null && $invoice->getBaseCustomerBalanceAmount()) {
            $order->setBaseCustomerBalanceInvoiced(
                $order->getBaseCustomerBalanceInvoiced() + $invoice->getBaseCustomerBalanceAmount()
            );
            $order->setCustomerBalanceInvoiced(
                $order->getCustomerBalanceInvoiced() + $invoice->getCustomerBalanceAmount()
            );
        }
        /**
         * Because of order doesn't save second time, added forced saving below attributes
         */
        $order->getResource()->saveAttribute($order, 'base_customer_balance_invoiced');
        $order->getResource()->saveAttribute($order, 'customer_balance_invoiced');
        return $this;
    }


    /**
     * Refund process
     * used for event: sales_order_creditmemo_save_after
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CustomerBalance_Model_Observer
     */
    public function creditmemoSaveAfter(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();

        if ($creditmemo->getAutomaticallyCreated()) {
            if (Mage::helper('enterprise_customerbalance')->isAutoRefundEnabled()) {
                $creditmemo->setCustomerBalanceRefundFlag(true)
                    ->setCustomerBalTotalRefunded($creditmemo->getCustomerBalanceAmount())
                    ->setBsCustomerBalTotalRefunded($creditmemo->getBaseCustomerBalanceAmount());
            } else {
                return $this;
            }
        }
        $customerBalanceReturnMax = ($creditmemo->getCustomerBalanceReturnMax() === null) ? 0 :
            $creditmemo->getCustomerBalanceReturnMax();

        if ((float)(string)$creditmemo->getCustomerBalanceTotalRefunded() > (float)(string)$customerBalanceReturnMax) {
            Mage::throwException(
                Mage::helper('enterprise_customerbalance')->__('Store credit amount cannot exceed order amount.')
            );
        }
        //doing actual refund to customer balance if user have submitted refund form
        if ($creditmemo->getCustomerBalanceRefundFlag() && $creditmemo->getBsCustomerBalTotalRefunded()) {
            $order->setBsCustomerBalTotalRefunded(
                $order->getBsCustomerBalTotalRefunded() + $creditmemo->getBsCustomerBalTotalRefunded()
            );
            $order->setCustomerBalTotalRefunded(
                $order->getCustomerBalTotalRefunded() + $creditmemo->getCustomerBalTotalRefunded()
            );

            $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();

            $balance = Mage::getModel('enterprise_customerbalance/balance')
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId($websiteId)
                ->setAmountDelta($creditmemo->getBsCustomerBalTotalRefunded())
                ->setHistoryAction(Enterprise_CustomerBalance_Model_Balance_History::ACTION_REFUNDED)
                ->setOrder($order)
                ->setCreditMemo($creditmemo)
                ->save();
        }

        return $this;
    }

    /**
     * Set refund flag to creditmemo based on user input
     * used for event: adminhtml_sales_order_creditmemo_register_before
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CustomerBalance_Model_Observer
     */
    public function creditmemoDataImport(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $creditmemo = $observer->getEvent()->getCreditmemo();

        $input = $request->getParam('creditmemo');

        if (isset($input['refund_customerbalance_return']) && isset($input['refund_customerbalance_return_enable'])) {
            $enable = $input['refund_customerbalance_return_enable'];
            $amount = $input['refund_customerbalance_return'];
            if ($enable && is_numeric($amount)) {
                $amount = max(0, min($creditmemo->getBaseCustomerBalanceReturnMax(), $amount));
                if ($amount) {
                    $amount = $creditmemo->getStore()->roundPrice($amount);
                    $creditmemo->setBsCustomerBalTotalRefunded($amount);

                    $amount = $creditmemo->getStore()->roundPrice(
                        $amount*$creditmemo->getOrder()->getStoreToOrderRate()
                    );
                    $creditmemo->setCustomerBalTotalRefunded($amount);
                    //setting flag to make actual refund to customer balance after creditmemo save
                    $creditmemo->setCustomerBalanceRefundFlag(true);
                    //allow online refund
                    $creditmemo->setPaymentRefundDisallowed(false);
                }
            }
        }

        if (isset($input['refund_customerbalance']) && $input['refund_customerbalance']) {
            $creditmemo->setRefundCustomerBalance(true);
        }

        if (isset($input['refund_real_customerbalance']) && $input['refund_real_customerbalance']) {
            $creditmemo->setRefundRealCustomerBalance(true);
            $creditmemo->setPaymentRefundDisallowed(true);
        }

        return $this;
    }

    /**
     * Set forced canCreditmemo flag
     * used for event: sales_order_load_after
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CustomerBalance_Model_Observer
     */
    public function salesOrderLoadAfter(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($order->canUnhold()) {
            return $this;
        }

        if ($order->isCanceled() ||
            $order->getState() === Mage_Sales_Model_Order::STATE_CLOSED ) {
            return $this;
        }

        if ($order->getCustomerBalanceInvoiced() - $order->getCustomerBalanceRefunded() > 0) {
            $order->setForcedCanCreditmemo(true);
        }

        return $this;
    }

    /**
     * Set refund amount to creditmemo
     * used for event: sales_order_creditmemo_refund
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CustomerBalance_Model_Observer
     */
    public function refund(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();


        if ($creditmemo->getRefundRealCustomerBalance() && $creditmemo->getBaseGrandTotal()) {
            $baseAmount = $creditmemo->getBaseGrandTotal();
            $amount = $creditmemo->getGrandTotal();

            $creditmemo->setBsCustomerBalTotalRefunded($creditmemo->getBsCustomerBalTotalRefunded() + $baseAmount);
            $creditmemo->setCustomerBalTotalRefunded($creditmemo->getCustomerBalTotalRefunded() + $amount);
        }

        if ($creditmemo->getBaseCustomerBalanceAmount()) {
            if ($creditmemo->getRefundCustomerBalance()) {
                $baseAmount = $creditmemo->getBaseCustomerBalanceAmount();
                $amount = $creditmemo->getCustomerBalanceAmount();

                $creditmemo->setBsCustomerBalTotalRefunded($creditmemo->getBsCustomerBalTotalRefunded() + $baseAmount);
                $creditmemo->setCustomerBalTotalRefunded($creditmemo->getCustomerBalTotalRefunded() + $amount);
            }

            $order->setBaseCustomerBalanceRefunded(
                $order->getBaseCustomerBalanceRefunded() + $creditmemo->getBaseCustomerBalanceAmount()
            );
            $order->setCustomerBalanceRefunded(
                $order->getCustomerBalanceRefunded() + $creditmemo->getCustomerBalanceAmount()
            );

            // we need to update flag after credit memo was refunded and order's properties changed
            if ($order->getCustomerBalanceInvoiced() > 0
                && $order->getCustomerBalanceInvoiced() == $order->getCustomerBalanceRefunded()
            ) {
                $order->setForcedCanCreditmemo(false);
            }
        }

        return $this;
    }

    /**
     * Modify the amount of invoiced funds for which reward points should not be voided after refund.
     * Prevent voiding of reward points for amount returned to store credit.
     *
     * @param Varien_Event_Observer $observer
     */
    public function modifyRewardedAmountOnRefund(Varien_Event_Observer $observer)
    {
        /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $result = $observer->getEvent()->getResult();
        $order = $creditmemo->getOrder();

        $rewardedAmountAfterRefund = $result->getRewardedAmountAfterRefund();

        $customerBalanceTotalRefunded = $order->getBaseCustomerBalanceTotalRefunded();
        $rewardedAmountRefunded = $order->getBaseTotalRefunded() - $order->getBaseTaxRefunded()
            - $order->getBaseShippingRefunded();
        if ($customerBalanceTotalRefunded > $rewardedAmountRefunded) {
            $rewardedAmountAfterRefund += $rewardedAmountRefunded;
        } else {
            $rewardedAmountAfterRefund += $customerBalanceTotalRefunded;
        }

        $result->setRewardedAmountAfterRefund($rewardedAmountAfterRefund);
    }

    /**
     * Defined in Logging/etc/logging.xml - special handler for setting second action for customerBalance change
     *
     * @param string action
     */
    public function predispatchPrepareLogging($action) {
        $request = Mage::app()->getRequest();
        $data = $request->getParam('customerbalance');
        if (isset($data['amount_delta']) && $data['amount_delta'] != '') {
            $actions = Mage::registry('enterprise_logged_actions');
            if (!is_array($actions)) {
                $actions = array($actions);
            }
            $actions[] = 'adminhtml_customerbalance_save';
            Mage::unregister('enterprise_logged_actions');
            Mage::register('enterprise_logged_actions', $actions);
        }
    }

    /**
     * Set customers balance currency code to website base currency code on website deletion
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CustomerBalance_Model_Observer
     */
    public function setCustomersBalanceCurrencyToWebsiteBaseCurrency(Varien_Event_Observer $observer)
    {
        Mage::getModel('enterprise_customerbalance/balance')->setCustomersBalanceCurrencyTo(
            $observer->getEvent()->getWebsite()->getWebsiteId(),
            $observer->getEvent()->getWebsite()->getBaseCurrencyCode()
        );
        return $this;
    }

    /**
     * Add customer balance amount to PayPal discount total
     *
     * @param Varien_Event_Observer $observer
     */
    public function addPaypalCustomerBalanceItem(Varien_Event_Observer $observer)
    {
        $paypalCart = $observer->getEvent()->getPaypalCart();
        if ($paypalCart) {
            $salesEntity = $paypalCart->getSalesEntity();
            if ($salesEntity instanceof Mage_Sales_Model_Quote) {
                $balanceField = 'base_customer_bal_amount_used';
            } elseif ($salesEntity instanceof Mage_Sales_Model_Order) {
                $balanceField = 'base_customer_balance_amount';
            } else {
                return;
            }

            $value = abs($salesEntity->getDataUsingMethod($balanceField));
            if ($value >= Mage_Sales_Helper_Data::PRECISION_VALUE) {
                $paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_DISCOUNT, (float)$value,
                    Mage::helper('enterprise_customerbalance')->__('Store Credit (%s)', Mage::app()->getStore()->convertPrice($value, true, false))
                );
            }
        }
    }

    /**
     * Extend sales amount expression with customer balance refunded value
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function extendSalesAmountExpression(Varien_Event_Observer $observer)
    {
        /** @var $expressionTransferObject Varien_Object */
        $expressionTransferObject = $observer->getEvent()->getExpressionObject();
        /** @var $adapter Varien_Db_Adapter_Interface */
        $adapter = $observer->getEvent()->getCollection()->getConnection();
        $expressionTransferObject->setExpression($expressionTransferObject->getExpression() . ' + (%s)');
        $arguments = $expressionTransferObject->getArguments();
        $arguments[] = $adapter->getCheckSql(
            $adapter->prepareSqlCondition('main_table.bs_customer_bal_total_refunded', array('null' => null)),
            0,
            sprintf(
                'main_table.bs_customer_bal_total_refunded - %s - %s',
                $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
                $adapter->getIfNullSql('main_table.base_shipping_refunded', 0)
            )
        );

        $expressionTransferObject->setArguments($arguments);
    }

    /**
     * Return funds to store credit
     *
     * @param   Varien_Event_Observer $observer
     *
     * @return Enterprise_CustomerBalance_Model_Observer|void
     */
    public function returnFundsToStoreCredit(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        if (!$order) {
            return $this;
        }

        $balance = $order->getBaseCustomerBalanceAmount();
        $customerId = $order->getCustomerId();
        if ($customerId && $balance > 0) {
            /** @var Enterprise_CustomerBalance_Model_Balance $balanceModel */
            Mage::getModel('enterprise_customerbalance/balance')
                ->setCustomerId($customerId)
                ->setWebsiteId(Mage::app()->getStore($order->getStoreId())->getWebsiteId())
                ->setAmountDelta($balance)
                ->setHistoryAction(Enterprise_CustomerBalance_Model_Balance_History::ACTION_REVERTED)
                ->setOrder($order)
                ->save();
        }
    }

    /**
     * Set virtual store credit amount to quote in case order editing when old order is not canceled yet,
     * and new one needs potentially reverted store credit to be available
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Enterprise_CustomerBalance_Model_Observer|void
     */
    public function setBaseCustomerBalanceVirtualAmountToQuote(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order && !$order->getReordered()
            && $order->getBaseCustomerBalanceAmount() >= Mage_Sales_Helper_Data::PRECISION_VALUE
        ) {
            /** @var Mage_Sales_Model_Quote $quote */
            $quote = $observer->getEvent()->getQuote();

            if (!$quote) {
                return $this;
            }

            $quote->setUseCustomerBalance(true);
            $quote->setBaseCustomerBalanceVirtualAmount($quote->getBaseCustomerBalanceVirtualAmount()
                + $order->getBaseCustomerBalanceAmount());
        }
    }
}
