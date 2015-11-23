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
 * @package     Enterprise_CatalogEvent
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_CatalogEvent_Model_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('enterprise_catalogevent/event_image')}`;
CREATE TABLE `{$installer->getTable('enterprise_catalogevent/event_image')}` (
    `event_id` int(10) unsigned NOT NULL,
    `store_id` smallint (5) unsigned NOT NULL,
    `image` varchar(255) NOT NULL,
    UNIQUE KEY `scope` (`event_id`, `store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Catalog Event Images';
");

$installer->getConnection()->addConstraint(
    'ENTERPRISE_CATALOGEVENT_EVENT_IMAGE_EVENT',
    $installer->getTable('enterprise_catalogevent/event_image'),
    'event_id',
    $installer->getTable('enterprise_catalogevent/event'),
    'event_id'
);

$installer->getConnection()->addConstraint(
    'ENTERPRISE_CATALOGEVENT_EVENT_IMAGE_STORE',
    $installer->getTable('enterprise_catalogevent/event_image'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$installer->getConnection()->addColumn(
    $installer->getTable('enterprise_catalogevent/event'),
    'sort_order',
    ' int(10) unsigned DEFAULT NULL'
);

$now = Mage::app()->getLocale()->date()
    ->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE)
    ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

$installer->getConnection()->insert($installer->getTable('cms/block'), array(
    'title' => 'Catalog Events Lister',
    'identifier' => 'catalog_events_lister',
    'content' => '{{block type="enterprise_catalogevent/event_lister" name="catalog.event.lister" template="catalogevent/lister.phtml"}}',
    'creation_time' => $now,
    'update_time' => $now,
    'is_active' => 1
));

$blockId = $installer->getConnection()->lastInsertId();
$select = $installer->getConnection()->select()
    ->from($installer->getTable('core/store'), array('block_id' => new Zend_Db_Expr($blockId), 'store_id'))
    ->where('store_id > 0');

$installer->run($select->insertFromSelect($installer->getTable('cms/block_store')), array('block_id', 'store_id'));

$installer->endSetup();
