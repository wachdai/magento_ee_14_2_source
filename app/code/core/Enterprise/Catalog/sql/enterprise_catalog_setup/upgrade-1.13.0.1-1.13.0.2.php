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

/** @var $client Enterprise_Mview_Model_Client */
$client = Mage::getModel('enterprise_mview/client');
$client->init('catalog_product_index_price');
$client->getMetadata()
    ->setKeyColumn('entity_id')
    ->setViewName('catalog_product_index_price_view')
    ->setGroupCode('catalog_product_price')
    ->setStatus(Enterprise_Mview_Model_Metadata::STATUS_INVALID)
    ->save();

// Create catalog product price changelog
$client->execute('enterprise_index/action_index_changelog_create');

// The list of tables for change subscription
$subscriptions = array(
    $this->getTable('catalog/product')                    => 'entity_id',
    $this->getTable(array('catalog/product', 'decimal'))  => 'entity_id',
    $this->getTable(array('catalog/product', 'int'))      => 'entity_id',
    $this->getTable(array('catalog/product', 'datetime')) => 'entity_id',
    $this->getTable('catalog/product_website')            => 'product_id',
    $this->getTable('catalogrule/rule_product_price')     => 'product_id',
    $this->getTable('cataloginventory/stock_item')        => 'product_id',
);

foreach ($subscriptions as $targetTable => $targetColumn) {
    $arguments = array(
        'target_table'  => $targetTable,
        'target_column' => $targetColumn,
    );
    $client->execute('enterprise_mview/action_changelog_subscription_create', $arguments);
}

//fullfilling metadata_event table
$indexHelper = Mage::helper('enterprise_index');
$eventsMetadataMapping = array(
    'catalog_product_price' => array(
        'config_price',
        'add_customer_group',
        'config_stock',
    ),
);

$eventModel = Mage::getModel('enterprise_mview/event');
foreach ($eventsMetadataMapping as $indexerName => $mappedEvents) {
    $indexTable = $indexHelper->getIndexTableByIndexerName($indexerName);
    $client->init($indexTable);
    foreach ($mappedEvents as $eventName) {
        $eventModel->load($eventName, 'name');
        $data = array(
            'mview_event_id' => $eventModel->getId(),
            'metadata_id'    => $client->getMetadata()->getId(),
        );
        $this->getConnection()->insert($this->getTable('enterprise_mview/metadata_event'), $data);
    }
}
