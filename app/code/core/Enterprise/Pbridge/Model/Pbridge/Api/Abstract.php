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
 * Abstract Pbridge API model
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pbridge_Model_Pbridge_Api_Abstract extends Varien_Object
{
    /**
     * Api response
     *
     * @var $_response array
     */
    protected $_response = array();

    /**
     * Make a call to Payment Bridge service with given request parameters
     *
     * @param array $request
     * @return array
     * @throws Mage_Core_Exception
     */
    protected function _call(array $request)
    {
        $response = null;
        $debugData = array('request' => $request);
        try {
            $http = new Varien_Http_Adapter_Curl();
            $config = array('timeout' => 60);
            $http->setConfig($config);
            $http->write(
                Zend_Http_Client::POST,
                $this->getPbridgeEndpoint(),
                '1.1',
                array(),
                $this->_prepareRequestParams($request)
            );
            $response = $http->read();
        } catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $this->_debug($debugData);
            throw $e;
        }

        $this->_debug($response);

        $curlErrorNumber = $http->getErrno();
        $curlError = $http->getError();
        $http->close();

        if ($response) {

            $response = preg_split('/^\r?$/m', $response, 2);
            $response = Mage::helper('core')->jsonDecode(trim($response[1]));

            $debugData['result'] = $response;
            $this->_debug($debugData);

            if ($curlErrorNumber) {
                Mage::logException(new Exception(
                    sprintf('Payment Bridge CURL connection error #%s: %s', $curlErrorNumber, $curlError)
                ));

                Mage::throwException(
                    Mage::helper('enterprise_pbridge')->__('Unable to communicate with Payment Bridge service.')
                );
            }
            if (isset($response['status']) && $response['status'] == 'Success') {
                $this->_response = $response;
                return true;
            }
        } else {
            $response = array(
                'status' => 'Fail',
                'error' => Mage::helper('enterprise_pbridge')->__('Empty response received from Payment Bridge.')
            );
        }

        $this->_handleError($response);
        $this->_response = $response;
        return false;
    }

    /**
     * Handle error of given response
     *
     * @param array $response
     * @return void
     * @throws Mage_Core_Exception
     */
    protected function _handleError($response)
    {
        if (isset($response['status']) && $response['status'] == 'Fail' && isset($response['error'])) {
            Mage::throwException($response['error']);
        }
        Mage::throwException(Mage::helper('enterprise_pbridge')->__('Payment gateway internal error.'));
    }

    /**
     * Prepare, merge, encrypt required params for Payment Bridge and payment request params.
     * Return request params as http query string
     *
     * @param array $request
     * @return string
     */
    protected function _prepareRequestParams($request)
    {
        $request = Mage::helper('enterprise_pbridge')->getRequestParams($request);
        $request = array('data' => Mage::helper('enterprise_pbridge')->encrypt(json_encode($request)));
        return http_build_query($request, '', '&');
    }

    /**
     * Retrieve Payment Bridge servise URL
     *
     * @return string
     */
    public function getPbridgeEndpoint()
    {
        return Mage::helper('enterprise_pbridge')->getRequestUrl();
    }

    /**
     * Log debug data to file
     *
     * @param mixed $debugData
     * @return void
     */
    protected function _debug($debugData)
    {
        $this->_debugFlag = (bool)Mage::getStoreConfigFlag('payment/pbridge/debug');
        if ($this->_debugFlag) {
            Mage::getModel('core/log_adapter', 'payment_pbridge.log')
               ->log($debugData);
        }
    }

    /**
     * Return API response
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->_response;
    }
}
