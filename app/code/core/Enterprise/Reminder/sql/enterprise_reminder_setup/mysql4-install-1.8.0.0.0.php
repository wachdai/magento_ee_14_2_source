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
 * @package     Enterprise_Reminder
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Enterprise_Reminder_Model_Mysql4_Setup */

$installer->run("

CREATE TABLE `{$this->getTable('enterprise_reminder/rule')}` (
    `rule_id` int(10) unsigned NOT NULL auto_increment,
    `name` varchar(255) NOT NULL default '',
    `description` text NOT NULL,
    `conditions_serialized` mediumtext NOT NULL,
    `condition_sql` mediumtext,
    `is_active` tinyint(1) unsigned NOT NULL default '0',
    `salesrule_id` int(10) unsigned default NULL,
    `schedule` varchar(255) NOT NULL DEFAULT '',
    `default_label` varchar(255) NOT NULL default '',
    `default_description` text NOT NULL,
    `active_from` datetime default NULL,
    `active_to` datetime default NULL,
    PRIMARY KEY  (`rule_id`),
    KEY `IDX_EE_REMINDER_SALESRULE` (`salesrule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('enterprise_reminder/website')}` (
    `rule_id` int(10) unsigned NOT NULL,
    `website_id` smallint(5) unsigned NOT NULL,
    PRIMARY KEY (`rule_id`,`website_id`),
    KEY `IDX_EE_REMINDER_WEBSITE` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('enterprise_reminder/template')}` (
    `rule_id` int(10) unsigned NOT NULL,
    `store_id` smallint(5) NOT NULL,
    `template_id` int(10) unsigned DEFAULT NULL,
    `label` varchar(255) NOT NULL default '',
    `description` text NOT NULL,
    PRIMARY KEY (`rule_id`,`store_id`),
    KEY `IDX_EE_REMINDER_TEMPLATE_RULE` (`rule_id`),
    KEY `IDX_EE_REMINDER_TEMPLATE` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('enterprise_reminder/coupon')}` (
    `rule_id` int(10) unsigned NOT NULL,
    `coupon_id` int(10) unsigned DEFAULT NULL,
    `customer_id` int(10) unsigned NOT NULL,
    `associated_at` datetime NOT NULL,
    `emails_failed` smallint(5) unsigned NOT NULL default '0',
    `is_active` tinyint(1) unsigned NOT NULL default '1',
    PRIMARY KEY (`rule_id`,`customer_id`),
    KEY `IDX_EE_REMINDER_RULE_COUPON` (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
CREATE TABLE `{$this->getTable('enterprise_reminder/log')}` (
    `log_id` int(10) unsigned NOT NULL auto_increment,
    `rule_id` int(10) unsigned NOT NULL,
    `customer_id` int(10) unsigned NOT NULL,
    `sent_at` datetime NOT NULL,
    PRIMARY KEY (`log_id`),
    KEY `IDX_EE_REMINDER_LOG_RULE` (`rule_id`),
    KEY `IDX_EE_REMINDER_LOG_CUSTOMER` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
");

$installer->getConnection()->addConstraint(
    'FK_EE_REMINDER_RULE',
    $this->getTable('enterprise_reminder/website'),
    'rule_id',
    $this->getTable('enterprise_reminder/rule'),
    'rule_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_REMINDER_SALESRULE',
    $this->getTable('enterprise_reminder/rule'),
    'salesrule_id',
    $this->getTable('salesrule'),
    'rule_id',
    'SET NULL'
);

$installer->getConnection()->addConstraint(
    'FK_EE_REMINDER_TEMPLATE_RULE',
    $this->getTable('enterprise_reminder/template'),
    'rule_id',
    $this->getTable('enterprise_reminder/rule'),
    'rule_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_REMINDER_TEMPLATE',
    $this->getTable('enterprise_reminder/template'),
    'template_id',
    $this->getTable('core_email_template'),
    'template_id',
    'SET NULL'
);

$installer->getConnection()->addConstraint(
    'FK_EE_REMINDER_RULE_COUPON',
    $this->getTable('enterprise_reminder/coupon'),
    'rule_id',
    $this->getTable('enterprise_reminder/rule'),
    'rule_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_REMINDER_LOG_RULE',
    $this->getTable('enterprise_reminder/log'),
    'rule_id',
    $this->getTable('enterprise_reminder/rule'),
    'rule_id'
);
