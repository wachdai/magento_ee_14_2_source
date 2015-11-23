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
 * Eway.Com.Au dummy payment method model
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pbridge_Model_Payment_Method_Eway_Direct extends Enterprise_Pbridge_Model_Payment_Method_Abstract
{
    /**
     * Eway Direct payment method code
     *
     * @var string
     */
    const PAYMENT_CODE = 'eway_direct';

    /**
     * List of default accepted currency codes supported by payment gateway
     *
     * @var array
     */
    protected $_allowCurrencyCode = array('USD', 'GBP', 'NZD', 'CAD', 'HKD', 'SGD', 'EUR', 'JPY');

    /**
     * @var string
     */
    protected $_code = self::PAYMENT_CODE;

    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canCaptureOnce          = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc               = true;

    /**
     * Return payment method Centinel validation status
     *
     * @return bool
     */
    public function getIsCentinelValidationEnabled()
    {
        return false;
    }

    /**
     * Check whether it's possible to void authorization
     *
     * @param Varien_Object $payment
     * @return bool
     */
    public function canVoid(Varien_Object $payment)
    {
        $canVoid = parent::canVoid($payment);

        if ($canVoid) {
            $order = $this->getInfoInstance()->getOrder();

            if ($order && count($order->getInvoiceCollection()) > 0) {
                $canVoid = false;
            }
        }

        return $canVoid;
    }
}
