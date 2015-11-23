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
 * @category    Mage
 * @package     Mage_Reports
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Report events SQL
 *
 * @category   Mage
 * @package    Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */

$installer = $this;
/* $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('report_event_types')} DROP INDEX `event_type_id`;
ALTER TABLE {$this->getTable('report_event_types')} CHANGE `event_name` `event_name` varchar(64) NOT NULL;    
UPDATE {$this->getTable('report_event_types')} SET `event_name`='catalog_product_compare_add_product' WHERE `event_type_id`=3;
ALTER TABLE {$this->getTable('report_event')} ADD `sybtype` tinyint(3) unsigned NOT NULL default '0' AFTER `subject_id`;
ALTER TABLE {$this->getTable('report_event')} ADD INDEX (`event_type_id`); 
ALTER TABLE {$this->getTable('report_event')} ADD INDEX (`sybtype`);
ALTER TABLE {$this->getTable('report_event')} ADD INDEX (`store_id`);
ALTER TABLE {$this->getTable('report_event_types')} ADD `customer_login` TINYINT UNSIGNED NOT NULL DEFAULT '0';
UPDATE {$this->getTable('report_event_types')} SET `customer_login`=1;
");

$installer->endSetup();
