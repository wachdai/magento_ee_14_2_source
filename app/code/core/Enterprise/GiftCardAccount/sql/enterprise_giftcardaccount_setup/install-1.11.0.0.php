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
 * @package     Enterprise_GiftCardAccount
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Enterprise_GiftCardAccount_Model_Resource_Setup */
$installer->startSetup();

/**
 * Create table 'enterprise_giftcardaccount/giftcardaccount'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftcardaccount/giftcardaccount'))
    ->addColumn('giftcardaccount_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Giftcardaccount Id')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Code')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        ), 'Status')
    ->addColumn('date_created', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        'nullable'  => false,
        ), 'Date Created')
    ->addColumn('date_expires', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Date Expires')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Website Id')
    ->addColumn('balance', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Balance')
    ->addColumn('state', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'State')
    ->addColumn('is_redeemable', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '1',
        ), 'Is Redeemable')
    ->addIndex($installer->getIdxName('enterprise_giftcardaccount/giftcardaccount', array('website_id')),
        array('website_id'))
    ->addForeignKey($installer->getFkName('enterprise_giftcardaccount/giftcardaccount', 'website_id', 'core/website', 'website_id'),
        'website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Giftcardaccount');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_giftcardaccount/pool'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftcardaccount/pool'))
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        'primary'   => true,
        ), 'Code')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0'
        ), 'Status')
    ->setComment('Enterprise Giftcardaccount Pool');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_giftcardaccount/history'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_giftcardaccount/history'))
    ->addColumn('history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'History Id')
    ->addColumn('giftcardaccount_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Giftcardaccount Id')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Updated At')
    ->addColumn('action', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Action')
    ->addColumn('balance_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Balance Amount')
    ->addColumn('balance_delta', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Balance Delta')
    ->addColumn('additional_info', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Additional Info')
    ->addIndex($installer->getIdxName('enterprise_giftcardaccount/history', array('giftcardaccount_id')),
        array('giftcardaccount_id'))
    ->addForeignKey($installer->getFkName('enterprise_giftcardaccount/history', 'giftcardaccount_id', 'enterprise_giftcardaccount/giftcardaccount', 'giftcardaccount_id'),
        'giftcardaccount_id', $installer->getTable('enterprise_giftcardaccount/giftcardaccount'), 'giftcardaccount_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Giftcardaccount History');
$installer->getConnection()->createTable($table);

// 0.0.1 => 0.0.2
$installer->addAttribute('quote', 'gift_cards', array('type'=>'text'));

// 0.0.2 => 0.0.3
$installer->addAttribute('quote', 'gift_cards_amount', array('type'=>'decimal'));
$installer->addAttribute('quote', 'base_gift_cards_amount', array('type'=>'decimal'));

$installer->addAttribute('quote_address', 'gift_cards_amount', array('type'=>'decimal'));
$installer->addAttribute('quote_address', 'base_gift_cards_amount', array('type'=>'decimal'));

$installer->addAttribute('quote', 'gift_cards_amount_used', array('type'=>'decimal'));
$installer->addAttribute('quote', 'base_gift_cards_amount_used', array('type'=>'decimal'));

// 0.0.3 => 0.0.4
$installer->addAttribute('quote_address', 'gift_cards', array('type'=>'text'));

// 0.0.4 => 0.0.5
$installer->addAttribute('order', 'gift_cards', array('type'=>'text'));
$installer->addAttribute('order', 'base_gift_cards_amount', array('type'=>'decimal'));
$installer->addAttribute('order', 'gift_cards_amount', array('type'=>'decimal'));

// 0.0.5 => 0.0.6
$installer->addAttribute('quote_address', 'used_gift_cards', array('type'=>'text'));

// 0.0.9 => 0.0.9
$installer->addAttribute('order', 'base_gift_cards_invoiced', array('type'=>'decimal'));
$installer->addAttribute('order', 'gift_cards_invoiced', array('type'=>'decimal'));

$installer->addAttribute('invoice', 'base_gift_cards_amount', array('type'=>'decimal'));
$installer->addAttribute('invoice', 'gift_cards_amount', array('type'=>'decimal'));

// 0.0.11 => 0.0.12
$installer->addAttribute('order', 'base_gift_cards_refunded', array('type'=>'decimal'));
$installer->addAttribute('order', 'gift_cards_refunded', array('type'=>'decimal'));

$installer->addAttribute('creditmemo', 'base_gift_cards_amount', array('type'=>'decimal'));
$installer->addAttribute('creditmemo', 'gift_cards_amount', array('type'=>'decimal'));

$installer->endSetup();
