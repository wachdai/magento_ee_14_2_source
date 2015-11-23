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

$installer->run("DROP TABLE IF EXISTS `".$this->getTable('enterprise_logging/event')."`");
$installer->run("CREATE TABLE `".$this->getTable('enterprise_logging/event')."` (
    `log_id` int(11) NOT NULL auto_increment,
    `ip` bigint(20) unsigned NOT NULL default '0',
    `event_code` char(20) NOT NULL default '',
    `time` datetime NOT NULL default '0000-00-00 00:00:00',
    `action` char(20) NOT NULL default '-',
    `info` varchar(255) NOT NULL default '-',
    `status` char(15) NOT NULL default 'success',
    `user` char(15) NOT NULL default '-',
    `fullaction` varchar(200) NOT NULL default '-',
    PRIMARY KEY  (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$installer->endSetup();
