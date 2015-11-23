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
 * @package     Enterprise_GiftWrapping
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Enterprise_GiftWrapping_Model_Resource_Mysql4_Setup */

$installer->run("
CREATE TABLE {$installer->getTable('enterprise_giftwrapping/wrapping')}(
    wrapping_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    status TINYINT(1) UNSIGNED NOT NULL,
    base_price DECIMAL(12,4) NOT NULL,
    image VARCHAR(255) NOT NULL,
    PRIMARY KEY (wrapping_id),
    KEY `IDX_EE_GW_STATUS` (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$installer->getTable('enterprise_giftwrapping/attribute')}(
    wrapping_id INT(10) UNSIGNED NOT NULL,
    store_id SMALLINT(5) UNSIGNED NOT NULL,
    design VARCHAR(255) NOT NULL,
    PRIMARY KEY (wrapping_id, store_id),
    KEY `IDX_EE_GW_STORE_ID` (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$installer->getTable('enterprise_giftwrapping/website')}(
    wrapping_id INT(10) UNSIGNED NOT NULL,
    website_id SMALLINT(5) UNSIGNED NOT NULL,
    PRIMARY KEY (wrapping_id, website_id),
    KEY `IDX_EE_GW_WEBSITE_ID` (website_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addConstraint(
    'FK_EE_GW_ATTR_WRAPPING_ID',
    $installer->getTable('enterprise_giftwrapping/attribute'),
    'wrapping_id',
    $installer->getTable('enterprise_giftwrapping/wrapping'),
    'wrapping_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_GW_STORE_ID',
    $installer->getTable('enterprise_giftwrapping/attribute'),
    'store_id',
    $installer->getTable('core_store'),
    'store_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_GW_WRAPPING_ID',
    $installer->getTable('enterprise_giftwrapping/website'),
    'wrapping_id',
    $installer->getTable('enterprise_giftwrapping/wrapping'),
    'wrapping_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_GW_WEBSITE_ID',
    $installer->getTable('enterprise_giftwrapping/website'),
    'website_id',
    $this->getTable('core_website'),
    'website_id'
);
