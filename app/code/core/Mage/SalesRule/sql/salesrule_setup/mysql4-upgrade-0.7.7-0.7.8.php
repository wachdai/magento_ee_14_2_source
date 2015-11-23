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
 * @category    Mage
 * @package     Mage_SalesRule
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;

/**
 * add attributes discount_description, shipping_discount_amount, base_shipping_discount_amount
 */
$installer->addAttribute('quote_address', 'discount_description', array('type'=>'varchar'));
$installer->addAttribute('quote_address', 'shipping_discount_amount', array('type'=>'decimal'));
$installer->addAttribute('quote_address', 'base_shipping_discount_amount', array('type'=>'decimal'));


$installer->addAttribute('order', 'discount_description', array('type'=>'varchar'));
$installer->addAttribute('order', 'shipping_discount_amount', array('type'=>'decimal'));
$installer->addAttribute('order', 'base_shipping_discount_amount', array('type'=>'decimal'));
