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
 * PayPal Instant Payment Notification processor model
 */
class Enterprise_Pbridge_Model_Payment_Method_Payone_Ipn
{
    /*
     * @param Mage_Sales_Model_Order
     */
    protected $_order = null;

    /**
     * IPN request data
     * @var array
     */
    protected $_ipnFormData = array();

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = array();

    /**
     * IPN request data setter
     * @param array $data
     * @return Enterprise_Pbridge_Model_Payment_Method_Payone_Ipn
     */
    public function setIpnFormData(array $data)
    {
        $this->_ipnFormData = $data;
        return $this;
    }

    /**
     * IPN request data getter
     * @param string $key
     * @return array|string
     */
    public function getIpnFormData($key = null)
    {
        if (null === $key) {
            return $this->_ipnFormData;
        }
        return isset($this->_ipnFormData[$key]) ? $this->_ipnFormData[$key] : null;
    }

    /**
     * Get ipn data, send verification to PayPal, run corresponding handler
     *
     * @throws Exception
     */
    public function processIpnRequest()
    {
        if (!$this->_ipnFormData) {
            return;
        }

        $sReq = '';

        foreach ($this->_ipnFormData as $k => $v) {
            $sReq .= '&'.$k.'='.urlencode(stripslashes($v));
        }
        // append ipn command
        $sReq .= "&cmd=_notify-validate";
        $sReq = substr($sReq, 1);

        $url = rtrim(Mage::helper('enterprise_pbridge')->getBridgeBaseUrl(), '/') . '/ipn.php?action=PayoneIpn';

        try {
            $http = new Varien_Http_Adapter_Curl();
            $config = array('timeout' => 60);
            $http->setConfig($config);
            $http->write(Zend_Http_Client::POST, $url, '1.1', array(), $sReq);
            $response = $http->read();
        } catch (Exception $e) {
            throw $e;
        }

        if ($error = $http->getError()) {
            $this->_notifyAdmin(Mage::helper('enterprise_pbridge')->__('IPN postback HTTP error: %s', $error));
            $http->close();

            return;
        }

        // cUrl resource must be closed after checking it for errors
        $http->close();

        if (false !== preg_match('~VERIFIED~si', $response)) {
            $this->processIpnVerified();
        } else {
            // TODO: possible PCI compliance issue - the $sReq may contain data that is supposed to be encrypted
            $this->_notifyAdmin(Mage::helper('enterprise_pbridge')->__('IPN postback Validation error: %s', $sReq));
        }
    }

    /**
     * Load and validate order
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    protected function _getOrder()
    {
        if (empty($this->_order)) {
            // get proper order
            $id = $this->getIpnFormData('order_id');
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($id);
            if (!$order->getId()) {
                // throws Exception intentionally, because cannot be logged to order comments
                throw new Exception(Mage::helper('enterprise_pbridge')->__('Wrong Order ID (%s) specified.', $id));
            }
            $this->_order = $order;
        }
        return $this->_order;
    }


    /**
     * IPN workflow implementation
     * Everything should be added to order comments. In positive processing cases customer will get email notifications.
     * Admin will be notified on errors.
     */
    public function processIpnVerified()
    {
        try {
            $paymentStatus = $this->getIpnFormData('txaction');
            switch ($paymentStatus) {
                case 'appointed':
                    $this->_registerPaymentAuthorization();
                    break;
                case 'cancellation':
                    $this->_registerPaymentFailure();
                    break;
                case 'paid':
                case 'capture':
                    $this->_registerPaymentCapture();
                    break;
            }
        } catch (Mage_Core_Exception $e) {
            $history = $this->_createIpnComment(Mage::helper('enterprise_pbridge')->__('Note: %s', $e->getMessage()))
                ->save();
            $this->_notifyAdmin($history->getComment(), $e);
        }
    }

    /**
     * Register authorization of a payment: create a non-paid invoice
     */
    protected function _registerPaymentAuthorization()
    {
        // authorize payment
        $order = $this->_getOrder();
        if ($order->getStatus() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            return false;
        }
        $comment = Mage::helper('enterprise_pbridge')->__('3D secure authentication passed.');
        $order->getPayment()
            ->setPreparedMessage($this->_createIpnComment($comment, false))
            ->setTransactionId($this->getIpnFormData('transaction_id'))
            ->setParentTransactionId($this->getIpnFormData('txid'))
            ->setIsTransactionClosed(0)
            ->registerAuthorizationNotification($this->getIpnFormData('price'));

        $order->save();
    }

    /**
     * Process completed payment
     * If an existing authorized invoice with specified txn_id exists - mark it as paid and save,
     * otherwise create a completely authorized/captured invoice
     *
     * Everything after saving order is not critical, thus done outside the transaction.
     *
     * @throws Mage_Core_Exception
     */
    protected function _registerPaymentCapture()
    {
        $order = $this->_getOrder();
        if ($order->getStatus() != Mage_Sales_Model_Order::STATE_PROCESSING) {
            return false;
        }
        $payment = $order->getPayment();
        $payment->setTransactionId($this->getIpnFormData('transaction_id'))
            ->setPreparedMessage($this->_createIpnComment('', false))
            ->setParentTransactionId($this->getIpnFormData('txid'))
            ->setShouldCloseParentTransaction(1)
            ->setIsTransactionClosed(0)
            ->registerCaptureNotification($this->getIpnFormData('price'));
        $order->save();

        // notify customer
        if ($invoice = $payment->getCreatedInvoice()) {
            $order->queueNewOrderEmail()->addStatusHistoryComment(
                Mage::helper('enterprise_pbridge')->__('Notified customer about invoice #%s.', $invoice->getIncrementId())
            )
            ->setIsCustomerNotified(true)
            ->save();
        }
    }

    /**
     * Treat failed payment as order cancellation
     */
    protected function _registerPaymentFailure($explanationMessage = '')
    {
        $order = $this->_getOrder();
        if ($order->getStatus() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            return false;
        }
        $order->registerCancellation($this->_createIpnComment($explanationMessage, false), false)
            ->save();
    }

    /**
     * Generate a "PayPal Verified" comment with additional explanation.
     * Returns the generated comment or order status history object
     *
     * @param string $comment
     * @param bool $addToHistory
     * @return string|Mage_Sales_Model_Order_Status_History
     */
    protected function _createIpnComment($comment = '', $addToHistory = true)
    {
        $paymentStatus = $this->getIpnFormData('txaction');
        $message = Mage::helper('enterprise_pbridge')->__('IPN verification "%s".', $paymentStatus);
        if ($comment) {
            $message .= ' ' . $comment;
        }
        if ($addToHistory) {
            $message = $this->_getOrder()->addStatusHistoryComment($message);
            $message->setIsCustomerNotified(null);
        }
        return $message;
    }

    /**
     * Notify Administrator about exceptional situation
     *
     * @param $message
     * @param Exception $exception
     */
    protected function _notifyAdmin($message, Exception $exception = null)
    {
        // prevent notification failure cause order procesing failure
        try {
            Mage::log($message);
            if ($exception) {
                Mage::logException($exception);
            }
            // @TODO: dump the message and IPN form data
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
