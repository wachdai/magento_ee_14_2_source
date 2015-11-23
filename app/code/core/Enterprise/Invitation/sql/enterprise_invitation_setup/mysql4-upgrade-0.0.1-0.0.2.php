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
 * @package     Enterprise_Invitation
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$tableInvitation = $installer->getTable('enterprise_invitation/invitation');
$tableCustomer   = $installer->getTable('customer/entity');

$installer->run("UPDATE {$tableInvitation} SET customer_id = NULL WHERE customer_id NOT IN (SELECT entity_id FROM {$tableCustomer})");
$installer->getConnection()->addConstraint('FK_INVITATION_CUSTOMER', $tableInvitation,
    'customer_id', $tableCustomer, 'entity_id', 'SET NULL'
);
$installer->run("UPDATE {$tableInvitation} SET referral_id = NULL WHERE customer_id NOT IN (SELECT entity_id FROM {$tableCustomer})");
$installer->getConnection()->addConstraint('FK_INVITATION_REFERRAL', $tableInvitation,
    'referral_id', $tableCustomer, 'entity_id', 'SET NULL'
);

$installer->endSetup();
