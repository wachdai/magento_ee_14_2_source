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

require_once  'Mage/Customer/controllers/AccountController.php';

/**
 * Invitation customer account frontend controller
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Customer_AccountController extends Mage_Customer_AccountController
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('createPost');

    /**
     * Predispatch custom logic
     *
     * Bypassing direct parent predispatch
     * Allowing only specific actions
     * Checking whether invitation functionality is enabled
     * Checking whether registration is allowed at all
     * No way to logged in customers
     */
    public function preDispatch()
    {
        Mage_Core_Controller_Front_Action::preDispatch();

        if (!preg_match('/^(create|createpost)/i', $this->getRequest()->getActionName())) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return;
        }
        if (!Mage::getSingleton('enterprise_invitation/config')->isEnabledOnFront()) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return;
        }
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('customer/account/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return;
        }

        return $this;
    }

    /**
     * Hack real module name in order to make translations working correctly
     *
     * @return string
     */
    protected function _getRealModuleName()
    {
        return 'Mage_Customer';
    }

    /**
     * Initialize invitation from request
     *
     * @return Enterprise_Invitation_Model_Invitation
     */
    protected function _initInvitation()
    {
        if (!Mage::registry('current_invitation')) {
            $invitation = Mage::getModel('enterprise_invitation/invitation');
            $invitation
                ->loadByInvitationCode(
                    Mage::helper('core')->urlDecode($this->getRequest()->getParam('invitation', false))
                )
                ->makeSureCanBeAccepted();
            Mage::register('current_invitation', $invitation);
        }
        return Mage::registry('current_invitation');
    }

    /**
     * Customer register form page
     */
    public function createAction()
    {
        try {
            $invitation = $this->_initInvitation();
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->renderLayout();
            return;
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('customer/account/login');
    }

    /**
     * Create customer account action
     */
    public function createPostAction()
    {
        try {
            $invitation = $this->_initInvitation();

            $customer = Mage::getModel('customer/customer')
                ->setId(null)->setSkipConfirmationIfEmail($invitation->getEmail());
            Mage::register('current_customer', $customer);

            if ($groupId = $invitation->getGroupId()) {
                $customer->setGroupId($groupId);
            }

            parent::createPostAction();

            if ($customerId = $customer->getId()) {
                $invitation->accept(Mage::app()->getWebsite()->getId(), $customerId);

                Mage::dispatchEvent('enterprise_invitation_customer_accepted', array(
                   'customer' => $customer,
                   'invitation' => $invitation
                ));
            }
            return;
        }
        catch (Mage_Core_Exception $e) {
            $_definedErrorCodes = array(
                Enterprise_Invitation_Model_Invitation::ERROR_CUSTOMER_EXISTS,
                Enterprise_Invitation_Model_Invitation::ERROR_INVALID_DATA
            );
            if (in_array($e->getCode(), $_definedErrorCodes)) {
                $this->_getSession()->addError($e->getMessage())
                    ->setCustomerFormData($this->getRequest()->getPost());
            } else {
                if (Mage::helper('customer')->isRegistrationAllowed()) {
                    $this->_getSession()->addError(
                        Mage::helper('enterprise_invitation')->__('Your invitation is not valid. Please create an account.')
                    );
                    $this->_redirect('customer/account/create');
                    return;
                } else {
                    $this->_getSession()->addError(
                        Mage::helper('enterprise_invitation')->__('Your invitation is not valid. Please contact us at %s.', Mage::getStoreConfig('trans_email/ident_support/email'))
                    );
                    $this->_redirect('customer/account/login');
                    return;
                }
            }
        }
        catch (Exception $e) {
            $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                ->addException($e, Mage::helper('customer')->__('Unable to save the customer.'));
        }

        $this->_redirectError('');

        return $this;
    }

    /**
     * Make success redirect constant
     *
     * @param string $defaultUrl
     * @return Enterprise_Invitation_Customer_AccountController
     */
    protected function _redirectSuccess($defaultUrl)
    {
        return $this->_redirect('customer/account/');
    }

    /**
     * Make failure redirect constant
     *
     * @param string $defaultUrl
     * @return Enterprise_Invitation_Customer_AccountController
     */
    protected function _redirectError($defaultUrl)
    {
        return $this->_redirect('enterprise_invitation/customer_account/create',
            array('_current' => true, '_secure' => true));
    }
}
