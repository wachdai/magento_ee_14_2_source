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
 * @package     Enterprise_TargetRule
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/* @var $installer Enterprise_TargetRule_Model_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

// add config attributes to catalog product
$installer->addAttribute('catalog_product', 'related_targetrule_position_limit', array(
    'group'        => 'General',
    'label'        => Mage::helper('enterprise_targetrule')->__('Related Target Rule Rule Based Positions'),
    'visible'      => false,
    'user_defined' => false,
    'required'     => false,
    'type'         => 'int',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'input'        => 'text',
));

$installer->addAttribute('catalog_product', 'related_targetrule_position_behavior', array(
    'group'        => 'General',
    'label'        => Mage::helper('enterprise_targetrule')->__('Related Target Rule Position Behavior'),
    'visible'      => false,
    'user_defined' => false,
    'required'     => false,
    'type'         => 'int',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'input'        => 'text',
));

$installer->addAttribute('catalog_product', 'upsell_targetrule_position_limit', array(
    'group'        => 'General',
    'label'        => Mage::helper('enterprise_targetrule')->__('Upsell Target Rule Rule Based Positions'),
    'visible'      => false,
    'user_defined' => false,
    'required'     => false,
    'type'         => 'int',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'input'        => 'text',
));

$installer->addAttribute('catalog_product', 'upsell_targetrule_position_behavior', array(
    'group'        => 'General',
    'label'        => Mage::helper('enterprise_targetrule')->__('Upsell Target Rule Position Behavior'),
    'visible'      => false,
    'user_defined' => false,
    'required'     => false,
    'type'         => 'int',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'input'        => 'text',
));

// add "used for target rules" setting to attribute management
$installer->getConnection()->addColumn($installer->getTable('catalog/eav_attribute'),
    "is_used_for_target_rules", "TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'");

// use all product attributes that are used for price rules, for target rule as well
$installer->run("UPDATE {$installer->getTable('catalog/eav_attribute')}
    SET is_used_for_target_rules = is_used_for_price_rules");

// create main target rules table
$installer->run("
CREATE TABLE `{$installer->getTable('enterprise_targetrule/rule')}` (
  `rule_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `from_date` DATE DEFAULT NULL,
  `to_date` DATE DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '0',
  `conditions_serialized` BLOB NOT NULL,
  `actions_serialized` BLOB NOT NULL,
  `positions_limit` INT(5) NOT NULL DEFAULT '0',
  `apply_to` TINYINT(3) UNSIGNED NOT NULL,
  `sort_order` INT(10) DEFAULT NULL,
  `use_customer_segment` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY  (`rule_id`),
  KEY `IDX_IS_ACTIVE` (`is_active`),
  KEY `IDX_APPLY_TO` (`apply_to`),
  KEY `IDX_SORT_ORDER` (`sort_order`),
  KEY `IDX_USE_CUSTOMER_SEGMENT` (`use_customer_segment`),
  KEY `IDX_DATE` (`from_date`,`to_date`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;
");

// create target rule and customer segment relation table
$installer->run("
CREATE TABLE `{$installer->getTable('enterprise_targetrule/customersegment')}` (
  `rule_id` INT(10) UNSIGNED NOT NULL,
  `segment_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY  (`rule_id`,`segment_id`),
  KEY `IDX_SEGMENT` (`segment_id`),
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_CUSTOMERSEGMENT_RULE` FOREIGN KEY (`rule_id`)
    REFERENCES `{$installer->getTable('enterprise_targetrule/rule')}` (`rule_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_CUSTOMERSEGMENT_SEGMENT` FOREIGN KEY (`segment_id`)
    REFERENCES `{$installer->getTable('enterprise_customersegment/segment')}` (`segment_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;
");

// create target rule matched product cache table
$installer->run("
CREATE TABLE `{$installer->getTable('enterprise_targetrule/product')}` (
  `rule_id` INT(10) UNSIGNED NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `store_id` SMALLINT(5) UNSIGNED NOT NULL,
  PRIMARY KEY  (`rule_id`,`product_id`,`store_id`),
  KEY `IDX_PRODUCT` (`product_id`),
  KEY `IDX_STORE` (`store_id`),
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_PRODUCT_STORE` FOREIGN KEY (`store_id`)
    REFERENCES `{$installer->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_PRODUCT_PRODUCT` FOREIGN KEY (`product_id`)
    REFERENCES `{$installer->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_PRODUCT_RULE` FOREIGN KEY (`rule_id`)
    REFERENCES `{$installer->getTable('enterprise_targetrule/rule')}` (`rule_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;
");

// create target rule frontend index tables
$installer->run("
CREATE TABLE `{$installer->getTable('enterprise_targetrule/index')}` (
  `entity_id` INT(10) UNSIGNED NOT NULL,
  `store_id` SMALLINT(5) UNSIGNED NOT NULL,
  `customer_group_id` SMALLINT(5) UNSIGNED NOT NULL,
  `type_id` tinyint(1) UNSIGNED NOT NULL,
  `flag` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`entity_id`,`store_id`,`customer_group_id`, `type_id`),
  KEY `IDX_STORE` (`store_id`),
  KEY `IDX_CUSTOMER_GROUP` (`customer_group_id`),
  KEY `IDX_TYPE` (`type_id`),
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_INDEX_CUSTOMER_GROUP` FOREIGN KEY (`customer_group_id`)
    REFERENCES `{$installer->getTable('customer/customer_group')}` (`customer_group_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_INDEX_PRODUCT` FOREIGN KEY (`entity_id`)
    REFERENCES `{$installer->getTable('catalog/product')}` (`entity_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_INDEX_STORE` FOREIGN KEY (`store_id`)
    REFERENCES `{$installer->getTable('core/store')}` (`store_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `{$installer->getTable('enterprise_targetrule/index_related')}` (
  `entity_id` INT(10) UNSIGNED NOT NULL,
  `store_id` SMALLINT(5) UNSIGNED NOT NULL,
  `customer_group_id` SMALLINT(5) UNSIGNED NOT NULL,
  `value` CHAR(255) NOT NULL,
  PRIMARY KEY  (`entity_id`,`store_id`,`customer_group_id`),
  KEY `IDX_STORE` (`store_id`),
  KEY `IDX_CUSTOMER_GROUP` (`customer_group_id`),
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_INDEX_RELATED_CUSTOMER_GROUP` FOREIGN KEY (`customer_group_id`)
    REFERENCES `{$installer->getTable('customer/customer_group')}` (`customer_group_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_INDEX_RELATED_PRODUCT` FOREIGN KEY (`entity_id`)
    REFERENCES `{$installer->getTable('catalog/product')}` (`entity_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_INDEX_RELATED_STORE` FOREIGN KEY (`store_id`)
    REFERENCES `{$installer->getTable('core/store')}` (`store_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `{$installer->getTable('enterprise_targetrule/index_upsell')}` (
  `entity_id` INT(10) UNSIGNED NOT NULL,
  `store_id` SMALLINT(5) UNSIGNED NOT NULL,
  `customer_group_id` SMALLINT(5) UNSIGNED NOT NULL,
  `value` CHAR(255) NOT NULL,
  PRIMARY KEY  (`entity_id`,`store_id`,`customer_group_id`),
  KEY `IDX_STORE` (`store_id`),
  KEY `IDX_CUSTOMER_GROUP` (`customer_group_id`),
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_INDEX_UPSELL_CUSTOMER_GROUP` FOREIGN KEY (`customer_group_id`)
    REFERENCES `{$installer->getTable('customer/customer_group')}` (`customer_group_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_INDEX_UPSELL_PRODUCT` FOREIGN KEY (`entity_id`)
    REFERENCES `{$installer->getTable('catalog/product')}` (`entity_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_INDEX_UPSELL_STORE` FOREIGN KEY (`store_id`)
    REFERENCES `{$installer->getTable('core/store')}` (`store_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `{$installer->getTable('enterprise_targetrule/index_crosssell')}` (
  `entity_id` INT(10) UNSIGNED NOT NULL,
  `store_id` SMALLINT(5) UNSIGNED NOT NULL,
  `customer_group_id` SMALLINT(5) UNSIGNED NOT NULL,
  `value` CHAR(255) NOT NULL,
  PRIMARY KEY  (`entity_id`,`store_id`,`customer_group_id`),
  KEY `IDX_STORE` (`store_id`),
  KEY `IDX_CUSTOMER_GROUP` (`customer_group_id`),
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_INDEX_CROSSSELL_CUSTOMER_GROUP` FOREIGN KEY (`customer_group_id`)
    REFERENCES `{$installer->getTable('customer/customer_group')}` (`customer_group_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_INDEX_CROSSSELL_PRODUCT` FOREIGN KEY (`entity_id`)
    REFERENCES `{$installer->getTable('catalog/product')}` (`entity_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `FK_ENTERPRISE_TARGETRULE_INDEX_CROSSSELL_STORE` FOREIGN KEY (`store_id`)
    REFERENCES `{$installer->getTable('core/store')}` (`store_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
