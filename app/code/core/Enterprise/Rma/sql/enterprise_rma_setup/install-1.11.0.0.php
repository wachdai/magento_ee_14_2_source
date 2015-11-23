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
 * @package     Enterprise_Rma
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/** @var $installer Enterprise_Rma_Model_Resource_Setup */
$installer = $this;

/**
 * Prepare database before module installation
 */
$installer->startSetup();

/**
 * Create table 'enterprise_rma/rma'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_rma/rma'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'RMA Id')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Status')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        ), 'Is Active')
    ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Increment Id')
    ->addColumn('date_requested', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
        ), 'RMA Requested At')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Order Id')
    ->addColumn('order_increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Order Increment Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Customer Id')
    ->addColumn('customer_custom_email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Custom Email')
    ->addIndex($installer->getIdxName('enterprise_rma/rma', array('status')),
        array('status'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma', array('is_active')),
        array('is_active'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma', array('increment_id')),
        array('increment_id'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma', array('date_requested')),
        array('date_requested'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma', array('order_id')),
        array('order_id'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma', array('order_increment_id')),
        array('order_increment_id'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma', array('customer_id')),
        array('customer_id'))
    ->addForeignKey($installer->getFkName('enterprise_rma/rma', 'customer_id', 'customer/entity', 'entity_id'),
        'customer_id', $installer->getTable('customer/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_rma/rma', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('RMA LIst');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_rma/rma_grid'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_rma/rma_grid'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'RMA Id')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Status')
    ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Increment Id')
    ->addColumn('date_requested', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
        ), 'RMA Requested At')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Order Id')
    ->addColumn('order_increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Order Increment Id')
    ->addColumn('order_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Order Created At')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Customer Id')
    ->addColumn('customer_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Billing Name')
    ->addIndex($installer->getIdxName('enterprise_rma/rma_grid', array('status')),
        array('status'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma_grid', array('increment_id')),
        array('increment_id'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma_grid', array('date_requested')),
        array('date_requested'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma_grid', array('order_id')),
        array('order_id'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma_grid', array('order_increment_id')),
        array('order_increment_id'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma_grid', array('order_date')),
        array('order_date'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma_grid', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma_grid', array('customer_id')),
        array('customer_id'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma_grid', array('customer_name')),
        array('customer_name'))
    ->addForeignKey($installer->getFkName('enterprise_rma/rma_grid', 'entity_id', 'enterprise_rma/rma', 'entity_id'),
        'entity_id', $installer->getTable('enterprise_rma/rma'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('RMA Grid');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_rma/rma_status_history'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_rma/rma_status_history'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('rma_entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'RMA Entity Id')
    ->addColumn('is_customer_notified', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Is Customer Notified')
    ->addColumn('is_visible_on_front', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Visible On Front')
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Comment')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Status')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
        ), 'Created At')
    ->addColumn('is_admin', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        ), 'Is this Merchant Comment')
    ->addIndex($installer->getIdxName('enterprise_rma/rma_status_history', array('rma_entity_id')),
        array('rma_entity_id'))
    ->addIndex($installer->getIdxName('enterprise_rma/rma_status_history', array('created_at')),
        array('created_at'))
    ->addForeignKey(
        $installer->getFkName('enterprise_rma/rma_status_history', 'rma_entity_id', 'enterprise_rma/rma', 'entity_id'),
        'rma_entity_id',
        $installer->getTable('enterprise_rma/rma'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('RMA status history enterprise_rma_status_history');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_rma/item_entity'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_rma/item_entity'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Type Id')
    ->addColumn('attribute_set_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Attribute Set Id')
    ->addColumn('rma_entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'RMA entity id')
    ->addColumn('is_qty_decimal', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Qty Decimal')
    ->addColumn('qty_requested', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Qty of requested for RMA items')
    ->addColumn('qty_authorized', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Qty of authorized items')
    ->addColumn('qty_approved', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Qty of approved items')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Status')
    ->addColumn('order_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Product Order Item Id')
    ->addColumn('product_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Product Name')
    ->addColumn('product_sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Product Sku')
    ->addIndex($installer->getIdxName('enterprise_rma/item_entity', array('entity_type_id')),
        array('entity_type_id'))
    ->addForeignKey(
        $installer->getFkName('enterprise_rma/item_entity', 'rma_entity_id', 'enterprise_rma/rma', 'entity_id'),
        'rma_entity_id',
        $installer->getTable('enterprise_rma/rma'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('RMA Item Entity');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_rma/item_eav_attribute'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_rma/item_eav_attribute'))
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity'  => false,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Attribute Id')
    ->addColumn('is_visible', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        ), 'Is Visible')
    ->addColumn('input_filter', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Input Filter')
    ->addColumn('multiline_count', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        ), 'Multiline Count')
    ->addColumn('validate_rules', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Validate Rules')
    ->addColumn('is_system', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is System')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Sort Order')
    ->addColumn('data_model', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Data Model')
    ->addForeignKey(
        $installer->getFkName('enterprise_rma/item_eav_attribute', 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('RMA Item EAV Attribute');
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_entity_datetime'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_rma_item_entity_datetime'))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Value Id')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Type Id')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Attribute Id')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Id')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false,
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            'enterprise_rma_item_entity_datetime',
            array('entity_id', 'attribute_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('entity_id', 'attribute_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_datetime', array('entity_type_id')),
        array('entity_type_id'))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_datetime', array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_datetime', array('entity_id')),
        array('entity_id'))
    ->addIndex(
        $installer->getIdxName('enterprise_rma_item_entity_datetime', array('entity_id', 'attribute_id', 'value')),
        array('entity_id', 'attribute_id', 'value'))
    ->addForeignKey(
        $installer->getFkName('enterprise_rma_item_entity_datetime', 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_rma_item_entity_datetime',
            'entity_id',
            'enterprise_rma/item_entity',
            'entity_id'),
        'entity_id', $installer->getTable('enterprise_rma/item_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_rma_item_entity_datetime',
            'entity_type_id',
            'eav/entity_type',
            'entity_type_id'),
        'entity_type_id', $installer->getTable('eav/entity_type'), 'entity_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('RMA Item Entity Datetime');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_rma_item_entity_decimal'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_rma_item_entity_decimal'))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Value Id')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Type Id')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Attribute Id')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Id')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            'enterprise_rma_item_entity_decimal',
            array('entity_id', 'attribute_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('entity_id', 'attribute_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_decimal', array('entity_type_id')),
        array('entity_type_id'))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_decimal', array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_decimal', array('entity_id')),
        array('entity_id'))
    ->addIndex(
        $installer->getIdxName('enterprise_rma_item_entity_decimal', array('entity_id', 'attribute_id', 'value')),
        array('entity_id', 'attribute_id', 'value'))
    ->addForeignKey(
        $installer->getFkName('enterprise_rma_item_entity_decimal', 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_rma_item_entity_decimal',
            'entity_id',
            'enterprise_rma/item_entity',
            'entity_id'),
        'entity_id', $installer->getTable('enterprise_rma/item_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_rma_item_entity_decimal',
            'entity_type_id',
            'eav/entity_type',
            'entity_type_id'),
        'entity_type_id', $installer->getTable('eav/entity_type'), 'entity_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('RMA Item Entity Decimal');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_rma_item_entity_int'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_rma_item_entity_int'))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Value Id')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Type Id')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Attribute Id')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Id')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            'enterprise_rma_item_entity_int',
            array('entity_id', 'attribute_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('entity_id', 'attribute_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_int', array('entity_type_id')),
        array('entity_type_id'))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_int', array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_int', array('entity_id')),
        array('entity_id'))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_int', array('entity_id', 'attribute_id', 'value')),
        array('entity_id', 'attribute_id', 'value'))
    ->addForeignKey(
        $installer->getFkName('enterprise_rma_item_entity_int', 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('enterprise_rma_item_entity_int', 'entity_id', 'enterprise_rma/item_entity', 'entity_id'),
        'entity_id', $installer->getTable('enterprise_rma/item_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('enterprise_rma_item_entity_int', 'entity_type_id', 'eav/entity_type', 'entity_type_id'),
        'entity_type_id', $installer->getTable('eav/entity_type'), 'entity_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('RMA Item Entity Int');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_rma_item_entity_text'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_rma_item_entity_text'))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Value Id')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Type Id')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Attribute Id')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Id')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => false,
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            'enterprise_rma_item_entity_text',
            array('entity_id', 'attribute_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('entity_id', 'attribute_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_text', array('entity_type_id')),
        array('entity_type_id'))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_text', array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_text', array('entity_id')),
        array('entity_id'))
    ->addForeignKey(
        $installer->getFkName('enterprise_rma_item_entity_text', 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_rma_item_entity_text',
            'entity_id',
            'enterprise_rma/item_entity',
            'entity_id'),
        'entity_id', $installer->getTable('enterprise_rma/item_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('enterprise_rma_item_entity_text', 'entity_type_id', 'eav/entity_type', 'entity_type_id'),
        'entity_type_id', $installer->getTable('eav/entity_type'), 'entity_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('RMA Item Entity Text');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_rma_item_entity_varchar'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_rma_item_entity_varchar'))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Value Id')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Type Id')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Attribute Id')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Id')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            'enterprise_rma_item_entity_varchar',
            array('entity_id', 'attribute_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('entity_id', 'attribute_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_varchar', array('entity_type_id')),
        array('entity_type_id'))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_varchar', array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName('enterprise_rma_item_entity_varchar', array('entity_id')),
        array('entity_id'))
    ->addIndex(
        $installer->getIdxName('enterprise_rma_item_entity_varchar', array('entity_id', 'attribute_id', 'value')),
        array('entity_id', 'attribute_id', 'value'))
    ->addForeignKey(
        $installer->getFkName('enterprise_rma_item_entity_varchar', 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_rma_item_entity_varchar',
            'entity_id',
            'enterprise_rma/item_entity',
            'entity_id'),
        'entity_id', $installer->getTable('enterprise_rma/item_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_rma_item_entity_varchar',
            'entity_type_id',
            'eav/entity_type',
            'entity_type_id'),
        'entity_type_id', $installer->getTable('eav/entity_type'), 'entity_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('RMA Item Entity Varchar');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_rma/item_form_attribute'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_rma/item_form_attribute'))
    ->addColumn('form_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => false,
        'primary'   => true,
        ), 'Form Code')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Attribute Id')
    ->addIndex($installer->getIdxName('enterprise_rma/item_form_attribute', array('attribute_id')),
        array('attribute_id'))
    ->addForeignKey(
        $installer->getFkName('enterprise_rma/item_form_attribute', 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav/attribute'),
        'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('RMA Item Form Attribute');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_rma/item_eav_attribute_website'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_rma/item_eav_attribute_website'))
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Attribute Id')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Website Id')
    ->addColumn('is_visible', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Is Visible')
    ->addColumn('is_required', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Is Required')
    ->addColumn('default_value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Default Value')
    ->addColumn('multiline_count', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Multiline Count')
    ->addIndex($installer->getIdxName('enterprise_rma/item_eav_attribute_website', array('website_id')),
        array('website_id'))
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_rma/item_eav_attribute_website',
            'attribute_id',
            'eav/attribute',
            'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('enterprise_rma/item_eav_attribute_website', 'website_id', 'core/website', 'website_id'),
        'website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise RMA Item Eav Attribute Website');
$installer->getConnection()->createTable($table);

$installer->endSetup();

$installer->installEntities();
$installer->installForms();

//Add Product's Attribute
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');

/**
 * Prepare database before module installation
 */
$installer->startSetup();

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'is_returnable', array(
    'group'             => 'General',
    'type'              => 'int',
    'backend'           => 'enterprise_rma/product_backend',
    'frontend'          => '',
    'label'             => 'Enable RMA',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '1',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          =>
        Mage_Catalog_Model_Product_Type::TYPE_SIMPLE . ',' .
        Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE . ',' .
        Mage_Catalog_Model_Product_Type::TYPE_GROUPED . ',' .
        Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
    'is_configurable'   => false,
    'input_renderer'    => 'enterprise_rma/adminhtml_product_renderer',
));

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'use_config_is_returnable', array(
    'group'             => 'General',
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Use Config Enable RMA',
    'input'             => 'text',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible'           => false,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '1',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          =>
        Mage_Catalog_Model_Product_Type::TYPE_SIMPLE . ',' .
        Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE . ',' .
        Mage_Catalog_Model_Product_Type::TYPE_GROUPED . ',' .
        Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
    'is_configurable'   => false
));

$installer->endSetup();
