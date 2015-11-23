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
 * @package     Enterprise_Reminder
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Reminder Rule data model
 *
 * @method Enterprise_Reminder_Model_Resource_Rule _getResource()
 * @method Enterprise_Reminder_Model_Resource_Rule getResource()
 * @method string getName()
 * @method Enterprise_Reminder_Model_Rule setName(string $value)
 * @method string getDescription()
 * @method Enterprise_Reminder_Model_Rule setDescription(string $value)
 * @method string getConditionsSerialized()
 * @method Enterprise_Reminder_Model_Rule setConditionsSerialized(string $value)
 * @method string getConditionSql()
 * @method Enterprise_Reminder_Model_Rule setConditionSql(string $value)
 * @method int getIsActive()
 * @method Enterprise_Reminder_Model_Rule setIsActive(int $value)
 * @method int getSalesruleId()
 * @method Enterprise_Reminder_Model_Rule setSalesruleId(int $value)
 * @method string getSchedule()
 * @method Enterprise_Reminder_Model_Rule setSchedule(string $value)
 * @method string getDefaultLabel()
 * @method Enterprise_Reminder_Model_Rule setDefaultLabel(string $value)
 * @method string getDefaultDescription()
 * @method Enterprise_Reminder_Model_Rule setDefaultDescription(string $value)
 * @method string getActiveFrom()
 * @method Enterprise_Reminder_Model_Rule setActiveFrom(string $value)
 * @method string getActiveTo()
 * @method Enterprise_Reminder_Model_Rule setActiveTo(string $value)
 *
 * @category    Enterprise
 * @package     Enterprise_Reminder
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reminder_Model_Rule extends Mage_Rule_Model_Abstract
{
    const XML_PATH_EMAIL_TEMPLATE  = 'enterprise_reminder_email_template';

    /**
     * Store template data defined per store view, will be used in email templates as variables
     */
    protected $_storeData = array();

    /**
     * @var Mage_Core_Model_Email_Template
     */
    protected $_mail;

    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_mail = isset($args['mail']) ? $args['mail'] : Mage::getModel('core/email_template');
        parent::__construct($args);
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('enterprise_reminder/rule');
    }

    /**
     * Set template, label and description data per store
     *
     * @return Enterprise_Reminder_Model_Rule
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $storeData = $this->_getResource()->getStoreData($this->getId());
        $defaultTemplate = self::XML_PATH_EMAIL_TEMPLATE;

        foreach ($storeData as $data) {
            $template = (empty($data['template_id'])) ? $defaultTemplate : $data['template_id'];
            $this->setData('store_template_' . $data['store_id'], $template);
            $this->setData('store_label_' . $data['store_id'], $data['label']);
            $this->setData('store_description_' . $data['store_id'], $data['description']);
        }

        return $this;
    }

    /**
     * Set aggregated conditions SQL and reset sales rule Id if applicable
     *
     * @return Enterprise_Reminder_Model_Rule
     */
    protected function _beforeSave()
    {
        $this->setConditionSql(
            $this->getConditions()->getConditionsSql(null, new Zend_Db_Expr(':website_id'))
        );

        if (!$this->getSalesruleId()) {
            $this->setSalesruleId(null);
        }

        parent::_beforeSave();
        return $this;
    }

    /**
     * Getter for rule combine conditions instance
     *
     * @return Enterprise_Reminder_Model_Rule_Condition_Combine
     */
    public function getConditionsInstance()
    {
        return Mage::getModel('enterprise_reminder/rule_condition_combine_root');
    }

    /**
     * Getter for rule actions collection instance
     *
     * @return Mage_Rule_Model_Action_Collection
     */
    public function getActionsInstance()
    {
        return Mage::getModel('rule/action_collection');
    }

    /**
     * Send reminder emails
     *
     * @return Enterprise_Reminder_Model_Rule
     */
    public function sendReminderEmails()
    {
        /* @var $translate Mage_Core_Model_Translate */
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $identity = Mage::helper('enterprise_reminder')->getEmailIdentity();

        $this->_matchCustomers();
        $limit = Mage::helper('enterprise_reminder')->getOneRunLimit();

        $recipients = $this->_getResource()->getCustomersForNotification($limit, $this->getRuleId());

        foreach ($recipients as $recipient) {
            /* @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')->load($recipient['customer_id']);
            if (!$customer || !$customer->getId()) {
                continue;
            }

            if ($customer->getStoreId()) {
                $store = $customer->getStore();
            } else {
                $store = Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore();
            }

            $storeData = $this->getStoreData($recipient['rule_id'], $store->getId());
            if (!$storeData) {
                continue;
            }

            /* @var $coupon Mage_SalesRule_Model_Coupon */
            $coupon = Mage::getModel('salesrule/coupon')->load($recipient['coupon_id']);

            $templateVars = array(
                'store'          => $store,
                'coupon'         => $coupon,
                'customer'       => $customer,
                'promotion_name' => $storeData['label'],
                'promotion_description' => $storeData['description']
            );

            $this->_mail->setDesignConfig(array('area' => 'frontend', 'store' => $store->getId()));
            $this->_mail->sendTransactional($storeData['template_id'], $identity,
                $customer->getEmail(), null, $templateVars, $store->getId()
            );

            if ($this->_mail->getSentSuccess()) {
                $this->_getResource()->addNotificationLog($recipient['rule_id'], $customer->getId());
            } else {
                $this->_getResource()->updateFailedEmailsCounter($recipient['rule_id'], $customer->getId());
            }
        }
        $translate->setTranslateInline(true);

        return $this;
    }

    /**
     * Match customers for current rule and assign coupons
     *
     * @return Enterprise_Reminder_Model_Observer
     */
    protected function _matchCustomers()
    {
        $threshold   = Mage::helper('enterprise_reminder')->getSendFailureThreshold();
        $currentDate = Mage::getSingleton('core/date')->date('Y-m-d');
        $rules       = $this->getCollection()->addDateFilter($currentDate)->addIsActiveFilter(1);

        if ($this->getRuleId()) {
            $rules->addRuleFilter($this->getRuleId());
        }

        foreach ($rules as $rule) {
            $this->_getResource()->deactivateMatchedCustomers($rule->getId());

            if ($rule->getSalesruleId()) {
                /* @var $salesRule Mage_SalesRule_Model_Rule */
                $salesRule = Mage::getSingleton('salesrule/rule')->load($rule->getSalesruleId());
                $websiteIds = array_intersect($rule->getWebsiteIds(), $salesRule->getWebsiteIds());
            } else {
                $salesRule = null;
                $websiteIds = $rule->getWebsiteIds();
            }

            foreach ($websiteIds as $websiteId) {
                $this->_getResource()->saveMatchedCustomers(clone $rule, $salesRule, $websiteId, $threshold);
            }
        }
        return $this;
    }

    /**
     * Retrieve store template data
     *
     * @param int $ruleId
     * @param int $storeId
     *
     * @return array|false
     */
    public function getStoreData($ruleId, $storeId)
    {
        if (!isset($this->_storeData[$ruleId][$storeId])) {
            if ($data = $this->_getResource()->getStoreTemplateData($ruleId, $storeId)) {
                if (empty($data['template_id'])) {
                    $data['template_id'] = self::XML_PATH_EMAIL_TEMPLATE;
                }
                $this->_storeData[$ruleId][$storeId] = $data;
            }
            else {
                return false;
            }
        }

        return $this->_storeData[$ruleId][$storeId];
    }

    /**
     * Detaches Sales Rule from all Email Remainder Rules that uses it
     *
     * @param int $salesRuleId
     * @return Enterprise_Reminder_Model_Rule
     */
    public function detachSalesRule($salesRuleId)
    {
        $this->getResource()->detachSalesRule($salesRuleId);
        return $this;
    }

    /**
     * Retrieve active from date.
     * Implemented for backwards compatibility with old property called "active_from"
     *
     * @return string
     */
    public function getActiveFrom()
    {
        return $this->getData('from_date');
    }

    /**
     * Retrieve active to date.
     * Implemented for backwards compatibility with old property called "active_to"
     *
     * @return string
     */
    public function getActiveTo()
    {
        return $this->getData('to_date');
    }
}
