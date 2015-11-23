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
 * @package     Enterprise_Customer
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_Customer_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_customer/sales_order'),
    'FK_ENTERPRISE_CUSTOMER_SALES_ORDER'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_customer/sales_order_address'),
    'FK_ENTERPRISE_CUSTOMER_SALES_ORDER_ADDRESS'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_customer/sales_quote'),
    'FK_ENTERPRISE_CUSTOMER_SALES_QUOTE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_customer/sales_quote_address'),
    'FK_ENTERPRISE_CUSTOMER_SALES_QUOTE_ADDRESS'
);

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_customer/sales_order') => array(
        'columns' => array(
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
                'comment'   => 'Entity Id'
            )
        ),
        'comment' => 'Enterprise Customer Sales Flat Order'
    ),
    $installer->getTable('enterprise_customer/sales_order_address') => array(
        'columns' => array(
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
                'comment'   => 'Entity Id'
            )
        ),
        'comment' => 'Enterprise Customer Sales Flat Order Address'
    ),
    $installer->getTable('enterprise_customer/sales_quote') => array(
        'columns' => array(
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
                'comment'   => 'Entity Id'
            )
        ),
        'comment' => 'Enterprise Customer Sales Flat Quote'
    ),
    $installer->getTable('enterprise_customer/sales_quote_address') => array(
        'columns' => array(
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
                'comment'   => 'Entity Id'
            )
        ),
        'comment' => 'Enterprise Customer Sales Flat Quote Address'
    )
);

$installer->getConnection()->modifyTables($tables);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_customer/sales_order',
        'entity_id',
        'sales/order',
        'entity_id'
    ),
    $installer->getTable('enterprise_customer/sales_order'),
    'entity_id',
    $installer->getTable('sales/order'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_customer/sales_order_address',
        'entity_id',
        'sales/order_address',
        'entity_id'
    ),
    $installer->getTable('enterprise_customer/sales_order_address'),
    'entity_id',
    $installer->getTable('sales/order_address'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_customer/sales_quote',
        'entity_id',
        'sales/quote',
        'entity_id'
    ),
    $installer->getTable('enterprise_customer/sales_quote'),
    'entity_id',
    $installer->getTable('sales/quote'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_customer/sales_quote_address',
        'entity_id',
        'sales/quote_address',
        'address_id'
    ),
    $installer->getTable('enterprise_customer/sales_quote_address'),
    'entity_id',
    $installer->getTable('sales/quote_address'),
    'address_id'
);

$installer->endSetup();
