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
 * @package     Enterprise_CatalogPermissions
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_CatalogPermissions_Model_Resource_Setup */
$installer = $this;


$installer->startSetup();

/**
 * Create table 'enterprise_catalogpermissions/permission'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_catalogpermissions/permission'))
    ->addColumn('permission_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Permission Id')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Category Id')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Website Id')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Customer Group Id')
    ->addColumn('grant_catalog_category_view', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        ), 'Grant Catalog Category View')
    ->addColumn('grant_catalog_product_price', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        ), 'Grant Catalog Product Price')
    ->addColumn('grant_checkout_items', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        ), 'Grant Checkout Items')
    ->addIndex($installer->getIdxName('enterprise_catalogpermissions/permission', array('category_id', 'website_id', 'customer_group_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('category_id', 'website_id', 'customer_group_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('enterprise_catalogpermissions/permission', array('website_id')),
        array('website_id'))
    ->addIndex($installer->getIdxName('enterprise_catalogpermissions/permission', array('customer_group_id')),
        array('customer_group_id'))
    ->addForeignKey($installer->getFkName('enterprise_catalogpermissions/permission', 'category_id', 'catalog/category', 'entity_id'),
        'category_id', $installer->getTable('catalog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_catalogpermissions/permission', 'customer_group_id', 'customer/customer_group', 'customer_group_id'),
        'customer_group_id', $installer->getTable('customer/customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_catalogpermissions/permission', 'website_id', 'core/website', 'website_id'),
        'website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Catalogpermissions');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_catalogpermissions/permission_index'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_catalogpermissions/permission_index'))
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Category Id')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Website Id')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Customer Group Id')
    ->addColumn('grant_catalog_category_view', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        ), 'Grant Catalog Category View')
    ->addColumn('grant_catalog_product_price', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        ), 'Grant Catalog Product Price')
    ->addColumn('grant_checkout_items', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        ), 'Grant Checkout Items')
    ->addIndex($installer->getIdxName('enterprise_catalogpermissions/permission_index', array('category_id')),
        array('category_id'))
    ->addIndex($installer->getIdxName('enterprise_catalogpermissions/permission_index', array('website_id')),
        array('website_id'))
    ->addIndex($installer->getIdxName('enterprise_catalogpermissions/permission_index', array('customer_group_id')),
        array('customer_group_id'))
    ->addForeignKey($installer->getFkName('enterprise_catalogpermissions/permission_index', 'customer_group_id', 'customer/customer_group', 'customer_group_id'),
        'customer_group_id', $installer->getTable('customer/customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_catalogpermissions/permission_index', 'category_id', 'catalog/category', 'entity_id'),
        'category_id', $installer->getTable('catalog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_catalogpermissions/permission_index', 'website_id', 'core/website', 'website_id'),
        'website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Catalogpermissions Index');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_catalogpermissions/permission_index_product'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_catalogpermissions/permission_index_product'))
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Product Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Store Id')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Category Id')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Customer Group Id')
    ->addColumn('grant_catalog_category_view', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        ), 'Grant Catalog Category View')
    ->addColumn('grant_catalog_product_price', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        ), 'Grant Catalog Product Price')
    ->addColumn('grant_checkout_items', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        ), 'Grant Checkout Items')
    ->addColumn('is_config', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Is Config')
    ->addIndex($installer->getIdxName('enterprise_catalogpermissions/permission_index_product', array('product_id', 'store_id', 'category_id', 'customer_group_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('product_id', 'store_id', 'category_id', 'customer_group_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('enterprise_catalogpermissions/permission_index_product', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('enterprise_catalogpermissions/permission_index_product', array('customer_group_id')),
        array('customer_group_id'))
    ->addIndex($installer->getIdxName('enterprise_catalogpermissions/permission_index_product', array('category_id')),
        array('category_id'))
    ->addForeignKey($installer->getFkName('enterprise_catalogpermissions/permission_index_product', 'product_id', 'catalog/product', 'entity_id'),
        'product_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_catalogpermissions/permission_index_product', 'category_id', 'catalog/category', 'entity_id'),
        'category_id', $installer->getTable('catalog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_catalogpermissions/permission_index_product', 'customer_group_id', 'customer/customer_group', 'customer_group_id'),
        'customer_group_id', $installer->getTable('customer/customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_catalogpermissions/permission_index_product', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Catalogpermissions Index Product');
$installer->getConnection()->createTable($table);

$installer->endSetup();
