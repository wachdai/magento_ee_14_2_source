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
 * @package     Enterprise_Reward
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_reward')}` (
  `reward_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `website_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `points_balance` int(11) unsigned NOT NULL DEFAULT '0',
  `reward_update_notification` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `reward_warning_notification` tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`reward_id`),
  UNIQUE KEY `UNQ_CUSTOMER_WEBSITE` (`customer_id`,`website_id`),
  KEY `FK_REWARD_WEBSITE_ID` (`website_id`),
  CONSTRAINT `FK_REWARD_CUSTOMER_ID` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_REWARD_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `{$installer->getTable('core_website')}` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_reward_history')}` (
  `history_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `reward_id` int(11) unsigned NOT NULL DEFAULT '0',
  `website_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `action` tinyint(3) NOT NULL DEFAULT '0',
  `entity` int(11) DEFAULT NULL,
  `points_balance` int(11) unsigned NOT NULL DEFAULT '0',
  `points_delta` int(11) NOT NULL DEFAULT '0',
  `currency_amount` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  `currency_delta` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `base_currency_code` varchar(5) NOT NULL,
  `additional_data` text NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expired_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `notification_sent` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`history_id`),
  KEY `IDX_REWARD_ID` (`reward_id`),
  KEY `IDX_WEBSITE_ID` (`website_id`),
  KEY `IDX_STORE_ID` (`store_id`),
  CONSTRAINT `FK_REWARD_HISTORY_REWARD_ID` FOREIGN KEY (`reward_id`) REFERENCES `{$installer->getTable('enterprise_reward')}` (`reward_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_REWARD_HISTORY_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `{$installer->getTable('core_website')}` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_REWARD_HISTORY_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_reward_rate')}` (
  `rate_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `website_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `customer_group_id` smallint(5) unsigned DEFAULT NULL,
  `direction` tinyint(3) NOT NULL DEFAULT '1',
  `points` int(11) NOT NULL DEFAULT '0',
  `currency_amount` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`rate_id`),
  UNIQUE KEY `IDX_WEBSITE_GROUP_DIRECTION` (`website_id`,`customer_group_id`,`direction`),
  KEY `IDX_WEBSITE_ID` (`website_id`),
  KEY `IDX_CUSTOMER_GROUP_ID` (`customer_group_id`),
  CONSTRAINT `FK_REWARD_RATE_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `{$installer->getTable('core_website')}` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
