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
 * @package     Enterprise_SalesArchive
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_SalesArchive_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
/**
 * Drop foreign keys
 */
$connection->dropForeignKey(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'FK_SALES_FLAT_CREDITMEMO_GRID_ARCHIVE_PARENT'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'FK_SALES_FLAT_CREDITMEMO_GRID_ARCHIVE_STORE'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    'FK_SALES_FLAT_INVOICE_GRID_ARCHIVE_PARENT'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    'FK_SALES_FLAT_INVOICE_GRID_ARCHIVE_STORE'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'FK_SALES_FLAT_ORDER_GRID_ARCHIVE_PARENT'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'FK_SALES_FLAT_ORDER_GRID_ARCHIVE_CUSTOMER'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'FK_SALES_FLAT_ORDER_GRID_ARCHIVE_STORE'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    'FK_SALES_FLAT_SHIPMENT_GRID_ARCHIVE_PARENT'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    'FK_SALES_FLAT_SHIPMENT_GRID_ARCHIVE_STORE'
);


/**
 * Drop indexes
 */
$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'IDX_STORE_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'IDX_GRAND_TOTAL'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'IDX_BASE_GRAND_TOTAL'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'IDX_ORDER_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'IDX_CREDITMEMO_STATUS'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'IDX_STATE'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'IDX_INCREMENT_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'IDX_ORDER_INCREMENT_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'IDX_CREATED_AT'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'IDX_ORDER_CREATED_AT'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'IDX_BILLING_NAME'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    'IDX_STORE_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    'IDX_GRAND_TOTAL'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    'IDX_ORDER_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    'IDX_STATE'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    'IDX_INCREMENT_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    'IDX_ORDER_INCREMENT_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    'IDX_CREATED_AT'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    'IDX_ORDER_CREATED_AT'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    'IDX_BILLING_NAME'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'IDX_STATUS'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'IDX_STORE_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'IDX_BASE_GRAND_TOTAL'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'IDX_BASE_TOTAL_PAID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'IDX_GRAND_TOTAL'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'IDX_TOTAL_PAID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'IDX_INCREMENT_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'IDX_SHIPPING_NAME'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'IDX_BILLING_NAME'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'IDX_CREATED_AT'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'IDX_CUSTOMER_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'IDX_UPDATED_AT'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    'IDX_STORE_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    'IDX_TOTAL_QTY'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    'IDX_ORDER_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    'IDX_SHIPMENT_STATUS'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    'IDX_INCREMENT_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    'IDX_ORDER_INCREMENT_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    'IDX_CREATED_AT'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    'IDX_ORDER_CREATED_AT'
);

$connection->dropIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    'IDX_SHIPPING_NAME'
);

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_salesarchive/order_grid') => array(
        'columns' => array(
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Entity Id'
            ),
            'status' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 32,
                'comment'   => 'Status'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Store Id'
            ),
            'store_name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Store Name'
            ),
            'customer_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Customer Id'
            ),
            'base_grand_total' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Grand Total'
            ),
            'base_total_paid' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Total Paid'
            ),
            'grand_total' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Grand Total'
            ),
            'total_paid' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Total Paid'
            ),
            'increment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'comment'   => 'Increment Id'
            ),
            'base_currency_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'comment'   => 'Base Currency Code'
            ),
            'order_currency_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Order Currency Code'
            ),
            'shipping_name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Shipping Name'
            ),
            'billing_name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Billing Name'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Created At'
            ),
            'updated_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Updated At'
            )
        ),
        'comment' => 'Enterprise Sales Order Grid Archive'
    ),
    $installer->getTable('enterprise_salesarchive/invoice_grid') => array(
        'columns' => array(
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Entity Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Store Id'
            ),
            'base_grand_total' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Grand Total'
            ),
            'grand_total' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Grand Total'
            ),
            'order_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Order Id'
            ),
            'state' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'State'
            ),
            'store_currency_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'comment'   => 'Store Currency Code'
            ),
            'order_currency_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'comment'   => 'Order Currency Code'
            ),
            'base_currency_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'comment'   => 'Base Currency Code'
            ),
            'global_currency_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'comment'   => 'Global Currency Code'
            ),
            'increment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'comment'   => 'Increment Id'
            ),
            'order_increment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'comment'   => 'Order Increment Id'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Created At'
            ),
            'order_created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Order Created At'
            ),
            'billing_name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Billing Name'
            )
        ),
        'comment' => 'Enterprise Sales Invoice Grid Archive'
    ),
    $installer->getTable('enterprise_salesarchive/creditmemo_grid') => array(
        'columns' => array(
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Entity Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Store Id'
            ),
            'store_to_order_rate' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Store To Order Rate'
            ),
            'base_to_order_rate' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base To Order Rate'
            ),
            'grand_total' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Grand Total'
            ),
            'store_to_base_rate' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Store To Base Rate'
            ),
            'base_to_global_rate' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base To Global Rate'
            ),
            'base_grand_total' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Grand Total'
            ),
            'order_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Order Id'
            ),
            'creditmemo_status' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Creditmemo Status'
            ),
            'state' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'State'
            ),
            'invoice_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Invoice Id'
            ),
            'store_currency_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'comment'   => 'Store Currency Code'
            ),
            'order_currency_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'comment'   => 'Order Currency Code'
            ),
            'base_currency_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'comment'   => 'Base Currency Code'
            ),
            'global_currency_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'comment'   => 'Global Currency Code'
            ),
            'increment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'comment'   => 'Increment Id'
            ),
            'order_increment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'comment'   => 'Order Increment Id'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Created At'
            ),
            'order_created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Order Created At'
            ),
            'billing_name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Billing Name'
            )
        ),
        'comment' => 'Enterprise Sales Creditmemo Grid Archive'
    ),
    $installer->getTable('enterprise_salesarchive/shipment_grid') => array(
        'columns' => array(
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Entity Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Store Id'
            ),
            'total_qty' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Total Qty'
            ),
            'order_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Order Id'
            ),
            'shipment_status' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Shipment Status'
            ),
            'increment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'comment'   => 'Increment Id'
            ),
            'order_increment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'comment'   => 'Order Increment Id'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Created At'
            ),
            'order_created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Order Created At'
            ),
            'shipping_name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Shipping Name'
            )
        ),
        'comment' => 'Enterprise Sales Shipment Grid Archive'
    )
);

$installer->getConnection()->modifyTables($tables);


/**
 * Add indexes
 */
$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    $installer->getIdxName(
        'enterprise_salesarchive/creditmemo_grid',
        array('increment_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('increment_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    $installer->getIdxName('enterprise_salesarchive/creditmemo_grid', array('store_id')),
    array('store_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    $installer->getIdxName('enterprise_salesarchive/creditmemo_grid', array('grand_total')),
    array('grand_total')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    $installer->getIdxName('enterprise_salesarchive/creditmemo_grid', array('base_grand_total')),
    array('base_grand_total')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    $installer->getIdxName('enterprise_salesarchive/creditmemo_grid', array('order_id')),
    array('order_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    $installer->getIdxName('enterprise_salesarchive/creditmemo_grid', array('creditmemo_status')),
    array('creditmemo_status')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    $installer->getIdxName('enterprise_salesarchive/creditmemo_grid', array('state')),
    array('state')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    $installer->getIdxName('enterprise_salesarchive/creditmemo_grid', array('order_increment_id')),
    array('order_increment_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    $installer->getIdxName('enterprise_salesarchive/creditmemo_grid', array('created_at')),
    array('created_at')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    $installer->getIdxName('enterprise_salesarchive/creditmemo_grid', array('order_created_at')),
    array('order_created_at')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    $installer->getIdxName('enterprise_salesarchive/creditmemo_grid', array('billing_name')),
    array('billing_name')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    $installer->getIdxName(
        'enterprise_salesarchive/invoice_grid',
        array('increment_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('increment_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    $installer->getIdxName('enterprise_salesarchive/invoice_grid', array('store_id')),
    array('store_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    $installer->getIdxName('enterprise_salesarchive/invoice_grid', array('grand_total')),
    array('grand_total')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    $installer->getIdxName('enterprise_salesarchive/invoice_grid', array('order_id')),
    array('order_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    $installer->getIdxName('enterprise_salesarchive/invoice_grid', array('state')),
    array('state')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    $installer->getIdxName('enterprise_salesarchive/invoice_grid', array('order_increment_id')),
    array('order_increment_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    $installer->getIdxName('enterprise_salesarchive/invoice_grid', array('created_at')),
    array('created_at')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    $installer->getIdxName('enterprise_salesarchive/invoice_grid', array('order_created_at')),
    array('order_created_at')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    $installer->getIdxName('enterprise_salesarchive/invoice_grid', array('billing_name')),
    array('billing_name')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    $installer->getIdxName(
        'enterprise_salesarchive/order_grid',
        array('increment_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('increment_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    $installer->getIdxName('enterprise_salesarchive/order_grid', array('status')),
    array('status')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    $installer->getIdxName('enterprise_salesarchive/order_grid', array('store_id')),
    array('store_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    $installer->getIdxName('enterprise_salesarchive/order_grid', array('base_grand_total')),
    array('base_grand_total')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    $installer->getIdxName('enterprise_salesarchive/order_grid', array('base_total_paid')),
    array('base_total_paid')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    $installer->getIdxName('enterprise_salesarchive/order_grid', array('grand_total')),
    array('grand_total')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    $installer->getIdxName('enterprise_salesarchive/order_grid', array('total_paid')),
    array('total_paid')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    $installer->getIdxName('enterprise_salesarchive/order_grid', array('shipping_name')),
    array('shipping_name')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    $installer->getIdxName('enterprise_salesarchive/order_grid', array('billing_name')),
    array('billing_name')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    $installer->getIdxName('enterprise_salesarchive/order_grid', array('created_at')),
    array('created_at')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    $installer->getIdxName('enterprise_salesarchive/order_grid', array('customer_id')),
    array('customer_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/order_grid'),
    $installer->getIdxName('enterprise_salesarchive/order_grid', array('updated_at')),
    array('updated_at')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    $installer->getIdxName(
        'enterprise_salesarchive/shipment_grid',
        array('increment_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('increment_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    $installer->getIdxName('enterprise_salesarchive/shipment_grid', array('store_id')),
    array('store_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    $installer->getIdxName('enterprise_salesarchive/shipment_grid', array('total_qty')),
    array('total_qty')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    $installer->getIdxName('enterprise_salesarchive/shipment_grid', array('order_id')),
    array('order_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    $installer->getIdxName('enterprise_salesarchive/shipment_grid', array('shipment_status')),
    array('shipment_status')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    $installer->getIdxName('enterprise_salesarchive/shipment_grid', array('order_increment_id')),
    array('order_increment_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    $installer->getIdxName('enterprise_salesarchive/shipment_grid', array('created_at')),
    array('created_at')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    $installer->getIdxName('enterprise_salesarchive/shipment_grid', array('order_created_at')),
    array('order_created_at')
);

$connection->addIndex(
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    $installer->getIdxName('enterprise_salesarchive/shipment_grid', array('shipping_name')),
    array('shipping_name')
);


/**
 * Add foreign keys
 */
$connection->addForeignKey(
    $installer->getFkName('enterprise_salesarchive/creditmemo_grid', 'store_id', 'core/store', 'store_id'),
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$connection->addForeignKey(
    $installer->getFkName('enterprise_salesarchive/creditmemo_grid', 'entity_id', 'sales/creditmemo', 'entity_id'),
    $installer->getTable('enterprise_salesarchive/creditmemo_grid'),
    'entity_id',
    $installer->getTable('sales/creditmemo'),
    'entity_id'
);

$connection->addForeignKey(
    $installer->getFkName('enterprise_salesarchive/invoice_grid', 'store_id', 'core/store', 'store_id'),
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$connection->addForeignKey(
    $installer->getFkName('enterprise_salesarchive/invoice_grid', 'entity_id', 'sales/invoice', 'entity_id'),
    $installer->getTable('enterprise_salesarchive/invoice_grid'),
    'entity_id',
    $installer->getTable('sales/invoice'),
    'entity_id'
);

$connection->addForeignKey(
    $installer->getFkName('enterprise_salesarchive/order_grid', 'store_id', 'core/store', 'store_id'),
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$connection->addForeignKey(
    $installer->getFkName('enterprise_salesarchive/order_grid', 'customer_id', 'customer/entity', 'entity_id'),
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'customer_id',
    $installer->getTable('customer/entity'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$connection->addForeignKey(
    $installer->getFkName('enterprise_salesarchive/order_grid', 'entity_id', 'sales/order', 'entity_id'),
    $installer->getTable('enterprise_salesarchive/order_grid'),
    'entity_id',
    $installer->getTable('sales/order'),
    'entity_id'
);

$connection->addForeignKey(
    $installer->getFkName('enterprise_salesarchive/shipment_grid', 'store_id', 'core/store', 'store_id'),
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$connection->addForeignKey(
    $installer->getFkName('enterprise_salesarchive/shipment_grid', 'entity_id', 'sales/shipment', 'entity_id'),
    $installer->getTable('enterprise_salesarchive/shipment_grid'),
    'entity_id',
    $installer->getTable('sales/shipment'),
    'entity_id'
);

$installer->endSetup();
