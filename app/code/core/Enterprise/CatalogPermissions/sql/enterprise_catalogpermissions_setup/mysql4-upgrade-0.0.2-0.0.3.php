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

/* @var $installer Enterprise_CatalogPermissions_Model_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('enterprise_catalogpermissions/permission_index_product');

$installer->run("
    DROP TABLE IF EXISTS `{$tableName}`;
    CREATE TABLE `{$tableName}` (
        `product_id` INT(10) UNSIGNED NOT NULL,
        `store_id` SMALLINT(5) UNSIGNED NOT NULL,
        `category_id` INT(10) UNSIGNED DEFAULT NULL,
        `customer_group_id` SMALLINT(3) UNSIGNED NOT NULL,
        `grant_catalog_category_view` TINYINT(1) DEFAULT NULL,
        `grant_catalog_product_price` TINYINT(1) DEFAULT NULL,
        `grant_checkout_items` TINYINT(1) DEFAULT NULL,
        UNIQUE KEY `UNQ_INDEX_SCOPE` (`product_id`, `store_id`, `category_id`, `customer_group_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addConstraint('ENTERPRISE_CATALOGPEMISSIONS_INDEX_PRODUCT', $tableName, 'product_id',
                                           $installer->getTable('catalog/product'), 'entity_id');

$installer->getConnection()->addConstraint('ENTERPRISE_CATALOGPEMISSIONS_INDEX_PRODUCT_STORE', $tableName, 'store_id',
                                           $installer->getTable('core/store'), 'store_id');

$installer->getConnection()->addConstraint(
    'ENTERPRISE_CATALOGPEMISSIONS_INDEX_PRODUCT_CUSTGROUP',
    $tableName,
    'customer_group_id',
    $installer->getTable('customer/customer_group'),
    'customer_group_id'
);

$installer->getConnection()->addConstraint('ENTERPRISE_CATALOGPEMISSIONS_INDEX_PRODUCT_CAT', $tableName, 'category_id',
                                           $installer->getTable('catalog/category'), 'entity_id');
$installer->endSetup();
