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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer->startSetup();

$installer->getConnection()->dropColumn($this->getTable('core/store'), 'is_staging');
$installer->getConnection()->dropColumn($this->getTable('core/store'), 'master_login');
$installer->getConnection()->dropColumn($this->getTable('core/store'), 'master_password');
$installer->getConnection()->dropColumn($this->getTable('core/store'), 'master_password_hash');

$installer->getConnection()->dropColumn($this->getTable('core/website'), 'master_password_hash');

$installer->getConnection()->addColumn($this->getTable('core/website'), 'is_staging', "TINYINT(1) NOT NULL DEFAULT '0'");
$installer->getConnection()->addColumn($this->getTable('core/website'), 'master_login', "VARCHAR(40) NOT NULL");
$installer->getConnection()->addColumn($this->getTable('core/website'), 'master_password', "VARCHAR(100) NOT NULL");

$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('enterprise_staging_dataset')}`;
DROP TABLE IF EXISTS `{$this->getTable('enterprise_staging_dataset_item')}`;

DROP TABLE IF EXISTS `{$this->getTable('enterprise_staging_store')}`;
DROP TABLE IF EXISTS `{$this->getTable('enterprise_staging_store_group')}`;
DROP TABLE IF EXISTS `{$this->getTable('enterprise_staging_website')}`;

DROP TABLE IF EXISTS `{$this->getTable('enterprise_staging/staging_item')}`;
DROP TABLE IF EXISTS `{$this->getTable('enterprise_staging/staging')}`;
");

$installer->run("
CREATE TABLE `{$installer->getTable('enterprise_staging/staging')}` (
  `staging_id` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(50) NOT NULL default '',
  `name` varchar(64) NOT NULL default '',
  `master_website_id` smallint(5) unsigned default NULL,
  `staging_website_id` smallint(5) unsigned NOT NULL default '0',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `state` varchar(20) NOT NULL default '',
  `status` varchar(10) NOT NULL default '',
  `sort_order` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY (`staging_id`),
  KEY `IDX_ENTERPRISE_STAGING_STATE` (`state`),
  KEY `IDX_ENTERPRISE_STAGING_STATUS` (`status`),
  KEY `IDX_ENTERPRISE_STAGING_SORT_ORDER` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Staging';
");
$installer->getConnection()->addConstraint(
    'FK_ENTERPRISE_STAGING_MASTER_WEBSITE_ID',
    $this->getTable('enterprise_staging/staging'),
    'master_website_id',
    $installer->getTable('core/website'),
    'website_id',
    'SET NULL'
);
$installer->getConnection()->addConstraint(
    'FK_ENTERPRISE_STAGING_STAGING_WEBSITE_ID',
    $this->getTable('enterprise_staging/staging'),
    'staging_website_id',
    $installer->getTable('core/website'),
    'website_id'
);

$installer->run("
CREATE TABLE `{$this->getTable('enterprise_staging/staging_item')}` (
  `staging_item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `staging_id` int(10) unsigned DEFAULT NULL,
  `code` varchar(50) NOT NULL DEFAULT '',
  `sort_order` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`staging_item_id`),
  UNIQUE KEY `UNQ_ENTERPRISE_STAGING_ITEM` (`staging_id`,`code`),
  KEY `IDX_ENTERPRISE_STAGING_ITEM_SORT_ORDER` (`staging_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Staging Items';
");
$installer->getConnection()->addConstraint(
    'FK_ENTERPRISE_STAGING_ITEM_STAGING_ID',
    $this->getTable('enterprise_staging/staging_item'),
    'staging_id',
    $installer->getTable('enterprise_staging/staging'),
    'staging_id'
);

$installer->endSetup();
