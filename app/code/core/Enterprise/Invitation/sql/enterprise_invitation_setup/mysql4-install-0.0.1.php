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

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$tableInvitation        = $installer->getTable('enterprise_invitation/invitation');
$tableInvitationHistory = $installer->getTable('enterprise_invitation/invitation_history');

$installer->run("
DROP TABLE IF EXISTS `{$tableInvitation}`;
CREATE TABLE `{$tableInvitation}` (
    `invitation_id` INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `customer_id` INT( 10 ) UNSIGNED DEFAULT NULL ,
    `date` DATETIME NOT NULL ,
    `email` VARCHAR( 255 ) NOT NULL ,
    `referral_id` INT( 10 ) UNSIGNED DEFAULT NULL ,
    `protection_code` CHAR(32) NOT NULL,
    `signup_date` DATETIME DEFAULT NULL,
    `store_id` SMALLINT(5) UNSIGNED NOT NULL,
    `group_id` SMALLINT(3) UNSIGNED NOT NULL,
    `message` TEXT DEFAULT NULL,
    `status` ENUM('sent','accepted', 'canceled') NOT NULL,
    INDEX `IDX_customer_id` (`customer_id`),
    INDEX `IDX_referral_id` (`referral_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addConstraint(
    'FK_INVITATION_STORE', $tableInvitation, 'store_id', $installer->getTable('core_store'), 'store_id'
);

$installer->run("
DROP TABLE IF EXISTS `{$tableInvitationHistory}`;
CREATE TABLE `{$tableInvitationHistory}` (
    `history_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `invitation_id` INT UNSIGNED NOT NULL,
    `date` DATETIME NOT NULL,
    `status` ENUM('sent','accepted', 'canceled') NOT NULL,
    INDEX `IDX_invitation_id` (`invitation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addConstraint(
    'FK_INVITATION_HISTORY_INVITATION', $tableInvitationHistory, 'invitation_id', $tableInvitation, 'invitation_id'
);

$installer->endSetup();
