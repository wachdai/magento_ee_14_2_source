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

/* @var $installer Enterprise_CatalogEvent_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_catalogevent/event'),
    sprintf('FK_%s_CATEGORY', $installer->getTable('enterprise_catalogevent/event'))
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_catalogevent/event_image'),
    'FK_ENTERPRISE_CATALOGEVENT_EVENT_IMAGE_EVENT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_catalogevent/event_image'),
    'FK_ENTERPRISE_CATALOGEVENT_EVENT_IMAGE_STORE'
);


/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_catalogevent/event'),
    'CATEGORY_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_catalogevent/event'),
    'SORT_ORDER'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_catalogevent/event_image'),
    'SCOPE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_catalogevent/event_image'),
    'FK_ENTERPRISE_CATALOGEVENT_EVENT_IMAGE_STORE'
);


/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_catalogevent/event') => array(
        'columns' => array(
            'event_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Event Id'
            ),
            'category_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Category Id'
            ),
            'date_start' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Date Start'
            ),
            'date_end' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Date End'
            ),
            'display_state' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'default'   => '0',
                'comment'   => 'Display State'
            ),
            'sort_order' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Sort Order'
            )
        ),
        'comment' => 'Enterprise Catalogevent Event'
    ),
    $installer->getTable('enterprise_catalogevent/event_image') => array(
        'columns' => array(
            'event_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Event Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Store Id'
            ),
            'image' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false,
                'comment'   => 'Image'
            )
        ),
        'comment' => 'Enterprise Catalogevent Event Image'
    ),
    $installer->getTable('sales/order_item') => array(
        'columns' => array(
            'event_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Event Id'
            )
        )
    ),
    $installer->getTable('sales/quote_item') => array(
        'columns' => array(
            'event_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Event Id'
            )
        )
    )
);

$installer->getConnection()->modifyTables($tables);


/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_catalogevent/event'),
    $installer->getIdxName('enterprise_catalogevent/event', array('category_id')),
    array('category_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_catalogevent/event'),
    $installer->getIdxName('enterprise_catalogevent/event', array('date_start', 'date_end')),
    array('date_start', 'date_end')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_catalogevent/event_image'),
    $installer->getIdxName('enterprise_catalogevent/event_image', array('store_id')),
    array('store_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_catalogevent/event_image'),
    $installer->getIdxName('enterprise_catalogevent/event_image', array('event_id', 'store_id')),
    array('event_id', 'store_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY
);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_catalogevent/event',
        'category_id',
        'catalog/category',
        'entity_id'
    ),
    $installer->getTable('enterprise_catalogevent/event'),
    'category_id',
    $installer->getTable('catalog/category'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_catalogevent/event_image',
        'event_id',
        'enterprise_catalogevent/event',
        'event_id'
    ),
    $installer->getTable('enterprise_catalogevent/event_image'),
    'event_id',
    $installer->getTable('enterprise_catalogevent/event'),
    'event_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_catalogevent/event_image',
        'store_id',
        'core/store',
        'store_id'
    ),
    $installer->getTable('enterprise_catalogevent/event_image'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$installer->endSetup();
