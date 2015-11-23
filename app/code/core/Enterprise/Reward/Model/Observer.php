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
 * @package     Enterprise_Reward
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Reward observer
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Model_Observer
{
    /**
     * Update reward points for customer, send notification
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function saveRewardPoints($observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return;
        }

        $request = $observer->getEvent()->getRequest();
        $customer = $observer->getEvent()->getCustomer();
        $data = $request->getPost('reward');
        if ($data) {
            if (!isset($data['store_id'])) {
                if ($customer->getStoreId() == 0) {
                    $data['store_id'] = Mage::app()->getAnyStoreView()->getStoreId();
                } else {
                    $data['store_id'] = $customer->getStoreId();
                }
            }
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomer($customer)
                ->setWebsiteId(Mage::app()->getStore($data['store_id'])->getWebsiteId())
                ->loadByCustomer();
            if (!empty($data['points_delta'])) {
                $reward->addData($data)
                    ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_ADMIN)
                    ->setActionEntity($customer)
                    ->updateRewardPoints();
            } else {
                $reward->save();
            }
        }
        return $this;
    }

    /**
     * Update reward notifications for customer
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function saveRewardNotifications($observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return;
        }

        $request = $observer->getEvent()->getRequest();
        $customer = $observer->getEvent()->getCustomer();

        $data = $request->getPost('reward');
        $subscribeByDefault = (int)Mage::helper('enterprise_reward')
            ->getNotificationConfig('subscribe_by_default', (int)$customer->getWebsiteId());
        if ($customer->isObjectNew()) {
            $data['reward_update_notification']  = $subscribeByDefault;
            $data['reward_warning_notification'] = $subscribeByDefault;
        }

        $customer->setRewardUpdateNotification(!empty($data['reward_update_notification']) ? 1 : 0);
        $customer->setRewardWarningNotification(!empty($data['reward_warning_notification']) ? 1 : 0);

        return $this;
    }

    /**
     * Update reward points after customer register
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function customerRegister($observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront()) {
            return $this;
        }
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = $observer->getEvent()->getCustomer();
        $customerOrigData = $customer->getOrigData();
        if (empty($customerOrigData)) {
            try {
                $subscribeByDefault = Mage::helper('enterprise_reward')
                    ->getNotificationConfig('subscribe_by_default', Mage::app()->getStore()->getWebsiteId());
                $reward = Mage::getModel('enterprise_reward/reward')
                    ->setCustomer($customer)
                    ->setActionEntity($customer)
                    ->setStore(Mage::app()->getStore()->getId())
                    ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_REGISTER)
                    ->updateRewardPoints();

                    $customer->setRewardUpdateNotification((int)$subscribeByDefault)
                    ->setRewardWarningNotification((int)$subscribeByDefault);
                $customer->getResource()->saveAttribute($customer, 'reward_update_notification');
                $customer->getResource()->saveAttribute($customer, 'reward_warning_notification');
            } catch (Exception $e) {
                //save exception if something were wrong during saving reward and allow to register customer
                Mage::logException($e);
            }
        }
        return $this;
    }

    /**
     * Update points balance after review submit
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function reviewSubmit($observer)
    {
        /* @var $review Mage_Review_Model_Review */
        $review = $observer->getEvent()->getObject();
        $websiteId = Mage::app()->getStore($review->getStoreId())->getWebsiteId();
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront($websiteId)) {
            return $this;
        }
        if ($review->isApproved() && $review->getCustomerId()) {
            /* @var $reward Enterprise_Reward_Model_Reward */
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomerId($review->getCustomerId())
                ->setStore($review->getStoreId())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_REVIEW)
                ->setActionEntity($review)
                ->updateRewardPoints();
        }
        return $this;
    }

    /**
     * Update points balance after tag submit
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function tagSubmit($observer)
    {
        /* @var $tag Mage_Tag_Model_Tag */
        $tag = $observer->getEvent()->getObject();
        $websiteId = Mage::app()->getStore($tag->getFirstStoreId())->getWebsiteId();
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront($websiteId)) {
            return $this;
        }
        if (($tag->getApprovedStatus() == $tag->getStatus()) && $tag->getFirstCustomerId()) {
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomerId($tag->getFirstCustomerId())
                ->setStore($tag->getFirstStoreId())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_TAG)
                ->setActionEntity($tag)
                ->updateRewardPoints();
        }
        return $this;
    }

    /**
     * Update points balance after first successful subscribtion
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function customerSubscribed($observer)
    {
        /* @var $subscriber Mage_Newsletter_Model_Subscriber */
        $subscriber = $observer->getEvent()->getSubscriber();
        // reward only new subscribtions
        if (!$subscriber->isObjectNew() || !$subscriber->getCustomerId()) {
            return $this;
        }
        $websiteId = Mage::app()->getStore($subscriber->getStoreId())->getWebsiteId();
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront($websiteId)) {
            return $this;
        }

        $reward = Mage::getModel('enterprise_reward/reward')
            ->setCustomerId($subscriber->getCustomerId())
            ->setStore($subscriber->getStoreId())
            ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_NEWSLETTER)
            ->setActionEntity($subscriber)
            ->updateRewardPoints();

        return $this;
    }

    /**
     * Update points balance after customer registered by invitation
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function invitationToCustomer($observer)
    {
        /* @var $invitation Enterprise_Invitation_Model_Invitation */
        $invitation = $observer->getEvent()->getInvitation();
        $websiteId = Mage::app()->getStore($invitation->getStoreId())->getWebsiteId();
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront($websiteId)) {
            return $this;
        }

        if ($invitation->getCustomerId() && $invitation->getReferralId()) {
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomerId($invitation->getCustomerId())
                ->setWebsiteId($websiteId)
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_INVITATION_CUSTOMER)
                ->setActionEntity($invitation)
                ->updateRewardPoints();
        }

        return $this;
    }

    /**
     * Update points balance after order becomes completed
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function orderCompleted($observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();
        if ($order->getCustomerIsGuest()
            || !Mage::helper('enterprise_reward')->isEnabledOnFront($order->getStore()->getWebsiteId())
        ) {
            return $this;
        }

        if ($order->getCustomerId() && $this->_isOrderPaidNow($order)) {
            /* @var $reward Enterprise_Reward_Model_Reward */
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setActionEntity($order)
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId($order->getStore()->getWebsiteId())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_ORDER_EXTRA)
                ->updateRewardPoints();
            if ($reward->getRewardPointsUpdated() && $reward->getPointsDelta()) {
                $order->addStatusHistoryComment(
                    Mage::helper('enterprise_reward')->__('Customer earned %s for the order.', Mage::helper('enterprise_reward')->formatReward($reward->getPointsDelta()))
                )->save();
            }
        }

        return $this;
    }

    /**
     * Check if order is paid exactly now
     * If order was paid before Rewards were enabled, reward points should not be added
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    protected function _isOrderPaidNow($order)
    {
        $isOrderPaid = (float)$order->getBaseTotalPaid() > 0
            && ($order->getBaseGrandTotal() - $order->getBaseSubtotalCanceled() - $order->getBaseTotalPaid()) < 0.0001;

        if (!$order->getOrigData('base_grand_total')) {//New order with "Sale" payment action
            return $isOrderPaid;
        }

        return $isOrderPaid && ($order->getOrigData('base_grand_total') - $order->getOrigData('base_subtotal_canceled')
            - $order->getOrigData('base_total_paid')) >= 0.0001;
    }

    /**
     * Update inviter points balance after referral's order completed
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    protected function _invitationToOrder($observer)
    {
        if (Mage::helper('Core')->isModuleEnabled('Enterprise_Invitation')) {
            $invoice = $observer->getEvent()->getInvoice();
            /* @var $invoice Mage_Sales_Model_Order_Invoice */
            $order = $invoice->getOrder();
            /* @var $order Mage_Sales_Model_Order */
            if ($order->getBaseTotalDue() > 0) {
                return $this;
            }
            $invitation = Mage::getModel('enterprise_invitation/invitation')
                ->load($order->getCustomerId(), 'referral_id');
            if (!$invitation->getId() || !$invitation->getCustomerId()) {
                return $this;
            }
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setActionEntity($invitation)
                ->setCustomerId($invitation->getCustomerId())
                ->setStore($order->getStoreId())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_INVITATION_ORDER)
                ->updateRewardPoints();
        }

        return $this;
    }

    /**
     * Set flag to reset reward points totals
     *
     * @param Varien_Event_Observer $observer
     * @@return Enterprise_Reward_Model_Observer
     */
    public function quoteCollectTotalsBefore(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quote->setRewardPointsTotalReseted(false);
        return $this;
    }

    /**
     * Set use reward points flag to new quote
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function quoteMergeAfter($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $source = $observer->getEvent()->getSource();

        if ($source->getUseRewardPoints()) {
            $quote->setUseRewardPoints($source->getUseRewardPoints());
        }
        return $this;
    }

    /**
     * Payment data import in checkout process
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function paymentDataImport(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront()) {
            return $this;
        }
        $input = $observer->getEvent()->getInput();
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getPayment()->getQuote();
        $this->_paymentDataImport($quote, $input, $input->getUseRewardPoints());
        return $this;
    }

    /**
     * Enable Zero Subtotal Checkout payment method
     * if customer has enough points to cover grand total
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function preparePaymentMethod($observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront()) {
            return $this;
        }

        $quote = $observer->getEvent()->getQuote();
        if (!is_object($quote) || !$quote->getId()) {
            return $this;
        }

        /* @var $reward Enterprise_Reward_Model_Reward */
        $reward = $quote->getRewardInstance();
        if (!$reward || !$reward->getId()) {
            return $this;
        }

        $baseQuoteGrandTotal = $quote->getBaseGrandTotal() + $quote->getBaseRewardCurrencyAmount();
        if ($reward->isEnoughPointsToCoverAmount($baseQuoteGrandTotal)) {
            $paymentCode = $observer->getEvent()->getMethodInstance()->getCode();
            $result = $observer->getEvent()->getResult();
            $result->isAvailable = $paymentCode === 'free' && empty($result->isDeniedInConfig);
        }
        return $this;
    }

    /**
     * Payment data import in admin order create process
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function processOrderCreationData(Varien_Event_Observer $observer)
    {
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getOrderCreateModel()->getQuote();
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront($quote->getStore()->getWebsiteId())) {
            return $this;
        }
        $request = $observer->getEvent()->getRequest();
        if (isset($request['payment']) && isset($request['payment']['use_reward_points'])) {
            $this->_paymentDataImport($quote, $quote->getPayment(), $request['payment']['use_reward_points']);
        }
        return $this;
    }

    /**
     * Prepare and set to quote reward balance instance,
     * set zero subtotal checkout payment if need
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Varien_Object $payment
     * @param boolean $useRewardPoints
     * @return Enterprise_Reward_Model_Observer
     */
    protected function _paymentDataImport($quote, $payment, $useRewardPoints)
    {
        if (!$quote || !$quote->getCustomerId()) {
            return $this;
        }
        $quote->setUseRewardPoints((bool)$useRewardPoints);
        if ($quote->getUseRewardPoints()) {
            /* @var $reward Enterprise_Reward_Model_Reward */
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomer($quote->getCustomer())
                ->setWebsiteId($quote->getStore()->getWebsiteId())
                ->loadByCustomer();
            $minPointsBalance = (int)Mage::getStoreConfig(
                Enterprise_Reward_Model_Reward::XML_PATH_MIN_POINTS_BALANCE,
                $quote->getStoreId()
            );

            if ($reward->getId() && $reward->getPointsBalance() >= $minPointsBalance) {
                $quote->setRewardInstance($reward);
                if (!$payment->getMethod()) {
                    $payment->setMethod('free');
                }
            }
            else {
                $quote->setUseRewardPoints(false);
            }
        }
        return $this;
    }

    /**
     * Check reward points balance
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  Enterprise_Reward_Model_Observer
     */
    protected function _checkRewardPointsBalance(Mage_Sales_Model_Order $order)
    {
        if ($order->getRewardPointsBalance() > 0) {
            $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();
            /* @var $reward Enterprise_Reward_Model_Reward */
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId($websiteId)
                ->loadByCustomer();
            if (($order->getRewardPointsBalance() - $reward->getPointsBalance()) >= 0.0001) {
                Mage::getSingleton('checkout/type_onepage')
                    ->getCheckout()
                    ->setUpdateSection('payment-method')
                    ->setGotoSection('payment');

                Mage::throwException(
                    Mage::helper('enterprise_reward')->__('Not enough Reward Points to complete this Order.')
                );
            }
        }

        return $this;
    }

    /**
     * Validate order, check if enough reward points to place order
     *
     * @param   Varien_Event_Observer $observer
     * @return  Enterprise_Reward_Model_Observer
     */
    public function processBeforeOrderPlace(Varien_Event_Observer $observer)
    {
        if (Mage::helper('enterprise_reward')->isEnabledOnFront()) {
            $order = $observer->getEvent()->getOrder();
            $this->_checkRewardPointsBalance($order);
        }

        return $this;
    }

    /**
     * Reduce reward points if points was used during checkout
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function processOrderPlace(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront()
            || (Mage::app()->getStore()->isAdmin()
                && !Mage::getSingleton('admin/session')
                    ->isAllowed(Enterprise_Reward_Helper_Data::XML_PATH_PERMISSION_AFFECT))
        ) {
            return $this;
        }
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();
        if ($order->getBaseRewardCurrencyAmount() > 0) {
            $this->_checkRewardPointsBalance($order);

            Mage::getModel('enterprise_reward/reward')
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId(Mage::app()->getStore($order->getStoreId())->getWebsiteId())
                ->setPointsDelta(-$order->getRewardPointsBalance())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_ORDER)
                ->setActionEntity($order)
                ->updateRewardPoints();
        }
        $ruleIds = explode(',', $order->getAppliedRuleIds());
        $ruleIds = array_unique($ruleIds);
        $data = Mage::getResourceModel('enterprise_reward/reward')
            ->getRewardSalesrule($ruleIds);
        $pointsDelta = 0;
        foreach ($data as $rule) {
            $pointsDelta += (int)$rule['points_delta'];
        }
        if ($pointsDelta) {
            $order->setRewardSalesrulePoints($pointsDelta);
        }
        return $this;
    }

    /**
     * Revert authorized reward points amount for order
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  Enterprise_Reward_Model_Observer
     */
    protected function _revertRewardPointsForOrder(Mage_Sales_Model_Order $order)
    {
        if (!$order->getCustomer()->getId()) {
            return $this;
        }
        Mage::getModel('enterprise_reward/reward')
            ->setCustomerId($order->getCustomer()->getId())
            ->setWebsiteId(Mage::app()->getStore($order->getStoreId())->getWebsiteId())
            ->setPointsDelta($order->getRewardPointsBalance())
            ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_REVERT)
            ->setActionEntity($order)
            ->updateRewardPoints();

        return $this;
    }

    /**
     * Revert reward points if order was not placed
     *
     * @param   Varien_Event_Observer $observer
     * @return  Enterprise_Reward_Model_Observer
     */
    public function revertRewardPoints(Varien_Event_Observer $observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();
        if ($order) {
            $this->_revertRewardPointsForOrder($order);
        }

        return $this;
    }

    /**
     * Revert authorized reward points amounts for all orders
     *
     * @param   Varien_Event_Observer $observer
     * @return  Enterprise_Reward_Model_Observer
     */
    public function revertRewardPointsForAllOrders(Varien_Event_Observer $observer)
    {
        $orders = $observer->getEvent()->getOrders();

        foreach ($orders as $order) {
            $this->_revertRewardPointsForOrder($order);
        }

        return $this;
    }

    /**
     * Set forced can creditmemo flag if refunded amount less then invoiced amount of reward points
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function orderLoadAfter(Varien_Event_Observer $observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();
        if ($order->canUnhold()) {
            return $this;
        }
        if ($order->isCanceled() ||
            $order->getState() === Mage_Sales_Model_Order::STATE_CLOSED ) {
            return $this;
        }
        if (($order->getBaseRewardCurrencyAmountInvoiced() - $order->getBaseRewardCurrencyAmountRefunded()) > 0) {
            $order->setForcedCanCreditmemo(true);
        }
        return $this;
    }

    /**
     * Set invoiced reward amount to order
     *
     * @deprecated after 1.9.0.0
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function invoiceSaveAfter(Varien_Event_Observer $observer)
    {
        $this->invoiceRegister($observer);
        return $this;
    }


    /**
     * Set invoiced reward amount to order after invoice register
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function invoiceRegister(Varien_Event_Observer $observer)
    {
        /* @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $observer->getEvent()->getInvoice();
        if ($invoice->getBaseRewardCurrencyAmount()) {
            $order = $invoice->getOrder();
            $order->setRewardCurrencyAmountInvoiced(
                $order->getRewardCurrencyAmountInvoiced() + $invoice->getRewardCurrencyAmount()
            );
            $order->setBaseRewardCurrencyAmountInvoiced(
                $order->getBaseRewardCurrencyAmountInvoiced() + $invoice->getBaseRewardCurrencyAmount()
            );
        }

        return $this;
    }

    /**
     * Update inviter balance if possible
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function invoicePay(Varien_Event_Observer $observer)
    {
        /* @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $observer->getEvent()->getInvoice();
        if (!$invoice->getOrigData($invoice->getResource()->getIdFieldName())) {
            $this->_invitationToOrder($observer);
        }

        return $this;
    }

    /**
     * Set reward points balance to refund before creditmemo register
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function setRewardPointsBalanceToRefund(Varien_Event_Observer $observer)
    {
        $input = $observer->getEvent()->getRequest()->getParam('creditmemo');
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if (isset($input['refund_reward_points']) && isset($input['refund_reward_points_enable'])) {
            $enable = $input['refund_reward_points_enable'];
            $balance = (int)$input['refund_reward_points'];
            $balance = min($creditmemo->getRewardPointsBalance(), $balance);
            if ($enable && $balance) {
                $creditmemo->setRewardPointsBalanceToRefund($balance);
            }
        }
        return $this;
    }

    /**
     * Clear forced can creditmemo if whole reward amount was refunded
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function creditmemoRefund(Varien_Event_Observer $observer)
    {
        /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getCreditmemo()->getOrder();
        $refundedAmount = (float)(
            $order->getBaseRewardCurrencyAmountRefunded() + $creditmemo->getBaseRewardCurrencyAmount()
        );
        $rewardAmount = (float)$order->getBaseRewardCurrencyAmountInvoiced();
        if ($rewardAmount > 0 &&  $rewardAmount == $refundedAmount) {
            $order->setForcedCanCreditmemo(false);
        }

        return $this;
    }

    /**
     * Set refunded reward amount order and update reward points balance if need
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function creditmemoSaveAfter(Varien_Event_Observer $observer)
    {
        /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        /* @var $order Mage_Sales_Model_Order */
        $order = $creditmemo->getOrder();

        /* @var $helper Enterprise_Reward_Helper_Data */
        $helper = Mage::helper('enterprise_reward');
        $allowRewardPointsRefund = true;
        if ($creditmemo->getAutomaticallyCreated()) {
            if ($helper->isAutoRefundEnabled()) {
                $creditmemo->setRewardPointsBalanceToRefund($creditmemo->getRewardPointsBalance());
            } else {
                $allowRewardPointsRefund = false;
            }
        }

        if ($creditmemo->getBaseRewardCurrencyAmount() && $allowRewardPointsRefund) {
            $order->setRewardPointsBalanceRefunded(
                $order->getRewardPointsBalanceRefunded() + $creditmemo->getRewardPointsBalance()
            );
            $order->setRewardCurrencyAmountRefunded(
                $order->getRewardCurrencyAmountRefunded() + $creditmemo->getRewardCurrencyAmount()
            );
            $order->setBaseRewardCurrencyAmountRefunded(
                $order->getBaseRewardCurrencyAmountRefunded() + $creditmemo->getBaseRewardCurrencyAmount()
            );
            $order->setRewardPointsBalanceToRefund(
                $order->getRewardPointsBalanceToRefund() + $creditmemo->getRewardPointsBalanceToRefund()
            );

            if ((int)$creditmemo->getRewardPointsBalanceToRefund() > 0) {
                Mage::getModel('enterprise_reward/reward')
                    ->setCustomerId($order->getCustomerId())
                    ->setStore($order->getStoreId())
                    ->setPointsDelta((int)$creditmemo->getRewardPointsBalanceToRefund())
                    ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_CREDITMEMO)
                    ->setActionEntity($order)
                    ->save();
            }
        }

        // Void reward points granted for refunded amount if there was any
        $customerId = $order->getCustomerId();
        $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();

        // @var $rewardHistoryCollection Enterprise_Reward_Model_Resource_Reward_History_Collection
        $rewardHistoryCollection = Mage::getModel('enterprise_reward/reward_history')->getCollection();
        $rewardHistoryCollection->addCustomerFilter($customerId)
            ->addWebsiteFilter($websiteId)
            // nothing to void if reward points are expired already
            ->addFilter('main_table.is_expired', 0)
            // void points acquired for purchase only
            ->addFilter('main_table.action', Enterprise_Reward_Model_Reward::REWARD_ACTION_ORDER_EXTRA);

        $isOrderRewardedWithPoints = false;
        /* @var $rewardHistoryRecord Enterprise_Reward_Model_Reward */
        foreach ($rewardHistoryCollection as $rewardHistoryRecord) {
            $additionalData = $rewardHistoryRecord->getAdditionalData();
            if (isset($additionalData['increment_id']) && $additionalData['increment_id'] == $order->getIncrementId()
                && isset($additionalData['rate']['direction']) && $additionalData['rate']['direction'] ==
                    Enterprise_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_POINTS
                && isset($additionalData['rate']['points'])
                && isset($additionalData['rate']['currency_amount'])
            ) {
                $isOrderRewardedWithPoints = true;
                break;
            }
        }
        unset($rewardHistoryCollection);

        if ($isOrderRewardedWithPoints) {
            /*
             * Calculating amount of funds from total refunded amount for which reward points were acquired
             * @see Enterprise_Reward_Model_Action_OrderExtra::getPoints()
             */
            $rewardedAmountForWholeOrder = $order->getBaseGrandTotal() - $order->getBaseTaxAmount()
                - $order->getBaseShippingAmount();
            $rewardedAmountRefunded = $order->getBaseTotalRefunded() - $order->getBaseTaxRefunded()
                - $order->getBaseShippingRefunded();
            $rewardedAmountAfterRefund = $rewardedAmountForWholeOrder - $rewardedAmountRefunded;

            /**
             * Modify amount for which reward points should not be voided at refund
             */
            $result = new Varien_Object();
            $result->setRewardedAmountAfterRefund($rewardedAmountAfterRefund);
            Mage::dispatchEvent('rewarded_amount_after_refund_calculation', array(
                'creditmemo' => $creditmemo,
                'result'     => $result
            ));
            $rewardedAmountAfterRefund = $result->getRewardedAmountAfterRefund();

            /**
             * Calculating amount of points to void considering reward points exchange rate when they were granted
             * @see Enterprise_Reward_Model_Reward_Rate::calculateToPoints()
             */
            $estimatedRewardPointsAfterRefund = (int)((string)$rewardedAmountAfterRefund /
                (string)$additionalData['rate']['currency_amount']) * $additionalData['rate']['points'];

            $rewardPointsVoided = $rewardHistoryRecord->getPointsVoided();
            $acquiredRewardPointsAvailableForVoid = $rewardHistoryRecord->getPointsDelta() - $rewardPointsVoided;

            /*
             * It's not allowed to void more points then were granted per this order.
             * Used points at current history record are not taken into consideration -
             * allowed to void from total amount if it's needed to void more then left at selected history record.
             */
            if ($acquiredRewardPointsAvailableForVoid > $estimatedRewardPointsAfterRefund) {
                $rewardPointsAmountToVoid = $acquiredRewardPointsAvailableForVoid - $estimatedRewardPointsAfterRefund;
            } else {
                $rewardPointsAmountToVoid = 0;
            }

            if ($rewardPointsAmountToVoid > 0) {
                /* @var $reward Enterprise_Reward_Model_Reward */
                $reward = Mage::getModel('enterprise_reward/reward')
                    ->setWebsiteId($websiteId)
                    ->setCustomerId($order->getCustomerId())
                    ->loadByCustomer();

                $rewardPointsBalance = $reward->getPointsBalance();
                if ($rewardPointsBalance > 0) {
                    // It's not allowed to void more points then is available for current customer
                    if ($rewardPointsAmountToVoid > $rewardPointsBalance) {
                        $rewardPointsAmountToVoid = $rewardPointsBalance;
                    }

                    if ($helper->getGeneralConfig('deduct_automatically')) {
                        $reward->setPointsDelta(-$rewardPointsAmountToVoid)
                            ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_CREDITMEMO_VOID)
                            ->setActionEntity($order)
                            ->updateRewardPoints();

                        if ($reward->getRewardPointsUpdated()) {
                            $order->addStatusHistoryComment(
                                $helper->__('%s was deducted because of refund.', $helper->formatReward($rewardPointsAmountToVoid))
                            );
                        }
                    }

                    /*
                     * Config option deduct_automatically is not considered here because points for refunded amount that
                     * were not been voided automatically need to be counted in possible future automatic deductions.
                     */
                    $rewardHistoryRecord->getResource()->updateHistoryRow($rewardHistoryRecord, array(
                        'points_voided' => $rewardPointsVoided + $rewardPointsAmountToVoid
                    ));
                }
            }
        }

        return $this;
    }

    /**
     * Disable entire RP layout
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function disableLayout(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            unset($observer->getUpdates()->enterprise_reward);
        }
        return $this;
    }

    /**
     * Send scheduled low balance warning notifications
     *
     * @return Enterprise_Reward_Model_Observer
     */
    public function scheduledBalanceExpireNotification()
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return $this;
        }

        foreach (Mage::app()->getWebsites() as $website) {
            if (!Mage::helper('enterprise_reward')->isEnabledOnFront($website->getId())) {
                continue;
            }
            $inDays = (int)Mage::helper('enterprise_reward')->getNotificationConfig('expiry_day_before');
            if (!$inDays) {
                continue;
            }
            $collection = Mage::getResourceModel('enterprise_reward/reward_history_collection')
                ->setExpiryConfig(Mage::helper('enterprise_reward')->getExpiryConfig())
                ->loadExpiredSoonPoints($website->getId(), true)
                ->addNotificationSentFlag(false)
                ->addCustomerInfo()
                ->setPageSize(20) // limit queues for each website
                ->setCurPage(1)
                ->load();

            foreach ($collection as $item) {
                Mage::getSingleton('enterprise_reward/reward')
                    ->sendBalanceWarningNotification($item, $website->getId());
            }

            // mark records as sent
            $historyIds = $collection->getExpiredSoonIds();
            Mage::getResourceModel('enterprise_reward/reward_history')->markAsNotified($historyIds);
        }

        return $this;
    }

    /**
     * Make points expired
     *
     * @return Enterprise_Reward_Model_Observer
     */
    public function scheduledPointsExpiration()
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return $this;
        }
        foreach (Mage::app()->getWebsites() as $website) {
            if (!Mage::helper('enterprise_reward')->isEnabledOnFront($website->getId())) {
                continue;
            }
            $expiryType = Mage::helper('enterprise_reward')->getGeneralConfig('expiry_calculation', $website->getId());
            Mage::getResourceModel('enterprise_reward/reward_history')
                ->expirePoints($website->getId(), $expiryType, 100);
        }

        return $this;
    }

    /**
     * Prepare orphan points of customers after website was deleted
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function prepareCustomerOrphanPoints(Varien_Event_Observer $observer)
    {
        /* @var $website Mage_Core_Model_Website */
        $website = $observer->getEvent()->getWebsite();
        Mage::getModel('enterprise_reward/reward')
            ->prepareOrphanPoints($website->getId(), $website->getBaseCurrencyCode());
        return $this;
    }

    /**
     * Prepare salesrule form. Add field to specify reward points delta
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function prepareSalesruleForm(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return $this;
        }
        $form = $observer->getEvent()->getForm();
        $fieldset = $form->getElement('action_fieldset');
        $fieldset->addField('reward_points_delta', 'text', array(
            'name'  => 'reward_points_delta',
            'label' => Mage::helper('enterprise_reward')->__('Add Reward Points'),
            'title' => Mage::helper('enterprise_reward')->__('Add Reward Points')
        ), 'stop_rules_processing');
        return $this;
    }

    /**
     * Set reward points delta to salesrule model after it loaded
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function loadRewardSalesruleData(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return $this;
        }
        /* @var $salesRule Mage_SalesRule_Model_Rule */
        $salesRule = $observer->getEvent()->getRule();
        if ($salesRule->getId()) {
            $data = Mage::getResourceModel('enterprise_reward/reward')
                ->getRewardSalesrule($salesRule->getId());
            if (isset($data['points_delta'])) {
                $salesRule->setRewardPointsDelta($data['points_delta']);
            }
        }
        return $this;
    }

    /**
     * Save reward points delta for salesrule
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function saveRewardSalesruleData(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return $this;
        }
        /* @var $salesRule Mage_SalesRule_Model_Rule */
        $salesRule = $observer->getEvent()->getRule();
        Mage::getResourceModel('enterprise_reward/reward')
            ->saveRewardSalesrule($salesRule->getId(), (int)$salesRule->getRewardPointsDelta());
        return $this;
    }

    /**
     * Update customer reward points balance by points from applied sales rules
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function applyRewardSalesrulePoints(Varien_Event_Observer $observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getInvoice()->getOrder();
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront($order->getStore()->getWebsiteId())) {
            return $this;
        }
        if ($order->getCustomerId() && !$order->canInvoice() && $order->getRewardSalesrulePoints()) {
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId($order->getStore()->getWebsiteId())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_SALESRULE)
                ->setActionEntity($order)
                ->setPointsDelta($order->getRewardSalesrulePoints())
                ->updateRewardPoints();

            /* Set to 0 to prevent further processing */
            $order->setRewardSalesrulePoints(0)
                ->save();

            $order->addStatusHistoryComment(
                Mage::helper('enterprise_reward')->__('Customer earned promotion extra %s.', Mage::helper('enterprise_reward')->formatReward($reward->getPointsDelta()))
            )->save();

        }
        return $this;
    }

    /**
     * If not all rates found, we should disable reward points on frontend
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function checkRates(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront()) {
            return $this;
        }

        $groupId    = $observer->getEvent()->getCustomerSession()->getCustomerGroupId();
        $websiteId  = Mage::app()->getStore()->getWebsiteId();

        $rate = Mage::getModel('enterprise_reward/reward_rate');

        $hasRates = $rate->fetch(
            $groupId, $websiteId, Enterprise_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_CURRENCY
        )->getId() &&
            $rate->reset()->fetch(
                $groupId,
                $websiteId,
                Enterprise_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_POINTS
            )->getId();

        Mage::helper('enterprise_reward')->setHasRates($hasRates);

        return $this;
    }

    /**
     * Add reward amount to PayPal discount total
     *
     * @param Varien_Event_Observer $observer
     */
    public function addPaypalRewardItem(Varien_Event_Observer $observer)
    {
        $paypalCart = $observer->getEvent()->getPaypalCart();
        if ($paypalCart && abs($paypalCart->getSalesEntity()->getBaseRewardCurrencyAmount()) > 0.0001) {
            $salesEntity = $paypalCart->getSalesEntity();
            $paypalCart->updateTotal(
                Mage_Paypal_Model_Cart::TOTAL_DISCOUNT,
                (float)$salesEntity->getBaseRewardCurrencyAmount(),
                Mage::helper('enterprise_reward')->formatReward($salesEntity->getRewardPointsBalance())
            );
        }
    }

    /**
     * Return reward points
     *
     * @param   Varien_Event_Observer $observer
     * @return  Enterprise_Reward_Model_Observer
     */
    public function returnRewardPoints(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order->getRewardPointsBalance() > 0) {
            Mage::getModel('enterprise_reward/reward')
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId(Mage::app()->getStore($order->getStoreId())->getWebsiteId())
                ->setPointsDelta($order->getRewardPointsBalance())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_REVERT)
                ->setActionEntity($order)
                ->updateRewardPoints();
        }

        return $this;
    }
}
