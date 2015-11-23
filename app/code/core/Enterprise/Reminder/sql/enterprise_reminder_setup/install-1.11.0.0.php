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

$installer = $this;
/** @var $installer Enterprise_Reminder_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'enterprise_reminder/rule'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_reminder/rule'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rule Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
        ), 'Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Description')
    ->addColumn('conditions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        'nullable'  => false,
        ), 'Conditions Serialized')
    ->addColumn('condition_sql', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'Condition Sql')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Active')
    ->addColumn('salesrule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Salesrule Id')
    ->addColumn('schedule', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Schedule')
    ->addColumn('default_label', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Default Label')
    ->addColumn('default_description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Default Description')
    ->addColumn('active_from', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Active From')
    ->addColumn('active_to', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Active To')
    ->addIndex($installer->getIdxName('enterprise_reminder/rule', array('salesrule_id')),
        array('salesrule_id'))
    ->addForeignKey($installer->getFkName('enterprise_reminder/rule', 'salesrule_id', 'salesrule/rule', 'rule_id'),
        'salesrule_id', $installer->getTable('salesrule/rule'), 'rule_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Reminder Rule');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_reminder/website'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_reminder/website'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rule Id')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Website Id')
    ->addIndex($installer->getIdxName('enterprise_reminder/website', array('website_id')),
        array('website_id'))
    ->addForeignKey(
        $installer->getFkName('enterprise_reminder/website', 'rule_id', 'enterprise_reminder/rule', 'rule_id'),
        'rule_id', $installer->getTable('enterprise_reminder/rule'), 'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Reminder Rule Website');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_reminder/template'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_reminder/template'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rule Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'primary'   => true,
        ), 'Store Id')
    ->addColumn('template_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Template Id')
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Label')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Description')
    ->addIndex($installer->getIdxName('enterprise_reminder/template', array('rule_id')),
        array('rule_id'))
    ->addIndex($installer->getIdxName('enterprise_reminder/template', array('template_id')),
        array('template_id'))
    ->addForeignKey(
        $installer->getFkName('enterprise_reminder/template', 'template_id', 'core/email_template', 'template_id'),
        'template_id', $installer->getTable('core/email_template'), 'template_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('enterprise_reminder/template', 'rule_id', 'enterprise_reminder/rule', 'rule_id'),
        'rule_id', $installer->getTable('enterprise_reminder/rule'), 'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Reminder Template');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_reminder/coupon'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_reminder/coupon'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rule Id')
    ->addColumn('coupon_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Coupon Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Id')
    ->addColumn('associated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Associated At')
    ->addColumn('emails_failed', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Emails Failed')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        ), 'Is Active')
    ->addIndex($installer->getIdxName('enterprise_reminder/coupon', array('rule_id')),
        array('rule_id'))
    ->addForeignKey(
        $installer->getFkName('enterprise_reminder/coupon', 'rule_id', 'enterprise_reminder/rule', 'rule_id'),
        'rule_id', $installer->getTable('enterprise_reminder/rule'), 'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Reminder Rule Coupon');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_reminder/log'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_reminder/log'))
    ->addColumn('log_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Log Id')
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Rule Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Customer Id')
    ->addColumn('sent_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Sent At')
    ->addIndex($installer->getIdxName('enterprise_reminder/log', array('rule_id')),
        array('rule_id'))
    ->addIndex($installer->getIdxName('enterprise_reminder/log', array('customer_id')),
        array('customer_id'))
    ->addForeignKey($installer->getFkName('enterprise_reminder/log', 'rule_id', 'enterprise_reminder/rule', 'rule_id'),
        'rule_id', $installer->getTable('enterprise_reminder/rule'), 'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Reminder Rule Log');
$installer->getConnection()->createTable($table);


$installer->endSetup();
