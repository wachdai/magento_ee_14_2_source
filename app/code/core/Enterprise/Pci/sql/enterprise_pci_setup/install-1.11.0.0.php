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
 * @package     Enterprise_Pci
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_Pci_Model_Resource_Setup */

$installer = $this;
$installer->startSetup();

/**
 * Create table 'enterprise_pci/admin_passwords'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_pci/admin_passwords'))
    ->addColumn('password_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Password Id')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'User Id')
    ->addColumn('password_hash', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        ), 'Password Hash')
    ->addColumn('expires', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Expires')
    ->addColumn('last_updated', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Last Updated')
    ->addIndex($installer->getIdxName('enterprise_pci/admin_passwords', array('user_id')),
        array('user_id'))
    ->addForeignKey($installer->getFkName('enterprise_pci/admin_passwords', 'user_id', 'admin/user', 'user_id'),
        'user_id', $installer->getTable('admin/user'), 'user_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Admin Passwords');
$installer->getConnection()->createTable($table);

$tableAdmins     = $installer->getTable('admin/user');
$tableApiUsers   = $installer->getTable('api/user');

$installer->getConnection()->changeColumn($tableAdmins, 'password', 'password', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 100,
    'comment'   => 'User Password'
));

$installer->getConnection()->changeColumn($tableApiUsers, 'api_key', 'api_key', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 100,
    'comment'   => 'Api key'
));

$installer->getConnection()->addColumn($tableAdmins, 'failures_num', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'nullable'  => true,
    'default'   => 0,
    'comment'   => 'Failure Number'
));

$installer->getConnection()->addColumn($tableAdmins, 'first_failure', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'comment'   => 'First Failure'
));

$installer->getConnection()->addColumn($tableAdmins, 'lock_expires', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'comment'   => 'Expiration Lock Dates'
));

$installer->endSetup();
