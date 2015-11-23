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
 * @package     Enterprise_GiftCard
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$connection = $installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftcard/amount'),
    'FK_GIFTCARD_AMOUNT_ATTRIBUTE_ID'
);

$connection = $installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftcard/amount'),
    'FK_GIFTCARD_AMOUNT_PRODUCT_ENTITY'
);

$connection = $installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftcard/amount'),
    'FK_GIFTCARD_AMOUNT_WEBSITE'
);


/**
 * Drop indexes
 */
$connection = $installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftcard/amount'),
    'FK_GIFTCARD_AMOUNT_PRODUCT_ENTITY'
);

$connection = $installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftcard/amount'),
    'FK_GIFTCARD_AMOUNT_WEBSITE'
);

$connection = $installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftcard/amount'),
    'FK_GIFTCARD_AMOUNT_ATTRIBUTE_ID'
);


/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_giftcard/amount') => array(
        'columns' => array(
            'value_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Value Id'
            ),
            'website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Website Id'
            ),
            'value' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Value'
            ),
            'entity_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Entity Id'
            ),
            'entity_type_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Entity Type Id'
            ),
            'attribute_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Attribute Id'
            )
        ),
        'comment' => 'Enterprise Giftcard Amount'
    ),
    $installer->getTable('sales/creditmemo') => array(
        'columns' => array(
            'base_gift_cards_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Gift Cards Amount'
            ),
            'gift_cards_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gift Cards Amount'
            )
        )
    ),
    $installer->getTable('sales/invoice') => array(
        'columns' => array(
            'base_gift_cards_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Gift Cards Amount'
            ),
            'gift_cards_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gift Cards Amount'
            )
        )
    ),
    $installer->getTable('sales/order') => array(
        'columns' => array(
            'gift_cards' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Gift Cards'
            ),
            'base_gift_cards_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Gift Cards Amount'
            ),
            'gift_cards_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gift Cards Amount'
            ),
            'base_gift_cards_invoiced' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Gift Cards Invoiced'
            ),
            'gift_cards_invoiced' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gift Cards Invoiced'
            ),
            'base_gift_cards_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Gift Cards Refunded'
            ),
            'gift_cards_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gift Cards Refunded'
            )
        )
    ),
    $installer->getTable('sales/quote') => array(
        'columns' => array(
            'gift_cards' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Gift Cards'
            ),
            'gift_cards_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gift Cards Amount'
            ),
            'base_gift_cards_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Gift Cards Amount'
            ),
            'gift_cards_amount_used' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gift Cards Amount Used'
            ),
            'base_gift_cards_amount_used' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Gift Cards Amount Used'
            )
        )
    ),
    $installer->getTable('sales/quote_address') => array(
        'columns' => array(
            'gift_cards' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Gift Cards'
            ),
            'gift_cards_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gift Cards Amount'
            ),
            'base_gift_cards_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Gift Cards Amount'
            ),
            'used_gift_cards' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Used Gift Cards'
            )
        )
    )
);

$installer->getConnection()->modifyTables($tables);


/**
 * Add indexes
 */
$connection = $installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftcard/amount'),
    $installer->getIdxName('enterprise_giftcard/amount', array('entity_id')),
    array('entity_id')
);

$connection = $installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftcard/amount'),
    $installer->getIdxName('enterprise_giftcard/amount', array('website_id')),
    array('website_id')
);

$connection = $installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftcard/amount'),
    $installer->getIdxName('enterprise_giftcard/amount', array('attribute_id')),
    array('attribute_id')
);


/**
 * Add foreign keys
 */
$connection = $installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftcard/amount',
        'entity_id',
        'catalog/product',
        'entity_id'
    ),
    $installer->getTable('enterprise_giftcard/amount'),
    'entity_id',
    $installer->getTable('catalog/product'),
    'entity_id'
);

$connection = $installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftcard/amount',
        'website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_giftcard/amount'),
    'website_id',
    $installer->getTable('core/website'),
    'website_id'
);

$connection = $installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftcard/amount',
        'attribute_id',
        'eav/attribute',
        'attribute_id'
    ),
    $installer->getTable('enterprise_giftcard/amount'),
    'attribute_id',
    $installer->getTable('eav/attribute'),
    'attribute_id'
);

$installer->endSetup();
