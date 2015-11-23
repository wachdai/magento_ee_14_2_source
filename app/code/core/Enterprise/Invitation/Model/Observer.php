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
 * Invitation data model
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Model_Observer
{
    /**
     * Flag that indicates customer registration page
     *
     * @var boolean
     */
    protected $_flagInCustomerRegistration = false;

    protected $_config;

    public function __construct()
    {
        $this->_config = Mage::getSingleton('enterprise_invitation/config');
    }

    /**
     * Observe customer registration for invitations
     *
     * @return void
     */
    public function restrictCustomerRegistration(Varien_Event_Observer $observer)
    {
        if (!$this->_config->isEnabledOnFront()) {
            return;
        }

        $result = $observer->getEvent()->getResult();

        if (!$result->getIsAllowed()) {
            Mage::helper('enterprise_invitation')->isRegistrationAllowed(false);
        } else {
            Mage::helper('enterprise_invitation')->isRegistrationAllowed(true);
            $result->setIsAllowed(!$this->_config->getInvitationRequired());
        }
    }

    /**
     * Handler for invitation mass update
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchInvitationMassUpdate($config, $eventModel)
    {
        $messages = Mage::getSingleton('admin/session')->getMessages();
        $errors = $messages->getErrors();
        $notices = $messages->getItemsByType(Mage_Core_Model_Message::NOTICE);
        $status = (empty($errors) && empty($notices))
            ? Enterprise_Logging_Model_Event::RESULT_SUCCESS : Enterprise_Logging_Model_Event::RESULT_FAILURE;
        return $eventModel->setStatus($status)
            ->setInfo(Mage::app()->getRequest()->getParam('invitations'));
    }

    /**
     * Custom log invitation log action
     *
     * @deprecated after 1.6.0.0
     *
     * @param Interprise_Invitation_Model_Invitation $model
     * @param Enterprise_Logging_Model_Processor $processor
     * @return Enterprise_Logging_Model_Event_Changes
     */
    public function logInvitationSave($model, $processor)
    {
        $processor->collectId($model);
        return Mage::getModel('enterprise_logging/event_changes')
            ->setOrigibalData(array())
            ->setResultData($model->getData());
    }
}
