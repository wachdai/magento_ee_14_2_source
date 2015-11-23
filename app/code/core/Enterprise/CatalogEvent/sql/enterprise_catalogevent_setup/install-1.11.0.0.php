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

/** @var $installer Enterprise_CatalogEvent_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'enterprise_catalogevent/event'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_catalogevent/event'))
    ->addColumn('event_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Event Id')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Category Id')
    ->addColumn('date_start', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Date Start')
    ->addColumn('date_end', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Date End')
    ->addColumn('display_state', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Display State')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Sort Order')
    ->addIndex($installer->getIdxName('enterprise_catalogevent/event', array('category_id'), true),
        array('category_id'), array('type' => 'unique'))
    ->addIndex($installer->getIdxName('enterprise_catalogevent/event', array('date_start', 'date_end')),
        array('date_start', 'date_end'))
    ->addForeignKey($installer->getFkName('enterprise_catalogevent/event', 'category_id', 'catalog/category', 'entity_id'),
        'category_id', $installer->getTable('catalog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Catalogevent Event');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_catalogevent/event_image'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_catalogevent/event_image'))
    ->addColumn('event_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Event Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Store Id')
    ->addColumn('image', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Image')
    ->addIndex($installer->getIdxName('enterprise_catalogevent/event_image', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('enterprise_catalogevent/event_image', 'event_id', 'enterprise_catalogevent/event', 'event_id'),
        'event_id', $installer->getTable('enterprise_catalogevent/event'), 'event_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_catalogevent/event_image', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Catalogevent Event Image');
$installer->getConnection()->createTable($table);

$installer->addAttribute('quote_item', 'event_id', array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER));
$installer->addAttribute('order_item', 'event_id', array('type' => Varien_Db_Ddl_Table::TYPE_INTEGER));

$installer->endSetup();
