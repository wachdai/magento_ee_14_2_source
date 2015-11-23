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

/* @var $installer Enterprise_Reward_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_reward/reward'),
    'FK_REWARD_CUSTOMER_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_reward/reward_history'),
    'FK_REWARD_HISTORY_REWARD_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_reward/reward_history'),
    'FK_REWARD_HISTORY_STORE_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_reward/reward_history'),
    'FK_REWARD_HISTORY_WEBSITE_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_reward/reward_rate'),
    'FK_REWARD_RATE_WEBSITE_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_reward/reward_salesrule'),
    'FK_REWARD_SALESRULE_RULE_ID'
);


/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reward/reward'),
    'UNQ_CUSTOMER_WEBSITE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reward/reward'),
    'FK_REWARD_WEBSITE_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reward/reward_history'),
    'IDX_REWARD_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reward/reward_history'),
    'IDX_WEBSITE_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reward/reward_history'),
    'IDX_STORE_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reward/reward_rate'),
    'IDX_WEBSITE_GROUP_DIRECTION'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reward/reward_rate'),
    'IDX_WEBSITE_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reward/reward_rate'),
    'IDX_CUSTOMER_GROUP_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reward/reward_salesrule'),
    'FK_REWARD_SALESRULE_RULE_ID'
);


/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_reward/reward') => array(
        'columns' => array(
            'reward_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Reward Id'
            ),
            'customer_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Customer Id'
            ),
            'website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Website Id'
            ),
            'points_balance' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Points Balance'
            ),
            'website_currency_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'comment'   => 'Website Currency Code'
            )
        ),
        'comment' => 'Enterprise Reward'
    ),
    $installer->getTable('enterprise_reward/reward_history') => array(
        'columns' => array(
            'history_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'History Id'
            ),
            'reward_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Reward Id'
            ),
            'website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Website Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Store Id'
            ),
            'action' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Action'
            ),
            'entity' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Entity'
            ),
            'points_balance' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Points Balance'
            ),
            'points_delta' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Points Delta'
            ),
            'points_used' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Points Used'
            ),
            'currency_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Currency Amount'
            ),
            'currency_delta' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Currency Delta'
            ),
            'base_currency_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'nullable'  => false,
                'comment'   => 'Base Currency Code'
            ),
            'additional_data' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'nullable'  => false,
                'comment'   => 'Additional Data'
            ),
            'comment' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Comment'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'nullable'  => false,
                'comment'   => 'Created At'
            ),
            'expired_at_static' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Expired At Static'
            ),
            'expired_at_dynamic' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Expired At Dynamic'
            ),
            'is_expired' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Is Expired'
            ),
            'is_duplicate_of' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Is Duplicate Of'
            ),
            'notification_sent' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Notification Sent'
            )
        ),
        'comment' => 'Enterprise Reward History'
    ),
    $installer->getTable('enterprise_reward/reward_rate') => array(
        'columns' => array(
            'rate_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Rate Id'
            ),
            'website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Website Id'
            ),
            'customer_group_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Customer Group Id'
            ),
            'direction' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '1',
                'comment'   => 'Direction'
            ),
            'points' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Points'
            ),
            'currency_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Currency Amount'
            )
        ),
        'comment' => 'Enterprise Reward Rate'
    ),
    $installer->getTable('enterprise_reward/reward_salesrule') => array(
        'columns' => array(
            'rule_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
                'comment'   => 'Rule Id'
            ),
            'points_delta' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Points Delta'
            )
        ),
        'comment' => 'Enterprise Reward Reward Salesrule'
    ),
    $installer->getTable('sales/creditmemo') => array(
        'columns' => array(
            'base_reward_currency_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Reward Currency Amount'
            ),
            'reward_currency_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Reward Currency Amount'
            ),
            'reward_points_balance' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Reward Points Balance'
            )
        )
    ),
    $installer->getTable('sales/invoice') => array(
        'columns' => array(
            'base_reward_currency_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Reward Currency Amount'
            ),
            'reward_currency_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Reward Currency Amount'
            ),
            'reward_points_balance' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Reward Points Balance'
            )
        )
    ),
    $installer->getTable('sales/order') => array(
        'columns' => array(
            'reward_points_balance' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Reward Points Balance'
            ),
            'reward_salesrule_points' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Reward Salesrule Points'
            ),
            'base_reward_currency_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Reward Currency Amount'
            ),
            'reward_currency_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Reward Currency Amount'
            ),
            'reward_points_balance_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Reward Points Balance Refunded'
            )
        )
    ),
    $installer->getTable('sales/quote') => array(
        'columns' => array(
            'use_reward_points' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Use Reward Points'
            ),
            'reward_points_balance' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Reward Points Balance'
            ),
            'base_reward_currency_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Reward Currency Amount'
            ),
            'reward_currency_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Reward Currency Amount'
            )
        )
    ),
    $installer->getTable('sales/quote_address') => array(
        'columns' => array(
            'reward_points_balance' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Reward Points Balance'
            ),
            'base_reward_currency_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Reward Currency Amount'
            ),
            'reward_currency_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Reward Currency Amount'
            )
        )
    )
);

$installer->getConnection()->modifyTables($tables);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/creditmemo'),
    'reward_points_balance_to_refund',
    'reward_points_balance_refund',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment'   => 'Reward Points Balance Refund'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'base_reward_currency_amount_invoiced',
    'base_rwrd_crrncy_amt_invoiced',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Base Rwrd Crrncy Amt Invoiced'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'reward_currency_amount_invoiced',
    'rwrd_currency_amount_invoiced',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Rwrd Currency Amount Invoiced'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'base_reward_currency_amount_refunded',
    'base_rwrd_crrncy_amnt_refnded',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Base Rwrd Crrncy Amnt Refnded'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'reward_currency_amount_refunded',
    'rwrd_crrncy_amnt_refunded',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Rwrd Crrncy Amnt Refunded'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'reward_points_balance_to_refund',
    'reward_points_balance_refund',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment'   => 'Reward Points Balance Refund'
    )
);


/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reward/reward'),
    $installer->getIdxName(
        'enterprise_reward/reward',
        array('customer_id', 'website_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('customer_id', 'website_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reward/reward'),
    $installer->getIdxName('enterprise_reward/reward', array('website_id')),
    array('website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reward/reward_history'),
    $installer->getIdxName('enterprise_reward/reward_history', array('reward_id')),
    array('reward_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reward/reward_history'),
    $installer->getIdxName('enterprise_reward/reward_history', array('website_id')),
    array('website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reward/reward_history'),
    $installer->getIdxName('enterprise_reward/reward_history', array('store_id')),
    array('store_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reward/reward_rate'),
    $installer->getIdxName(
        'enterprise_reward/reward_rate',
        array('website_id', 'customer_group_id', 'direction'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('website_id', 'customer_group_id', 'direction'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reward/reward_rate'),
    $installer->getIdxName('enterprise_reward/reward_rate', array('website_id')),
    array('website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reward/reward_rate'),
    $installer->getIdxName('enterprise_reward/reward_rate', array('customer_group_id')),
    array('customer_group_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reward/reward_salesrule'),
    $installer->getIdxName(
        'enterprise_reward/reward_salesrule',
        array('rule_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('rule_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_reward/reward',
        'customer_id',
        'customer/entity',
        'entity_id'
    ),
    $installer->getTable('enterprise_reward/reward'),
    'customer_id',
    $installer->getTable('customer/entity'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_reward/reward_history',
        'reward_id',
        'enterprise_reward/reward',
        'reward_id'
    ),
    $installer->getTable('enterprise_reward/reward_history'),
    'reward_id',
    $installer->getTable('enterprise_reward/reward'),
    'reward_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_reward/reward_history',
        'store_id',
        'core/store',
        'store_id'
    ),
    $installer->getTable('enterprise_reward/reward_history'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_reward/reward_history',
        'website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_reward/reward_history'),
    'website_id',
    $installer->getTable('core/website'),
    'website_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_reward/reward_rate',
        'website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_reward/reward_rate'),
    'website_id',
    $installer->getTable('core/website'),
    'website_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_reward/reward_salesrule',
        'rule_id',
        'salesrule/rule',
        'rule_id'
    ),
    $installer->getTable('enterprise_reward/reward_salesrule'),
    'rule_id',
    $installer->getTable('salesrule/rule'),
    'rule_id'
);

$installer->endSetup();
