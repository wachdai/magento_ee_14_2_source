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
 * @package     Enterprise_Reward
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Mage_Sales_Model_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$installer->addAttribute('quote', 'use_reward_points', array('type' => 'int'));
$installer->addAttribute('quote', 'reward_points_balance', array('type' => 'int'));
$installer->addAttribute('quote', 'base_reward_currency_amount', array('type' => 'decimal'));
$installer->addAttribute('quote', 'reward_currency_amount', array('type' => 'decimal'));

$installer->addAttribute('quote_address', 'reward_points_balance', array('type' => 'int'));
$installer->addAttribute('quote_address', 'base_reward_currency_amount', array('type' => 'decimal'));
$installer->addAttribute('quote_address', 'reward_currency_amount', array('type' => 'decimal'));

$installer->addAttribute('order', 'reward_points_balance', array('type' => 'int'));
$installer->addAttribute('order', 'base_reward_currency_amount', array('type' => 'decimal'));
$installer->addAttribute('order', 'reward_currency_amount', array('type' => 'decimal'));
$installer->addAttribute('order', 'base_reward_currency_amount_invoiced', array('type' => 'decimal'));
$installer->addAttribute('order', 'reward_currency_amount_invoiced', array('type' => 'decimal'));
$installer->addAttribute('order', 'base_reward_currency_amount_refunded', array('type' => 'decimal'));
$installer->addAttribute('order', 'reward_currency_amount_refunded', array('type' => 'decimal'));

$installer->addAttribute('invoice', 'base_reward_currency_amount', array('type' => 'decimal'));
$installer->addAttribute('invoice', 'reward_currency_amount', array('type' => 'decimal'));

$installer->addAttribute('creditmemo', 'base_reward_currency_amount', array('type' => 'decimal'));
$installer->addAttribute('creditmemo', 'reward_currency_amount', array('type' => 'decimal'));

$installer->endSetup();
