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
 * @package     Enterprise_Catalog
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $this Mage_Core_Model_Resource_Setup */

//Subscribe to catalog_category
/** @var $client Enterprise_Mview_Model_Client */
$client = Mage::getModel('enterprise_mview/client');
$client->init('catalog_category_flat');
$client->getMetadata()
    ->setKeyColumn('entity_id')
    ->setViewName('catalog_category_view')
    ->setGroupCode('catalog_category_flat')
    ->setStatus(Enterprise_Mview_Model_Metadata::STATUS_INVALID)
    ->save();

// Create catalog_category changelog
$client->execute('enterprise_index/action_index_changelog_create');

$subscriptions = array(
    $this->getTable('catalog/category')                    => 'entity_id',
    $this->getTable(array('catalog/category', 'decimal'))  => 'entity_id',
    $this->getTable(array('catalog/category', 'int'))      => 'entity_id',
    $this->getTable(array('catalog/category', 'text'))     => 'entity_id',
    $this->getTable(array('catalog/category', 'varchar'))  => 'entity_id',
    $this->getTable(array('catalog/category', 'datetime')) => 'entity_id',
);

foreach ($subscriptions as $targetTable => $targetColumn) {
    $arguments = array(
        'target_table'  => $targetTable,
        'target_column' => $targetColumn,
    );
    $client->execute('enterprise_mview/action_changelog_subscription_create', $arguments);
}

//Create product flat subscriptions
/** @var $client Enterprise_Mview_Model_Client */
$client = Mage::getModel('enterprise_mview/client');
$client->init('catalog_product_flat');
$client->getMetadata()
    ->setKeyColumn('entity_id')
    ->setViewName('catalog_product_view')
    ->setGroupCode('catalog_product_flat')
    ->setStatus(Enterprise_Mview_Model_Metadata::STATUS_INVALID)
    ->save();

// Create product flat changelog
$client->execute('enterprise_index/action_index_changelog_create');

$subscriptions = array(
    $this->getTable('catalog/product')                    => 'entity_id',
    $this->getTable(array('catalog/product', 'decimal'))  => 'entity_id',
    $this->getTable(array('catalog/product', 'int'))      => 'entity_id',
    $this->getTable(array('catalog/product', 'text'))     => 'entity_id',
    $this->getTable(array('catalog/product', 'varchar'))  => 'entity_id',
    $this->getTable(array('catalog/product', 'datetime')) => 'entity_id',
);

foreach ($subscriptions as $targetTable => $targetColumn) {
    $arguments = array(
        'target_table'  => $targetTable,
        'target_column' => $targetColumn,
    );
    $client->execute('enterprise_mview/action_changelog_subscription_create', $arguments);
}


$events       = array();
$eventRecords = Mage::getModel('enterprise_mview/event')->getCollection()->load();

foreach ($eventRecords as $event) {
    $events[$event->getName()] = $event->getMviewEventId();
}

//Flat system triggers subscriptions
/* @var $indexHelper Enterprise_Index_Helper_Data */
$indexHelper = Mage::helper('enterprise_index');
$eventsMetadataMapping = array(
    'catalog_product_flat' => array(
        'add_store',
        'add_store_group',
        'add_attribute',
        'delete_attribute',
        'update_attribute',
        'config_flat_product',
    ),
    'catalog_category_flat' => array(
        'add_store',
        'add_store_group',
        'update_root_category_id',
        'add_attribute',
        'delete_attribute',
        'update_attribute',
        'config_flat_category',
    )
);

$metadataModel = Mage::getModel('enterprise_mview/metadata');
foreach ($eventsMetadataMapping as $indexerName => $mappedEvents) {
    $indexTable = $this->getTable($indexHelper->getIndexTableByIndexerName($indexerName));

    foreach ($mappedEvents as $eventName) {
        if (isset($events[$eventName])) {
            $data = array(
                'mview_event_id' => $events[$eventName],
                'metadata_id'    => $metadataModel->load($indexTable, 'table_name')->getId()
            );

            $this->getConnection()->insert($this->getTable('enterprise_mview/metadata_event'), $data);
        }
    }
}

