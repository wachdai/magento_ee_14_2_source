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
 * Authoreze.Net dummy payment method model
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pbridge_Model_Payment_Method_Authorizenet extends Enterprise_Pbridge_Model_Payment_Method_Abstract
{
    protected $_code  = 'authorizenet';

    /**#@+
     * Authorize.net transaction status
     */
    const TRANSACTION_STATUS_AUTHORIZED_PENDING_PAYMENT = 'authorizedPendingCapture';
    const TRANSACTION_STATUS_CAPTURED_PENDING_SETTLEMENT = 'capturedPendingSettlement';
    const TRANSACTION_STATUS_VOIDED = 'voided';
    const TRANSACTION_STATUS_DECLINED = 'declined';
    /**#@-*/

    /**
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc               = false;
    protected $_canFetchTransactionInfo = true;

    /**
     * List of default accepted currency codes supported by payment gateway
     *
     * @var array
     */
    protected $_allowCurrencyCode = array('USD');

    /**
     * Flag for separate switcher 3D Secure for backend
     *
     * @var bool
     */
    protected $_isAdmin3dSecureSeparate = true;

    /**
     * Centinel validation enabling should be done on PB side
     *
     * @return bool
     */
    public function getIsCentinelValidationEnabled()
    {
        return true;
    }

    /**
     * Return 3D validation flag
     *
     * @return bool
     */
    public function is3dSecureEnabled()
    {
        if($this->_isAdmin3dSecureSeparate && Mage::app()->getStore()->isAdmin()) {
            return $this->getConfigData('centinel') && $this->getConfigData('centinel_backend');
        }
        return (bool)$this->getConfigData('centinel');
    }

    /**
     * Fetch transaction info
     *
     * @param Mage_Payment_Model_Info $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(Mage_Payment_Model_Info $payment, $transactionId)
    {
        $result = $this->getPbridgeMethodInstance()->fetchTransactionInfo($payment, $transactionId);
        $result = new Varien_Object($result);
        $this->importPaymentInfo($result, $payment);
        $data = $result->getRawSuccessResponseData();
        return ($data) ? $data : array();
    }

    /**
     * Capturing method being executed via Payment Bridge
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return Enterprise_Pbridge_Model_Payment_Method_Authorizenet
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $response = $this->getPbridgeMethodInstance()->capture($payment, $amount);
        if (!$response) {
            $response = $this->getPbridgeMethodInstance()->authorize($payment, $amount);
        }
        $payment->addData((array)$response);
        return $this;
    }

    /**
     * Refunding method being executed via Payment Bridge
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return Enterprise_Pbridge_Model_Payment_Method_Authorizenet
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $response = $this->getPbridgeMethodInstance()->refund($payment, $amount);
        $payment->addData((array)$response);
        $payment->setIsTransactionClosed(1);
        $payment->setShouldCloseParentTransaction($response['is_transaction_closed']);
        return $this;
    }

    /**
     * Get transaction status from gateway response array and change payment status to appropriate
     *
     * @param Varien_Object $from
     * @param Mage_Payment_Model_Info $to
     * @return Enterprise_Pbridge_Model_Payment_Method_Authorizenet
     */
    public function importPaymentInfo(Varien_Object $from, Mage_Payment_Model_Info $to)
    {
        $approvedTransactionStatuses = array(
            self::TRANSACTION_STATUS_AUTHORIZED_PENDING_PAYMENT,
            self::TRANSACTION_STATUS_CAPTURED_PENDING_SETTLEMENT
        );

        $transactionStatus = $from->getTransactionStatus();

        if (in_array($transactionStatus, $approvedTransactionStatuses)) {
            $to->setIsTransactionApproved(true);
        } elseif (in_array($transactionStatus,
            array(self::TRANSACTION_STATUS_VOIDED, self::TRANSACTION_STATUS_DECLINED))
        ) {
            $to->setIsTransactionDenied(true);
        }

        return $this;
    }
}
