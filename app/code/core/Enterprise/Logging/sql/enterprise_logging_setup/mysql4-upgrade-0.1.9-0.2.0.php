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
 * @package     Enterprise_Logging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$tableLog = $this->getTable('enterprise_logging/event');
$tableLogChanges = $this->getTable('enterprise_logging/event_changes');

$installer->getConnection()->addColumn($tableLog, 'x_forwarded_ip', "bigint(20) unsigned NULL DEFAULT NULL AFTER `ip`");

$installer->run("DROP TABLE IF EXISTS `{$tableLogChanges}`");
$installer->run("CREATE TABLE `".$tableLogChanges."` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(150) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `model_id` int(11) DEFAULT NULL,
  `original_data` text NOT NULL,
  `result_data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `FK_LOGGING_EVENT_CHANGES_EVENT_ID` FOREIGN KEY (`event_id`) REFERENCES `{$tableLog}` (`log_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$installer->getConnection()->addColumn($tableLog, 'error_message', "tinytext DEFAULT NULL");
$installer->endSetup();
