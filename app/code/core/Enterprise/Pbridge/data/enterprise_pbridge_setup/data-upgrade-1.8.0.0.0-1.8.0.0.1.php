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
 * @package     Enterprise_Pbridge
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();
$acceptedCurrencyConfig = Mage::getStoreConfig('payment/eway_direct/currency');
$baseCurrencyConfig = Mage::getStoreConfig('currency/options/base');
//check wrong setup
if ($baseCurrencyConfig != 'AUD' && $acceptedCurrencyConfig != $baseCurrencyConfig) {
    //disable eWAY Direct: default scope and all websites
    Mage::getConfig()->deleteConfig('payment/eway_direct/active');
    foreach (Mage::app()->getWebsites() as $website) {
        Mage::getConfig()->deleteConfig('payment/eway_direct/active', 'websites', (int)$website->getId());
    }
}
//delete currency restriction
Mage::getConfig()->deleteConfig('payment/eway_direct/currency');

/**
 * Change "sagepay_direct" method name to "pbridge_sagepay_direct" in table of orders
 */
$installer->getConnection()->update(
    $installer->getTable('sales/order_payment'),
    array('method' => 'pbridge_sagepay_direct'),
    array('method = ?' => 'sagepay_direct')
);
