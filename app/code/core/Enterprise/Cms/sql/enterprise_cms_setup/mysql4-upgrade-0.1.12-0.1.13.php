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
 * @package     Enterprise_Cms
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/* @var $installer Enterprise_Cms_Model_Mysql4_Setup */
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('enterprise_cms_widget_instance'), 'store_ids', 'varchar(255) NOT NULL default \'0\'');
$installer->getConnection()->addColumn($installer->getTable('enterprise_cms_widget_instance'), 'widget_parameters', 'text');
$installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_cms_widget_instance_page')}` (
        `page_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `instance_id` int(11) unsigned NOT NULL DEFAULT '0',
        `group` varchar(25) NOT NULL DEFAULT '',
        `layout_handle` varchar(255) NOT NULL DEFAULT '',
        `block_reference` varchar(255) NOT NULL DEFAULT '',
        `for` varchar(25) NOT NULL DEFAULT '',
        `entities` text,
        PRIMARY KEY (`page_id`),
        KEY `IDX_WIDGET_INSTANCE_ID` (`instance_id`),
        CONSTRAINT `FK_WIDGET_INSTANCE_ID` FOREIGN KEY (`instance_id`) REFERENCES `{$installer->getTable('enterprise_cms_widget_instance')}` (`instance_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_cms_widget_instance_page_layout')}` (
        `page_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
        `layout_update_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
        UNIQUE KEY `page_id` (`page_id`,`layout_update_id`),
        KEY `IDX_WIDGET_INSTANCE_PAGE_ID` (`page_id`),
        KEY `IDX_WIDGET_INSTANCE_LAYOUT_UPDATE_ID` (`layout_update_id`),
        CONSTRAINT `FK_WIDGET_INSTANCE_PAGE_ID` FOREIGN KEY (`page_id`) REFERENCES `{$installer->getTable('enterprise_cms_widget_instance_page')}` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `FK_WIDGET_INSTANCE_LAYOUT_UPDATE_ID` FOREIGN KEY (`layout_update_id`) REFERENCES `{$installer->getTable('core/layout_update')}` (`layout_update_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
");
$installer->endSetup();
