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
 * @package     Enterprise_Index
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $this Mage_Core_Model_Resource_Setup */

$table = $this->getConnection()
    ->newTable($this->getTable('enterprise_index/multiplier'))
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Multiplier Value')
    ->setComment('Index Multiplier Table');

$this->getConnection()->createTable($table);

$this->getConnection()->insertMultiple(
    $this->getTable('enterprise_index/multiplier'),
    array(
        array('value' => 1),
        array('value' => 2)
    )
);

//fullfilling events table
$eventNames = array(
    'add_website',
    'add_store',
    'add_store_group',
    'update_root_category_id',
    'add_attribute',
    'add_customer_group',
    'delete_website',
    'delete_store',
    'delete_store_group',
    'delete_attribute',
    'delete_customer_group',
    'update_attribute',
    'config_price',
    'config_stock',
    'config_flat_product',
    'config_flat_category',
);

$eventModel = Mage::getModel('enterprise_mview/event');
foreach ($eventNames as $eventName) {
    $data = array('name' => $eventName);
    $eventModel->setData($data)->save();
}

//create system trigger subscriptions for flat indexers
$subscriptions = array(
    array(
        'target_table'  => $this->getTable('core/store_group'),
        'trigger_event' => Magento_Db_Sql_Trigger::SQL_EVENT_INSERT,
        'event_time'    => Magento_Db_Sql_Trigger::SQL_TIME_AFTER,
        'entity_events' =>  array(
            'add_store_group' => array(),
        ),
    ),
    array(
        'target_table'  => $this->getTable('core/store_group'),
        'trigger_event' => Magento_Db_Sql_Trigger::SQL_EVENT_UPDATE,
        'event_time'    => Magento_Db_Sql_Trigger::SQL_TIME_AFTER,
        'entity_events' =>  array(
            'update_root_category_id' => array(
                'conditions' => array(
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION =>
                        '(OLD.root_category_id != NEW.root_category_id)',
                    ),
                ),
            ),
        ),
    ),
    array(
        'target_table'  => $this->getTable('core/store'),
        'trigger_event' => Magento_Db_Sql_Trigger::SQL_EVENT_INSERT,
        'event_time'    => Magento_Db_Sql_Trigger::SQL_TIME_AFTER,
        'entity_events' => array(
            'add_store' => array(),
        ),
    ),
    array(
        'target_table'  => $this->getTable('catalog/eav_attribute'),
        'trigger_event' => Magento_Db_Sql_Trigger::SQL_EVENT_INSERT,
        'event_time'    => Magento_Db_Sql_Trigger::SQL_TIME_AFTER,
        'entity_events' => array(
            'add_attribute' => array(
                'conditions' => array(
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(NEW.is_searchable = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(1),
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(NEW.is_visible_in_advanced_search = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(1),
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(NEW.is_filterable > ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(0),
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(NEW.is_filterable_in_search = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(1),
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(NEW.used_for_sort_by = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(1),
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(NEW.is_used_for_promo_rules = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(1),
                    ),
                ),
            ),
        ),
    ),
    array(
        'target_table'  => $this->getTable('catalog/eav_attribute'),
        'trigger_event' => Magento_Db_Sql_Trigger::SQL_EVENT_UPDATE,
        'event_time'    => Magento_Db_Sql_Trigger::SQL_TIME_AFTER,
        'entity_events' => array(
            'update_attribute' => array(
                'conditions' => array(
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION =>
                        '(NEW.is_searchable IS NOT NULL AND NEW.is_searchable != OLD.is_searchable)',
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION =>
                        '(NEW.is_visible_in_advanced_search IS NOT NULL
                        AND
                        NEW.is_visible_in_advanced_search != OLD.is_visible_in_advanced_search)',
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION =>
                        '(NEW.is_filterable IS NOT NULL AND NEW.is_filterable != OLD.is_filterable)',
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION =>
                        '(NEW.is_filterable_in_search IS NOT NULL
                        AND
                        NEW.is_filterable_in_search != OLD.is_filterable_in_search)',
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION =>
                        '(NEW.used_for_sort_by IS NOT NULL AND NEW.used_for_sort_by != OLD.used_for_sort_by)',
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION =>
                        '(NEW.is_used_for_promo_rules IS NOT NULL
                            AND NEW.is_used_for_promo_rules != OLD.is_used_for_promo_rules)',
                    ),
                ),
            ),
        ),
    ),
    array(
        'target_table'  => $this->getTable('catalog/eav_attribute'),
        'trigger_event' => Magento_Db_Sql_Trigger::SQL_EVENT_DELETE,
        'event_time'    => Magento_Db_Sql_Trigger::SQL_TIME_AFTER,
        'entity_events' => array(
            'delete_attribute' => array(
                'conditions' => array(
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(OLD.is_searchable = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(1),
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(OLD.is_visible_in_advanced_search = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(1),
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(OLD.is_filterable > ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(0),
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(OLD.is_filterable_in_search = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(1),
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(OLD.used_for_sort_by = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(1),
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(OLD.is_used_for_promo_rules = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(1),
                    ),
                ),
            ),
        ),
    ),
    array(
        'target_table'  => $this->getTable('core/store'),
        'trigger_event' => Magento_Db_Sql_Trigger::SQL_EVENT_DELETE,
        'event_time'    => Magento_Db_Sql_Trigger::SQL_TIME_AFTER,
        'entity_events' => array(
            'delete_store' => array(),
        )
    ),
    array(
        'target_table'  => $this->getTable('core/store_group'),
        'trigger_event' => Magento_Db_Sql_Trigger::SQL_EVENT_DELETE,
        'event_time'    => Magento_Db_Sql_Trigger::SQL_TIME_AFTER,
        'entity_events' => array(
            'delete_store_group' => array(),
        )
    ),
    array(
        'target_table'  => $this->getTable('core/website'),
        'trigger_event' => Magento_Db_Sql_Trigger::SQL_EVENT_DELETE,
        'event_time'    => Magento_Db_Sql_Trigger::SQL_TIME_AFTER,
        'entity_events' => array(
            'delete_website' => array(),
        )
    ),
    array(
        'target_table'  => $this->getTable('customer/customer_group'),
        'trigger_event' => Magento_Db_Sql_Trigger::SQL_EVENT_INSERT,
        'event_time'    => Magento_Db_Sql_Trigger::SQL_TIME_AFTER,
        'entity_events' => array(
            'add_customer_group' => array(),
        ),
    ),
    array(
        'target_table'  => $this->getTable('core/config_data'),
        'trigger_event' => Magento_Db_Sql_Trigger::SQL_EVENT_INSERT,
        'event_time'    => Magento_Db_Sql_Trigger::SQL_TIME_AFTER,
        'entity_events' => array(
            'config_price' => array(
                'conditions' => array(
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(NEW.path = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(
                            Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE
                        ),
                    ),
                ),
            ),
            'config_stock' => array(
                'conditions' => array(
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(NEW.path = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(
                            Mage_CatalogInventory_Helper_Data::XML_PATH_SHOW_OUT_OF_STOCK
                        ),
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(NEW.path = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(
                            Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK
                        ),
                    ),
                ),
            ),
            'config_flat_product' => array(
                'conditions' => array(
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(NEW.path = ?) AND (NEW.value = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(
                            Mage_Catalog_Helper_Product_Flat::XML_PATH_USE_PRODUCT_FLAT,
                            1
                        ),
                    ),
                ),
            ),
            'config_flat_category' => array(
                'conditions' => array(
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(NEW.path = ?) AND (NEW.value = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(
                            Mage_Catalog_Helper_Category_Flat::XML_PATH_IS_ENABLED_FLAT_CATALOG_CATEGORY,
                            1
                        ),
                    ),
                ),
            ),
        ),
    ),
    array(
        'target_table'  => $this->getTable('core/config_data'),
        'trigger_event' => Magento_Db_Sql_Trigger::SQL_EVENT_UPDATE,
        'event_time'    => Magento_Db_Sql_Trigger::SQL_TIME_AFTER,
        'entity_events' => array(
            'config_price' => array(
                'conditions' => array(
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION =>
                        '((NEW.path = ?) AND (NEW.value != OLD.value))',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(
                            Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE
                        ),
                    ),
                ),
            ),
            'config_stock' => array(
                'conditions' => array(
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION =>
                        '((NEW.path = ?) AND (NEW.value != OLD.value))',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(
                            Mage_CatalogInventory_Helper_Data::XML_PATH_SHOW_OUT_OF_STOCK
                        ),
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION =>
                        '((NEW.path = ?) AND (NEW.value != OLD.value))',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(
                            Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK
                        ),
                    ),
                ),
            ),
            'config_flat_product' => array(
                'conditions' => array(
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION =>
                        '(NEW.path = ?) AND (NEW.value != OLD.value) AND (NEW.value = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(
                            Mage_Catalog_Helper_Product_Flat::XML_PATH_USE_PRODUCT_FLAT,
                            1
                        ),
                    ),
                ),
            ),
            'config_flat_category' => array(
                'conditions' => array(
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION =>
                        '(NEW.path = ?) AND (NEW.value != OLD.value) AND (NEW.value = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(
                            Mage_Catalog_Helper_Category_Flat::XML_PATH_IS_ENABLED_FLAT_CATALOG_CATEGORY,
                            1
                        ),
                    ),
                ),
            ),
        ),
    ),
    array(
        'target_table'  => $this->getTable('core/config_data'),
        'trigger_event' => Magento_Db_Sql_Trigger::SQL_EVENT_DELETE,
        'event_time'    => Magento_Db_Sql_Trigger::SQL_TIME_AFTER,
        'entity_events' => array(
            'config_price' => array(
                'conditions'    => array(
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(OLD.path = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(
                            Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE
                        ),
                    ),
                ),
            ),
            'config_stock' => array(
                'conditions' => array(
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(OLD.path = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(
                            Mage_CatalogInventory_Helper_Data::XML_PATH_SHOW_OUT_OF_STOCK
                        ),
                    ),
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(OLD.path = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(
                            Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK
                        ),
                    ),
                ),
            ),
        ),
    ),
);

/* @var $triggerHelper Enterprise_Index_Helper_Trigger */
$triggerHelper = Mage::helper('enterprise_index/trigger');
foreach ($subscriptions as $arguments) {
    /* @var $trigger Enterprise_Index_Helper_Trigger */
    $triggerCreateQuery = $triggerHelper->buildSystemTrigger(
        $arguments['trigger_event'],
        $arguments['event_time'],
        $arguments['entity_events'],
        $arguments['target_table'],
        Zend_Db_Select::SQL_OR
    )->assemble();

    $this->getConnection()->query($triggerCreateQuery);
}

