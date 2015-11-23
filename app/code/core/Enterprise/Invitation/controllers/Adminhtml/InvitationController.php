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
 * Invitation adminhtml controller
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */

class Enterprise_Invitation_Adminhtml_InvitationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Invitation list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Invitations'));

        $this->loadLayout()->_setActiveMenu('customer/invitation');
        $this->renderLayout();
    }

    /**
     * Init invitation model by request
     *
     * @return Enterprise_Invitation_Model_Invitation
     */
    protected function _initInvitation()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Invitations'));

        $invitation = Mage::getModel('enterprise_invitation/invitation')->load($this->getRequest()->getParam('id'));
        if (!$invitation->getId()) {
            Mage::throwException(Mage::helper('enterprise_invitation')->__('Invitation not found.'));
        }
        Mage::register('current_invitation', $invitation);

        return $invitation;
    }

    /**
     * Invitation view action
     */
    public function viewAction()
    {
        try {
            $this->_initInvitation();
            $this->loadLayout()->_setActiveMenu('customer/invitation');
            $this->renderLayout();
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
        }
    }

    /**
     * Create new invitatoin form
     */
    public function newAction()
    {
        $this->loadLayout()->_setActiveMenu('enterprise_invitation');
        $this->renderLayout();
    }

    /**
     * Create & send new invitations
     */
    public function saveAction()
    {
        try {
            // parse POST data
            if (!$this->getRequest()->isPost()) {
                $this->_redirect('*/*/');
                return;
            }
            $this->_getSession()->setInvitationFormData($this->getRequest()->getPost());
            $emails = preg_split('/\s+/s', $this->getRequest()->getParam('email'));
            foreach ($emails as $key => $email) {
                $email = trim($email);
                if (empty($email)) {
                    unset($emails[$key]);
                }
                else {
                    $emails[$key] = $email;
                }
            }
            if (empty($emails)) {
                Mage::throwException(Mage::helper('enterprise_invitation')->__('Please specify at least one email.'));
            }
            if (Mage::app()->isSingleStoreMode()) {
                $storeId = Mage::app()->getStore(true)->getId();
            }
            else {
                $storeId = $this->getRequest()->getParam('store_id');
            }

            // try to send invitation(s)
            $sentCount   = 0;
            $failedCount = 0;
            $customerExistsCount = 0;
            foreach ($emails as $key => $email) {
                try {
                    $invitation = Mage::getModel('enterprise_invitation/invitation')->setData(array(
                        'email'    => $email,
                        'store_id' => $storeId,
                        'message'  => $this->getRequest()->getParam('message'),
                        'group_id' => $this->getRequest()->getParam('group_id'),
                    ))->save();
                    if ($invitation->sendInvitationEmail()) {
                        $sentCount++;
                    }
                    else {
                        $failedCount++;
                    }
                }
                catch (Mage_Core_Exception $e) {
                    if ($e->getCode()) {
                        $failedCount++;
                        if ($e->getCode() == Enterprise_Invitation_Model_Invitation::ERROR_CUSTOMER_EXISTS) {
                            $customerExistsCount++;
                        }
                    }
                    else {
                        throw $e;
                    }
                }
            }
            if ($sentCount) {
                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_invitation')->__('%d invitation(s) were sent.', $sentCount)
                );
            }
            if ($failedCount) {
                $this->_getSession()->addError(
                    Mage::helper('enterprise_invitation')->__('Failed to send %1$d of %2$d invitation(s).', $failedCount, count($emails))
                );
            }
            if ($customerExistsCount) {
                $this->_getSession()->addNotice(
                    Mage::helper('enterprise_invitation')->__('%d invitation(s) were not sent, because customer accounts already exist for specified email addresses.', $customerExistsCount)
                );
            }
            $this->_getSession()->unsInvitationFormData();
            $this->_redirect('*/*/');
            return;
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/new');
    }

    /**
     * Edit invitation's information
     */
    public function saveInvitationAction()
    {
        try {
            $invitation = $this->_initInvitation();

            if ($this->getRequest()->isPost()) {
                $email = $this->getRequest()->getParam('email');

                $invitation->setMessage($this->getRequest()->getParam('message'))
                    ->setEmail($email);

                $result = $invitation->validate();
                //checking if there was validation
                if (is_array($result) && !empty($result)) {
                    foreach ($result as $message) {
                        $this->_getSession()->addError($message);
                    }
                    $this->_redirect('*/*/view', array('_current' => true));
                    return $this;
                }

                //If there was no validation errors trying to save
                $invitation->save();

                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_invitation')->__('The invitation has been saved.')
                );
            }
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/view', array('_current' => true));
    }

    /**
     * Action for mass-resending invitations
     */
    public function massResendAction()
    {
        try {
            $invitationsPost = $this->getRequest()->getParam('invitations', array());
            if (empty($invitationsPost) || !is_array($invitationsPost)) {
                Mage::throwException(Mage::helper('enterprise_invitation')->__('Please select invitations.'));
            }
            $collection = Mage::getModel('enterprise_invitation/invitation')->getCollection()
                ->addFieldToFilter('invitation_id', array('in' => $invitationsPost))
                ->addCanBeSentFilter();
            $found = 0;
            $sent  = 0;
            $customerExists = 0;
            foreach ($collection as $invitation) {
                try {
                    $invitation->makeSureCanBeSent();
                    $found++;
                    if ($invitation->sendInvitationEmail()) {
                        $sent++;
                    }
                }
                catch (Mage_Core_Exception $e) {
                    // jam all exceptions with codes
                    if (!$e->getCode()) {
                        throw $e;
                    }
                    // close irrelevant invitations
                    if ($e->getCode() === Enterprise_Invitation_Model_Invitation::ERROR_CUSTOMER_EXISTS) {
                        $customerExists++;
                        $invitation->cancel();
                    }
                }
            }
            if (!$found) {
                $this->_getSession()->addError(
                    Mage::helper('enterprise_invitation')->__('No invitations have been resent')
                );
            }
            if ($sent) {
                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_invitation')->__('%1$d of %2$d invitations were sent.', $sent, $found)
                );
            }
            if ($failed = ($found - $sent)) {
                $this->_getSession()->addError(
                    Mage::helper('enterprise_invitation')->__('Failed to send %d invitation(s).', $failed)
                );
            }
            if ($customerExists) {
                $this->_getSession()->addNotice(
                    Mage::helper('enterprise_invitation')->__('%d invitation(s) cannot be sent, because customer already exists for their emails. These invitations were discarded.', $customerExists)
                );
            }
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * Action for mass-cancelling invitations
     */
    public function massCancelAction()
    {
        try {
            $invitationsPost = $this->getRequest()->getParam('invitations', array());
            if (empty($invitationsPost) || !is_array($invitationsPost)) {
                Mage::throwException(Mage::helper('enterprise_invitation')->__('Please select invitations.'));
            }
            $collection = Mage::getModel('enterprise_invitation/invitation')->getCollection()
                ->addFieldToFilter('invitation_id', array('in' => $invitationsPost))
                ->addCanBeCanceledFilter();
            $found     = 0;
            $cancelled = 0;
            foreach ($collection as $invitation) {
                try {
                    $found++;
                    if ($invitation->canBeCanceled()) {
                        $invitation->cancel();
                        $cancelled++;
                    }
                }
                catch (Mage_Core_Exception $e) {
                    // jam all exceptions with codes
                    if (!$e->getCode()) {
                        throw $e;
                    }
                }
            }
            if ($cancelled) {
                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_invitation')->__('%1$d of %2$d invitations were discarded.', $cancelled, $found)
                );
            }
            if ($failed = ($found - $cancelled)) {
                $this->_getSession()->addNotice(
                    Mage::helper('enterprise_invitation')->__('%d of selected invitation(s) were skipped.', $failed)
                );
            }
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * Acl admin user check
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('enterprise_invitation/config')->isEnabled()
            && Mage::getSingleton('admin/session')->isAllowed('customer/enterprise_invitation');
    }
}
