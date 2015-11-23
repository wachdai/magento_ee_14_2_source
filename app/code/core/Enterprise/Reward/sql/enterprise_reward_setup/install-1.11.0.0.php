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
 * @package     Enterprise_Reward
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/** @var $installer Enterprise_Reward_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'enterprise_reward'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_reward/reward'))
    ->addColumn('reward_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Reward Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Customer Id')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Website Id')
    ->addColumn('points_balance', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Points Balance')
    ->addColumn('website_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Website Currency Code')
    ->addIndex($installer->getIdxName('enterprise_reward/reward', array('customer_id', 'website_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('customer_id', 'website_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('enterprise_reward/reward', array('website_id')),
        array('website_id'))
    ->addForeignKey($installer->getFkName('enterprise_reward/reward', 'customer_id', 'customer/entity', 'entity_id'),
        'customer_id', $installer->getTable('customer/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Reward');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_reward_history'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_reward/reward_history'))
    ->addColumn('history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'History Id')
    ->addColumn('reward_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Reward Id')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Website Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('action', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Action')
    ->addColumn('entity', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Entity')
    ->addColumn('points_balance', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Points Balance')
    ->addColumn('points_delta', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Points Delta')
    ->addColumn('points_used', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Points Used')
    ->addColumn('currency_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Currency Amount')
    ->addColumn('currency_delta', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Currency Delta')
    ->addColumn('base_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
        'nullable'  => false,
        ), 'Base Currency Code')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => false,
        ), 'Additional Data')
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => true,
        ), 'Comment')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('expired_at_static', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Expired At Static')
    ->addColumn('expired_at_dynamic', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Expired At Dynamic')
    ->addColumn('is_expired', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Expired')
    ->addColumn('is_duplicate_of', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Is Duplicate Of')
    ->addColumn('notification_sent', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Notification Sent')
    ->addIndex($installer->getIdxName('enterprise_reward/reward_history', array('reward_id')),
        array('reward_id'))
    ->addIndex($installer->getIdxName('enterprise_reward/reward_history', array('website_id')),
        array('website_id'))
    ->addIndex($installer->getIdxName('enterprise_reward/reward_history', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('enterprise_reward/reward_history', 'reward_id', 'enterprise_reward', 'reward_id'),
        'reward_id', $installer->getTable('enterprise_reward'), 'reward_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_reward/reward_history', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_reward/reward_history', 'website_id', 'core/website', 'website_id'),
        'website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Reward History');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_reward_rate'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_reward/reward_rate'))
    ->addColumn('rate_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rate Id')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Website Id')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Customer Group Id')
    ->addColumn('direction', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '1',
        ), 'Direction')
    ->addColumn('points', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Points')
    ->addColumn('currency_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Currency Amount')
    ->addIndex($installer->getIdxName('enterprise_reward/reward_rate', array('website_id', 'customer_group_id', 'direction'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('website_id', 'customer_group_id', 'direction'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('enterprise_reward/reward_rate', array('website_id')),
        array('website_id'))
    ->addIndex($installer->getIdxName('enterprise_reward/reward_rate', array('customer_group_id')),
        array('customer_group_id'))
    ->addForeignKey($installer->getFkName('enterprise_reward/reward_rate', 'website_id', 'core/website', 'website_id'),
        'website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Reward Rate');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_reward/reward_salesrule'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_reward/reward_salesrule'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Rule Id')
    ->addColumn('points_delta', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Points Delta')
    ->addIndex($installer->getIdxName('enterprise_reward/reward_salesrule', array('rule_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('rule_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey($installer->getFkName('enterprise_reward/reward_salesrule', 'rule_id', 'salesrule/rule', 'rule_id'),
        'rule_id', $installer->getTable('salesrule/rule'), 'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Reward Reward Salesrule');
$installer->getConnection()->createTable($table);


$installer->addAttribute('quote', 'use_reward_points',
    array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER)
);
$installer->addAttribute('quote', 'reward_points_balance',
    array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER)
);
$installer->addAttribute('quote', 'base_reward_currency_amount', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));
$installer->addAttribute('quote', 'reward_currency_amount', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));

$installer->addAttribute('quote_address', 'reward_points_balance',
    array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER)
);
$installer->addAttribute('quote_address', 'base_reward_currency_amount', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));
$installer->addAttribute('quote_address', 'reward_currency_amount', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));

$installer->addAttribute('order', 'reward_points_balance',
    array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER)
);
$installer->addAttribute('order', 'base_reward_currency_amount', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));
$installer->addAttribute('order', 'reward_currency_amount', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));
$installer->addAttribute('order', 'base_rwrd_crrncy_amt_invoiced', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));
$installer->addAttribute('order', 'rwrd_currency_amount_invoiced', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));
$installer->addAttribute('order', 'base_rwrd_crrncy_amnt_refnded', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));
$installer->addAttribute('order', 'rwrd_crrncy_amnt_refunded', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));

$installer->addAttribute('invoice', 'base_reward_currency_amount', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));
$installer->addAttribute('invoice', 'reward_currency_amount', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));

$installer->addAttribute('creditmemo', 'base_reward_currency_amount', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));
$installer->addAttribute('creditmemo', 'reward_currency_amount', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));

$installer->addAttribute('invoice', 'reward_points_balance',
    array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER)
);

$installer->addAttribute('creditmemo', 'reward_points_balance',
    array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER)
);

$installer->addAttribute('order', 'reward_points_balance_refund',
    array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER)
);
$installer->addAttribute('creditmemo', 'reward_points_balance_refund',
    array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER)
);

$installer->addAttribute('quote', 'base_reward_currency_amount', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));
$installer->addAttribute('quote', 'reward_currency_amount', array('type' => Varien_Db_Ddl_Table::TYPE_DECIMAL));

$installer->addAttribute('order', 'reward_points_balance_refunded',
    array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER)
);

$installer->addAttribute('order', 'reward_salesrule_points',
    array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER)
);

$installer->addAttribute('customer', 'reward_update_notification',
    array(
        'type' => 'int',
        'visible' => 0,
        'visible_on_front' => 1,
        'is_user_defined' => 0,
        'is_system' => 1,
        'is_hidden' => 1
    )
);

$installer->addAttribute('customer', 'reward_warning_notification',
    array(
        'type' => 'int',
        'visible' => 0,
        'visible_on_front' => 1,
        'is_user_defined' => 0,
        'is_system' => 1,
        'is_hidden' => 1
    )
);

$installer->endSetup();
