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
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_catalogpermissions/permission'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_PERMISSION_CATEGORY'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_catalogpermissions/permission'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_PERMISSION_CUSTGROUP'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_catalogpermissions/permission'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_PERMISSION_WEBSITE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_catalogpermissions/permission_index'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_INDEX_CATEGORY'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_catalogpermissions/permission_index'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_INDEX_CUSTGROUP'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_catalogpermissions/permission_index'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_INDEX_WEBSITE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_INDEX_PRODUCT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_INDEX_PRODUCT_CAT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_INDEX_PRODUCT_CUSTGROUP'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_INDEX_PRODUCT_STORE'
);


/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_catalogpermissions/permission'),
    'UNQ_PERMISSION_SCOPE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_catalogpermissions/permission'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_PERMISSION_WEBSITE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_catalogpermissions/permission'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_PERMISSION_CUSTGROUP'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_catalogpermissions/permission_index'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_INDEX_CATEGORY'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_catalogpermissions/permission_index'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_INDEX_WEBSITE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_catalogpermissions/permission_index'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_INDEX_CUSTGROUP'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    'UNQ_PRODUCT_STORE_CATEGORY_AND_CUSTOMERGROUP'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_INDEX_PRODUCT_STORE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_INDEX_PRODUCT_CUSTGROUP'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    'FK_ENTERPRISE_CATALOGPEMISSIONS_INDEX_PRODUCT_CAT'
);


/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_catalogpermissions/permission') => array(
        'columns' => array(
            'permission_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Permission Id'
            ),
            'category_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Category Id'
            ),
            'website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Website Id'
            ),
            'customer_group_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Customer Group Id'
            ),
            'grant_catalog_category_view' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'comment'   => 'Grant Catalog Category View'
            ),
            'grant_catalog_product_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'comment'   => 'Grant Catalog Product Price'
            ),
            'grant_checkout_items' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'comment'   => 'Grant Checkout Items'
            )
        ),
        'comment' => 'Enterprise Catalogpermissions'
    ),
    $installer->getTable('enterprise_catalogpermissions/permission_index') => array(
        'columns' => array(
            'category_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Category Id'
            ),
            'website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Website Id'
            ),
            'customer_group_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Customer Group Id'
            ),
            'grant_catalog_category_view' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'comment'   => 'Grant Catalog Category View'
            ),
            'grant_catalog_product_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'comment'   => 'Grant Catalog Product Price'
            ),
            'grant_checkout_items' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'comment'   => 'Grant Checkout Items'
            )
        ),
        'comment' => 'Enterprise Catalogpermissions Index'
    ),
    $installer->getTable('enterprise_catalogpermissions/permission_index_product') => array(
        'columns' => array(
            'product_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Product Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Store Id'
            ),
            'category_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Category Id'
            ),
            'customer_group_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Customer Group Id'
            ),
            'grant_catalog_category_view' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'comment'   => 'Grant Catalog Category View'
            ),
            'grant_catalog_product_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'comment'   => 'Grant Catalog Product Price'
            ),
            'grant_checkout_items' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'comment'   => 'Grant Checkout Items'
            ),
            'is_config' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'default'   => '0',
                'comment'   => 'Is Config'
            )
        ),
        'comment' => 'Enterprise Catalogpermissions Index Product'
    )
);

$installer->getConnection()->modifyTables($tables);


/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_catalogpermissions/permission'),
    $installer->getIdxName(
        'enterprise_catalogpermissions/permission',
        array('category_id', 'website_id', 'customer_group_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('category_id', 'website_id', 'customer_group_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_catalogpermissions/permission'),
    $installer->getIdxName('enterprise_catalogpermissions/permission', array('website_id')),
    array('website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_catalogpermissions/permission'),
    $installer->getIdxName('enterprise_catalogpermissions/permission', array('customer_group_id')),
    array('customer_group_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_catalogpermissions/permission_index'),
    $installer->getIdxName('enterprise_catalogpermissions/permission_index', array('category_id')),
    array('category_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_catalogpermissions/permission_index'),
    $installer->getIdxName('enterprise_catalogpermissions/permission_index', array('website_id')),
    array('website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_catalogpermissions/permission_index'),
    $installer->getIdxName('enterprise_catalogpermissions/permission_index', array('customer_group_id')),
    array('customer_group_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    $installer->getIdxName(
        'enterprise_catalogpermissions/permission_index_product',
        array('product_id', 'store_id', 'category_id', 'customer_group_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('product_id', 'store_id', 'category_id', 'customer_group_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    $installer->getIdxName('enterprise_catalogpermissions/permission_index_product', array('store_id')),
    array('store_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    $installer->getIdxName('enterprise_catalogpermissions/permission_index_product', array('customer_group_id')),
    array('customer_group_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    $installer->getIdxName('enterprise_catalogpermissions/permission_index_product', array('category_id')),
    array('category_id')
);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_catalogpermissions/permission',
        'category_id',
        'catalog_category_entity',
        'entity_id'
    ),
    $installer->getTable('enterprise_catalogpermissions/permission'),
    'category_id',
    $installer->getTable('catalog_category_entity'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_catalogpermissions/permission',
        'customer_group_id',
        'customer/customer_group',
        'customer_group_id'
    ),
    $installer->getTable('enterprise_catalogpermissions/permission'),
    'customer_group_id',
    $installer->getTable('customer/customer_group'),
    'customer_group_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_catalogpermissions/permission',
        'website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_catalogpermissions/permission'),
    'website_id',
    $installer->getTable('core/website'),
    'website_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_catalogpermissions/permission_index',
        'customer_group_id',
        'customer/customer_group',
        'customer_group_id'
    ),
    $installer->getTable('enterprise_catalogpermissions/permission_index'),
    'customer_group_id',
    $installer->getTable('customer/customer_group'),
    'customer_group_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_catalogpermissions/permission_index',
        'category_id',
        'catalog/category',
        'entity_id'
    ),
    $installer->getTable('enterprise_catalogpermissions/permission_index'),
    'category_id',
    $installer->getTable('catalog/category'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_catalogpermissions/permission_index',
        'website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_catalogpermissions/permission_index'),
    'website_id',
    $installer->getTable('core/website'),
    'website_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_catalogpermissions/permission_index_product',
        'product_id',
        'catalog/product',
        'entity_id'
    ),
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    'product_id',
    $installer->getTable('catalog/product'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_catalogpermissions/permission_index_product',
        'category_id',
        'catalog/category',
        'entity_id'
    ),
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    'category_id',
    $installer->getTable('catalog/category'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_catalogpermissions/permission_index_product',
        'customer_group_id',
        'customer/customer_group',
        'customer_group_id'
    ),
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    'customer_group_id',
    $installer->getTable('customer/customer_group'),
    'customer_group_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_catalogpermissions/permission_index_product',
        'store_id',
        'core/store',
        'store_id'
    ),
    $installer->getTable('enterprise_catalogpermissions/permission_index_product'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$installer->endSetup();
