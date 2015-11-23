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
 * Pbridge payment method model
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pbridge_Model_Payment_Method_Pbridge extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Config path for system default country
     */
    const XML_CONFIG_PATH_DEFAULT_COUNTRY = 'general/country/default';

    /**
     * Payment code name
     *
     * @var string
     */
    protected $_code = 'pbridge';

    /**
     * Payment method instance wrapped by Payment Bridge
     *
     * @var Mage_Payment_Model_Method_Abstract
     */
    protected $_originalMethodInstance = null;

    /**
     * Code for wrapped payment method
     *
     * @var string
     */
    protected $_originalMethodCode = null;

    /**
     * Pbridge Api object
     *
     * @var Enterprise_Pbridge_Model_Payment_Method_Pbridge_Api
     */
    protected $_api = null;

    /**
     * List of address fields
     *
     * @var unknown_type
     */
    protected $_addressFileds = array(
        'prefix', 'firstname', 'middlename', 'lastname', 'suffix',
        'company', 'city', 'country_id', 'telephone', 'fax', 'postcode',
    );

    /**
     * Array of additional parameters, which need to be included in Pbridge request
     * @var array
     */
    protected $_additionalRequestParameters = array();

    /**
     * Initialize and return Pbridge Api object
     *
     * @return Enterprise_Pbridge_Model_Payment_Method_Pbridge_Api
     */
    protected function _getApi()
    {
        if ($this->_api === null) {
            $this->_api = Mage::getModel('enterprise_pbridge/payment_method_pbridge_api');
            $this->_api->setMethodInstance($this);
        }
        return $this->_api;
    }

    /**
     * Check whether payment method can be used
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        return false;
    }

    /**
     * Method sets additional request parameters due to the type of the payment method
     * @param Varien_Object $request
     * @param Varien_Object $payment
     */
    protected function _setAdditionalRequestParameters($request, $payment)
    {
        if (!$payment->getMethodInstance() instanceof Enterprise_Pbridge_Model_Payment_Method_Paypal_Interface) {
            return;
        }
        $request->setData(
            'additional_params',
            array('BNCODE' => Mage::getModel('paypal/config')->getBuildNotationCode())
        );
    }

    /**
     * Check if dummy payment method is available
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function isDummyMethodAvailable($quote = null)
    {
        $storeId = $quote ? $quote->getStoreId() : null;
        $checkResult = new StdClass;
        $checkResult->isAvailable = (bool)(int)$this->getOriginalMethodInstance()->getConfigData('active', $storeId);
        Mage::dispatchEvent('payment_method_is_active', array(
            'result'          => $checkResult,
            'method_instance' => $this->getOriginalMethodInstance(),
            'quote'           => $quote,
        ));
        $usingPbridge = $this->getOriginalMethodInstance()->getConfigData('using_pbridge', $storeId);
        return $checkResult->isAvailable && Mage::helper('enterprise_pbridge')->isEnabled($storeId)
            && $usingPbridge;
    }

    /**
     * Assign data to info model instance
     *
     * @param  mixed $data
     * @return Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        $pbridgeData = array();
        if (is_array($data)) {
            if (isset($data['pbridge_data'])) {
                $pbridgeData = $data['pbridge_data'];
                $data['cc_last4'] = $pbridgeData['cc_last4'];
                $data['cc_type'] = $pbridgeData['cc_type'];
                unset($data['pbridge_data']);
            }
        } else {
            $pbridgeData = $data->getData('pbridge_data');
            $data->setData('cc_last4',$pbridgeData['cc_last4']);
            $data->setData('cc_type',$pbridgeData['cc_type']);
            $data->unsetData('pbridge_data');
        }

        parent::assignData($data);
        $this->setPbridgeResponse($pbridgeData);
        Mage::getSingleton('enterprise_pbridge/session')->setToken($this->getPbridgeResponse('token'));
        return $this;
    }

    /**
     * Save Payment Bridge response into the Info instance additional data storage
     *
     * @param array $data
     * @return Enterprise_Pbridge_Model_Payment_Method_Pbridge
     */
    public function setPbridgeResponse($data)
    {
        $data = array('pbridge_data' => $data);
        if (!($additionalData = unserialize($this->getInfoInstance()->getAdditionalData()))) {
            $additionalData = array();
        }
        $additionalData = array_merge($additionalData, $data);
        $this->getInfoInstance()->setAdditionalData(serialize($additionalData));
        return $this;
    }

    /**
     * Retrieve Payment Bridge response from the Info instance additional data storage
     *
     * @param string $key
     * @return mixed
     */
    public function getPbridgeResponse($key = null)
    {
        $additionalData = unserialize($this->getInfoInstance()->getAdditionalData());
        if (!is_array($additionalData) || !isset($additionalData['pbridge_data'])) {
            return null;
        }
        if ($key !== null) {
            return isset($additionalData['pbridge_data'][$key]) ? $additionalData['pbridge_data'][$key] : null;
        }
        return $additionalData['pbridge_data'];
    }

    /**
     * Setter
     *
     * @param Mage_Payment_Model_Method_Abstract $methodInstance
     * @return Enterprise_Pbridge_Model_Payment_Method_Pbridge
     */
    public function setOriginalMethodInstance($methodInstance)
    {
        $this->_originalMethodInstance = $methodInstance;
        return $this;
    }

    /**
     * Getter.
     * Retrieve the wrapped payment method instance
     *
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function getOriginalMethodInstance()
    {
        if (null === $this->_originalMethodInstance) {
            $this->_originalMethodCode = $this->getPbridgeResponse('original_payment_method');
            if (null === $this->_originalMethodCode) {
                return null;
            }
            $this->_originalMethodInstance = Mage::helper('payment')
                 ->getMethodInstance($this->_originalMethodCode);
        }
        return $this->_originalMethodInstance;
    }

    /**
     * Retrieve payment iformation model object
     *
     * @return Mage_Payment_Model_Info
     */
    public function getInfoInstance()
    {
        return $this->getOriginalMethodInstance()->getInfoInstance();
    }

    /**
     * To check billing country is allowed for the payment method
     *
     * @param string $country
     * @return bool
     */
    public function canUseForCountry($country)
    {
        return $this->getOriginalMethodInstance()->canUseForCountry($country);
    }

    /**
     * Validate payment data
     *
     * @throws Mage_Core_Exception
     * @return Enterprise_Pbridge_Model_Payment_Method_Pbridge
     */
    public function validate()
    {
        parent::validate();
        if (!$this->getPbridgeResponse('token')) {
            Mage::throwException(
                Mage::helper('enterprise_pbridge')->__('Payment Bridge authentication data is not present')
            );
        }
        return $this;
    }

    /**
     * Authorize
     *
     * @param   Varien_Object $payment
     * @param   float $amount
     * @return  Mage_Payment_Model_Abstract
     */
    public function authorize(Varien_Object $payment, $amount)
    {
//        parent::authorize($payment, $amount);
        $order = $payment->getOrder();
        $request = $this->_getApiRequest();
        $this->_setAdditionalRequestParameters($request, $payment);
        $request
            ->setData('magento_payment_action' , $this->getOriginalMethodInstance()->getConfigPaymentAction())
            ->setData('client_ip', Mage::app()->getRequest()->getClientIp(false))
            ->setData('amount', (string)$amount)
            ->setData('currency_code', $order->getBaseCurrencyCode())
            ->setData('order_id', $order->getIncrementId())
            ->setData('customer_email', $order->getCustomerEmail())
            ->setData('is_virtual', $order->getIsVirtual())
            ->setData('notify_url', $this->_getNotifyUrl($order))
        ;

        $request->setData('billing_address', $this->_getAddressInfo($order->getBillingAddress()));
        if ($order->getCustomer() && $order->getCustomer()->getId()) {
            $email = $order->getCustomerEmail();
            $id = $order->getCustomer()->getId();
            $request->setData('customer_id',
                Mage::helper('enterprise_pbridge')->getCustomerIdentifierByEmail($id, $order->getStore()->getId())
            );
            $request->setData('numeric_customer_id', $id);
        }

        if (!$order->getIsVirtual()) {
            $request->setData('shipping_address', $this->_getAddressInfo($order->getShippingAddress()));
        }

        $request->setData('cart', $this->_getCart($order));

        $api = $this->_getApi()->doAuthorize($request);
        $apiResponse = $api->getResponse();

        $this->_importResultToPayment($payment, $apiResponse);

        if (isset($apiResponse['fraud']) && (bool)$apiResponse['fraud']) {
            $payment->setIsTransactionPending(true);
            $message = Mage::helper('enterprise_pbridge')->__('Merchant review is required for further processing.');
            $payment->getOrder()->setState(
                  Mage_Sales_Model_Order::STATE_PROCESSING,
                  Mage_Sales_Model_Order::STATUS_FRAUD,
                  $message
            );
        }
        return $apiResponse;
    }

    /**
     * Cancel payment
     *
     * @param   Varien_Object $payment
     * @return  Mage_Payment_Model_Abstract
     */
    public function cancel(Varien_Object $payment)
    {
        parent::cancel($payment);
        return $this;
    }

    /**
     * Capture payment
     *
     * @param   Varien_Object $payment
     * @param   float $amount
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        //parent::capture($payment, $amount);

        $authTransactionId = $payment->getParentTransactionId();

        if (!$authTransactionId) {
            return false;//$this->authorize($payment, $amount);
        }

        $request = $this->_getApiRequest();
        $this->_setAdditionalRequestParameters($request, $payment);
        $request
            ->setData('transaction_id', $authTransactionId)
            ->setData('is_capture_complete', (int)$payment->getShouldCloseParentTransaction())
            ->setData('amount', $amount)
            ->setData('currency_code', $payment->getOrder()->getBaseCurrencyCode())
            ->setData('order_id', $payment->getOrder()->getIncrementId())
            ->setData('is_first_capture', $payment->hasFirstCaptureFlag() ? $payment->getFirstCaptureFlag() : true)
            ->setData('notify_url', $this->_getNotifyUrl($payment->getOrder()));
        $api = $this->_getApi()->doCapture($request);
        $this->_importResultToPayment($payment, $api->getResponse());
        $apiResponse = $api->getResponse();

        if (isset($apiResponse['fraud']) && (bool)$apiResponse['fraud']) {
            $message = Mage::helper('enterprise_pbridge')->__('Merchant review is required for further processing.');
            $payment->getOrder()->setState(
                  Mage_Sales_Model_Order::STATE_PROCESSING,
                  Mage_Sales_Model_Order::STATUS_FRAUD,
                  $message
            );
        }
        return $apiResponse;
    }

    /**
     * Refund money
     *
     * @param   Varien_Object $payment
     * @param   float $amount
     * @return  Mage_Payment_Model_Abstract
     */
    public function refund(Varien_Object $payment, $amount)
    {
        //parent::refund($payment, $amount);

        $captureTxnId = $payment->getParentTransactionId();
        if ($captureTxnId) {
            $order = $payment->getOrder();

            $request = $this->_getApiRequest();
            $this->_setAdditionalRequestParameters($request, $payment);
            $request
                ->setData('transaction_id', $captureTxnId)
                ->setData('amount', $amount)
                ->setData('currency_code', $order->getBaseCurrencyCode())
                ->setData('cc_number', $payment->getCcLast4())
                ->setData('order_id', $payment->getOrder()->getIncrementId())
                ->setData('notify_url', $this->_getNotifyUrl($order));
            $canRefundMore = $order->canCreditmemo();
            $allRefunds = (float)$amount
                + (float)$order->getBaseTotalOnlineRefunded()
                + (float)$order->getBaseTotalOfflineRefunded();
            $isFullRefund = !$canRefundMore && (0.0001 > (float)$order->getBaseGrandTotal() - $allRefunds);
            $request->setData('is_full_refund', (int)$isFullRefund);

            // whether to close capture transaction
            $invoiceCanRefundMore = $payment->getCreditmemo()->getInvoice()->canRefund();
            $payment->setShouldCloseParentTransaction($invoiceCanRefundMore ? 0 : 1);
            $payment->setIsTransactionClosed(1);

            $api = $this->_getApi()->doRefund($request);
            $this->_importResultToPayment($payment, $api->getResponse());

            return $api->getResponse();

        } else {
            Mage::throwException(
                Mage::helper('enterprise_pbridge')->__('Impossible to issue a refund transaction, because capture transaction does not exist.')
            );
        }
    }

    /**
     * Void payment
     *
     * @param   Varien_Object $payment
     * @return  Mage_Payment_Model_Abstract
     */
    public function void(Varien_Object $payment)
    {
        //parent::void($payment);

        if ($authTransactionId = $payment->getParentTransactionId()) {
            $request = $this->_getApiRequest();
            $this->_setAdditionalRequestParameters($request, $payment);
            $request->addData(array(
                'transaction_id' => $authTransactionId,
                'amount' => $payment->getOrder()->getBaseTotalDue()
            ));

            $this->_getApi()->doVoid($request);

        } else {
            Mage::throwException(Mage::helper('enterprise_pbridge')->__('Authorization transaction is required to void.'));
        }
        return $this->_getApi()->getResponse();
    }

    /**
     * Accept payment
     * @param Mage_Payment_Model_Info $payment
     * @return mixed
     */
    public function acceptPayment(Mage_Payment_Model_Info $payment)
    {
        $transactionId = $payment->getLastTransId();

        if (!$transactionId) {
            return false;
        }

        $request = $this->_getApiRequest();
        $this->_setAdditionalRequestParameters($request, $payment);
        $request
            ->setData('transaction_id', $transactionId)
            ->setData('order_id', $payment->getOrder()->getIncrementId());

        $api = $this->_getApi()->doAccept($request);
        $this->_importResultToPayment($payment, $api->getResponse());
        $apiResponse = $api->getResponse();

        return $apiResponse;
    }

    /**
     * Deny payment
     * @param Mage_Payment_Model_Info $payment
     * @return mixed
     */
    public function denyPayment(Mage_Payment_Model_Info $payment)
    {
        $transactionId = $payment->getLastTransId();

        if (!$transactionId) {
            return false;
        }

        $request = $this->_getApiRequest();
        $this->_setAdditionalRequestParameters($request, $payment);
        $request
            ->setData('transaction_id', $transactionId)
            ->setData('order_id', $payment->getOrder()->getIncrementId());

        $api = $this->_getApi()->doDeny($request);
        $this->_importResultToPayment($payment, $api->getResponse());
        $apiResponse = $api->getResponse();

        return $apiResponse;
    }

    /**
     * Retrieve transaction info details
     * @param Mage_Payment_Model_Info $payment
     * @param int $transactionId
     * @return bool
     */
    public function fetchTransactionInfo(Mage_Payment_Model_Info $payment, $transactionId)
    {
        if (!$transactionId) {
            return false;
        }

        $request = $this->_getApiRequest();
        $this->_setAdditionalRequestParameters($request, $payment);
        $request
            ->setData('transaction_id', $transactionId)
            ->setData('order_id', $payment->getOrder()->getIncrementId());

        $api = $this->_getApi()->doFetchTransactionInfo($request);
        $this->_importResultToPayment($payment, $api->getResponse());
        $apiResponse = $api->getResponse();

        return $apiResponse;
    }

    /**
     * Create address request data
     *
     * @param Mage_Sales_Model_Order_Address $address
     * @return array
     */
    protected function _getAddressInfo($address)
    {
        $result = array();

        foreach ($this->_addressFileds as $addressField) {
            if ($address->hasData($addressField)) {
                $result[$addressField] = $address->getData($addressField);
            }
        }
        //Streets must be transfered separately
        $streets = $address->getStreet();
        $result['street'] = array_shift($streets) ;
        if ($street2 = array_shift($streets)) {
            $result['street2'] = $street2;
        }
        //Region code lookup
        $region = Mage::getModel('directory/region')->load($address->getData('region_id'));
        if ($region && $region->getId()) {
            $result['region'] = $region->getCode();
        } else {
            $result['region'] = $address->getRegion();
        }

        return $result;
    }

    /**
     * Public wrapper for _getAddressInfo
     * @param  Mage_Sales_Model_Order_Address $address
     * @return array
     */
    public function getAddressInfo($address)
    {
        return $this->_getAddressInfo($address);
    }

    /**
     * Fill cart request section from order
     *
     * @param Mage_Core_Model_Abstract $order
     *
     * @return array
     */
    protected function _getCart(Mage_Core_Model_Abstract $order)
    {
        list($items, $totals, $areItemsValid) = Mage::helper('enterprise_pbridge')->prepareCart($order);
        //Getting cart items
        $result = array();

        foreach ($items as $item) {
            $result['items'][] = $item->getData();
        }

        return array_merge($result, $totals, array('items_valid' => $areItemsValid));
    }

    /**
     * Public wrapper for _getCart
     * @param Mage_Core_Model_Abstract $order
     * @return array
     */
    public function getCart($order)
    {
        return $this->_getCart($order);
    }

    /**
     * Transfer API results to payment.
     * Api response must be compatible with payment response expectation
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param array $apiResponse
     */
    protected function _importResultToPayment(Mage_Sales_Model_Order_Payment $payment, $apiResponse)
    {
        if (!empty($apiResponse['gateway_transaction_id'])) {
            $htmlTransactionId = Mage::helper('enterprise_pbridge')->getHtmlTransactionId(
                $payment,
                $apiResponse['gateway_transaction_id']
            );
            $payment->setPreparedMessage(
                Mage::helper('enterprise_pbridge')->__('Original gateway transaction id: #%s.', $htmlTransactionId)
            );
        }

        if (isset($apiResponse['transaction_id'])) {
            $payment->setTransactionId($apiResponse['transaction_id']);
            unset($apiResponse['transaction_id']);
        }
    }

    /**
     * Return Api request object
     *
     * @return Varien_Object
     */
    protected function _getApiRequest()
    {
        $request = new Varien_Object();
        $request->setCountryCode(Mage::getStoreConfig(self::XML_CONFIG_PATH_DEFAULT_COUNTRY));
        $request->setClientIdentifier($this->_getCustomerIdentifier());
        return $request;
    }

    /**
     * Return order id
     *
     * @return string
     */
    protected function _getOrderId()
    {
        $orderId = null;
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $orderId = $paymentInfo->getOrder()->getIncrementId();
        } else {
            if (!$paymentInfo->getQuote()->getReservedOrderId()) {
                $paymentInfo->getQuote()->reserveOrderId()->save();
            }
            $orderId = $paymentInfo->getQuote()->getReservedOrderId();
        }
        return $orderId;
    }

    /**
     * Return customer identifier
     *
     * @return string
     */
    protected function _getCustomerIdentifier()
    {
        return md5($this->getInfoInstance()->getOrder()->getQuoteId());
    }

    /**
     * Returns reformatted notify url of Magento instance
     *
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function _getNotifyUrl(Mage_Sales_Model_Order $order)
    {
        return Mage::getUrl('enterprise_pbridge/PbridgeIpn/', array('_store' => $order->getStore()->getStoreId()));
    }
}
