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
 * @package     Enterprise_TargetRule
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_TargetRule_Model_Resource_Setup */

$installer = $this;
$installer->startSetup();

// add config attributes to catalog product
$installer->addAttribute('catalog_product', 'related_tgtr_position_limit', array(
    'group'        => 'General',
    'label'        => Mage::helper('enterprise_targetrule')->__('Related Target Rule Rule Based Positions'),
    'visible'      => false,
    'user_defined' => false,
    'required'     => false,
    'type'         => 'int',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'input'        => 'text',
    'backend'      => 'enterprise_targetrule/catalog_product_attribute_backend_rule',
));

$installer->addAttribute('catalog_product', 'related_tgtr_position_behavior', array(
    'group'        => 'General',
    'label'        => Mage::helper('enterprise_targetrule')->__('Related Target Rule Position Behavior'),
    'visible'      => false,
    'user_defined' => false,
    'required'     => false,
    'type'         => 'int',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'input'        => 'text',
    'backend'      => 'enterprise_targetrule/catalog_product_attribute_backend_rule',
));

$installer->addAttribute('catalog_product', 'upsell_tgtr_position_limit', array(
    'group'        => 'General',
    'label'        => Mage::helper('enterprise_targetrule')->__('Upsell Target Rule Rule Based Positions'),
    'visible'      => false,
    'user_defined' => false,
    'required'     => false,
    'type'         => 'int',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'input'        => 'text',
    'backend'      =>'enterprise_targetrule/catalog_product_attribute_backend_rule',
));

$installer->addAttribute('catalog_product', 'upsell_tgtr_position_behavior', array(
    'group'        => 'General',
    'label'        => Mage::helper('enterprise_targetrule')->__('Upsell Target Rule Position Behavior'),
    'visible'      => false,
    'user_defined' => false,
    'required'     => false,
    'type'         => 'int',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'input'        => 'text',
    'backend'      =>'enterprise_targetrule/catalog_product_attribute_backend_rule',
));

/**
 * Create table 'enterprise_targetrule/rule'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_targetrule/rule'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rule Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('from_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'From Date')
    ->addColumn('to_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'To Date')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Active')
    ->addColumn('conditions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '64K', array(
        'nullable'  => false,
        ), 'Conditions Serialized')
    ->addColumn('actions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '64K', array(
        'nullable'  => false,
        ), 'Actions Serialized')
    ->addColumn('positions_limit', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Positions Limit')
    ->addColumn('apply_to', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Apply To')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Sort Order')
    ->addColumn('use_customer_segment', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Use Customer Segment')
    ->addColumn('action_select', Varien_Db_Ddl_Table::TYPE_TEXT, '64K', array(
        ), 'Action Select')
    ->addColumn('action_select_bind', Varien_Db_Ddl_Table::TYPE_TEXT, '64K', array(
        ), 'Action Select Bind')
    ->addIndex($installer->getIdxName('enterprise_targetrule/rule', array('is_active')),
        array('is_active'))
    ->addIndex($installer->getIdxName('enterprise_targetrule/rule', array('apply_to')),
        array('apply_to'))
    ->addIndex($installer->getIdxName('enterprise_targetrule/rule', array('sort_order')),
        array('sort_order'))
    ->addIndex($installer->getIdxName('enterprise_targetrule/rule', array('use_customer_segment')),
        array('use_customer_segment'))
    ->addIndex($installer->getIdxName('enterprise_targetrule/rule', array('from_date', 'to_date')),
        array('from_date', 'to_date'))
    ->setComment('Enterprise Targetrule');
$installer->getConnection()->createTable($table);


/**
 * Create table 'enterprise_targetrule/customersegment'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_targetrule/customersegment'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rule Id')
    ->addColumn('segment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Segment Id')
    ->addIndex($installer->getIdxName('enterprise_targetrule/customersegment', array('segment_id')),
        array('segment_id'))
    ->addForeignKey($installer->getFkName('enterprise_targetrule/customersegment', 'rule_id', 'enterprise_targetrule/rule', 'rule_id'),
        'rule_id', $installer->getTable('enterprise_targetrule/rule'), 'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_targetrule/customersegment', 'segment_id', 'enterprise_customersegment/segment', 'segment_id'),
        'segment_id', $installer->getTable('enterprise_customersegment/segment'), 'segment_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Targetrule Customersegment');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_targetrule/product'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_targetrule/product'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rule Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Product Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Store Id')
    ->addIndex($installer->getIdxName('enterprise_targetrule/product', array('product_id')),
        array('product_id'))
    ->addIndex($installer->getIdxName('enterprise_targetrule/product', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('enterprise_targetrule/product', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_targetrule/product', 'product_id', 'catalog/product', 'entity_id'),
        'product_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_targetrule/product', 'rule_id', 'enterprise_targetrule/rule', 'rule_id'),
        'rule_id', $installer->getTable('enterprise_targetrule/rule'), 'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Targetrule Product');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_targetrule/index'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_targetrule/index'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Store Id')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Group Id')
    ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Type Id')
    ->addColumn('flag', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        ), 'Flag')
    ->addIndex($installer->getIdxName('enterprise_targetrule/index', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('enterprise_targetrule/index', array('customer_group_id')),
        array('customer_group_id'))
    ->addIndex($installer->getIdxName('enterprise_targetrule/index', array('type_id')),
        array('type_id'))
    ->addForeignKey($installer->getFkName('enterprise_targetrule/index', 'customer_group_id', 'customer/customer_group', 'customer_group_id'),
        'customer_group_id', $installer->getTable('customer/customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_targetrule/index', 'entity_id', 'catalog/product', 'entity_id'),
        'entity_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_targetrule/index', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Targetrule Index');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_targetrule/index_related'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_targetrule/index_related'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Store Id')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Group Id')
    ->addColumn('product_ids', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Related Product Ids')
    ->addIndex($installer->getIdxName('enterprise_targetrule/index_related', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('enterprise_targetrule/index_related', array('customer_group_id')),
        array('customer_group_id'))
    ->addForeignKey($installer->getFkName('enterprise_targetrule/index_related', 'customer_group_id', 'customer/customer_group', 'customer_group_id'),
        'customer_group_id', $installer->getTable('customer/customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_targetrule/index_related', 'entity_id', 'catalog/product', 'entity_id'),
        'entity_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_targetrule/index_related', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Targetrule Index Related');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_targetrule/index_upsell'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_targetrule/index_upsell'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Store Id')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Group Id')
    ->addColumn('product_ids', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Upsell Product Ids')
    ->addIndex($installer->getIdxName('enterprise_targetrule/index_upsell', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('enterprise_targetrule/index_upsell', array('customer_group_id')),
        array('customer_group_id'))
    ->addForeignKey($installer->getFkName('enterprise_targetrule/index_upsell', 'customer_group_id', 'customer/customer_group', 'customer_group_id'),
        'customer_group_id', $installer->getTable('customer/customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_targetrule/index_upsell', 'entity_id', 'catalog/product', 'entity_id'),
        'entity_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_targetrule/index_upsell', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Targetrule Index Upsell');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_targetrule/index_crosssell'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_targetrule/index_crosssell'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Store Id')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Group Id')
    ->addColumn('product_ids', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'CrossSell Product Ids')
    ->addIndex($installer->getIdxName('enterprise_targetrule/index_crosssell', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('enterprise_targetrule/index_crosssell', array('customer_group_id')),
        array('customer_group_id'))
    ->addForeignKey($installer->getFkName('enterprise_targetrule/index_crosssell', 'customer_group_id', 'customer/customer_group', 'customer_group_id'),
        'customer_group_id', $installer->getTable('customer/customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_targetrule/index_crosssell', 'entity_id', 'catalog/product', 'entity_id'),
        'entity_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_targetrule/index_crosssell', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Targetrule Index Crosssell');
$installer->getConnection()->createTable($table);

$installer->endSetup();
