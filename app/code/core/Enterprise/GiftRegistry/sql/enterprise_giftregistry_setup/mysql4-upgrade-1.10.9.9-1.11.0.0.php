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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_GiftRegistry_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftregistry_data'),
    'FK_EE_GR_DATA_ENTITY'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftregistry/entity'),
    'FK_EE_GR_ENTITY_CUSTOMER'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftregistry/entity'),
    'FK_EE_GR_ENTITY_TYPE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftregistry/entity'),
    'FK_EE_GR_ENTITY_WEBSITE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftregistry/item'),
    'FK_EE_GR_ITEM_ENTITY'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftregistry/item'),
    'FK_EE_GR_ITEM_PRODUCT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftregistry/item_option'),
    'FK_GIFTREGISTRY_ITEM_OPTION_ITEM_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftregistry/label'),
    'FK_EE_GR_LABEL_STORE_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftregistry/label'),
    'FK_EE_GR_LABEL_TYPE_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftregistry/person'),
    'FK_EE_GR_PERSON_ENTITY'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftregistry/info'),
    'FK_EE_GR_INFO_STORE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftregistry/info'),
    'FK_EE_GR_INFO_TYPE'
);


/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftregistry/entity'),
    'IDX_EE_GR_ENTITY_CUSTOMER'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftregistry/entity'),
    'IDX_EE_GR_ENTITY_WEBSITE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftregistry/entity'),
    'IDX_EE_GR_ENTITY_TYPE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftregistry/item'),
    'IDX_EE_GR_ITEM_ENTITY'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftregistry/item'),
    'IDX_EE_GR_ITEM_PRODUCT'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftregistry/item_option'),
    'FK_GIFTREGISTRY_ITEM_OPTION_ITEM_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftregistry/label'),
    'IDX_EE_GR_LABEL_TYPE_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftregistry/label'),
    'IDX_EE_GR_LABEL_STORE_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftregistry/person'),
    'IDX_EE_GR_PERSON_ENTITY'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftregistry/info'),
    'IDX_EE_GR_INFO_STORE'
);

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_giftregistry/type') => array(
        'columns' => array(
            'type_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Type Id'
            ),
            'code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 15,
                'comment'   => 'Code'
            ),
            'meta_xml' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_BLOB,
                'length'    => '64K',
                'comment'   => 'Meta Xml'
            )
        ),
        'comment' => 'Enterprise Gift Registry Type Table'
    ),
    $installer->getTable('enterprise_giftregistry/info') => array(
        'columns' => array(
            'type_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
                'comment'   => 'Type Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
                'comment'   => 'Store Id'
            ),
            'label' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Label'
            ),
            'is_listed' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Is Listed'
            ),
            'sort_order' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Sort Order'
            )
        ),
        'comment' => 'Enterprise Gift Registry Info Table'
    ),
    $installer->getTable('enterprise_giftregistry/label') => array(
        'columns' => array(
            'type_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
                'comment'   => 'Type Id'
            ),
            'attribute_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 32,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Attribute Code'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
                'comment'   => 'Store Id'
            ),
            'option_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 32,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Option Code'
            ),
            'label' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Label'
            )
        ),
        'comment' => 'Enterprise Gift Registry Label Table'
    ),
    $installer->getTable('enterprise_giftregistry/entity') => array(
        'columns' => array(
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Entity Id'
            ),
            'type_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Type Id'
            ),
            'customer_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Customer Id'
            ),
            'website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Website Id'
            ),
            'is_public' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '1',
                'comment'   => 'Is Public'
            ),
            'url_key' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 100,
                'comment'   => 'Url Key'
            ),
            'title' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Title'
            ),
            'message' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'nullable'  => false,
                'comment'   => 'Message'
            ),
            'shipping_address' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_BLOB,
                'length'    => '64K',
                'comment'   => 'Shipping Address'
            ),
            'custom_values' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Custom Values'
            ),
            'is_active' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Is Active'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Created At'
            )
        ),
        'comment' => 'Enterprise Gift Registry Entity Table'
    ),
    $installer->getTable('enterprise_giftregistry/item') => array(
        'columns' => array(
            'item_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Item Id'
            ),
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Entity Id'
            ),
            'product_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Product Id'
            ),
            'qty' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Qty'
            ),
            'qty_fulfilled' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Qty Fulfilled'
            ),
            'note' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Note'
            ),
            'added_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Added At'
            ),
            'custom_options' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Custom Options'
            )
        ),
        'comment' => 'Enterprise Gift Registry Item Table'
    ),
    $installer->getTable('enterprise_giftregistry/item_option') => array(
        'columns' => array(
            'option_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Option Id'
            ),
            'item_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Item Id'
            ),
            'product_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Product Id'
            ),
            'code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false,
                'comment'   => 'Code'
            ),
            'value' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'nullable'  => false,
                'comment'   => 'Value'
            )
        ),
        'comment' => 'Enterprise Gift Registry Item Option Table'
    ),
    $installer->getTable('enterprise_giftregistry/person') => array(
        'columns' => array(
            'person_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Person Id'
            ),
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Entity Id'
            ),
            'firstname' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 100,
                'comment'   => 'Firstname'
            ),
            'lastname' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 100,
                'comment'   => 'Lastname'
            ),
            'email' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 150,
                'comment'   => 'Email'
            ),
            'role' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 32,
                'comment'   => 'Role'
            ),
            'custom_values' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'nullable'  => false,
                'comment'   => 'Custom Values'
            )
        ),
        'comment' => 'Enterprise Gift Registry Person Table'
    ),
    $installer->getTable('enterprise_giftregistry/data') => array(
        'columns' => array(
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
                'comment'   => 'Entity Id'
            ),
            'event_date' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
                'comment'   => 'Event Date'
            ),
            'event_country' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'comment'   => 'Event Country'
            ),
            'event_country_region' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Event Country Region'
            ),
            'event_country_region_text' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 30,
                'comment'   => 'Event Country Region Text'
            ),
            'event_location' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Event Location'
            )
        ),
        'comment' => 'Enterprise Gift Registry Data Table'
    ),
    $installer->getTable('sales/order_address') => array(
        'columns' => array(
            'giftregistry_item_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Giftregistry Item Id'
            )
        )
    ),
    $installer->getTable('sales/order_item') => array(
        'columns' => array(
            'giftregistry_item_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Giftregistry Item Id'
            )
        )
    ),
    $installer->getTable('sales/quote_address') => array(
        'columns' => array(
            'giftregistry_item_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Giftregistry Item Id'
            )
        )
    ),
    $installer->getTable('sales/quote_item') => array(
        'columns' => array(
            'giftregistry_item_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Giftregistry Item Id'
            )
        )
    )
);

$installer->getConnection()->modifyTables($tables);


/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftregistry/entity'),
    $installer->getIdxName('enterprise_giftregistry/entity', array('customer_id')),
    array('customer_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftregistry/entity'),
    $installer->getIdxName('enterprise_giftregistry/entity', array('website_id')),
    array('website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftregistry/entity'),
    $installer->getIdxName('enterprise_giftregistry/entity', array('type_id')),
    array('type_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftregistry/item'),
    $installer->getIdxName('enterprise_giftregistry/item', array('entity_id')),
    array('entity_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftregistry/item'),
    $installer->getIdxName('enterprise_giftregistry/item', array('product_id')),
    array('product_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftregistry/label'),
    $installer->getIdxName('enterprise_giftregistry/label', array('type_id')),
    array('type_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftregistry/label'),
    $installer->getIdxName('enterprise_giftregistry/label', array('store_id')),
    array('store_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftregistry/person'),
    $installer->getIdxName('enterprise_giftregistry/person', array('entity_id')),
    array('entity_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftregistry/info'),
    $installer->getIdxName('enterprise_giftregistry/info', array('store_id')),
    array('store_id')
);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftregistry/data',
        'entity_id',
        'enterprise_giftregistry/entity',
        'entity_id'
    ),
    $installer->getTable('enterprise_giftregistry/data'),
    'entity_id',
    $installer->getTable('enterprise_giftregistry/entity'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftregistry/entity',
        'type_id',
        'enterprise_giftregistry/type',
        'type_id'
    ),
    $installer->getTable('enterprise_giftregistry/entity'),
    'type_id',
    $installer->getTable('enterprise_giftregistry/type'),
    'type_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftregistry/entity',
        'customer_id',
        'customer/entity',
        'entity_id'
    ),
    $installer->getTable('enterprise_giftregistry/entity'),
    'customer_id',
    $installer->getTable('customer/entity'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftregistry/entity',
        'website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_giftregistry/entity'),
    'website_id',
    $installer->getTable('core/website'),
    'website_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftregistry/item',
        'entity_id',
        'enterprise_giftregistry/entity',
        'entity_id'
    ),
    $installer->getTable('enterprise_giftregistry/item'),
    'entity_id',
    $installer->getTable('enterprise_giftregistry/entity'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftregistry/item',
        'product_id',
        'catalog/product',
        'entity_id'
    ),
    $installer->getTable('enterprise_giftregistry/item'),
    'product_id',
    $installer->getTable('catalog/product'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftregistry/item_option',
        'item_id',
        'enterprise_giftregistry/item',
        'item_id'
    ),
    $installer->getTable('enterprise_giftregistry/item_option'),
    'item_id',
    $installer->getTable('enterprise_giftregistry/item'),
    'item_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftregistry/label',
        'type_id',
        'enterprise_giftregistry/type',
        'type_id'
    ),
    $installer->getTable('enterprise_giftregistry/label'),
    'type_id',
    $installer->getTable('enterprise_giftregistry/type'),
    'type_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftregistry/label',
        'store_id',
        'core/store',
        'store_id'
    ),
    $installer->getTable('enterprise_giftregistry/label'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftregistry/person',
        'entity_id',
        'enterprise_giftregistry/entity',
        'entity_id'
    ),
    $installer->getTable('enterprise_giftregistry/person'),
    'entity_id',
    $installer->getTable('enterprise_giftregistry/entity'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftregistry/info',
        'type_id',
        'enterprise_giftregistry/type',
        'type_id'
    ),
    $installer->getTable('enterprise_giftregistry/info'),
    'type_id',
    $installer->getTable('enterprise_giftregistry/type'),
    'type_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftregistry/info',
        'store_id',
        'core/store',
        'store_id'
    ),
    $installer->getTable('enterprise_giftregistry/info'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$installer->endSetup();
