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

$tableName = $installer->getTable('enterprise_catalogpermissions/permission');

$installer->run("
    CREATE TABLE `{$tableName}` (
        `permission_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `category_id` INT(10) UNSIGNED NOT NULL,
        `website_id` SMALLINT(5) UNSIGNED NOT NULL,
        `customer_group_id` SMALLINT(3) UNSIGNED NOT NULL,
        `grant_catalog_category_view` TINYINT(1) NOT NULL,
        `grant_catalog_product_price` TINYINT(1) NOT NULL,
        `grant_checkout_items` TINYINT(1) NOT NULL,
        PRIMARY KEY (`permission_id`),
        UNIQUE KEY `UNQ_PERMISSION_SCOPE` (`category_id`, `website_id`, `customer_group_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addConstraint('ENTERPRISE_CATALOGPEMISSIONS_PERMISSION_CATEGORY', $tableName, 'category_id',
                                           $installer->getTable('catalog/category'), 'entity_id');

$installer->getConnection()->addConstraint('ENTERPRISE_CATALOGPEMISSIONS_PERMISSION_WEBSITE', $tableName, 'website_id',
                                           $installer->getTable('core/website'), 'website_id');

$installer->getConnection()->addConstraint('ENTERPRISE_CATALOGPEMISSIONS_PERMISSION_CUSTGROUP', $tableName, 'customer_group_id',
                                           $installer->getTable('customer/customer_group'), 'customer_group_id');

$installer->endSetup();
