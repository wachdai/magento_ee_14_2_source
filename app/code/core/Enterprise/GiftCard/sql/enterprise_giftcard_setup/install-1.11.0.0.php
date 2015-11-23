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

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'enterprise_giftcard/amount'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftcard/amount'))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Value Id')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Website Id')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Value')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Id')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Entity Type Id')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Attribute Id')
    ->addIndex($installer->getIdxName('enterprise_giftcard/amount', array('entity_id')),
        array('entity_id'))
    ->addIndex($installer->getIdxName('enterprise_giftcard/amount', array('website_id')),
        array('website_id'))
    ->addIndex($installer->getIdxName('enterprise_giftcard/amount', array('attribute_id')),
        array('attribute_id'))
    ->addForeignKey($installer->getFkName('enterprise_giftcard/amount', 'entity_id', 'catalog/product', 'entity_id'),
        'entity_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_giftcard/amount', 'website_id', 'core/website', 'website_id'),
        'website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_giftcard/amount', 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Giftcard Amount');
$installer->getConnection()->createTable($table);



// 0.0.2 => 0.0.3
$installer->addAttribute('catalog_product', 'giftcard_amounts', array(
        'group'             => 'Prices',
        'type'              => 'decimal',
        'backend'           => 'enterprise_giftcard/attribute_backend_giftcard_amount',
        'frontend'          => '',
        'label'             => 'Amounts',
        'input'             => 'price',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'giftcard',
        'is_configurable'   => false,
        'used_in_product_listing' => true,
        'sort_order'        => -5,
    ));

$installer->addAttribute('catalog_product', 'allow_open_amount', array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Allow Open Amount',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'enterprise_giftcard/source_open',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => true,
        'required'          => true,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'giftcard',
        'is_configurable'   => false,
        'used_in_product_listing' => true,
        'sort_order'        => -4,
    ));
$installer->addAttribute('catalog_product', 'open_amount_min', array(
        'group'             => 'Prices',
        'type'              => 'decimal',
        'backend'           => 'catalog/product_attribute_backend_price',
        'frontend'          => '',
        'label'             => 'Open Amount Min Value',
        'input'             => 'price',
        'class'             => 'validate-number',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'giftcard',
        'is_configurable'   => false,
        'used_in_product_listing' => true,
        'sort_order'        => -3,
    ));
$installer->addAttribute('catalog_product', 'open_amount_max', array(
        'group'             => 'Prices',
        'type'              => 'decimal',
        'backend'           => 'catalog/product_attribute_backend_price',
        'frontend'          => '',
        'label'             => 'Open Amount Max Value',
        'input'             => 'price',
        'class'             => 'validate-number',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'giftcard',
        'is_configurable'   => false,
        'used_in_product_listing' => true,
        'sort_order'        => -2,
    ));

$installer->addAttribute('catalog_product', 'giftcard_type', array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Card Type',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'enterprise_giftcard/source_type',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => false,
        'required'          => true,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'giftcard',
        'is_configurable'   => false
    ));

$installer->addAttribute('catalog_product', 'is_redeemable', array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Is Redeemable',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'giftcard',
        'is_configurable'   => false
    ));

$installer->addAttribute('catalog_product', 'use_config_is_redeemable', array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Use Config Is Redeemable',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'giftcard',
        'is_configurable'   => false
    ));

$installer->addAttribute('catalog_product', 'lifetime', array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Lifetime',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'giftcard',
        'is_configurable'   => false
    ));

$installer->addAttribute('catalog_product', 'use_config_lifetime', array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Use Config Lifetime',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'giftcard',
        'is_configurable'   => false
    ));

$installer->addAttribute('catalog_product', 'email_template', array(
        'group'             => 'Prices',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Email Template',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'giftcard',
        'is_configurable'   => false
    ));

$installer->addAttribute('catalog_product', 'use_config_email_template', array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Use Config Email Template',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'giftcard',
        'is_configurable'   => false
    ));
// 0.0.3 => 0.0.4
$installer->addAttribute('catalog_product', 'allow_message', array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Allow Message',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'giftcard',
        'is_configurable'   => false
    ));

$installer->addAttribute('catalog_product', 'use_config_allow_message', array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Use Config Allow Message',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => false,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'giftcard',
        'is_configurable'   => false
    ));

// 0.0.4 => 0.0.5 make 'weight' attribute applicable to gift card products
$applyTo = $installer->getAttribute('catalog_product', 'weight', 'apply_to');
if ($applyTo) {
    $applyTo = explode(',', $applyTo);
    if (!in_array('giftcard', $applyTo)) {
        $applyTo[] = 'giftcard';
        $installer->updateAttribute('catalog_product', 'weight', 'apply_to', join(',', $applyTo));
    }
}

// 0.0.6 => 0.0.7
$fieldList = array(
    'cost',
);

// make these attributes not applicable to gift card products
foreach ($fieldList as $field) {
    $applyTo = explode(',', $installer->getAttribute('catalog_product', $field, 'apply_to'));
    if (in_array('giftcard', $applyTo)) {
        foreach ($applyTo as $k => $v) {
            if ($v == 'giftcard') {
                unset($applyTo[$k]);
                break;
            }
        }
        $installer->updateAttribute('catalog_product', $field, 'apply_to', join(',', $applyTo));
    }
}

$installer->endSetup();
