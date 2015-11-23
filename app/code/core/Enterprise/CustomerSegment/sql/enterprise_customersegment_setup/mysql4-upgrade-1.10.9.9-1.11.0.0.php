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
 * @package     Enterprise_CustomerSegment
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_CustomerSegment_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_customersegment/customer'),
    'FK_EE_CUSTOMER_SEGMENT_WEBSIE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_customersegment/customer'),
    'FK_ENTERPRISE_CUSTOMERSEGMENT_CUSTOMER_CUSTOMER'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_customersegment/customer'),
    'FK_ENTERPRISE_CUSTOMERSEGMENT_CUSTOMER_SEGMENT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_customersegment/event'),
    'FK_ENTERPRISE_CUSTOMERSEGMENT_EVENT_SEGMENT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_customersegment/website'),
    'FK_EE_SEGMENT_SEFMENT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_customersegment/website'),
    'FK_EE_SEGMENT_WEBSITE'
);


/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_customersegment/customer'),
    'UNQ_CUSTOMER'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_customersegment/customer'),
    'FK_EE_CUSTOMER_SEGMENT_WEBSIE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_customersegment/customer'),
    'FK_CUSTOMER'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_customersegment/event'),
    'IDX_ENTERPRISE_CUSTOMERSEGMENT_EVENT_EVENT'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_customersegment/event'),
    'FK_ENTERPRISE_CUSTOMERSEGMENT_EVENT_SEGMENT'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_customersegment/website'),
    'FK_EE_SEGMENT_WEBSITE'
);

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_customersegment/segment') => array(
        'columns' => array(
            'segment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Segment Id'
            ),
            'name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Name'
            ),
            'description' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Description'
            ),
            'is_active' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Is Active'
            ),
            'conditions_serialized' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '2M',
                'comment'   => 'Conditions Serialized'
            ),
            'processing_frequency' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'nullable'  => false,
                'comment'   => 'Processing Frequency'
            ),
            'condition_sql' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '2M',
                'comment'   => 'Condition Sql'
            )
        ),
        'comment' => 'Enterprise Customersegment Segment'
    ),
    $installer->getTable('enterprise_customersegment/event') => array(
        'columns' => array(
            'segment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Segment Id'
            ),
            'event' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Event'
            )
        ),
        'comment' => 'Enterprise Customersegment Event'
    ),
    $installer->getTable('enterprise_customersegment/customer') => array(
        'columns' => array(
            'segment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Segment Id'
            ),
            'customer_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Customer Id'
            ),
            'added_date' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'nullable'  => false,
                'comment'   => 'Added Date'
            ),
            'updated_date' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'nullable'  => false,
                'comment'   => 'Updated Date'
            ),
            'website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Website Id'
            )
        ),
        'comment' => 'Enterprise Customersegment Customer'
    ),
    $installer->getTable('enterprise_customersegment/website') => array(
        'columns' => array(
            'segment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Segment Id'
            ),
            'website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Website Id'
            )
        ),
        'comment' => 'Enterprise Customersegment Website'
    ),
    $installer->getTable('customer/eav_attribute') => array(
        'columns' => array(
            'is_used_for_customer_segment' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Customer Segment'
            )
        )
    )
);

$installer->getConnection()->modifyTables($tables);

$installer->getConnection()->dropColumn(
    $installer->getTable('catalog/eav_attribute'),
    'is_used_for_customer_segment'
);


/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_customersegment/customer'),
    'PRIMARY',
    array('segment_id', 'customer_id', 'website_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_customersegment/customer'),
    $installer->getIdxName(
        'enterprise_customersegment/customer',
        array('segment_id', 'website_id', 'customer_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('segment_id', 'website_id', 'customer_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_customersegment/customer'),
    $installer->getIdxName('enterprise_customersegment/customer', array('website_id')),
    array('website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_customersegment/customer'),
    $installer->getIdxName('enterprise_customersegment/customer', array('customer_id')),
    array('customer_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_customersegment/event'),
    $installer->getIdxName('enterprise_customersegment/event', array('event')),
    array('event')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_customersegment/event'),
    $installer->getIdxName('enterprise_customersegment/event', array('segment_id')),
    array('segment_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_customersegment/website'),
    $installer->getIdxName('enterprise_customersegment/website', array('website_id')),
    array('website_id')
);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_customersegment/customer',
        'website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_customersegment/customer'),
    'website_id',
    $installer->getTable('core/website'),
    'website_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_customersegment/customer',
        'customer_id',
        'customer/entity',
        'entity_id'
    ),
    $installer->getTable('enterprise_customersegment/customer'),
    'customer_id',
    $installer->getTable('customer/entity'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_customersegment/customer',
        'segment_id',
        'enterprise_customersegment/segment',
        'segment_id'
    ),
    $installer->getTable('enterprise_customersegment/customer'),
    'segment_id',
    $installer->getTable('enterprise_customersegment/segment'),
    'segment_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_customersegment/event',
        'segment_id',
        'enterprise_customersegment/segment',
        'segment_id'
    ),
    $installer->getTable('enterprise_customersegment/event'),
    'segment_id',
    $installer->getTable('enterprise_customersegment/segment'),
    'segment_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_customersegment/website',
        'segment_id',
        'enterprise_customersegment/segment',
        'segment_id'
    ),
    $installer->getTable('enterprise_customersegment/website'),
    'segment_id',
    $installer->getTable('enterprise_customersegment/segment'),
    'segment_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_customersegment/website',
        'website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_customersegment/website'),
    'website_id',
    $installer->getTable('core/website'),
    'website_id'
);

$installer->endSetup();
