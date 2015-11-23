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
 * @package     Enterprise_CustomerBalance
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_CustomerBalance_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_customerbalance/balance'),
    'FK_CUSTOMERBALANCE_CUSTOMER'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_customerbalance/balance'),
    'FK_CUSTOMERBALANCE_WEBSITE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_customerbalance/balance_history'),
    'FK_CUSTOMERBALANCE_HISTORY_BALANCE'
);


/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_customerbalance/balance'),
    'UNQ_CUSTOMERBALANCE_CW'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_customerbalance/balance'),
    'FK_CUSTOMERBALANCE_WEBSITE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_customerbalance/balance_history'),
    'FK_CUSTOMERBALANCE_HISTORY_BALANCE'
);


/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_customerbalance/balance') => array(
        'columns' => array(
            'balance_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Balance Id'
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
            'amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Balance Amount'
            ),
            'base_currency_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'comment'   => 'Base Currency Code'
            )
        ),
        'comment' => 'Enterprise Customerbalance'
    ),
    $installer->getTable('enterprise_customerbalance/balance_history') => array(
        'columns' => array(
            'history_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'History Id'
            ),
            'balance_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Balance Id'
            ),
            'updated_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Updated At'
            ),
            'action' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Action'
            ),
            'balance_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Balance Amount'
            ),
            'balance_delta' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Balance Delta'
            ),
            'additional_info' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Additional Info'
            ),
            'is_customer_notified' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Is Customer Notified'
            )
        ),
        'comment' => 'Enterprise Customerbalance History'
    ),
    $installer->getTable('sales/creditmemo') => array(
        'columns' => array(
            'base_customer_balance_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Customer Balance Amount'
            ),
            'customer_balance_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Customer Balance Amount'
            )
        )
    ),
    $installer->getTable('sales/invoice') => array(
        'columns' => array(
            'base_customer_balance_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Customer Balance Amount'
            ),
            'customer_balance_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Customer Balance Amount'
            )
        )
    ),
    $installer->getTable('sales/order') => array(
        'columns' => array(
            'base_customer_balance_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Customer Balance Amount'
            ),
            'customer_balance_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Customer Balance Amount'
            ),
            'base_customer_balance_invoiced' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Customer Balance Invoiced'
            ),
            'customer_balance_invoiced' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Customer Balance Invoiced'
            ),
            'base_customer_balance_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Customer Balance Refunded'
            ),
            'customer_balance_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Customer Balance Refunded'
            )
        )
    ),
    $installer->getTable('sales/quote') => array(
        'columns' => array(
            'customer_balance_amount_used' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Customer Balance Amount Used'
            ),
            'use_customer_balance' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Use Customer Balance'
            )
        )
    ),
    $installer->getTable('sales/quote_address') => array(
        'columns' => array(
            'base_customer_balance_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Customer Balance Amount'
            ),
            'customer_balance_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Customer Balance Amount'
            )
        )
    )
);

$installer->getConnection()->modifyTables($tables);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/creditmemo'),
    'base_customer_balance_total_refunded',
    'bs_customer_bal_total_refunded',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Bs Customer Bal Total Refunded'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/creditmemo'),
    'customer_balance_total_refunded',
    'customer_bal_total_refunded',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Customer Bal Total Refunded'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'base_customer_balance_total_refunded',
    'bs_customer_bal_total_refunded',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Bs Customer Bal Total Refunded'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'customer_balance_total_refunded',
    'customer_bal_total_refunded',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Customer Bal Total Refunded'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/quote'),
    'base_customer_balance_amount_used',
    'base_customer_bal_amount_used',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Base Customer Bal Amount Used'
    )
);

/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_customerbalance/balance'),
    $installer->getIdxName(
        'enterprise_customerbalance/balance',
        array('customer_id', 'website_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('customer_id', 'website_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_customerbalance/balance'),
    $installer->getIdxName('enterprise_customerbalance/balance', array('website_id')),
    array('website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_customerbalance/balance_history'),
    $installer->getIdxName('enterprise_customerbalance/balance_history', array('balance_id')),
    array('balance_id')
);

/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_customerbalance/balance',
        'website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_customerbalance/balance'),
    'website_id',
    $installer->getTable('core/website'),
    'website_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_customerbalance/balance',
        'customer_id',
        'customer/entity',
        'entity_id'
    ),
    $installer->getTable('enterprise_customerbalance/balance'),
    'customer_id',
    $installer->getTable('customer/entity'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_customerbalance/balance_history',
        'balance_id',
        'enterprise_customerbalance/balance',
        'balance_id'
    ),
    $installer->getTable('enterprise_customerbalance/balance_history'),
    'balance_id',
    $installer->getTable('enterprise_customerbalance/balance'),
    'balance_id'
);

$installer->endSetup();
