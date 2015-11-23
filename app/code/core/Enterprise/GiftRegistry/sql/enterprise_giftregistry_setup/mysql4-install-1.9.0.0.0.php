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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Enterprise_GiftRegistry_Model_Mysql4_Setup */

$installer->run("
CREATE TABLE `{$this->getTable('enterprise_giftregistry/type')}` (
    `type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `code` varchar(15) NOT NULL DEFAULT '',
    `meta_xml` blob,
    PRIMARY KEY (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('enterprise_giftregistry/info')}` (
    `type_id` int(10) unsigned NOT NULL DEFAULT '0',
    `store_id` smallint(5) unsigned NOT NULL DEFAULT '0',
    `label` varchar(255) DEFAULT NULL,
    `is_listed` tinyint(1) unsigned DEFAULT NULL,
    `sort_order` tinyint(3) unsigned DEFAULT NULL,
    PRIMARY KEY (`type_id`,`store_id`),
    KEY `IDX_EE_GR_INFO_STORE` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('enterprise_giftregistry/label')}` (
    `type_id` int(10) unsigned NOT NULL DEFAULT '0',
    `attribute_code` varchar(32) NOT NULL DEFAULT '',
    `store_id` smallint(5) unsigned NOT NULL DEFAULT '0',
    `option_code` varchar(32) NOT NULL,
    `label` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`type_id`,`attribute_code`,`store_id`,`option_code`),
    KEY `IDX_EE_GR_LABEL_TYPE_ID` (`type_id`),
    KEY `IDX_EE_GR_LABEL_STORE_ID` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('enterprise_giftregistry/entity')}` (
    `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `type_id` int(10) unsigned NOT NULL DEFAULT '0',
    `customer_id` int(10) unsigned NOT NULL DEFAULT '0',
    `website_id` smallint(5) unsigned NOT NULL DEFAULT '0',
    `is_public` tinyint(1) unsigned NOT NULL DEFAULT '1',
    `url_key` varchar(100) DEFAULT NULL,
    `title` varchar(255) NOT NULL DEFAULT '',
    `message` text NOT NULL,
    `shipping_address` blob NOT NULL,
    `custom_values` text NOT NULL,
    `is_active` tinyint(4) NOT NULL DEFAULT '0',
    `created_at` datetime DEFAULT NULL,
    PRIMARY KEY (`entity_id`),
    KEY `IDX_EE_GR_ENTITY_CUSTOMER` (`customer_id`),
    KEY `IDX_EE_GR_ENTITY_WEBSITE` (`website_id`),
    KEY `IDX_EE_GR_ENTITY_TYPE` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('enterprise_giftregistry/item')}` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `qty` decimal(12,4) NOT NULL,
  `qty_fulfilled` decimal(12,4) NOT NULL,
  `note` text NOT NULL,
  `added_at` datetime DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `IDX_EE_GR_ITEM_ENTITY` (`entity_id`),
  KEY `IDX_EE_GR_ITEM_PRODUCT` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('enterprise_giftregistry/person')}` (
    `person_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `entity_id` int(10) unsigned NOT NULL DEFAULT '0',
    `firstname` varchar(100) NOT NULL DEFAULT '',
    `lastname` varchar(100) NOT NULL DEFAULT '',
    `email` varchar(150) NOT NULL DEFAULT '',
    `role` varchar(32) NOT NULL DEFAULT '',
    PRIMARY KEY (`person_id`),
    KEY `IDX_EE_GR_PERSON_ENTITY` (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('enterprise_giftregistry/data')}` (
  `entity_id` int(10) unsigned NOT NULL DEFAULT '0',
  `event_date` date DEFAULT NULL,
  `event_country_code` char(3) DEFAULT NULL,
  `event_region` int(10) DEFAULT NULL,
  `event_region_text` varchar(30) DEFAULT NULL,
  `event_location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addConstraint(
    'FK_EE_GR_INFO_STORE',
    $this->getTable('enterprise_giftregistry/info'),
    'store_id',
    $this->getTable('core_store'),
    'store_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_GR_INFO_TYPE',
    $this->getTable('enterprise_giftregistry/info'),
    'type_id',
    $this->getTable('enterprise_giftregistry/type'),
    'type_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_GR_LABEL_STORE_ID',
    $this->getTable('enterprise_giftregistry/label'),
    'store_id',
    $this->getTable('core_store'),
    'store_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_GR_LABEL_TYPE_ID',
    $this->getTable('enterprise_giftregistry/label'),
    'type_id',
    $this->getTable('enterprise_giftregistry/type'),
    'type_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_GR_ENTITY_CUSTOMER',
    $this->getTable('enterprise_giftregistry/entity'),
    'customer_id',
    $this->getTable('customer_entity'),
    'entity_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_GR_ENTITY_WEBSITE',
    $this->getTable('enterprise_giftregistry/entity'),
    'website_id',
    $this->getTable('core_website'),
    'website_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_GR_ENTITY_TYPE',
    $this->getTable('enterprise_giftregistry/entity'),
    'type_id',
    $this->getTable('enterprise_giftregistry/type'),
    'type_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_GR_ITEM_ENTITY',
    $this->getTable('enterprise_giftregistry/item'),
    'entity_id',
    $this->getTable('enterprise_giftregistry/entity'),
    'entity_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_GR_ITEM_PRODUCT',
    $this->getTable('enterprise_giftregistry/item'),
    'product_id',
    $this->getTable('catalog/product'),
    'entity_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_GR_PERSON_ENTITY',
    $this->getTable('enterprise_giftregistry/person'),
    'entity_id',
    $this->getTable('enterprise_giftregistry/entity'),
    'entity_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_GR_DATA_ENTITY',
    $this->getTable('enterprise_giftregistry/data'),
    'entity_id',
    $this->getTable('enterprise_giftregistry/entity'),
    'entity_id'
);
