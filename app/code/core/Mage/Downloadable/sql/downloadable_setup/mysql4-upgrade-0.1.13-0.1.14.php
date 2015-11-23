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
 * @package     Mage_Downloadable
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('downloadable/link_purchased'), 'order_item_id', "int(10) unsigned NOT NULL default '0' AFTER `order_increment_id`");

$conn->addKey($installer->getTable('downloadable/link_purchased'), 'KEY_DOWNLOADABLE_ORDER_ITEM_ID', 'order_item_id');

$conn->addConstraint(
    'FK_DOWNLOADABLE_PURCHASED_ORDER_ITEM_ID', $installer->getTable('downloadable/link_purchased'), 'order_item_id', $installer->getTable('sales/order_item'), 'item_id'
);

$select = $installer->getConnection()->select()
    ->from($installer->getTable('downloadable/link_purchased_item'), array(
        'purchased_id',
        'order_item_id',
    ));
$result = $installer->getConnection()->fetchAll($select);

foreach ($result as $row) {
    $installer->getConnection()->update(
        $installer->getTable('downloadable/link_purchased'),
        array('order_item_id' => $row['order_item_id']),
        $installer->getConnection()->quoteInto('purchased_id = ?', $row['purchased_id'])
    );
}

$installer->endSetup();
