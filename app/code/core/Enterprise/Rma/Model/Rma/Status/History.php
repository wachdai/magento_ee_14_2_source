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
 * @package     Enterprise_Rma
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * RMA model
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Model_Rma_Status_History extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('enterprise_rma/rma_status_history');
    }

    /**
     * Get store object
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if ($this->getOrder()) {
            return $this->getOrder()->getStore();
        }
        return Mage::app()->getStore();
    }

    /**
     * Get RMA object
     *
     * @return Enterprise_Rma_Model_Rma;
     */
    public function getRma()
    {
        if (!$this->hasData('rma') && $this->getRmaEntityId()) {
            $rma = Mage::getModel('enterprise_rma/rma')->load($this->getRmaEntityId());
            $this->setData('rma', $rma);
        }
        return $this->getData('rma');
    }

    /**
     * Sending email with comment data
     *
     * @return Enterprise_Rma_Model_Rma_Status_History
     */
    public function sendCommentEmail()
    {
        /** @var $configRmaEmail Enterprise_Rma_Model_Config */
        $configRmaEmail = Mage::getSingleton('enterprise_rma/config');
        $order = $this->getRma()->getOrder();
        if ($order->getCustomerIsGuest()) {
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $customerName = $order->getCustomerName();
        }
        $sendTo = array(
            array(
                'email' => $order->getCustomerEmail(),
                'name'  => $customerName
            )
        );

        return $this->_sendCommentEmail($configRmaEmail->getRootCommentEmail(), $sendTo, true);
    }

    /**
     * Sending email to admin with customer's comment data
     *
     * @return Enterprise_Rma_Model_Rma_Status_History
     */
    public function sendCustomerCommentEmail()
    {
        /** @var $configRmaEmail Enterprise_Rma_Model_Config */
        $configRmaEmail = Mage::getSingleton('enterprise_rma/config');
        $sendTo = array(
            array(
                'email' => $configRmaEmail->getCustomerEmailRecipient($this->getStoreId()),
                'name'  => null
            )
        );

        return $this->_sendCommentEmail($configRmaEmail->getRootCustomerCommentEmail(), $sendTo, false);
    }

    /**
     * Sending email to admin with customer's comment data
     *
     * @param string $rootConfig Current config root
     * @param array $sendTo mail recipient array
     * @param bool $isGuestAvailable
     * @return Enterprise_Rma_Model_Rma_Status_History
     */
    public function _sendCommentEmail($rootConfig, $sendTo, $isGuestAvailable = true)
    {
        /** @var $configRmaEmail Enterprise_Rma_Model_Config */
        $configRmaEmail = Mage::getSingleton('enterprise_rma/config');
        $configRmaEmail->init($rootConfig, $this->getStoreId());

        if (!$configRmaEmail->isEnabled()) {
            return $this;
        }

        $order = $this->getRma()->getOrder();
        $comment = $this->getComment();

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $copyTo = $configRmaEmail->getCopyTo();
        $copyMethod = $configRmaEmail->getCopyMethod();
        if ($copyTo && $copyMethod == 'bcc') {
            foreach ($copyTo as $email) {
                $mailTemplate->addBcc($email);
            }
        }

        if ($isGuestAvailable && $order->getCustomerIsGuest()) {
            $template = $configRmaEmail->getGuestTemplate();
        } else {
            $template = $configRmaEmail->getTemplate();
        }

        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $sendTo[] = array(
                    'email' => $email,
                    'name'  => null
                );
            }
        }

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))
                ->sendTransactional(
                    $template,
                    $configRmaEmail->getIdentity(),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'rma'       => $this->getRma(),
                        'order'     => $this->getRma()->getOrder(),
                        'comment'   => $comment
                    )
                );
        }
        $this->setEmailSent(true);
        $translate->setTranslateInline(true);

        return $this;
    }

    /**
     * Save system comment
     *
     * @return null
     */
    public function saveSystemComment()
    {
        $systemComments = array(
            Enterprise_Rma_Model_Rma_Source_Status::STATE_PENDING =>
                Mage::helper('enterprise_rma')->__('Your Return request has been placed.'),
            Enterprise_Rma_Model_Rma_Source_Status::STATE_AUTHORIZED =>
                Mage::helper('enterprise_rma')->__('Your Return request has been authorized.'),
            Enterprise_Rma_Model_Rma_Source_Status::STATE_PARTIAL_AUTHORIZED =>
                Mage::helper('enterprise_rma')->__('Your Return request has been partially authorized. '),
            Enterprise_Rma_Model_Rma_Source_Status::STATE_RECEIVED =>
                Mage::helper('enterprise_rma')->__('Your Return request has been received.'),
            Enterprise_Rma_Model_Rma_Source_Status::STATE_RECEIVED_ON_ITEM =>
                Mage::helper('enterprise_rma')->__('Your Return request has been partially received.'),
            Enterprise_Rma_Model_Rma_Source_Status::STATE_APPROVED_ON_ITEM =>
                Mage::helper('enterprise_rma')->__('Your Return request has been partially approved.'),
            Enterprise_Rma_Model_Rma_Source_Status::STATE_REJECTED_ON_ITEM =>
                Mage::helper('enterprise_rma')->__('Your Return request has been partially rejected.'),
            Enterprise_Rma_Model_Rma_Source_Status::STATE_CLOSED =>
                Mage::helper('enterprise_rma')->__('Your Return request has been closed.'),
            Enterprise_Rma_Model_Rma_Source_Status::STATE_PROCESSED_CLOSED =>
                Mage::helper('enterprise_rma')->__('Your Return request has been processed and closed.'),
        );

        $rma = $this->getRma();
        if (!($rma instanceof Enterprise_Rma_Model_Rma)) {
            return;
        }

        if (($rma->getStatus() !== $rma->getOrigData('status') && isset($systemComments[$rma->getStatus()]))) {
            $this->setRmaEntityId($rma->getEntityId())
                ->setComment($systemComments[$rma->getStatus()])
                ->setIsVisibleOnFront(true)
                ->setStatus($rma->getStatus())
                ->setCreatedAt(Mage::getSingleton('core/date')->gmtDate())
                ->setIsAdmin(1)
                ->save();
        }
    }
}
