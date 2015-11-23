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
 * @package     Enterprise_Invitation
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$tableInvitation    = $installer->getTable('enterprise_invitation/invitation');
$tableCustomer      = $installer->getTable('customer/entity');
$tableCustomerGroup = $installer->getTable('customer/customer_group');

$installer->getConnection()->changeColumn($tableInvitation, 'status', 'status', "enum('new','sent','accepted','canceled') NOT NULL DEFAULT 'new'");
$installer->getConnection()->changeColumn($installer->getTable('enterprise_invitation/invitation_history'),
    'status', 'status', "enum('new','sent','accepted','canceled') NOT NULL DEFAULT 'new'");
$installer->getConnection()->changeColumn($tableInvitation, 'group_id', 'group_id', 'smallint(3) unsigned NULL DEFAULT NULL');

$installer->run("UPDATE {$tableInvitation} SET group_id = NULL WHERE group_id NOT IN (SELECT customer_group_id FROM {$tableCustomerGroup})");
$installer->getConnection()->addConstraint('FK_INVITATION_CUSTOMER_GROUP', $tableInvitation,
    'group_id', $tableCustomerGroup, 'customer_group_id', $onDelete = 'SET NULL');

$installer->run("
CREATE TABLE `{$installer->getTable('enterprise_invitation/invitation_track')}` (
  `track_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `inviter_id` int(10) unsigned NOT NULL DEFAULT 0,
  `referral_id` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`track_id`),
  UNIQUE KEY `UNQ_INVITATION_TRACK_IDS` (`inviter_id`,`referral_id`),
  KEY `FK_INVITATION_TRACK_REFERRAL` (`referral_id`),
  CONSTRAINT `FK_INVITATION_TRACK_INVITER` FOREIGN KEY (`inviter_id`) REFERENCES `{$tableCustomer}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_INVITATION_TRACK_REFERRAL` FOREIGN KEY (`referral_id`) REFERENCES `{$tableCustomer}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();
