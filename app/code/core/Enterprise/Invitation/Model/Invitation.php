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
 * @method Enterprise_Invitation_Model_Resource_Invitation _getResource()
 * @method Enterprise_Invitation_Model_Resource_Invitation getResource()
 * @method int getCustomerId()
 * @method Enterprise_Invitation_Model_Invitation setCustomerId(int $value)
 * @method string getDate()
 * @method Enterprise_Invitation_Model_Invitation setDate(string $value)
 * @method string getEmail()
 * @method Enterprise_Invitation_Model_Invitation setEmail(string $value)
 * @method int getReferralId()
 * @method Enterprise_Invitation_Model_Invitation setReferralId(int $value)
 * @method string getProtectionCode()
 * @method Enterprise_Invitation_Model_Invitation setProtectionCode(string $value)
 * @method string getSignupDate()
 * @method Enterprise_Invitation_Model_Invitation setSignupDate(string $value)
 * @method Enterprise_Invitation_Model_Invitation setStoreId(int $value)
 * @method int getGroupId()
 * @method Enterprise_Invitation_Model_Invitation setGroupId(int $value)
 * @method string getMessage()
 * @method Enterprise_Invitation_Model_Invitation setMessage(string $value)
 * @method string getStatus()
 * @method Enterprise_Invitation_Model_Invitation setStatus(string $value)
 *
 * @category    Enterprise
 * @package     Enterprise_Invitation
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Invitation_Model_Invitation extends Mage_Core_Model_Abstract
{
    const STATUS_NEW      = 'new';
    const STATUS_SENT     = 'sent';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_CANCELED = 'canceled';

    const XML_PATH_EMAIL_IDENTITY = 'enterprise_invitation/email/identity';
    const XML_PATH_EMAIL_TEMPLATE = 'enterprise_invitation/email/template';

    const ERROR_STATUS          = 1;
    const ERROR_INVALID_DATA    = 2;
    const ERROR_CUSTOMER_EXISTS = 3;

    private static $_customerExistsLookup = array();

    protected $_eventPrefix = 'enterprise_invitation';
    protected $_eventObject = 'invitation';

    /**
     * Mapping old field names
     * @var array
     */
    protected $_oldFieldsMap = array('invitation_date' => 'date');

    /**
     * Intialize resource
     */
    protected function _construct()
    {
        $this->_init('enterprise_invitation/invitation');
    }

    /**
     * Store ID getter
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->_getData('store_id');
        }
        return Mage::app()->getStore()->getId();
    }

    /**
     * Load invitation by an encrypted code
     *
     * @param string $code
     * @return Enterprise_Invitation_Model_Invitation
     * @throws Mage_Core_Exception
     */
    public function loadByInvitationCode($code)
    {
        $code = explode(':', $code, 2);
        if (count($code) != 2) {
            Mage::throwException(Mage::helper('enterprise_invitation')->__('Invalid invitation code.'));
        }
        list($id, $protectionCode) = $code;
        $this->load($id);
        if (!$this->getId() || $this->getProtectionCode() != $protectionCode) {
            Mage::throwException(Mage::helper('enterprise_invitation')->__('Invalid invitation code.'));
        }
        return $this;
    }

    /**
     * Model before save
     *
     * @return Enterprise_Invitation_Model_Invitation
     */
    protected function _beforeSave()
    {
        if (!$this->getId()) {
            // set initial data for new one
            $this->addData(array(
                'protection_code' => Mage::helper('core')->uniqHash(),
                'status'          => self::STATUS_NEW,
                'invitation_date' => $this->getResource()->formatDate(time()),
                'store_id'        => $this->getStoreId(),
            ));
            $inviter = $this->getInviter();
            if ($inviter) {
                $this->setCustomerId($inviter->getId());
            }
            if (Mage::getSingleton('enterprise_invitation/config')->getUseInviterGroup()) {
                if ($inviter) {
                    $this->setGroupId($inviter->getGroupId());
                }
                if (!$this->hasGroupId()) {
                    throw new Mage_Core_Exception(
                        Mage::helper('enterprise_invitation')->__('No customer group id specified.'),
                        self::ERROR_INVALID_DATA
                    );
                }
            }
            else {
                $this->unsetData('group_id');
            }

            if (!(int)$this->getStoreId()) {
                throw new Mage_Core_Exception(
                    Mage::helper('enterprise_invitation')->__('Wrong store specified.'),
                    self::ERROR_INVALID_DATA
                );
            }
            $this->makeSureCustomerNotExists();
        }
        else {
            if ($this->dataHasChangedFor('message') && !$this->canMessageBeUpdated()) {
                throw new Mage_Core_Exception(
                    Mage::helper('enterprise_invitation')->__('Message cannot be updated.'),
                    self::ERROR_STATUS
                );
            }
        }
        return parent::_beforeSave();
    }

    /**
     * Update status history after save
     *
     * @return Enterprise_Invitation_Model_Invitation
     */
    protected function _afterSave()
    {
        Mage::getModel('enterprise_invitation/invitation_history')
            ->setInvitationId($this->getId())->setStatus($this->getStatus())
            ->save();
        $parent = parent::_afterSave();
        if ($this->getStatus() === self::STATUS_NEW) {
            $this->setOrigData();
        }
        return $parent;
    }

    /**
     * Send invitation email
     *
     * @return bool
     */
    public function sendInvitationEmail()
    {
        $this->makeSureCanBeSent();
        $store = Mage::app()->getStore($this->getStoreId());
        $mail  = Mage::getModel('core/email_template');
        $mail->setDesignConfig(array('area'=>'frontend', 'store' => $this->getStoreId()))
            ->sendTransactional(
                $store->getConfig(self::XML_PATH_EMAIL_TEMPLATE), $store->getConfig(self::XML_PATH_EMAIL_IDENTITY),
                $this->getEmail(), null, array(
                    'url'           => Mage::helper('enterprise_invitation')->getInvitationUrl($this),
                    'allow_message' => Mage::app()->getStore()->isAdmin()
                        || Mage::getSingleton('enterprise_invitation/config')->isInvitationMessageAllowed(),
                    'message'       => $this->getMessage(),
                    'store'         => $store,
                    'store_name'    => $store->getGroup()->getName(), // @deprecated after 1.4.0.0-beta1
                    'inviter_name'  => ($this->getInviter() ? $this->getInviter()->getName() : null)
            ));
        if ($mail->getSentSuccess()) {
            $this->setStatus(self::STATUS_SENT)->setUpdateDate(true)->save();
            return true;
        }
        return false;
    }

    /**
     * Get an encrypted invitation code
     *
     * @return string
     */
    public function getInvitationCode()
    {
        if (!$this->getId()) {
            Mage::throwException(Mage::helper('enterprise_invitation')->__('Unable to generate encrypted code.'));
        }
        return $this->getId() . ':' . $this->getProtectionCode();
    }

    /**
     * Check and get customer if it was set
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getInviter()
    {
        $inviter = $this->getCustomer();
        if (!$inviter || !$inviter->getId()) {
            $inviter = null;
        }
        return $inviter;
    }

    /**
     * Check whether invitation can be sent
     *
     * @throws Mage_Core_Exception
     */
    public function makeSureCanBeSent()
    {
        if (!$this->getId()) {
            throw new Mage_Core_Exception(
                Mage::helper('enterprise_invitation')->__('Invitation has no ID.'),
                self::ERROR_INVALID_DATA
            );
        }
        if ($this->getStatus() !== self::STATUS_NEW) {
            throw new Mage_Core_Exception(
                Mage::helper('enterprise_invitation')->__('Invitation with status "%s" cannot be sent.', $this->getStatus()),
                self::ERROR_STATUS
            );
        }
        if (!$this->getEmail() || !Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            throw new Mage_Core_Exception(
                Mage::helper('enterprise_invitation')->__('Invalid or empty invitation email.'),
                self::ERROR_INVALID_DATA
            );
        }
        $this->makeSureCustomerNotExists();
    }

    /**
     * Check whether customer with specified email exists
     *
     * @param string $email
     * @param string $websiteId
     * @throws Mage_Core_Exception
     */
    public function makeSureCustomerNotExists($email = null, $websiteId = null)
    {
        if (null === $websiteId) {
            $websiteId = Mage::app()->getStore($this->getStoreId())->getWebsiteId();
        }
        if (!$websiteId) {
            throw new Mage_Core_Exception(
                Mage::helper('enterprise_invitation')->__('Unable to determine proper website.'),
                self::ERROR_INVALID_DATA
            );
        }
        if (null === $email) {
            $email = $this->getEmail();
        }
        if (!$email) {
            throw new Mage_Core_Exception(
                Mage::helper('enterprise_invitation')->__('Email is not specified.'),
                self::ERROR_INVALID_DATA
            );
        }

        // lookup customer by specified email/website id
        if (!isset(self::$_customerExistsLookup[$email]) || !isset(self::$_customerExistsLookup[$email][$websiteId])) {
            $customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail($email);
            self::$_customerExistsLookup[$email][$websiteId] = ($customer->getId() ? $customer->getId() : false);
        }
        if (false === self::$_customerExistsLookup[$email][$websiteId]) {
            return;
        }
        throw new Mage_Core_Exception(
            Mage::helper('enterprise_invitation')->__('Customer with email "%s" already exists.', $email),
            self::ERROR_CUSTOMER_EXISTS
        );
    }

    /**
     * Check whether this invitation can be accepted
     *
     * @param int|string $websiteId
     * @throws Mage_Core_Exception
     */
    public function makeSureCanBeAccepted($websiteId = null)
    {
        $messageInvalid = Mage::helper('enterprise_invitation')->__('This invitation is not valid.');
        if (!$this->getId()) {
            throw new Mage_Core_Exception($messageInvalid, self::ERROR_STATUS);
        }
        if (!in_array($this->getStatus(), array(self::STATUS_NEW, self::STATUS_SENT))) {
            throw new Mage_Core_Exception($messageInvalid, self::ERROR_STATUS);
        }
        if (null === $websiteId) {
            $websiteId = Mage::app()->getWebsite()->getId();
        }
        if ($websiteId != Mage::app()->getStore($this->getStoreId())->getWebsiteId()) {
            throw new Mage_Core_Exception($messageInvalid, self::ERROR_STATUS);
        }
    }

    /**
     * Check whether message can be updated
     *
     * @return bool
     */
    public function canMessageBeUpdated()
    {
        return (bool)(int)$this->getId() && $this->getStatus() === self::STATUS_NEW;
    }

    /**
     * Check whether invitation can be cancelled
     *
     * @return bool
     */
    public function canBeCanceled()
    {
        return (bool)(int)$this->getId()
            && !in_array($this->getStatus(), array(self::STATUS_CANCELED, self::STATUS_ACCEPTED));
    }

    /**
     * Check whether invitation can be sent. Will throw exception on invalid data.
     *
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function canBeSent()
    {
        try {
            $this->makeSureCanBeSent();
            return true;
        }
        catch (Mage_Core_Exception $e) {
            if ($e->getCode() && $e->getCode() === self::ERROR_INVALID_DATA) {
                throw $e;
            }
        }
        return false;
    }

    /**
     * Cancel the invitation
     *
     * @return Enterprise_Invitation_Model_Invitation
     */
    public function cancel()
    {
        if ($this->canBeCanceled()) {
            $this->setStatus(self::STATUS_CANCELED)->save();
        }
        return $this;
    }

    /**
     * Accept the invitation
     *
     * @param int|string $websiteId
     * @param int $referralId
     * @return Enterprise_Invitation_Model_Invitation
     */
    public function accept($websiteId, $referralId)
    {
        $this->makeSureCanBeAccepted($websiteId);
        $this->setReferralId($referralId)
            ->setStatus(self::STATUS_ACCEPTED)
            ->setSignupDate($this->getResource()->formatDate(time()))
            ->save();
        if ($inviterId = $this->getCustomerId()) {
            $this->getResource()->trackReferral($inviterId, $referralId);
        }
        return $this;
    }

    /**
     * Check whether invitation can be accepted
     *
     * @param int $websiteId
     * @return bool
     */
    public function canBeAccepted($websiteId = null)
    {
        try {
            $this->makeSureCanBeAccepted($websiteId);
            return true;
        }
        catch (Mage_Core_Exception $e) {
            // intentionally jammed
        }
        return false;
    }

    /**
     * Validating invitation's parameters
     *
     * Returns true or array of errors
     *
     * @return mixed
     */
    public function validate()
    {
        $errors = array();

        if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = Mage::helper('enterprise_invitation')->__("Invalid invitation email.");
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

}
