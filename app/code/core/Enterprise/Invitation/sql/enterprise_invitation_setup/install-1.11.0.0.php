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

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'enterprise_invitation/invitation'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_invitation/invitation'))
    ->addColumn('invitation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Invitation Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Customer Id')
    ->addColumn('invitation_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Invitation Date')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Email')
    ->addColumn('referral_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Referral Id')
    ->addColumn('protection_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Protection Code')
    ->addColumn('signup_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Signup Date')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Store Id')
    ->addColumn('group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Group Id')
    ->addColumn('message', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Message')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 8, array(
        'nullable'  => false,
        'default'   => 'new',
        ), 'Status')
    ->addIndex($installer->getIdxName('enterprise_invitation/invitation', array('customer_id')),
        array('customer_id'))
    ->addIndex($installer->getIdxName('enterprise_invitation/invitation', array('referral_id')),
        array('referral_id'))
    ->addIndex($installer->getIdxName('enterprise_invitation/invitation', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('enterprise_invitation/invitation', array('group_id')),
        array('group_id'))
    ->addForeignKey($installer->getFkName('enterprise_invitation/invitation', 'group_id', 'customer/customer_group', 'customer_group_id'),
        'group_id', $installer->getTable('customer/customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_invitation/invitation', 'customer_id', 'customer/entity', 'entity_id'),
        'customer_id', $installer->getTable('customer/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_invitation/invitation', 'referral_id', 'customer/entity', 'entity_id'),
        'referral_id', $installer->getTable('customer/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_invitation/invitation', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Invitation');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_invitation/invitation_history'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_invitation/invitation_history'))
    ->addColumn('history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'History Id')
    ->addColumn('invitation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Invitation Id')
    ->addColumn('invitation_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Invitation Date')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 8, array(
        'nullable'  => false,
        'default'   => 'new',
        ), 'Invitation Status')
    ->addIndex($installer->getIdxName('enterprise_invitation/invitation_history', array('invitation_id')),
        array('invitation_id'))
    ->addForeignKey($installer->getFkName('enterprise_invitation/invitation_history', 'invitation_id', 'enterprise_invitation/invitation', 'invitation_id'),
        'invitation_id', $installer->getTable('enterprise_invitation/invitation'), 'invitation_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Invitation Status History');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_invitation/invitation_track'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_invitation/invitation_track'))
    ->addColumn('track_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Track Id')
    ->addColumn('inviter_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Inviter Id')
    ->addColumn('referral_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Referral Id')
    ->addIndex($installer->getIdxName('enterprise_invitation/invitation_track', array('inviter_id', 'referral_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('inviter_id', 'referral_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('enterprise_invitation/invitation_track', array('referral_id')),
        array('referral_id'))
    ->addForeignKey($installer->getFkName('enterprise_invitation/invitation_track', 'inviter_id', 'customer/entity', 'entity_id'),
        'inviter_id', $installer->getTable('customer/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_invitation/invitation_track', 'referral_id', 'customer/entity', 'entity_id'),
        'referral_id', $installer->getTable('customer/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Invitation Track');
$installer->getConnection()->createTable($table);

$installer->endSetup();
