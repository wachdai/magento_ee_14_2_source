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

$installer->getConnection()->beginTransaction();

$actionTable = $installer->getTable('enterprise_staging/staging_action');
$logTable = $this->getTable('enterprise_staging/staging_log');

$stagingTable = $this->getTable('enterprise_staging/staging');

$eventTable = $this->getTable('enterprise_staging_event');
$backupTable = $this->getTable('enterprise_staging_backup');
$rollbackTable = $installer->getTable('enterprise_staging_rollback');

/**
 * CREATE NEW TABLES
 */

$installer->run("
CREATE TABLE IF NOT EXISTS `" . $actionTable . "` (
  `action_id` int(10) NOT NULL auto_increment,
  `staging_id` int(10) unsigned NOT NULL default '0',
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL default '',
  `status` char(20) NOT NULL default '',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `staging_table_prefix` varchar(255) NOT NULL default '',
  `map` text,
  `mage_version` char(50) NOT NULL default '',
  `mage_modules_version` text NOT NULL,
  `staging_website_id` smallint(5) unsigned default NULL,
  `master_website_id` smallint(5) unsigned default NULL,
  PRIMARY KEY  (`action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Staging Actions';
");

$installer->getConnection()->addKey($actionTable, 'IDX_STAGING_ACTION_STAGING_ID', 'staging_id');
$installer->getConnection()->addKey($actionTable, 'IDX_STAGING_ACTION_STATUS', 'status');
$installer->getConnection()->addKey($actionTable, 'IDX_STAGING_ACTION_VERSION', 'mage_version');
$installer->getConnection()->addKey($actionTable, 'FK_STAGING_ACTION_MASTER_WEBSITE', 'master_website_id');
$installer->getConnection()->addKey($actionTable, 'FK_STAGING_ACTION_STAGING_WEBSITE', 'staging_website_id');
$installer->getConnection()->addKey($actionTable, 'IDX_STAGING_ACTION_TYPE', 'type');

$installer->getConnection()->addConstraint(
    'FK_STAGING_ACTION_MASTER_WEBSITE', $actionTable, 'master_website_id',
    $installer->getTable('core/website'), 'website_id', 'SET NULL', 'CASCADE'
);

$installer->getConnection()->addConstraint(
    'FK_STAGING_ACTION_STAGING_WEBSITE', $actionTable, 'staging_website_id',
    $installer->getTable('core/website'), 'website_id', 'SET NULL', 'CASCADE'
);

$installer->getConnection()->query("
CREATE TABLE IF NOT EXISTS `" . $logTable . "` (
  `log_id` int(10) NOT NULL auto_increment,
  `staging_id` int(10) unsigned NOT NULL default '0',
  `ip` bigint(20) NOT NULL default '0',
  `code` char(20) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `status` char(20) NOT NULL default '',
  `is_backuped` tinyint(1) NOT NULL default '0',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_id` int(11) NOT NULL default '0',
  `username` varchar(255) NOT NULL default '',
  `is_admin_notified` tinyint(1) unsigned NOT NULL default '0',
  `comment` text NOT NULL,
  `log` text NOT NULL,
  `map` text,
  `staging_website_id` smallint(5) unsigned default NULL,
  `master_website_id` smallint(5) unsigned default NULL,
  PRIMARY KEY  (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Staging Log History';
");

$installer->getConnection()->addKey($logTable, 'IDX_STAGING_LOG_STAGING_ID', 'staging_id');
$installer->getConnection()->addKey($logTable, 'IDX_STAGING_LOG_STATUS', 'status');
$installer->getConnection()->addKey($logTable, 'IDX_STAGING_LOG_IS_BACKUPED', 'is_backuped');
$installer->getConnection()->addKey($logTable, 'IDX_STAGING_LOG_NOTIFY', 'is_admin_notified');
$installer->getConnection()->addKey($logTable, 'FK_STAGING_LOG_MASTER_WEBSITE', 'master_website_id');
$installer->getConnection()->addKey($logTable, 'FK_STAGING_LOG_STAGING_WEBSITE', 'staging_website_id');

$installer->getConnection()->addConstraint(
    'FK_STAGING_LOG_MASTER_WEBSITE', $logTable, 'master_website_id',
    $installer->getTable('core/website'), 'website_id', 'CASCADE', 'CASCADE'
);

$installer->getConnection()->addConstraint(
    'FK_STAGING_LOG_STAGING_WEBSITE', $logTable, 'staging_website_id',
    $installer->getTable('core/website'), 'website_id', 'CASCADE', 'CASCADE'
);

/**
 * MODIFIY OLD ONE
 */

$installer->getConnection()->addColumn($stagingTable, 'merge_scheduling_date', 'datetime NOT NULL default "0000-00-00 00:00:00"');
$installer->getConnection()->addColumn($stagingTable, 'merge_scheduling_map', 'text');

/**
 * DATA OPERATIONS
 */

$tables = $installer->getConnection()->listTables();

if (in_array($eventTable, $tables)) {
    $installer->getConnection()->query("UPDATE `" . $stagingTable . "` as s, `" . $eventTable . "` as e
        SET `s`.`merge_scheduling_date` = `e`.`merge_schedule_date`,
            s.`merge_scheduling_map` = `e`.`map`
             WHERE `e`.`event_id` = `s`.`schedule_merge_event_id`");
}

if (in_array($backupTable,  $tables)) {
    $installer->getConnection()->query("
    INSERT INTO `" . $actionTable . "`
        (`type`,
        `staging_id`,
        `name`,
        `status`,
        `created_at`,
        `updated_at`,
        `staging_table_prefix`,
        `map`,
        `mage_version`,
        `mage_modules_version`,
        `staging_website_id`,
        `master_website_id`)
    SELECT 'backup', `staging_id`, `name`, `status`, `created_at`, `updated_at`,
        `staging_table_prefix`, `map`, `mage_version`, `mage_modules_version`, `staging_website_id`, `master_website_id`
        FROM `" . $backupTable . "`
    ");
}

if (in_array($eventTable, $tables)) {
    $installer->getConnection()->query("
    INSERT INTO `" . $logTable . "`
    (`staging_id`,
      `ip`,
      `code`,
      `name`,
      `status`,
      `is_backuped`,
      `created_at`,
      `user_id`,
      `username`,
      `is_admin_notified`,
      `comment`,
      `log`,
      `map`,
      `staging_website_id`,
      `master_website_id`)
    SELECT  `staging_id`, `ip`, `code`, `name`, `status`, `is_backuped`, `created_at`, `user_id`,
        `username`, `is_admin_notified`, `comment`, `log`, `map`,
        IF(`code` = '" . Enterprise_Staging_Model_Staging_Config::ACTION_BACKUP . "', 0, IF(`code` = '" . Enterprise_Staging_Model_Staging_Config::ACTION_MERGE . "' OR `code` = '" . Enterprise_Staging_Model_Staging_Config::ACTION_ROLLBACK . "',`master_website_id`,`staging_website_id`)),
        IF(`code` = '" . Enterprise_Staging_Model_Staging_Config::ACTION_ROLLBACK . "', 0, IF(`code` = '" . Enterprise_Staging_Model_Staging_Config::ACTION_MERGE . "', `staging_website_id`,`master_website_id`))
    FROM `" . $eventTable . "`
    ");
}

$installer->getConnection()->query("DROP TABLE IF EXISTS `" . $backupTable . "`");
$installer->getConnection()->query("DROP TABLE IF EXISTS `" . $eventTable . "`");
$installer->getConnection()->query("DROP TABLE IF EXISTS `" . $rollbackTable . "`");

$installer->getConnection()->dropColumn($stagingTable, 'schedule_merge_event_id');
