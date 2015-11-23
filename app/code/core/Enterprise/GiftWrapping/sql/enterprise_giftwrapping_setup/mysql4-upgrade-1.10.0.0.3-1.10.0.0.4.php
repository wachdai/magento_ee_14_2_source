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
/* @var $installer Enterprise_GiftWrapping_Model_Resource_Mysql4_Setup */

$entityAttributesCodes = array(
    'int' => 'gw_id',
    'int' => 'gw_allow_gift_receipt',
    'int' => 'gw_add_printed_card',
    'decimal' => 'gw_base_price',
    'decimal' => 'gw_price',
    'decimal' => 'gw_items_base_price',
    'decimal' => 'gw_items_price',
    'decimal' => 'gw_printed_card_base_price',
    'decimal' => 'gw_printed_card_price',
    'decimal' => 'gw_base_tax_amount',
    'decimal' => 'gw_tax_amount',
    'decimal' => 'gw_items_base_tax_amount',
    'decimal' => 'gw_items_tax_amount',
    'decimal' => 'gw_printed_card_base_tax_amount',
    'decimal' => 'gw_printed_card_tax_amount'
);
foreach ($entityAttributesCodes as $type => $code) {
    $installer->addAttribute('quote', $code, array('type' => $type, 'visible' => false));
    $installer->addAttribute('quote_address', $code, array('type' => $type, 'visible' => false));
    $installer->addAttribute('order', $code, array('type' => $type, 'visible' => false));
}

$itemsAttributesCodes = array(
    'int' => 'gw_id',
    'decimal' => 'gw_base_price',
    'decimal' => 'gw_price',
    'decimal' => 'gw_base_tax_amount',
    'decimal' => 'gw_tax_amount'
);
foreach ($itemsAttributesCodes as $type => $code) {
    $installer->addAttribute('quote_item', $code, array('type' => $type, 'visible' => false));
    $installer->addAttribute('quote_address_item', $code, array('type' => $type, 'visible' => false));
    $installer->addAttribute('order_item', $code, array('type' => $type, 'visible' => false));
}

$entityAttributesCodes = array(
    'gw_base_price_invoiced' => 'decimal',
    'gw_price_invoiced' => 'decimal',
    'gw_items_base_price_invoiced' => 'decimal',
    'gw_items_price_invoiced' => 'decimal',
    'gw_printed_card_base_price_invoiced' => 'decimal',
    'gw_printed_card_price_invoiced' => 'decimal',
    'gw_base_tax_amount_invoiced' => 'decimal',
    'gw_tax_amount_invoiced' => 'decimal',
    'gw_items_base_tax_amount_invoiced' => 'decimal',
    'gw_items_tax_amount_invoiced' => 'decimal',
    'gw_printed_card_base_tax_amount_invoiced' => 'decimal',
    'gw_printed_card_tax_amount_invoiced' => 'decimal',
    'gw_base_price_refunded' => 'decimal',
    'gw_price_refunded' => 'decimal',
    'gw_items_base_price_refunded' => 'decimal',
    'gw_items_price_refunded' => 'decimal',
    'gw_printed_card_base_price_refunded' => 'decimal',
    'gw_printed_card_price_refunded' => 'decimal',
    'gw_base_tax_amount_refunded' => 'decimal',
    'gw_tax_amount_refunded' => 'decimal',
    'gw_items_base_tax_amount_refunded' => 'decimal',
    'gw_items_tax_amount_refunded' => 'decimal',
    'gw_printed_card_base_tax_amount_refunded' => 'decimal',
    'gw_printed_card_tax_amount_refunded' => 'decimal'
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
    'gw_printed_card_base_price' => 'decimal',
    'gw_printed_card_price' => 'decimal',
    'gw_base_tax_amount' => 'decimal',
    'gw_tax_amount' => 'decimal',
    'gw_items_base_tax_amount' => 'decimal',
    'gw_items_tax_amount' => 'decimal',
    'gw_printed_card_base_tax_amount' => 'decimal',
    'gw_printed_card_tax_amount' => 'decimal'
);
foreach ($entityAttributesCodes as $code => $type) {
    $installer->addAttribute('invoice', $code, array('type'=>$type));
    $installer->addAttribute('creditmemo', $code, array('type'=>$type));
}
