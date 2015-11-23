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
 * @package     Mage_Core
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$rows = $installer->_conn->fetchAll(
    "select * from {$this->getTable('core_config_data')} where
    path in (
    'paypal/wpp/api_password', 'paypal/wpp/api_signature', 'paypal/wpp/api_username',
    'paypal/wps/business_account', 'paypal/wpuk/user', 'paypal/wpuk/pwd', 'carriers/dhl/id',
    'carriers/dhl/password', 'carriers/dhl/shipping_key', 'carriers/dhl/shipping_intlkey',
    'carriers/fedex/account', 'carriers/ups/account_license_number', 'carriers/ups/username',
    'carriers/usps/userid', 'payment/authorizenet/login', 'payment/authorizenet/trans_key',
    'payment/verisign/pwd', 'payment/verisign/user')"
);

$hlp = Mage::helper('core');
foreach ($rows as $r) {
    if (!empty($r['value'])) {
        $r['value'] = $hlp->encrypt($r['value']);
        $installer->_conn->update($this->getTable('core_config_data'), $r, 'config_id='.$r['config_id']);
    }
}
$installer->endSetup();
