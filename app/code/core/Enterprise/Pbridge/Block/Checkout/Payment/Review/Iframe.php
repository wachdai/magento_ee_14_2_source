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
 * Dibs payment block
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento
 */
class Enterprise_Pbridge_Block_Checkout_Payment_Review_Iframe extends Enterprise_Pbridge_Block_Iframe_Abstract
{
    /**
     * Default iframe height
     *
     * @var string
     */
    protected $_iframeHeight = '400';

    /**
     * Return redirect url for Payment Bridge application
     *
     * @return string
     */
    public function getRedirectUrlSuccess()
    {
        if ($this->_getData('redirect_url_success')) {
            return $this->_getData('redirect_url_success');
        }
        return $this->getUrl('enterprise_pbridge/pbridge/success', array('_current' => true, '_secure' => true));
    }

    /**
     * Return redirect url for Payment Bridge application
     *
     * @return string
     */
    public function getRedirectUrlError()
    {
        if ($this->_getData('redirect_url_error')) {
            return $this->_getData('redirect_url_error');
        }
        return $this->getUrl('enterprise_pbridge/pbridge/error', array('_current' => true, '_secure' => true));
    }

    /**
     * Getter.
     * Return Payment Bridge url with required parameters (such as merchant code, merchant key etc.)
     * Can include quote shipping and billing address if its required in payment processing
     *
     * @return string
     */
    public function getSourceUrl()
    {
        $requestParams = array(
            'notify_url'             => Mage::getUrl('enterprise_pbridge/PbridgeIpn/'),
            'redirect_url_success'   => $this->getRedirectUrlSuccess(),
            'redirect_url_error'     => $this->getRedirectUrlError(),
            'request_gateway_code'   => $this->getMethod()->getOriginalCode(),
            'token'                  => Mage::getSingleton('enterprise_pbridge/session')->getToken(),
            'already_entered'        => '1',
            'magento_payment_action' => $this->getMethod()->getConfigPaymentAction(),
            'css_url'                => $this->getCssUrl(),
            'customer_id'            => $this->getCustomerIdentifier(),
            'customer_name'          => $this->getCustomerName(),
            'customer_email'         => $this->getCustomerEmail(),
            'client_ip'              => Mage::app()->getRequest()->getClientIp(false)
        );

        $sourceUrl = Mage::helper('enterprise_pbridge')->getGatewayFormUrl($requestParams, $this->getQuote());
        return $sourceUrl;
    }
}
