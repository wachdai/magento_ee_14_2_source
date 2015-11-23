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
 * @package     Enterprise_GiftCard
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$installer->run("
CREATE TABLE {$installer->getTable('enterprise_giftcard_amount')} (
  `value_id` int(11) NOT NULL auto_increment,
  `website_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `value` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `entity_id` int(10) unsigned NOT NULL DEFAULT '0',
  `entity_type_id` smallint (5) unsigned NOT NULL,
  `attribute_id` smallint (5) unsigned NOT NULL,
  PRIMARY KEY  (`value_id`),
  KEY `FK_GIFTCARD_AMOUNT_PRODUCT_ENTITY` (`entity_id`),
  KEY `FK_GIFTCARD_AMOUNT_WEBSITE` (`website_id`),
  KEY `FK_GIFTCARD_AMOUNT_ATTRIBUTE_ID` (`attribute_id`),
  CONSTRAINT `FK_GIFTCARD_AMOUNT_PRODUCT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES {$this->getTable('catalog_product_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_GIFTCARD_AMOUNT_WEBSITE` FOREIGN KEY (`website_id`) REFERENCES {$this->getTable('core_website')} (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_GIFTCARD_AMOUNT_ATTRIBUTE_ID` FOREIGN KEY (`attribute_id`) REFERENCES {$this->getTable('eav_attribute')} (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
