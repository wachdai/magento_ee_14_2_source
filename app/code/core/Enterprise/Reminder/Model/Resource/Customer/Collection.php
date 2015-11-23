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
 * Resource collection of customers matched by reminder rule
 *
 * @category    Enterprise
 * @package     Enterprise_Reminder
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reminder_Model_Resource_Customer_Collection extends Mage_Customer_Model_Resource_Customer_Collection
{
    /**
     * Use analytic function flag
     * If true - allows to prepare final select with analytic functions
     *
     * @var bool
     */
    protected $_useAnalyticFunction = true;

    /**
     * Instantiate select to get matched customers
     *
     * @return Enterprise_Reminder_Model_Resource_Customer_Collection
     */
    protected function _initSelect()
    {
        $rule = Mage::registry('current_reminder_rule');
        $select = $this->getSelect();

        $customerTable = $this->getTable('customer/entity');
        $couponTable = $this->getTable('enterprise_reminder/coupon');
        $logTable = $this->getTable('enterprise_reminder/log');
        $salesRuleCouponTable = $this->getTable('salesrule/coupon');

        $select->from(array('c' => $couponTable), array('associated_at', 'emails_failed', 'is_active'));
        $select->where('c.rule_id = ?', $rule->getId());

        $select->joinInner(
            array('e' => $customerTable),
            'e.entity_id = c.customer_id',
            array('entity_id', 'email')
        );

        $subSelect = $this->getConnection()->select();
        $subSelect->from(array('g' => $logTable), array(
            'customer_id',
            'rule_id',
            'emails_sent' => new Zend_Db_Expr('COUNT(log_id)'),
            'last_sent' => new Zend_Db_Expr('MAX(sent_at)')
        ));

        $subSelect->where('rule_id = ?', $rule->getId());
        $subSelect->group(array('customer_id', 'rule_id'));

        $select->joinLeft(
            array('l' => $subSelect),
            'l.rule_id = c.rule_id AND l.customer_id = c.customer_id',
            array('l.emails_sent', 'l.last_sent')
        );

        $select->joinLeft(
            array('sc' => $salesRuleCouponTable),
            'sc.coupon_id = c.coupon_id',
            array('code', 'usage_limit', 'usage_per_customer')
        );

        $this->_joinFields['associated_at'] = array('table' => 'c', 'field' => 'associated_at');
        $this->_joinFields['emails_failed'] = array('table' => 'c', 'field' => 'emails_failed');
        $this->_joinFields['is_active']     = array('table' => 'c', 'field' => 'is_active');
        $this->_joinFields['code']          = array('table' => 'sc', 'field' => 'code');
        $this->_joinFields['usage_limit']   = array('table' => 'sc', 'field' => 'usage_limit');
        $this->_joinFields['usage_per_customer'] = array('table' => 'sc', 'field' => 'usage_per_customer');
        $this->_joinFields['emails_sent']   = array('table' => 'l', 'field' => 'emails_sent');
        $this->_joinFields['last_sent']     = array('table' => 'l', 'field' => 'last_sent');

        return $this;
    }
}
