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

$installer = $this;
/* @var $installer Enterprise_GiftWrapping_Model_Resource_Setup */

/**
 * Create table 'enterprise_giftwrapping/wrapping'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftwrapping/wrapping'))
    ->addColumn('wrapping_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Wrapping Id')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Status')
    ->addColumn('base_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        ), 'Base Price')
    ->addColumn('image', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Image')
    ->addIndex($installer->getIdxName('enterprise_giftwrapping/wrapping', array('status')),
        array('status'))
    ->setComment('Enterprise Gift Wrapping Table');
$installer->getConnection()->createTable($table);


/**
 * Create table 'enterprise_giftwrapping/attribute'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftwrapping/attribute'))
    ->addColumn('wrapping_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Wrapping Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Store Id')
    ->addColumn('design', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Design')
    ->addIndex($installer->getIdxName('enterprise_giftwrapping/attribute', array('store_id')),
        array('store_id'))
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_giftwrapping/attribute',
            'wrapping_id',
            'enterprise_giftwrapping/wrapping',
            'wrapping_id'
        ),
        'wrapping_id', $installer->getTable('enterprise_giftwrapping/wrapping'), 'wrapping_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_giftwrapping/attribute',
            'store_id',
            'core/store',
            'store_id'
        ),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Gift Wrapping Attribute Table');
$installer->getConnection()->createTable($table);


/**
 * Create table 'enterprise_giftwrapping/website'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftwrapping/website'))
    ->addColumn('wrapping_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Wrapping Id')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Website Id')
    ->addIndex($installer->getIdxName('enterprise_giftwrapping/website', array('website_id')),
        array('website_id'))
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_giftwrapping/website',
            'wrapping_id',
            'enterprise_giftwrapping/wrapping',
            'wrapping_id'
        ),
        'wrapping_id', $installer->getTable('enterprise_giftwrapping/wrapping'), 'wrapping_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_giftwrapping/website',
            'website_id',
            'core/website',
            'website_id'
        ),
        'website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Gift Wrapping Website Table');
$installer->getConnection()->createTable($table);

/**
 * Add gift wrapping attributes for sales entities
 */
$entityAttributesCodes = array(
    'gw_id' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'gw_allow_gift_receipt' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'gw_add_card' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'gw_base_price' => 'decimal',
    'gw_price' => 'decimal',
    'gw_items_base_price' => 'decimal',
    'gw_items_price' => 'decimal',
    'gw_card_base_price' => 'decimal',
    'gw_card_price' => 'decimal',
    'gw_base_tax_amount' => 'decimal',
    'gw_tax_amount' => 'decimal',
    'gw_items_base_tax_amount' => 'decimal',
    'gw_items_tax_amount' => 'decimal',
    'gw_card_base_tax_amount' => 'decimal',
    'gw_card_tax_amount' => 'decimal'
);
foreach ($entityAttributesCodes as $code => $type) {
    $installer->addAttribute('quote', $code, array('type' => $type, 'visible' => false));
    $installer->addAttribute('quote_address', $code, array('type' => $type, 'visible' => false));
    $installer->addAttribute('order', $code, array('type' => $type, 'visible' => false));
}

$itemsAttributesCodes = array(
    'gw_id' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'gw_base_price' => 'decimal',
    'gw_price' => 'decimal',
    'gw_base_tax_amount' => 'decimal',
    'gw_tax_amount' => 'decimal'
);
foreach ($itemsAttributesCodes as $code => $type) {
    $installer->addAttribute('quote_item', $code, array('type' => $type, 'visible' => false));
    $installer->addAttribute('quote_address_item', $code, array('type' => $type, 'visible' => false));
    $installer->addAttribute('order_item', $code, array('type' => $type, 'visible' => false));
}

$entityAttributesCodes = array(
    'gw_base_price_invoiced' => 'decimal',
    'gw_price_invoiced' => 'decimal',
    'gw_items_base_price_invoiced' => 'decimal',
    'gw_items_price_invoiced' => 'decimal',
    'gw_card_base_price_invoiced' => 'decimal',
    'gw_card_price_invoiced' => 'decimal',
    'gw_base_tax_amount_invoiced' => 'decimal',
    'gw_tax_amount_invoiced' => 'decimal',
    'gw_items_base_tax_invoiced' => 'decimal',
    'gw_items_tax_invoiced' => 'decimal',
    'gw_card_base_tax_invoiced' => 'decimal',
    'gw_card_tax_invoiced' => 'decimal',
    'gw_base_price_refunded' => 'decimal',
    'gw_price_refunded' => 'decimal',
    'gw_items_base_price_refunded' => 'decimal',
    'gw_items_price_refunded' => 'decimal',
    'gw_card_base_price_refunded' => 'decimal',
    'gw_card_price_refunded' => 'decimal',
    'gw_base_tax_amount_refunded' => 'decimal',
    'gw_tax_amount_refunded' => 'decimal',
    'gw_items_base_tax_refunded' => 'decimal',
    'gw_items_tax_refunded' => 'decimal',
    'gw_card_base_tax_refunded' => 'decimal',
    'gw_card_tax_refunded' => 'decimal'
);
foreach ($entityAttributesCodes as $code => $type) {
    $installer->addAttribute('order', $code, array('type' => $type, 'visible' => false));
}

$itemsAttributesCodes = array(
    'gw_base_price_invoiced' => 'decimal',
    'gw_price_invoiced' => 'decimal',
    'gw_base_tax_amount_invoiced' => 'decimal',
    'gw_tax_amount_invoiced' => 'decimal',
    'gw_base_price_refunded' => 'decimal',
    'gw_price_refunded' => 'decimal',
    'gw_base_tax_amount_refunded' => 'decimal',
    'gw_tax_amount_refunded' => 'decimal'
);
foreach ($itemsAttributesCodes as $code => $type) {
    $installer->addAttribute('order_item', $code, array('type' => $type, 'visible' => false));
}

$entityAttributesCodes = array(
    'gw_base_price' => 'decimal',
    'gw_price' => 'decimal',
    'gw_items_base_price' => 'decimal',
    'gw_items_price' => 'decimal',
    'gw_card_base_price' => 'decimal',
    'gw_card_price' => 'decimal',
    'gw_base_tax_amount' => 'decimal',
    'gw_tax_amount' => 'decimal',
    'gw_items_base_tax_amount' => 'decimal',
    'gw_items_tax_amount' => 'decimal',
    'gw_card_base_tax_amount' => 'decimal',
    'gw_card_tax_amount' => 'decimal'
);
foreach ($entityAttributesCodes as $code => $type) {
    $installer->addAttribute('invoice', $code, array('type' => $type));
    $installer->addAttribute('creditmemo', $code, array('type' => $type));
}


/**
 * Add gift wrapping attributes for catalog product entity
 */
$types = Mage::getModel('catalog/product_type')->getOptionArray();
unset($types['virtual'], $types['downloadable'], $types['grouped']);
$applyTo = join(',', array_keys($types));

$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'gift_wrapping_available', array(
    'group'         => 'Gift Options',
    'backend'       => 'catalog/product_attribute_backend_boolean',
    'frontend'      => '',
    'label'         => 'Allow Gift Wrapping',
    'input'         => 'select',
    'source'        => 'eav/entity_attribute_source_boolean',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'       => true,
    'required'      => false,
    'user_defined'  => false,
    'default'       => '',
    'apply_to'      => $applyTo,
    'frontend_class' => 'hidden-for-virtual',
    'frontend_input_renderer' => 'enterprise_giftwrapping/adminhtml_product_helper_form_config',
    'input_renderer'   => 'enterprise_giftwrapping/adminhtml_product_helper_form_config',
    'visible_on_front' => false
));

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'gift_wrapping_price', array(
    'group'         => 'Gift Options',
    'type'          => 'decimal',
    'backend'       => 'catalog/product_attribute_backend_price',
    'frontend'      => '',
    'label'         => 'Price for Gift Wrapping',
    'input'         => 'price',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible'       => true,
    'required'      => false,
    'user_defined'  => false,
    'apply_to'      => $applyTo,
    'frontend_class' => 'hidden-for-virtual',
    'visible_on_front' => false
));
