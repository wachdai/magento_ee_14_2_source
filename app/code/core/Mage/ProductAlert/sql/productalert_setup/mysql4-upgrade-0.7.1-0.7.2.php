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
 * @package     Mage_ProductAlert
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
// fix for sample data 1.2.0
$installer->getConnection()->changeTableEngine($installer->getTable('productalert/stock'), 'INNODB');
$installer->getConnection()->dropKey($installer->getTable('productalert/stock'), 'FK_PRODUCT_ALERT_PRICE_CUSTOMER');
$installer->getConnection()->dropKey($installer->getTable('productalert/stock'), 'FK_PRODUCT_ALERT_PRICE_PRODUCT');
$installer->getConnection()->dropKey($installer->getTable('productalert/stock'), 'FK_PRODUCT_ALERT_PRICE_WEBSITE');
$installer->getConnection()->addConstraint('FK_PRODUCT_ALERT_STOCK_CUSTOMER',
    $installer->getTable('productalert/stock'), 'customer_id',
    $installer->getTable('customer/entity'), 'entity_id',
    'CASCADE', 'CASCADE', true);
$installer->getConnection()->addConstraint('FK_PRODUCT_ALERT_STOCK_PRODUCT',
    $installer->getTable('productalert/stock'), 'product_id',
    $installer->getTable('catalog/product'), 'entity_id',
    'CASCADE', 'CASCADE', true);
$installer->getConnection()->addConstraint('FK_PRODUCT_ALERT_STOCK_WEBSITE',
    $installer->getTable('productalert/stock'), 'website_id',
    $installer->getTable('core/website'), 'website_id',
    'CASCADE', 'CASCADE', true);
$installer->endSetup();
