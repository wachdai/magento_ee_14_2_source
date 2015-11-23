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
 * @package     Enterprise_Pci
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$tableAdmins     = $installer->getTable('admin/user');
$tableApiUsers   = $installer->getTable('api/user');
$tableOldPasswds = $installer->getTable('enterprise_pci/admin_passwords');

$installer->getConnection()->changeColumn($tableAdmins, 'password', 'password', 'varchar(100) NOT NULL DEFAULT \'\'');
$installer->getConnection()->changeColumn($tableApiUsers, 'api_key', 'api_key', 'varchar(100) NOT NULL DEFAULT \'\'');

$installer->getConnection()->addColumn($tableAdmins, 'failures_num', 'smallint(6) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($tableAdmins, 'first_failure', 'datetime NULL DEFAULT NULL');
$installer->getConnection()->addColumn($tableAdmins, 'lock_expires', 'datetime NULL DEFAULT NULL');

$installer->run("
CREATE TABLE IF NOT EXISTS `{$tableOldPasswds}` (
  `password_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0,
  `password_hash` varchar(100) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `expires` int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`password_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->getConnection()->addConstraint('FK_ADMIN_PASSWORDS_USER', $tableOldPasswds, 'user_id', $tableAdmins, 'user_id');

$installer->endSetup();
