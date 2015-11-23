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
 * Customer invitation list block
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Block_Customer_List extends Mage_Customer_Block_Account_Dashboard
{
    /**
     * Return list of invitations
     *
     * @return Enterprise_Invitation_Model_Mysql4_Invitation_Collection
     */
    public function getInvitationCollection()
    {
        if (!$this->hasInvitationCollection()) {
            $this->setData('invitation_collection', Mage::getModel('enterprise_invitation/invitation')->getCollection()
                ->addOrder('invitation_id', Varien_Data_Collection::SORT_ORDER_DESC)
                ->loadByCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
            );
        }
        return $this->_getData('invitation_collection');
    }

    /**
     * Return status text for invitation
     *
     * @param Enterprise_Invitation_Model_Invitation $invitation
     * @return string
     */
    public function getStatusText($invitation)
    {
        return Mage::getSingleton('enterprise_invitation/source_invitation_status')
            ->getOptionText($invitation->getStatus());
    }
}
