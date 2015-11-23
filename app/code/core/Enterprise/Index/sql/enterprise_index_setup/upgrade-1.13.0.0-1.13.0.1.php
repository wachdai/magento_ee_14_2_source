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
$subscriptions = array(
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
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(NEW.used_in_product_listing = ?)',
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
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION =>
                        '(NEW.used_in_product_listing IS NOT NULL
                            AND NEW.used_in_product_listing != OLD.used_in_product_listing)',
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
                    array(
                        Enterprise_Index_Helper_Trigger::INDEX_CONDITION => '(OLD.used_in_product_listing = ?)',
                        Enterprise_Index_Helper_Trigger::INDEX_PARAM     => array(1),
                    ),
                ),
            ),
        ),
    ),
);

/* @var $triggerHelper Enterprise_Index_Helper_Trigger */
$triggerHelper = Mage::helper('enterprise_index/trigger');
/* @var $factory Enterprise_Mview_Model_Factory */
$factory = Mage::getSingleton('enterprise_mview/factory');
foreach ($subscriptions as $arguments) {
    /* @var $trigger Enterprise_Index_Helper_Trigger */
    $sqlTrigger = $triggerHelper->buildSystemTrigger(
        $arguments['trigger_event'],
        $arguments['event_time'],
        $arguments['entity_events'],
        $arguments['target_table'],
        Zend_Db_Select::SQL_OR
    );
    $objTrigger = $factory->getMagentoDbObjectTrigger($this->getConnection(), $sqlTrigger->getName());
    // Drop trigger before insert with updated body
    if ($objTrigger->isExists()) {
        $objTrigger->drop();
    }
    $query = $sqlTrigger->assemble();
    $this->getConnection()->query($query);
}
