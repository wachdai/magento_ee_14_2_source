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


/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_cms_hierarchy')}` (
  `tree_id` int(10) unsigned NOT NULL auto_increment,
  `meta_first_last` tinyint(1) NOT NULL default '0',
  `meta_next_previous` tinyint(1) NOT NULL default '0',
  `meta_chapter` tinyint(1) NOT NULL default '0',
  `meta_section` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`tree_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_cms/hierarchy_node')}` (
  `node_id` int(10) unsigned NOT NULL auto_increment,
  `parent_node_id` int(10) unsigned default NULL,
  `tree_id` int(10) unsigned NOT NULL,
  `page_id` smallint(6) default NULL,
  `identifier` varchar(100) default NULL,
  `label` varchar(255) default NULL,
  `level` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `sort_order` int(11) NOT NULL,
  `request_url` varchar(255) NOT NULL,
  PRIMARY KEY  (`node_id`),
  UNIQUE KEY `UNQ_REQUEST_URL` (`request_url`),
  KEY `IDX_PARENT_NODE` (`parent_node_id`),
  KEY `IDX_TREE` (`tree_id`),
  KEY `IDX_PAGE` (`page_id`),
  CONSTRAINT `FK_ENTERPRISE_CMS_HIERARCHY_NODE_PAGE` FOREIGN KEY (`page_id`) REFERENCES `{$installer->getTable('cms/page')}` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ENTERPRISE_CMS_HIERARCHY_NODE_PARENT_NODE` FOREIGN KEY (`parent_node_id`) REFERENCES `{$installer->getTable('enterprise_cms/hierarchy_node')}` (`node_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ENTERPRISE_CMS_HIERARCHY_NODE_TREE` FOREIGN KEY (`tree_id`) REFERENCES `{$installer->getTable('enterprise_cms_hierarchy')}` (`tree_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
