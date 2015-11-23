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
 * @package     Enterprise_GoogleAnalyticsUniversal
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_Banner_Model_Mysql4_Setup */
$installer = $this;

if (!Mage::helper('core')->isModuleEnabled('Enterprise_Banner')) {
    return;
}

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('enterprise_banner/banner'),
    'is_ga_enabled',
    "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER types"
);

$installer->getConnection()->addColumn(
    $installer->getTable('enterprise_banner/banner'), 'ga_creative', 'TEXT NULL DEFAULT NULL'
);

$installer->endSetup();
