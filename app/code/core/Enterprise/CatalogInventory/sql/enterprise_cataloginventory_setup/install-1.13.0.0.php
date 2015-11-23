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
 * @package     Enterprise_CatalogInventory
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

/** @var $client Enterprise_Mview_Model_Client */
$client = Mage::getModel('enterprise_mview/client');
$client->init('cataloginventory_stock_status');
$client->getMetadata()
    ->setKeyColumn('product_id')
    ->setViewName('cataloginventory_stock_status_view')
    ->setStatus(Enterprise_Mview_Model_Metadata::STATUS_INVALID)
    ->setGroupCode('cataloginventory_stock')
    ->save();

// Create catalog product price changelog
$client->execute('enterprise_index/action_index_changelog_create');

// The list of tables for change subscription
$subscriptions = array(
    $installer->getTable('catalog/product_website')       => 'product_id',
    $installer->getTable('cataloginventory/stock_item')   => 'product_id',
    $installer->getTable(array('catalog/product', 'int')) => 'entity_id',
    $installer->getTable('bundle/selection')              => 'parent_product_id',
    $installer->getTable('catalog/product_super_link')    => 'parent_id',
    $installer->getTable('catalog/product_link')          => 'product_id',
);

foreach($subscriptions as $targetTable => $targetColumn) {
    $arguments = array(
        'target_table'  => $targetTable,
        'target_column' => $targetColumn,
    );
    $client->execute('enterprise_mview/action_changelog_subscription_create', $arguments);
}

//fullfilling events table
$eventNames = array(
    'update_stock'
);

/** @var $eventModel Enterprise_Mview_Model_Event */
$eventModel = Mage::getModel('enterprise_mview/event');
foreach ($eventNames as $eventName) {
    $data = array('name' => $eventName);
    $eventModel->setData($data)->save();
}

//fullfilling metadata_event table
/** @var $indexHelper Enterprise_Index_Helper_Data */
$indexHelper = Mage::helper('enterprise_index');
$eventsMetadataMapping = array(
    'cataloginventory_stock' => array(
        'config_stock',
    ),
);

foreach ($eventsMetadataMapping as $indexerName => $mappedEvents) {
    $indexTable = $indexHelper->getIndexTableByIndexerName($indexerName);
    $client->init($indexTable);
    foreach ($mappedEvents as $eventName) {
        $eventModel->load($eventName, 'name');
        $data = array(
            'mview_event_id' => $eventModel->getId(),
            'metadata_id'    => $client->getMetadata()->getId(),
        );
        $installer->getConnection()->insert($installer->getTable('enterprise_mview/metadata_event'), $data);
    }
}
