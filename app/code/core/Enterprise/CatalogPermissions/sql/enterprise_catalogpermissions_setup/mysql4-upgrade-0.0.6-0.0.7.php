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
$coreConfigTable = $installer->getTable('core_config_data');
$installer->run("
    UPDATE `{$coreConfigTable}` SET path='catalog/enterprise_catalogpermissions/enabled' WHERE path='catalog/general/enabled';
    UPDATE `{$coreConfigTable}` SET path='catalog/enterprise_catalogpermissions/grant_catalog_category_view' WHERE path='catalog/general/grant_catalog_category_view';
    UPDATE `{$coreConfigTable}` SET path='catalog/enterprise_catalogpermissions/grant_catalog_category_view_groups' WHERE path='catalog/general/grant_catalog_category_view_groups';
    UPDATE `{$coreConfigTable}` SET path='catalog/enterprise_catalogpermissions/grant_catalog_product_price' WHERE path='catalog/general/grant_catalog_product_price';
    UPDATE `{$coreConfigTable}` SET path='catalog/enterprise_catalogpermissions/grant_catalog_product_price_groups' WHERE path='catalog/general/grant_catalog_product_price_groups';
    UPDATE `{$coreConfigTable}` SET path='catalog/enterprise_catalogpermissions/grant_checkout_items' WHERE path='catalog/general/grant_checkout_items';
    UPDATE `{$coreConfigTable}` SET path='catalog/enterprise_catalogpermissions/grant_checkout_items_groups' WHERE path='catalog/general/grant_checkout_items_groups';
    UPDATE `{$coreConfigTable}` SET path='catalog/enterprise_catalogpermissions/restricted_landing_page' WHERE path='catalog/general/restricted_landing_page';
    UPDATE `{$coreConfigTable}` SET path='catalog/enterprise_catalogpermissions/deny_catalog_search' WHERE path='catalog/general/deny_catalog_search';
");

$installer->endSetup();
