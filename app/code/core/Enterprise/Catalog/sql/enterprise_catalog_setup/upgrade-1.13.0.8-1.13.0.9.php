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
$events = array();
/** @var $eventModel  Enterprise_Mview_Model_Event */
$eventCollection = Mage::getModel('enterprise_mview/event')->getCollection()->load();
foreach ($eventCollection as $event) {
    /** @var $event Enterprise_Mview_Model_Event */
    $events[$event->getName()] = $event->getMviewEventId();
}

$eventsMetadataMapping = array(
    'enterprise_url_rewrite_category' => array(
        'add_store',
        'delete_store',
        'delete_store_group',
        'delete_website',
    ),
);
/** @var $metadataModel Enterprise_Mview_Model_Metadata */
$metadataModel = Mage::getModel('enterprise_mview/metadata');
foreach ($eventsMetadataMapping as $indexTable => $mappedEvents) {
    $metadataModel->load($this->getTable($indexTable), 'table_name');
    foreach ($mappedEvents as $eventName) {
        $data = array(
            'mview_event_id' => $events[$eventName],
            'metadata_id'    => $metadataModel->getId(),
        );
        $this->getConnection()->insert($this->getTable('enterprise_mview/metadata_event'), $data);
    }
}
