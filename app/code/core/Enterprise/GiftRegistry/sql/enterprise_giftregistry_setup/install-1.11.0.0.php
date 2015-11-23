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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/** @var $installer Enterprise_GiftRegistry_Model_Resource_Setup */
$installer = $this;

/**
 * Prepare database before module installation
 */
$installer->startSetup();

/**
 * Create table 'enterprise_giftregistry/type'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftregistry/type'))
    ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Type Id')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 15, array(
        'nullable'  => true,
        ), 'Code')
    ->addColumn('meta_xml', Varien_Db_Ddl_Table::TYPE_BLOB, '64K', array(
        ), 'Meta Xml')
    ->setComment('Enterprise Gift Registry Type Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_giftregistry/info'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftregistry/info'))
    ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Type Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Store Id')
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Label')
    ->addColumn('is_listed', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Is Listed')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Sort Order')
    ->addIndex($installer->getIdxName('enterprise_giftregistry/info', array('store_id')),
        array('store_id'))
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_giftregistry/info',
            'type_id',
            'enterprise_giftregistry/type',
            'type_id'
        ),
        'type_id', $installer->getTable('enterprise_giftregistry/type'), 'type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_giftregistry/info', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Gift Registry Info Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_giftregistry/label'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftregistry/label'))
    ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Type Id')
    ->addColumn('attribute_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'primary'   => true,
        'nullable'  => false,
        ), 'Attribute Code')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Store Id')
    ->addColumn('option_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'primary'   => true,
        'nullable'  => false,
        ), 'Option Code')
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Label')
    ->addIndex($installer->getIdxName('enterprise_giftregistry/label', array('type_id')),
        array('type_id'))
    ->addIndex($installer->getIdxName('enterprise_giftregistry/label', array('store_id')),
        array('store_id'))
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_giftregistry/label',
            'type_id',
            'enterprise_giftregistry/type',
            'type_id'
        ),
        'type_id', $installer->getTable('enterprise_giftregistry/type'), 'type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_giftregistry/label', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Gift Registry Label Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_giftregistry/entity'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftregistry/entity'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Type Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Customer Id')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Website Id')
    ->addColumn('is_public', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        ), 'Is Public')
    ->addColumn('url_key', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        ), 'Url Key')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        ), 'Title')
    ->addColumn('message', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => false,
        ), 'Message')
    ->addColumn('shipping_address', Varien_Db_Ddl_Table::TYPE_BLOB, '64K', array(
        ), 'Shipping Address')
    ->addColumn('custom_values', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Custom Values')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Active')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addIndex($installer->getIdxName('enterprise_giftregistry/entity', array('customer_id')),
        array('customer_id'))
    ->addIndex($installer->getIdxName('enterprise_giftregistry/entity', array('website_id')),
        array('website_id'))
    ->addIndex($installer->getIdxName('enterprise_giftregistry/entity', array('type_id')),
        array('type_id'))
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_giftregistry/entity',
            'type_id',
            'enterprise_giftregistry/type',
            'type_id'
        ),
        'type_id', $installer->getTable('enterprise_giftregistry/type'), 'type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_giftregistry/entity',
            'customer_id',
            'customer/entity',
            'entity_id'
        ),
        'customer_id', $installer->getTable('customer/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_giftregistry/entity',
            'website_id',
            'core/website',
            'website_id'
        ),
        'website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Gift Registry Entity Table');

$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_giftregistry/item'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftregistry/item'))
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Item Id')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Product Id')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Qty')
    ->addColumn('qty_fulfilled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Qty Fulfilled')
    ->addColumn('note', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Note')
    ->addColumn('added_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Added At')
    ->addColumn('custom_options', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Custom Options')
    ->addIndex($installer->getIdxName('enterprise_giftregistry/item', array('entity_id')),
        array('entity_id'))
    ->addIndex($installer->getIdxName('enterprise_giftregistry/item', array('product_id')),
        array('product_id'))
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_giftregistry/item',
            'entity_id',
            'enterprise_giftregistry/entity',
            'entity_id'
        ),
        'entity_id', $installer->getTable('enterprise_giftregistry/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_giftregistry/item',
            'product_id',
            'catalog/product',
            'entity_id'
        ),
        'product_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Gift Registry Item Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_giftregistry/person'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftregistry/person'))
    ->addColumn('person_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Person Id')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Id')
    ->addColumn('firstname', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable'  => true,
        ), 'Firstname')
    ->addColumn('lastname', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable'  => true,
        ), 'Lastname')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 150, array(
        'nullable'  => true,
        ), 'Email')
    ->addColumn('role', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => true,
        ), 'Role')
    ->addColumn('custom_values', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => false,
        ), 'Custom Values')
    ->addIndex($installer->getIdxName('enterprise_giftregistry/person', array('entity_id')),
        array('entity_id'))
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_giftregistry/person',
            'entity_id',
            'enterprise_giftregistry/entity',
            'entity_id'
        ),
        'entity_id', $installer->getTable('enterprise_giftregistry/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Gift Registry Person Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_giftregistry/data'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftregistry/data'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Entity Id')
    ->addColumn('event_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Event Date')
    ->addColumn('event_country', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Event Country')
    ->addColumn('event_country_region', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Event Country Region')
    ->addColumn('event_country_region_text', Varien_Db_Ddl_Table::TYPE_TEXT, 30, array(
        ), 'Event Country Region Text')
    ->addColumn('event_location', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Event Location')
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_giftregistry/data',
            'entity_id',
            'enterprise_giftregistry/entity',
            'entity_id'
        ),
        'entity_id', $installer->getTable('enterprise_giftregistry/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Gift Registry Data Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_giftregistry/item_option'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftregistry/item_option'))
    ->addColumn('option_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Option Id')
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Item Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Product Id')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Code')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => false,
        ), 'Value')
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_giftregistry/item_option',
            'item_id',
            'enterprise_giftregistry/item',
            'item_id'
        ),
        'item_id', $installer->getTable('enterprise_giftregistry/item'), 'item_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Gift Registry Item Option Table');
$installer->getConnection()->createTable($table);

/**
 * Add attributes
 */
$installer->addAttribute(
    'quote_item', 'giftregistry_item_id', array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER, 'visible' => false)
);
$installer->addAttribute(
    'order_item', 'giftregistry_item_id', array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER, 'visible' => false)
);
$installer->addAttribute(
    'quote_address', 'giftregistry_item_id', array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER, 'visible' => false)
);
$installer->addAttribute(
    'order_address', 'giftregistry_item_id', array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER, 'visible' => false)
);

/**
 * Prepare database after module installation
 */
$installer->endSetup();
