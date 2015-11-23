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
 * Invitation view general tab block
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Block_Adminhtml_Invitation_View_Tab_General extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Set template
     *
     */
    protected function _construct()
    {
        $this->setTemplate('enterprise/invitation/view/tab/general.phtml');
    }

    /**
     * Tab label getter
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('enterprise_invitation')->__('General');
    }

    /**
     * Tab Title getter
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Check whether tab can be showed
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check whether tab is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Return Invitation for view
     *
     * @return Enterprise_Invitation_Model_Invitation
     */
    public function getInvitation()
    {
        return Mage::registry('current_invitation');
    }

    /**
     * Check whether it is possible to edit invitation message
     *
     * @return bool
     */
    public function canEditMessage()
    {
        return $this->getInvitation()->canMessageBeUpdated();
    }

    /**
     * Return save message button html
     *
     * @return string
     */
    public function getSaveMessageButtonHtml()
    {
        return $this->getChildHtml('save_message_button');
    }

    /**
     * Retrieve formating date
     *
     * @param   string $date
     * @param   string $format
     * @param   bool $showTime
     * @return  string
     */
    public function formatDate($date=null, $format='short', $showTime=false)
    {
        if (is_string($date)) {
            $date = Mage::app()->getLocale()->date($date, Varien_Date::DATETIME_INTERNAL_FORMAT);
        }

        return parent::formatDate($date, $format, $showTime);
    }

    /**
     * Return invintation customer model
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getReferral()
    {
        if (!$this->hasData('referral')) {
            if ($this->getInvitation()->getReferralId()) {
                $referral = Mage::getModel('customer/customer')->load(
                    $this->getInvitation()->getReferralId()
                );
            } else {
                $referral = false;
            }

            $this->setData('referral', $referral);
        }

        return $this->getData('referral');
    }

    /**
     * Return invitation customer model
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (!$this->hasData('customer')) {
            if ($this->getInvitation()->getCustomerId()) {
                $customer = Mage::getModel('customer/customer')->load(
                    $this->getInvitation()->getCustomerId()
                );
            } else {
                $customer = false;
            }

            $this->setData('customer', $customer);
        }

        return $this->getData('customer');
    }

    /**
     * Return customer group collection
     *
     * @return Mage_Customer_Model_Entity_Group_Collection
     */
    public function getCustomerGroupCollection()
    {
        if (!$this->hasData('customer_groups_collection')) {
            $groups = Mage::getModel('customer/group')->getCollection()
                ->addFieldToFilter('customer_group_id', array('gt'=> 0))
                ->load();
            $this->setData('customer_groups_collection', $groups);
        }

        return $this->getData('customer_groups_collection');
    }

    /**
     * Return customer group code by group id
     * If $configUsed passed as true then result will be default string
     * instead of N/A sign
     *
     * @param int $groupId
     * @param bool $configUsed
     * @return string
     */
    public function getCustomerGroupCode($groupId, $configUsed = false)
    {
        $group = $this->getCustomerGroupCollection()->getItemById($groupId);
        if ($group) {
            return $group->getCustomerGroupCode();
        } else {
            if ($configUsed) {
                return $this->helper('enterprise_invitation')->__('Default from System Configuration');
            } else {
                return $this->helper('enterprise_invitation')->__('N/A');
            }
        }
    }

    /**
     * Invitation website name getter
     *
     * @return string
     */
    public function getWebsiteName()
    {
        return Mage::app()->getStore($this->getInvitation()->getStoreId())
            ->getWebsite()->getName();
    }

    /**
     * Invitation store name getter
     *
     * @return string
     */
    public function getStoreName()
    {
        return Mage::app()->getStore($this->getInvitation()->getStoreId())
            ->getName();
    }

    /**
     * Get invitation URL in case if it can be accepted
     *
     * @return string|false
     */
    public function getInvitationUrl()
    {
        if (!$this->getInvitation()->canBeAccepted(
            Mage::app()->getStore($this->getInvitation()->getStoreId())->getWebsiteId())) {
            return false;
        }
        return Mage::helper('enterprise_invitation')->getInvitationUrl($this->getInvitation());
    }

    /**
     * Checks if this invitation was sent by admin
     *
     * @return boolean - true if this invitation was sent by admin, false otherwise
     */
    public function isInvitedByAdmin()
    {
        $invitedByAdmin = ($this->getInvitation()->getCustomerId() == null);
        return $invitedByAdmin;
    }

    /**
     * Check whether can show referral link
     *
     * @return bool
     */
    public function canShowReferralLink()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }
}
