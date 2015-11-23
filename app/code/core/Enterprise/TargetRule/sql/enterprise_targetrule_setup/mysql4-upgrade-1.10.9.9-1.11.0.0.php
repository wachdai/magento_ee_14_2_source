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

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/customersegment'),
    'FK_ENTERPRISE_TARGETRULE_CUSTOMERSEGMENT_RULE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/customersegment'),
    'FK_ENTERPRISE_TARGETRULE_CUSTOMERSEGMENT_SEGMENT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/index'),
    'FK_ENTERPRISE_TARGETRULE_INDEX_PRODUCT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/index'),
    'FK_ENTERPRISE_TARGETRULE_INDEX_STORE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/index_crosssell'),
    'FK_ENTERPRISE_TARGETRULE_INDEX_CROSSSELL_CUSTOMER_GROUP'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/index_crosssell'),
    'FK_ENTERPRISE_TARGETRULE_INDEX_CROSSSELL_PRODUCT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/index_crosssell'),
    'FK_ENTERPRISE_TARGETRULE_INDEX_CROSSSELL_STORE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/index_related'),
    'FK_ENTERPRISE_TARGETRULE_INDEX_RELATED_CUSTOMER_GROUP'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/index_related'),
    'FK_ENTERPRISE_TARGETRULE_INDEX_RELATED_PRODUCT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/index_related'),
    'FK_ENTERPRISE_TARGETRULE_INDEX_RELATED_STORE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/index'),
    'FK_ENTERPRISE_TARGETRULE_INDEX_CUSTOMER_GROUP'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/index_upsell'),
    'FK_ENTERPRISE_TARGETRULE_INDEX_UPSELL_CUSTOMER_GROUP'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/index_upsell'),
    'FK_ENTERPRISE_TARGETRULE_INDEX_UPSELL_PRODUCT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/index_upsell'),
    'FK_ENTERPRISE_TARGETRULE_INDEX_UPSELL_STORE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/product'),
    'FK_ENTERPRISE_TARGETRULE_PRODUCT_PRODUCT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/product'),
    'FK_ENTERPRISE_TARGETRULE_PRODUCT_RULE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_targetrule/product'),
    'FK_ENTERPRISE_TARGETRULE_PRODUCT_STORE'
);


/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/rule'),
    'IDX_IS_ACTIVE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/rule'),
    'IDX_APPLY_TO'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/rule'),
    'IDX_SORT_ORDER'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/rule'),
    'IDX_USE_CUSTOMER_SEGMENT'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/rule'),
    'IDX_DATE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/index'),
    'IDX_STORE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/index'),
    'IDX_CUSTOMER_GROUP'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/index'),
    'IDX_TYPE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/index_crosssell'),
    'IDX_STORE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/index_crosssell'),
    'IDX_CUSTOMER_GROUP'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/customersegment'),
    'IDX_SEGMENT'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/index_related'),
    'IDX_STORE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/index_related'),
    'IDX_CUSTOMER_GROUP'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/index_upsell'),
    'IDX_STORE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/index_upsell'),
    'IDX_CUSTOMER_GROUP'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/product'),
    'IDX_PRODUCT'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_targetrule/product'),
    'IDX_STORE'
);


/*
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_targetrule/rule') => array(
        'columns' => array(
            'rule_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Rule Id'
            ),
            'name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Name'
            ),
            'from_date' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
                'comment'   => 'From Date'
            ),
            'to_date' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
                'comment'   => 'To Date'
            ),
            'is_active' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Is Active'
            ),
            'conditions_serialized' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'nullable'  => false,
                'comment'   => 'Conditions Serialized'
            ),
            'actions_serialized' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'nullable'  => false,
                'comment'   => 'Actions Serialized'
            ),
            'positions_limit' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Positions Limit'
            ),
            'apply_to' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Apply To'
            ),
            'sort_order' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Sort Order'
            ),
            'use_customer_segment' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Use Customer Segment'
            ),
            'action_select' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Action Select'
            ),
            'action_select_bind' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Action Select Bind'
            )
        ),
        'comment' => 'Enterprise Targetrule'
    ),
    $installer->getTable('enterprise_targetrule/customersegment') => array(
        'columns' => array(
            'rule_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Rule Id'
            ),
            'segment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Segment Id'
            )
        ),
        'comment' => 'Enterprise Targetrule Customersegment'
    ),
    $installer->getTable('enterprise_targetrule/product') => array(
        'columns' => array(
            'rule_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Rule Id'
            ),
            'product_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Product Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Store Id'
            )
        ),
        'comment' => 'Enterprise Targetrule Product'
    ),
    $installer->getTable('enterprise_targetrule/index') => array(
        'columns' => array(
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Entity Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Store Id'
            ),
            'customer_group_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Customer Group Id'
            ),
            'type_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Type Id'
            ),
            'flag' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '1',
                'comment'   => 'Flag'
            )
        ),
        'comment' => 'Enterprise Targetrule Index'
    ),
    $installer->getTable('enterprise_targetrule/index_related') => array(
        'columns' => array(
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Entity Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Store Id'
            ),
            'customer_group_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Customer Group Id'
            )
        ),
        'comment' => 'Enterprise Targetrule Index Related'
    ),
    $installer->getTable('enterprise_targetrule/index_crosssell') => array(
        'columns' => array(
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Entity Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Store Id'
            ),
            'customer_group_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Customer Group Id'
            )
        ),
        'comment' => 'Enterprise Targetrule Index Crosssell'
    ),
    $installer->getTable('enterprise_targetrule/index_upsell') => array(
        'columns' => array(
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Entity Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Store Id'
            ),
            'customer_group_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Customer Group Id'
            )
        ),
        'comment' => 'Enterprise Targetrule Index Upsell'
    )
);

$installer->getConnection()->modifyTables($tables);

$installer->getConnection()->dropColumn(
    $installer->getTable('catalog/eav_attribute'),
    'is_used_for_target_rules'
);

$installer->getConnection()->changeColumn(
    $installer->getTable('enterprise_targetrule/index_crosssell'),
    'value',
    'product_ids',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment'   => 'CrossSell Product Ids'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('enterprise_targetrule/index_related'),
    'value',
    'product_ids',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment'   => 'Related Product Ids'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('enterprise_targetrule/index_upsell'),
    'value',
    'product_ids',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment'   => 'Upsell Product Ids'
    )
);


/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/rule'),
    $installer->getIdxName('enterprise_targetrule/rule', array('is_active')),
    array('is_active')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/rule'),
    $installer->getIdxName('enterprise_targetrule/rule', array('apply_to')),
    array('apply_to')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/rule'),
    $installer->getIdxName('enterprise_targetrule/rule', array('sort_order')),
    array('sort_order')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/rule'),
    $installer->getIdxName('enterprise_targetrule/rule', array('use_customer_segment')),
    array('use_customer_segment')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/rule'),
    $installer->getIdxName('enterprise_targetrule/rule', array('from_date', 'to_date')),
    array('from_date', 'to_date')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/customersegment'),
    $installer->getIdxName('enterprise_targetrule/customersegment', array('segment_id')),
    array('segment_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/index'),
    $installer->getIdxName('enterprise_targetrule/index', array('store_id')),
    array('store_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/index'),
    $installer->getIdxName('enterprise_targetrule/index', array('customer_group_id')),
    array('customer_group_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/index'),
    $installer->getIdxName('enterprise_targetrule/index', array('type_id')),
    array('type_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/index_crosssell'),
    $installer->getIdxName('enterprise_targetrule/index_crosssell', array('store_id')),
    array('store_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/index_crosssell'),
    $installer->getIdxName('enterprise_targetrule/index_crosssell', array('customer_group_id')),
    array('customer_group_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/index_related'),
    $installer->getIdxName('enterprise_targetrule/index_related', array('store_id')),
    array('store_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/index_related'),
    $installer->getIdxName('enterprise_targetrule/index_related', array('customer_group_id')),
    array('customer_group_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/index_upsell'),
    $installer->getIdxName('enterprise_targetrule/index_upsell', array('store_id')),
    array('store_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/index_upsell'),
    $installer->getIdxName('enterprise_targetrule/index_upsell', array('customer_group_id')),
    array('customer_group_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/product'),
    $installer->getIdxName('enterprise_targetrule/product', array('product_id')),
    array('product_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/product'),
    $installer->getIdxName('enterprise_targetrule/product', array('store_id')),
    array('store_id')
);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/customersegment',
        'rule_id',
        'enterprise_targetrule/rule',
        'rule_id'
    ),
    $installer->getTable('enterprise_targetrule/customersegment'),
    'rule_id',
    $installer->getTable('enterprise_targetrule/rule'),
    'rule_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/customersegment',
        'segment_id',
        'enterprise_customersegment/segment',
        'segment_id'
    ),
    $installer->getTable('enterprise_targetrule/customersegment'),
    'segment_id',
    $installer->getTable('enterprise_customersegment/segment'),
    'segment_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/index',
        'customer_group_id',
        'customer/customer_group',
        'customer_group_id'
    ),
    $installer->getTable('enterprise_targetrule/index'),
    'customer_group_id',
    $installer->getTable('customer/customer_group'),
    'customer_group_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/index',
        'entity_id',
        'catalog/product',
        'entity_id'
    ),
    $installer->getTable('enterprise_targetrule/index'),
    'entity_id',
    $installer->getTable('catalog/product'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/index',
        'store_id',
        'core/store',
        'store_id'
    ),
    $installer->getTable('enterprise_targetrule/index'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/index_crosssell',
        'customer_group_id',
        'customer/customer_group',
        'customer_group_id'
    ),
    $installer->getTable('enterprise_targetrule/index_crosssell'),
    'customer_group_id',
    $installer->getTable('customer/customer_group'),
    'customer_group_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/index_crosssell',
        'entity_id',
        'catalog/product',
        'entity_id'
    ),
    $installer->getTable('enterprise_targetrule/index_crosssell'),
    'entity_id',
    $installer->getTable('catalog/product'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/index_crosssell',
        'store_id',
        'core/store',
        'store_id'
    ),
    $installer->getTable('enterprise_targetrule/index_crosssell'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/index_related',
        'customer_group_id',
        'customer/customer_group',
        'customer_group_id'
    ),
    $installer->getTable('enterprise_targetrule/index_related'),
    'customer_group_id',
    $installer->getTable('customer/customer_group'),
    'customer_group_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/index_related',
        'entity_id',
        'catalog/product',
        'entity_id'
    ),
    $installer->getTable('enterprise_targetrule/index_related'),
    'entity_id',
    $installer->getTable('catalog/product'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/index_related',
        'store_id',
        'core/store',
        'store_id'
    ),
    $installer->getTable('enterprise_targetrule/index_related'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/index_upsell',
        'customer_group_id',
        'customer/customer_group',
        'customer_group_id'
    ),
    $installer->getTable('enterprise_targetrule/index_upsell'),
    'customer_group_id',
    $installer->getTable('customer/customer_group'),
    'customer_group_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/index_upsell',
        'entity_id',
        'catalog/product',
        'entity_id'
    ),
    $installer->getTable('enterprise_targetrule/index_upsell'),
    'entity_id',
    $installer->getTable('catalog/product'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/index_upsell',
        'store_id',
        'core/store',
        'store_id'
    ),
    $installer->getTable('enterprise_targetrule/index_upsell'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/product',
        'store_id',
        'core/store',
        'store_id'
    ),
    $installer->getTable('enterprise_targetrule/product'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/product',
        'product_id',
        'catalog/product',
        'entity_id'
    ),
    $installer->getTable('enterprise_targetrule/product'),
    'product_id',
    $installer->getTable('catalog/product'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_targetrule/product',
        'rule_id',
        'enterprise_targetrule/rule',
        'rule_id'
    ),
    $installer->getTable('enterprise_targetrule/product'),
    'rule_id',
    $installer->getTable('enterprise_targetrule/rule'),
    'rule_id'
);

$installer->endSetup();
