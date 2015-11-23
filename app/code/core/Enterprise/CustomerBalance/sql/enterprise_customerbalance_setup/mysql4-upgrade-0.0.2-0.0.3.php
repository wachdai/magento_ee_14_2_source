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
 * @package     Enterprise_CustomerBalance
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$installer->run("
CREATE TABLE {$installer->getTable('enterprise_customerbalance_history')} (
`primary_id` INT( 10 ) NOT NULL AUTO_INCREMENT,
`customer_id` INT( 10 ) UNSIGNED NOT NULL,
`website_id` SMALLINT( 5 ) UNSIGNED NOT NULL,
`action` TINYINT( 1 ) UNSIGNED NOT NULL,
`date` DATETIME NOT NULL,
`admin_user_id` MEDIUMINT( 9 ) UNSIGNED NOT NULL,
`delta` DECIMAL( 12, 4 ) NOT NULL,
`order_increment_id` VARCHAR(30) NULL,
    PRIMARY KEY ( `primary_id` ),
    KEY `FK_CUSTOMERBALANCE_HISTORY_CUSTOMER_ENTITY` (`customer_id`),
    KEY `FK_CUSTOMERBALANCE_HISTORY_CORE_WEBSITE` ( `website_id` ),
    CONSTRAINT `FK_CUSTOMERBALANCE_HISTORY_CUSTOMER_ENTITY` FOREIGN KEY (customer_id) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_CUSTOMERBALANCE_HISTORY_CORE_WEBSITE` FOREIGN KEY ( website_id ) REFERENCES {$installer->getTable('core_website')}( `website_id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;
");

$installer->endSetup();
