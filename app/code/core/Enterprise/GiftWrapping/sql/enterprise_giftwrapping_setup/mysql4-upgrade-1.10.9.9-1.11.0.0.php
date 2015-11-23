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
 * @package     Enterprise_GiftWrapping
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_GiftWrapping_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftwrapping/attribute'),
    'FK_EE_GW_ATTR_WRAPPING_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftwrapping/attribute'),
    'FK_EE_GW_STORE_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftwrapping/website'),
    'FK_EE_GW_WEBSITE_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftwrapping/website'),
    'FK_EE_GW_WRAPPING_ID'
);


/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftwrapping/wrapping'),
    'IDX_EE_GW_STATUS'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftwrapping/attribute'),
    'IDX_EE_GW_STORE_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftwrapping/website'),
    'IDX_EE_GW_WEBSITE_ID'
);


/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_giftwrapping/wrapping') => array(
        'columns' => array(
            'wrapping_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Wrapping Id'
            ),
            'status' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Status'
            ),
            'base_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'comment'   => 'Base Price'
            ),
            'image' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Image'
            )
        ),
        'comment' => 'Enterprise Gift Wrapping Table'
    ),
    $installer->getTable('enterprise_giftwrapping/attribute') => array(
        'columns' => array(
            'wrapping_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Wrapping Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Store Id'
            ),
            'design' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false,
                'comment'   => 'Design'
            )
        ),
        'comment' => 'Enterprise Gift Wrapping Attribute Table'
    ),
    $installer->getTable('enterprise_giftwrapping/website') => array(
        'columns' => array(
            'wrapping_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Wrapping Id'
            ),
            'website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Website Id'
            )
        ),
        'comment' => 'Enterprise Gift Wrapping Website Table'
    ),
    $installer->getTable('sales/creditmemo') => array(
        'columns' => array(
            'gw_base_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Price'
            ),
            'gw_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Price'
            ),
            'gw_items_base_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Base Price'
            ),
            'gw_items_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Price'
            ),
            'gw_base_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Tax Amount'
            ),
            'gw_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Tax Amount'
            ),
            'gw_items_base_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Base Tax Amount'
            ),
            'gw_items_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Tax Amount'
            )
        )
    ),
    $installer->getTable('sales/invoice') => array(
        'columns' => array(
            'gw_base_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Price'
            ),
            'gw_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Price'
            ),
            'gw_items_base_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Base Price'
            ),
            'gw_items_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Price'
            ),
            'gw_base_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Tax Amount'
            ),
            'gw_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Tax Amount'
            ),
            'gw_items_base_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Base Tax Amount'
            ),
            'gw_items_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Tax Amount'
            )
        )
    ),
    $installer->getTable('sales/order_item') => array(
        'columns' => array(
            'gw_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Gw Id'
            ),
            'gw_base_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Price'
            ),
            'gw_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Price'
            ),
            'gw_base_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Tax Amount'
            ),
            'gw_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Tax Amount'
            ),
            'gw_base_price_invoiced' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Price Invoiced'
            ),
            'gw_price_invoiced' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Price Invoiced'
            ),
            'gw_base_tax_amount_invoiced' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Tax Amount Invoiced'
            ),
            'gw_tax_amount_invoiced' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Tax Amount Invoiced'
            ),
            'gw_base_price_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Price Refunded'
            ),
            'gw_price_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Price Refunded'
            ),
            'gw_base_tax_amount_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Tax Amount Refunded'
            ),
            'gw_tax_amount_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Tax Amount Refunded'
            )
        )
    ),
    $installer->getTable('sales/order') => array(
        'columns' => array(
            'gw_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Gw Id'
            ),
            'gw_allow_gift_receipt' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Gw Allow Gift Receipt'
            ),
            'gw_base_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Price'
            ),
            'gw_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Price'
            ),
            'gw_items_base_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Base Price'
            ),
            'gw_items_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Price'
            ),
            'gw_base_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Tax Amount'
            ),
            'gw_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Tax Amount'
            ),
            'gw_items_base_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Base Tax Amount'
            ),
            'gw_items_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Tax Amount'
            ),
            'gw_base_price_invoiced' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Price Invoiced'
            ),
            'gw_price_invoiced' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Price Invoiced'
            ),
            'gw_items_base_price_invoiced' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Base Price Invoiced'
            ),
            'gw_items_price_invoiced' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Price Invoiced'
            ),
            'gw_base_tax_amount_invoiced' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Tax Amount Invoiced'
            ),
            'gw_tax_amount_invoiced' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Tax Amount Invoiced'
            ),
            'gw_base_price_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Price Refunded'
            ),
            'gw_price_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Price Refunded'
            ),
            'gw_items_base_price_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Base Price Refunded'
            ),
            'gw_items_price_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Price Refunded'
            ),
            'gw_base_tax_amount_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Tax Amount Refunded'
            ),
            'gw_tax_amount_refunded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Tax Amount Refunded'
            )
        )
    ),
    $installer->getTable('sales/quote') => array(
        'columns' => array(
            'gw_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Gw Id'
            ),
            'gw_allow_gift_receipt' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Gw Allow Gift Receipt'
            ),
            'gw_base_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Price'
            ),
            'gw_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Price'
            ),
            'gw_items_base_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Base Price'
            ),
            'gw_items_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Price'
            ),
            'gw_base_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Tax Amount'
            ),
            'gw_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Tax Amount'
            ),
            'gw_items_base_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Base Tax Amount'
            ),
            'gw_items_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Tax Amount'
            )
        )
    ),
    $installer->getTable('sales/quote_address') => array(
        'columns' => array(
            'gw_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Gw Id'
            ),
            'gw_allow_gift_receipt' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Gw Allow Gift Receipt'
            ),
            'gw_base_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Price'
            ),
            'gw_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Price'
            ),
            'gw_items_base_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Base Price'
            ),
            'gw_items_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Price'
            ),
            'gw_base_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Tax Amount'
            ),
            'gw_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Tax Amount'
            ),
            'gw_items_base_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Base Tax Amount'
            ),
            'gw_items_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Items Tax Amount'
            )
        )
    ),
    $installer->getTable('sales/quote_address_item') => array(
        'columns' => array(
            'gw_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Gw Id'
            ),
            'gw_base_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Price'
            ),
            'gw_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Price'
            ),
            'gw_base_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Tax Amount'
            ),
            'gw_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Tax Amount'
            )
        )
    ),
    $installer->getTable('sales/quote_item') => array(
        'columns' => array(
            'gw_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Gw Id'
            ),
            'gw_base_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Price'
            ),
            'gw_price' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Price'
            ),
            'gw_base_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Base Tax Amount'
            ),
            'gw_tax_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Gw Tax Amount'
            )
        )
    )
);

$installer->getConnection()->modifyTables($tables);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/creditmemo'),
    'gw_printed_card_base_price',
    'gw_card_base_price',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Base Price'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/creditmemo'),
    'gw_printed_card_price',
    'gw_card_price',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Price'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/creditmemo'),
    'gw_printed_card_base_tax_amount',
    'gw_card_base_tax_amount',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Base Tax Amount'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/creditmemo'),
    'gw_printed_card_tax_amount',
    'gw_card_tax_amount',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Tax Amount'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/invoice'),
    'gw_printed_card_base_price',
    'gw_card_base_price',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Base Price'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/invoice'),
    'gw_printed_card_price',
    'gw_card_price',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Price'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/invoice'),
    'gw_printed_card_base_tax_amount',
    'gw_card_base_tax_amount',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Base Tax Amount'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/invoice'),
    'gw_printed_card_tax_amount',
    'gw_card_tax_amount',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Tax Amount'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_add_printed_card',
    'gw_add_card',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment'   => 'Gw Add Card'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_printed_card_base_price',
    'gw_card_base_price',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Base Price'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_printed_card_price',
    'gw_card_price',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Price'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_printed_card_base_tax_amount',
    'gw_card_base_tax_amount',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Base Tax Amount'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_printed_card_tax_amount',
    'gw_card_tax_amount',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Tax Amount'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_printed_card_base_price_invoiced',
    'gw_card_base_price_invoiced',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Base Price Invoiced'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_printed_card_price_invoiced',
    'gw_card_price_invoiced',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Price Invoiced'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_items_base_tax_amount_invoiced',
    'gw_items_base_tax_invoiced',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Items Base Tax Invoiced'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_items_tax_amount_invoiced',
    'gw_items_tax_invoiced',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Items Tax Invoiced'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_printed_card_base_tax_amount_invoiced',
    'gw_card_base_tax_invoiced',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Base Tax Invoiced'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_printed_card_tax_amount_invoiced',
    'gw_card_tax_invoiced',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Tax Invoiced'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_printed_card_base_price_refunded',
    'gw_card_base_price_refunded',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Base Price Refunded'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_printed_card_price_refunded',
    'gw_card_price_refunded',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Price Refunded'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_items_base_tax_amount_refunded',
    'gw_items_base_tax_refunded',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Items Base Tax Refunded'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_items_tax_amount_refunded',
    'gw_items_tax_refunded',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Items Tax Refunded'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_printed_card_base_tax_amount_refunded',
    'gw_card_base_tax_refunded',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Base Tax Refunded'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/order'),
    'gw_printed_card_tax_amount_refunded',
    'gw_card_tax_refunded',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Tax Refunded'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/quote'),
    'gw_add_printed_card',
    'gw_add_card',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment'   => 'Gw Add Card'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/quote'),
    'gw_printed_card_base_price',
    'gw_card_base_price',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Base Price'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/quote'),
    'gw_printed_card_price',
    'gw_card_price',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Price'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/quote'),
    'gw_printed_card_base_tax_amount',
    'gw_card_base_tax_amount',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Base Tax Amount'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/quote'),
    'gw_printed_card_tax_amount',
    'gw_card_tax_amount',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Tax Amount'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/quote_address'),
    'gw_add_printed_card',
    'gw_add_card',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment'   => 'Gw Add Card'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/quote_address'),
    'gw_printed_card_base_price',
    'gw_card_base_price',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Base Price'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/quote_address'),
    'gw_printed_card_price',
    'gw_card_price',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Price'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/quote_address'),
    'gw_printed_card_base_tax_amount',
    'gw_card_base_tax_amount',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Base Tax Amount'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('sales/quote_address'),
    'gw_printed_card_tax_amount',
    'gw_card_tax_amount',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Gw Card Tax Amount'
    )
);

/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftwrapping/wrapping'),
    $installer->getIdxName('enterprise_giftwrapping/wrapping', array('status')),
    array('status')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftwrapping/attribute'),
    $installer->getIdxName('enterprise_giftwrapping/attribute', array('store_id')),
    array('store_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftwrapping/website'),
    $installer->getIdxName('enterprise_giftwrapping/website', array('website_id')),
    array('website_id')
);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftwrapping/attribute',
        'wrapping_id',
        'enterprise_giftwrapping/wrapping',
        'wrapping_id'
    ),
    $installer->getTable('enterprise_giftwrapping/attribute'),
    'wrapping_id',
    $installer->getTable('enterprise_giftwrapping/wrapping'),
    'wrapping_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftwrapping/attribute',
        'store_id',
        'core/store',
        'store_id'
    ),
    $installer->getTable('enterprise_giftwrapping/attribute'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftwrapping/website',
        'wrapping_id',
        'enterprise_giftwrapping/wrapping',
        'wrapping_id'
    ),
    $installer->getTable('enterprise_giftwrapping/website'),
    'wrapping_id',
    $installer->getTable('enterprise_giftwrapping/wrapping'),
    'wrapping_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftwrapping/website',
        'website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_giftwrapping/website'),
    'website_id',
    $installer->getTable('core/website'),
    'website_id'
);

$installer->endSetup();
