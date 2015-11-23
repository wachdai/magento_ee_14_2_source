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
 * Invitation view block
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Block_Adminhtml_Invitation_View extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Set header text, add some buttons
     *
     * @return Enterprise_Invitation_Block_Adminhtml_Invitation_View
     */
    protected function _prepareLayout()
    {
        $invitation = $this->getInvitation();
        $this->_headerText = Mage::helper('enterprise_invitation')->__('View Invitation for %s (ID: %s)', $invitation->getEmail(), $invitation->getId());
        $this->_addButton('back', array(
            'label' => Mage::helper('enterprise_invitation')->__('Back'),
            'onclick' => "setLocation('{$this->getUrl('*/*/')}')",
            'class' => 'back',
        ), -1);
        if ($invitation->canBeCanceled()) {
            $massCancelUrl = $this->getUrl('*/*/massCancel', array('_query' => array('invitations' => array($invitation->getId()))));
            $this->_addButton('cancel', array(
                'label' => Mage::helper('enterprise_invitation')->__('Discard Invitation'),
                'onclick' => 'deleteConfirm(\''. $this->jsQuoteEscape(
                            Mage::helper('enterprise_invitation')->__('Are you sure you want to discard this invitation?')
                        ) . '\', \'' . $massCancelUrl . '\' )',
                'class' => 'cancel'
            ), -1);
        }
        if ($invitation->canMessageBeUpdated()) {
            $this->_addButton('save_message_button', array(
                'label'   => $this->helper('enterprise_invitation')->__('Save Invitation'),
                'onclick' => 'invitationForm.submit()',
            ), -1);
        }
        if ($invitation->canBeSent()) {
            $massResendUrl = $this->getUrl('*/*/massResend', array('_query' => http_build_query(array('invitations' => array($invitation->getId())))));
            $this->_addButton('resend', array(
                'label' => Mage::helper('enterprise_invitation')->__('Send Invitation'),
                'onclick' => "setLocation('{$massResendUrl}')",
            ), -1);
        }

        parent::_prepareLayout();
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
     * Retrieve save message url
     *
     * @return string
     */
    public function getSaveMessageUrl()
    {
        return $this->getUrl('*/*/saveInvitation', array('id'=>$this->getInvitation()->getId()));
    }
}
