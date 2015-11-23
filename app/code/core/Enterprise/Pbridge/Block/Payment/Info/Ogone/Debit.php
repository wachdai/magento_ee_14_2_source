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
 * Info payment block for Ogone Direct Debit
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pbridge_Block_Payment_Info_Ogone_Debit extends Mage_Payment_Block_Info
{
    /**
     * Prepare credit card related payment info
     *
     * @param Varien_Object|array $transport
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);

        $data = array();

        $details = @unserialize($this->getInfo()->getAdditionalData());
        if (!isset($details['pbridge_data']['x_params'])) {
            return $transport;
        }

        $xParams = @unserialize($details['pbridge_data']['x_params']);

        if (isset($xParams['dd_bankaccountholder']) && !empty($xParams['dd_bankaccountholder'])) {
            $data[Mage::helper('enterprise_pbridge')->__('Account holder')] = $xParams['dd_bankaccountholder'];
        }

        if (isset($xParams['dd_bankaccount'])) {
            $data[Mage::helper('enterprise_pbridge')->__('Account number')] = sprintf('xxxx-%s', $xParams['dd_bankaccount']);
        }

        if (isset($xParams['dd_bankcode']) && !empty($xParams['dd_bankcode'])) {
            $data[Mage::helper('enterprise_pbridge')->__('Bank code')] = $xParams['dd_bankcode'];
        }

        if (!empty($data)) {
            return $transport->setData(array_merge($data, $transport->getData()));
        } else {
            return $transport;
        }
    }
}
