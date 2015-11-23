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
 * Invitation config model, used for retrieve data from configuration
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Model_Config
{
    const XML_PATH_ENABLED = 'enterprise_invitation/general/enabled';
    const XML_PATH_ENABLED_ON_FRONT = 'enterprise_invitation/general/enabled_on_front';

    const XML_PATH_USE_INVITATION_MESSAGE = 'enterprise_invitation/general/allow_customer_message';
    const XML_PATH_MAX_INVITATION_AMOUNT_PER_SEND = 'enterprise_invitation/general/max_invitation_amount_per_send';

    const XML_PATH_REGISTRATION_REQUIRED_INVITATION = 'enterprise_invitation/general/registration_required_invitation';
    const XML_PATH_REGISTRATION_USE_INVITER_GROUP = 'enterprise_invitation/general/registration_use_inviter_group';

    /**
     * Return max Invitation amount per send by config
     *
     * @param int $storeId
     * @return int
     */
    public function getMaxInvitationsPerSend($storeId = null)
    {
        $max = (int)Mage::getStoreConfig(self::XML_PATH_MAX_INVITATION_AMOUNT_PER_SEND, $storeId);
        return ($max < 1 ? 1 : $max);
    }

    /**
     * Return config value for required cutomer registration by invitation
     *
     * @param int $storeId
     * @return boolean
     */
    public function getInvitationRequired($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_REGISTRATION_REQUIRED_INVITATION, $storeId);
    }

    /**
     * Return config value for use same group as inviter
     *
     * @param int $storeId
     * @return boolean
     */
    public function getUseInviterGroup($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_REGISTRATION_USE_INVITER_GROUP, $storeId);
    }

    /**
     * Check whether invitations allow to set custom message
     *
     * @param int $storeId
     * @return bool
     */
    public function isInvitationMessageAllowed($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_INVITATION_MESSAGE, $storeId);
    }

    /**
     * Retrieve configuration for availability of invitations
     * on global level. Also will disallowe any functionality in admin.
     *
     * @param int $storeId
     * @return boolean
     */
    public function isEnabled($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED, $storeId);
    }

    /**
     * Retrieve configuration for availability of invitations
     * on front for specified store. Global parameter 'enabled' has more priority.
     *
     * @param int $storeId
     * @return boolean
     */
    public function isEnabledOnFront($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED_ON_FRONT, $storeId);
        }

        return false;
    }
}
