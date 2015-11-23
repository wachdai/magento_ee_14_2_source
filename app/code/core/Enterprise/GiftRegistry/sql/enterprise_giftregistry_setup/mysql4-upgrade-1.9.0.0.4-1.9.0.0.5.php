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
CREATE TABLE `{$this->getTable('enterprise_giftregistry/item_option')}` (
    `option_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `item_id` INT(10) UNSIGNED NOT NULL,
    `product_id` INT(10) UNSIGNED NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `value` TEXT NOT NULL,
    PRIMARY KEY (`option_id`)
) COMMENT='Additional options for giftregistry item' ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addConstraint(
    'FK_GIFTREGISTRY_ITEM_OPTION_ITEM_ID',
    $this->getTable('enterprise_giftregistry/item_option'),
    'item_id',
    $this->getTable('enterprise_giftregistry/item'),
    'item_id'
);

$installer->getConnection()->modifyColumn(
    $this->getTable('enterprise_giftregistry/item'), 'custom_options',
    "TEXT NOT NULL COMMENT 'Deprecated since 1.10'"
);
