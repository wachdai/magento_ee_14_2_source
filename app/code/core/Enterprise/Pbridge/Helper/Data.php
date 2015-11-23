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
 * Pbridge helper
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pbridge_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Payment Bridge action name to fetch Payment Bridge gateway form
     *
     * @var string
     */
    const PAYMENT_GATEWAY_FORM_ACTION = 'GatewayForm';

    /**
     * Payment Bridge action name to fetch Payment Bridge Saved Payment (Credit Card) profiles
     *
     * @var string
     */
    const PAYMENT_GATEWAY_PAYMENT_PROFILE_ACTION = 'ManageSavedPayment';

    /**
     * Payment Bridge payment methods available for the current merchant
     *
     * $var array
     */
    protected $_pbridgeAvailableMethods = array();

    /**
     * Payment Bridge payment methods available for the current merchant
     * and usable for current conditions
     *
     * $var array
     */
    protected $_pbridgeUsableMethods = array();

    /**
     * Encryptor model
     *
     * @var Enterprise_Pbridge_Model_Encryption
     */
    protected $_encryptor = null;

    /**
     * Store id
     *
     * @var unknown_type
     */
    protected $_storeId = null;

    /**
     * Check if Payment Bridge Magento Module is enabled in configuration
     *
     * @param Mage_Core_Model_Store $store
     * @return boolean
     */
    public function isEnabled($store = null)
    {
        return (bool)Mage::getStoreConfigFlag('payment/pbridge/active', $store) && $this->isAvailable($store);
    }

    /**
     * Check if Payment Bridge supports Payment Profiles
     *
     * @param Mage_Core_Model_Store $store
     * @return boolean
     */
    public function arePaymentProfilesEnables($store = null)
    {
        return (bool)Mage::getStoreConfigFlag('payment/pbridge/profilestatus', $store) && $this->isEnabled($store);
    }

    /**
     * Check if enough config paramters to use Pbridge module
     *
     * @param Mage_Core_Model_Store | integer $store
     * @return boolean
     */
    public function isAvailable($store = null)
    {
        return (bool)Mage::getStoreConfig('payment/pbridge/gatewayurl', $store) &&
            (bool)Mage::getStoreConfig('payment/pbridge/merchantcode', $store) &&
            (bool)Mage::getStoreConfig('payment/pbridge/merchantkey', $store);
    }

    /**
     * Getter
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Sales_Model_Quote | null
     */
    protected function _getQuote($quote = null)
    {
        if ($quote && $quote instanceof Mage_Sales_Model_Quote) {
            return $quote;
        }
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Generate identifier based on email or ID
     *
     * @param string $email Customer e-mail or customer ID
     * @param int $storeId
     * @return null|string
     */
    public function getCustomerIdentifierByEmail($email, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $merchantCode = Mage::getStoreConfig('payment/pbridge/merchantcode', $storeId);
        $uniqueId = Mage::getStoreConfig('payment/pbridge/uniquekey');
        if ($uniqueId) {
            $uniqueId .= '@';
        }
        return md5($uniqueId . $email . '@' . $merchantCode);
    }

    /**
     * Prepare and return Payment Bridge request url with parameters if passed.
     * Encrypt parameters by default.
     *
     * @param array $params OPTIONAL
     * @param boolean $encryptParams OPTIONAL true by default
     * @return string
     */
    protected function _prepareRequestUrl($params = array(), $encryptParams = true)
    {
        $storeId = (isset($params['store_id'])) ? $params['store_id']: $this->_storeId;
        $pbridgeUrl = $this->getBridgeBaseUrl($storeId);
        $sourceUrl =  rtrim($pbridgeUrl, '/') . '/bridge.php';

        if (!empty($params)) {
            if ($encryptParams) {
                $params = array('data' => $this->encrypt(json_encode($params)));
            }
        }

        $params['merchant_code'] = trim(Mage::getStoreConfig('payment/pbridge/merchantcode', $storeId));

        $sourceUrl .= '?' . http_build_query($params);

        return $sourceUrl;
    }

    /**
     * Prepare required request params.
     * Optinal accept additional params to merge with required
     *
     * @param array $params OPTIONAL
     * @return array
     */
    public function getRequestParams(array $params = array())
    {
        $params = array_merge(array(
            'locale' => Mage::app()->getLocale()->getLocaleCode(),
        ), $params);

        $params['merchant_key']  = trim(Mage::getStoreConfig('payment/pbridge/merchantkey', $this->_storeId));

        $params['scope'] = Mage::app()->getStore()->isAdmin() ? 'backend' : 'frontend';

        return $params;
    }

    /**
     * Return payment Bridge request URL to display gateway form
     *
     * @param array $params OPTIONAL
     * @param Mage_Sales_Model_Quote $quote
     * @return string
     */
    public function getGatewayFormUrl(array $params = array(), $quote = null)
    {
        $quote = $this->_getQuote($quote);
        $reservedOrderId = '';
        if ($quote && $quote->getId()) {
            if (!$quote->getReservedOrderId()) {
                $quote->reserveOrderId()->save();
            }
            $reservedOrderId = $quote->getReservedOrderId();
        }
        $params = array_merge(array(
            'order_id'      => $reservedOrderId,
            'amount'        => $quote ? $quote->getBaseGrandTotal() : '0',
            'currency_code' => $quote ? $quote->getBaseCurrencyCode() : '',
            'client_identifier' => md5($quote->getId()),
            'store_id'      => $quote ? $quote->getStoreId() : '0',
        ), $params);

        if ($quote->getStoreId()) {
            $this->setStoreId($quote->getStoreId());
        }

        $params = $this->getRequestParams($params);
        $params['action'] = self::PAYMENT_GATEWAY_FORM_ACTION;
        return $this->_prepareRequestUrl($params, true);
    }

    /**
     * Return Payment Bridge target URL to display Credit card profiles
     *
     * @param array $params Additional URL query params
     * @return string
     */
    public function getPaymentProfileUrl(array $params = array())
    {
        $params = $this->getRequestParams($params);
        $params['action'] = self::PAYMENT_GATEWAY_PAYMENT_PROFILE_ACTION;
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $params['customer_name'] = $customer->getName();
        $params['customer_email'] = $customer->getEmail();
        return $this->_prepareRequestUrl($params, true);
    }

    /**
     * Getter.
     * Retrieve Payment Bridge url
     *
     * @param array $params
     * @return string
     */
    public function getRequestUrl($params = array())
    {
        return $this->_prepareRequestUrl($params);
    }

    /**
     * Return a modified encryptor
     *
     * @return Enterprise_Pbridge_Model_Encryption
     */
    public function getEncryptor()
    {
        if ($this->_encryptor === null) {
            $key = trim((string)Mage::getStoreConfig('payment/pbridge/transferkey', $this->_storeId));
            $this->_encryptor = Mage::getModel('enterprise_pbridge/encryption', $key);
            $this->_encryptor->setHelper($this);
        }
        return $this->_encryptor;
    }

    /**
     * Decrypt data array
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data)
    {
        return $this->getEncryptor()->decrypt($data);
    }

    /**
     * Encrypt data array
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data)
    {
        return $this->getEncryptor()->encrypt($data);
    }

    /**
     * Retrieve Payment Bridge specific GET parameters
     *
     * @return array
     */
    public function getPbridgeParams()
    {
        $decryptData = $this->decrypt($this->_getRequest()->getParam('data', ''));
        $data = json_decode($decryptData, true);
        $data = array(
            'original_payment_method' => isset($data['original_payment_method'])?$data['original_payment_method']:null,
            'token'                   => isset($data['token']) ? $data['token'] : null,
            'cc_last4'                => isset($data['cc_last4']) ? $data['cc_last4'] : null,
            'cc_type'                 => isset($data['cc_type']) ? $data['cc_type'] : null,
            'x_params'                => isset($data['x_params']) ? serialize($data['x_params']) : null,
        );

        return $data;
    }

    /**
     * Prepare cart from order
     *
     * @param Mage_Core_Model_Abstract $order
     * @return array
     */
    public function prepareCart($order)
    {
        /** @var $paypalCart Mage_Paypal_Model_Cart */
        $paypalCart = Mage::getModel('paypal/cart', array($order))->isDiscountAsItem(true);
        return array($paypalCart->getItems(true), $paypalCart->getTotals(), $paypalCart->areItemsValid());
    }

    /**
     * Return base bridge URL
     *
     * @return string
     */
    public function getBridgeBaseUrl()
    {
        return trim(Mage::getStoreConfig('payment/pbridge/gatewayurl', $this->_storeId));
    }

    /**
     * Store id setter
     *
     * @param int $storeId
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
    }

    /**
     * Get template for button in order review page if HSS method was selected
     *
     * @param string $name template name
     * @param string $block buttons block name
     * @return string
     */
    public function getReviewButtonTemplate($name, $block)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($quote) {
            $payment = $quote->getPayment();
            if ($payment->getMethodInstance()->getIsDeferred3dCheck()) {
                return $name;
            }
        }

        if ($blockObject = Mage::getSingleton('core/layout')->getBlock($block)) {
            return $blockObject->getTemplate();
        }

        return '';
    }

    /**
     * Get template for Continue button to save order and load iframe
     *
     * @param string $name template name
     * @param string $block buttons block name
     * @return string
     */
    public function getContiueButtonTemplate($name, $block)
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($quote) {
            $payment = $quote->getPayment();
            if ($payment && $payment->getMethodInstance()->getIsPendingOrderRequired()) {
                return $name;
            }
        }

        $blockObject = Mage::getSingleton('core/layout')->getBlock($block);
        if ($blockObject) {
            return $blockObject->getTemplate();
        }

        return '';
    }

    /**
     * Get HTML representation for transaction id for Payment Bridge methods
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param string $txnId
     * @return string
     */
    public function getHtmlTransactionId(Mage_Sales_Model_Order_Payment $payment, $txnId)
    {
        $methodInstance = $payment->getMethodInstance();
        $methodCode = method_exists($methodInstance, 'getOriginalCode')
            ? $methodInstance->getOriginalCode()
            : $methodInstance->getCode();
        return Mage::helper('paypal')->getHtmlTransactionId($methodCode, $txnId);
    }
}
