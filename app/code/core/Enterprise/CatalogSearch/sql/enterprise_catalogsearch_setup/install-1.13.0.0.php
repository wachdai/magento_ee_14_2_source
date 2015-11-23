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
 * @package     Enterprise_CatalogSearch
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $this Mage_Core_Model_Resource_Setup */

/** @var $indexHelper Enterprise_Index_Helper_Data */
$indexHelper =  Mage::helper('enterprise_index');

/** @var $client Enterprise_Mview_Model_Client */
$client = Mage::getModel('enterprise_mview/client');
$client->init('catalogsearch_fulltext');
$client->getMetadata()
    ->setKeyColumn('product_id')
    ->setViewName('catalogsearch_fulltext_cl')
    ->setStatus(Enterprise_Mview_Model_Metadata::STATUS_INVALID)
    ->setGroupCode('catalogsearch_fulltext')
    ->save();

$client->execute('enterprise_index/action_index_changelog_create');

$subscriptions = array(
    $this->getTable('catalog/product')                    => 'entity_id',
    $this->getTable(array('catalog/product', 'decimal'))  => 'entity_id',
    $this->getTable(array('catalog/product', 'int'))      => 'entity_id',
    $this->getTable(array('catalog/product', 'text'))     => 'entity_id',
    $this->getTable(array('catalog/product', 'varchar'))  => 'entity_id',
    $this->getTable(array('catalog/product', 'datetime')) => 'entity_id',
);

/** @var $resources mage_core_model_resource */
$resources = Mage::getSingleton('core/resource');
/** @var $productType mage_catalog_model_product_type */
$productType = Mage::getSingleton('catalog/product_type');

$productEmulator = new Varien_Object();
$productEmulator->setIdFieldName('entity_id');
foreach (Mage_Catalog_Model_Product_Type::getCompositeTypes() as $typeId) {
    $productEmulator->setTypeId($typeId);
    /** @var $typeInstance Mage_Catalog_Model_Product_Type_Abstract */
    $typeInstance = $productType->factory($productEmulator);
    /** @var $relation bool|Varien_Object */
    $relation = $typeInstance->isComposite()
        ? $typeInstance->getRelationInfo()
        : false;
    if ($relation && $relation->getTable()) {
        $tableName = $resources->getTableName($relation->getTable());
        $subscriptions[$tableName] = $relation->getParentFieldName();
    }
}

foreach ($subscriptions as $targetTable => $targetColumn) {
    $arguments = array(
        'target_table'  => $targetTable,
        'target_column' => $targetColumn,
    );
    $client->execute('enterprise_mview/action_changelog_subscription_create', $arguments);
}

$events = array();
/** @var $eventCollection Enterprise_Mview_Model_Resource_Event_Collection*/
$eventCollection = Mage::getModel('enterprise_mview/event')->getCollection();
foreach ($eventCollection as $event) {
    /** @var $event enterprise_mview_model_event */
    $events[$event->getName()] = $event->getMviewEventId();
}

$eventsMetadataMapping = array(
    'catalogsearch_fulltext' => array(
        'add_store',
        'delete_store',
        'delete_store_group',
        'delete_website',
        'add_attribute',
        'delete_attribute',
        'update_attribute'
    ),
);
/** @var $metadataModel enterprise_mview_model_metadata */
$metadataModel = Mage::getModel('enterprise_mview/metadata');
foreach ($eventsMetadataMapping as $indexerName => $mappedEvents) {
    $indexTable = $this->getTable($indexHelper->getIndexTableByIndexerName($indexerName));

    foreach ($mappedEvents as $eventName) {
        $data = array(
            'mview_event_id' => $events[$eventName],
            'metadata_id'    => $metadataModel->load($indexTable, 'table_name')->getId()
        );

        $this->getConnection()->insert($this->getTable('enterprise_mview/metadata_event'), $data);
    }
}
