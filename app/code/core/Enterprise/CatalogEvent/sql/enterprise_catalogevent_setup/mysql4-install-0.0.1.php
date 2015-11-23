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
 * @package     Enterprise_CatalogEvent
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_CatalogEvent_Model_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('enterprise_catalogevent/event')}`;
CREATE TABLE `{$installer->getTable('enterprise_catalogevent/event')}` (
    `event_id` int(10) unsigned NOT NULL auto_increment,
    `category_id` int(10) unsigned default NULL,
    `date_start` datetime default NULL,
    `date_end` datetime default NULL,
    `status` enum('upcoming','open','closed') NOT NULL default 'upcoming',
    `display_state` tinyint(3) unsigned default 0,
    PRIMARY KEY  (`event_id`),
    UNIQUE KEY `category_id` (`category_id`),
    KEY `sort_order` (`date_start`,`date_end`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog Events';
");

$fkName = strtoupper($installer->getTable('enterprise_catalogevent/event'));

$installer->getConnection()->addConstraint($fkName . '_CATEGORY', $installer->getTable('enterprise_catalogevent/event'), 'category_id', $this->getTable('catalog/category'), 'entity_id');

$installer->addAttribute('quote_item', 'event_id', array('type' => 'int'));
$installer->addAttribute('quote_item', 'event_name', array());
$installer->addAttribute('order_item', 'event_id', array('type'=>'int'));
$installer->addAttribute('order_item', 'event_name', array());


$installer->endSetup();
