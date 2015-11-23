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
 * @package     Enterprise_Invitation
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Invitation data helper
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_isRegistrationAllowed = null;

    /**
     * Return max Invitation amount per send by config.
     * Depricated. Config model 'enterprise_invitation/config' should be used directly.
     *
     * @return int
     */
    public function getMaxInvitationsPerSend()
    {
        return Mage::getSingleton('enterprise_invitation/config')->getMaxInvitationsPerSend();
    }

    /**
     * Return config value for required cutomer registration by invitation
     * Depricated. Config model 'enterprise_invitation/config' should be used directly.
     *
     * @return boolean
     */
    public function getInvitationRequired()
    {
        return Mage::getSingleton('enterprise_invitation/config')->getInvitationRequired();
    }


    /**
     * Return config value for use same group as inviter
     * Depricated. Config model 'enterprise_invitation/config' should be used directly.
     *
     * @return boolean
     */
    public function getUseInviterGroup()
    {
        return Mage::getSingleton('enterprise_invitation/config')->getUseInviterGroup();
    }

    /**
     * Check whether invitations allow to set custom message
     * Depricated. Config model 'enterprise_invitation/config' should be used directly.
     *
     * @return bool
     */
    public function isInvitationMessageAllowed()
    {
        return Mage::getSingleton('enterprise_invitation/config')->isInvitationMessageAllowed();
    }

    /**
     * Return text for invetation status
     *
     * @return Enterprise_Invitation_Model_Invitation $invitation
     * @return string
     */
    public function getInvitationStatusText($invitation)
    {
        return Mage::getSingleton('enterprise_invitation/source_invitation_status')->getOptionText($invitation->getStatus());
    }

    /**
     * Return invitation url
     *
     * @param Enterprise_Invitation_Model_Invitation $invitation
     * @return string
     */
    public function getInvitationUrl($invitation)
    {
        return Mage::getModel('core/url')->setStore($invitation->getStoreId())
            ->getUrl('enterprise_invitation/customer_account/create', array(
                'invitation' => Mage::helper('core')->urlEncode($invitation->getInvitationCode()),
                '_store_to_url' => true,
                '_nosid' => true
            ));
    }

    /**
     * Return account dashboard invitation url
     *
     * @return string
     */
    public function getCustomerInvitationUrl()
    {
        return $this->_getUrl('enterprise_invitation/');
    }

    /**
     * Return invitation send form url
     *
     * @return string
     */
    public function getCustomerInvitationFormUrl()
    {
        return $this->_getUrl('enterprise_invitation/index/send');
    }

    /**
     * Checks is allowed registration in invitation controller
     *
     * @param boolean $isAllowed
     * @return boolean
     */
    public function isRegistrationAllowed($isAllowed = null)
    {
        if ($isAllowed === null && $this->_isRegistrationAllowed === null) {
            $result = Mage::helper('customer')->isRegistrationAllowed();
            if ($this->_isRegistrationAllowed === null) {
                $this->_isRegistrationAllowed = $result;
            }
        } elseif ($isAllowed !== null) {
            $this->_isRegistrationAllowed = $isAllowed;
        }

        return $this->_isRegistrationAllowed;
    }

    /**
     * Retrieve configuration for availability of invitations
     * Depricated. Config model 'enterprise_invitation/config' should be used directly.
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getSingleton('enterprise_invitation/config')->isEnabled();
    }
}
