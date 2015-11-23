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
 * @package     Enterprise_Logging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/**
 * Create table 'enterprise_logging/event'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_logging/event'))
    ->addColumn('log_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Log Id')
    ->addColumn('ip', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Ip address')
    ->addColumn('x_forwarded_ip', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Real ip address if visitor used proxy')
    ->addColumn('event_code', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        ), 'Event Code')
    ->addColumn('time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Even date')
    ->addColumn('action', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        ), 'Event action')
    ->addColumn('info', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Additional information')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 15, array(
        ), 'Status')
    ->addColumn('user', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        ), 'User name')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'User Id')
    ->addColumn('fullaction', Varien_Db_Ddl_Table::TYPE_TEXT, 200, array(
        ), 'Full action description')
    ->addColumn('error_message', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Error Message')
    ->addIndex($installer->getIdxName('enterprise_logging/event', array('user_id')),
        array('user_id'))
    ->addIndex($installer->getIdxName('enterprise_logging/event', array('user')),
        array('user'))
    ->addForeignKey($installer->getFkName('enterprise_logging/event', 'user_id', 'admin/user', 'user_id'),
        'user_id', $installer->getTable('admin/user'), 'user_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Logging Event');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_logging/event_changes'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_logging/event_changes'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Enterprise logging id')
    ->addColumn('source_name', Varien_Db_Ddl_Table::TYPE_TEXT, 150, array(
        ), 'Logged Source Name')
    ->addColumn('event_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Logged event id')
    ->addColumn('source_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Logged Source Id')
    ->addColumn('original_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Logged Original Data')
    ->addColumn('result_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Logged Result Data')
    ->addIndex($installer->getIdxName('enterprise_logging/event_changes', array('event_id')),
        array('event_id'))
    ->addForeignKey($installer->getFkName('enterprise_logging/event_changes', 'event_id', 'enterprise_logging/event', 'log_id'),
        'event_id', $installer->getTable('enterprise_logging/event'), 'log_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Logging Event Changes');
$installer->getConnection()->createTable($table);
