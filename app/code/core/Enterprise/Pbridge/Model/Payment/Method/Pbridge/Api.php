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
 * Pbridge API model
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pbridge_Model_Payment_Method_Pbridge_Api extends Enterprise_Pbridge_Model_Pbridge_Api_Abstract
{
    /**
     * Prepare, merge, encrypt required params for Payment Bridge and payment request params.
     * Return request params as http query string
     *
     * @param array $request
     * @return string
     */
    protected function _prepareRequestParams($request)
    {
        $request['action'] = 'Payments';
        $request['token'] = $this->getMethodInstance()->getPbridgeResponse('token');
        $request = Mage::helper('enterprise_pbridge')->getRequestParams($request);
        $request = array('data' => Mage::helper('enterprise_pbridge')->encrypt(json_encode($request)));
        return http_build_query($request, '', '&');
    }

    public function validateToken($orderId)
    {
        $this->_call(array(
            'client_identifier' => $orderId,
            'payment_action' => 'validate_token'
        ));
        return $this;
    }

    /**
     * Authorize
     *
     * @param Varien_Object $request
     * @return Enterprise_Pbridge_Model_Payment_Method_Pbridge_Api
     */
    public function doAuthorize($request)
    {
        $request->setData('payment_action', 'place');
        $this->_call($request->getData());
        return $this;
    }

    /**
     * Capture
     *
     * @param Varien_Object $request
     * @return Enterprise_Pbridge_Model_Payment_Method_Pbridge_Api
     */
    public function doCapture($request)
    {
        $request->setData('payment_action', 'capture');
        $this->_call($request->getData());
        return $this;
    }

    /**
     * Refund
     *
     * @param Varien_Object $request
     * @return Enterprise_Pbridge_Model_Payment_Method_Pbridge_Api
     */
    public function doRefund($request)
    {
        $request->setData('payment_action', 'refund');
        $this->_call($request->getData());
        return $this;
    }

    /**
     * Void
     *
     * @param Varien_Object $request
     * @return Enterprise_Pbridge_Model_Payment_Method_Pbridge_Api
     */
    public function doVoid($request)
    {
        $request->setData('payment_action', 'void');
        $this->_call($request->getData());
        return $this;
    }

    /**
     * Accept payment transaction
     *
     * @param Varien_Object $request
     * @return Enterprise_Pbridge_Model_Payment_Method_Pbridge_Api
     */
    public function doAccept($request)
    {
        $request->setData('payment_action', 'accept');
        $this->_call($request->getData());
        return $this;
    }

    /**
     * Deny payment transaction
     *
     * @param Varien_Object $request
     * @return Enterprise_Pbridge_Model_Payment_Method_Pbridge_Api
     */
    public function doDeny($request)
    {
        $request->setData('payment_action', 'deny');
        $this->_call($request->getData());
        return $this;
    }

    /**
     * Fetch transaction info
     *
     * @param Varien_Object $request
     * @return Enterprise_Pbridge_Model_Payment_Method_Pbridge_Api
     */
    public function doFetchTransactionInfo($request)
    {
        $request->setData('payment_action', 'fetch');
        $this->_call($request->getData());
        return $this;
    }
}
