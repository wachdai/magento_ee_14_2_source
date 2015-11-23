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
/* @var $installer Enterprise_GiftCardAccount_Model_Mysql4_Setup */
$installer->startSetup();

$installer->run("
CREATE TABLE `{$installer->getTable('enterprise_giftcardaccount/history')}` (
  `history_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `giftcardaccount_id` int(10) unsigned NOT NULL DEFAULT 0,
  `updated_at` datetime NULL DEFAULT NULL,
  `action` tinyint(3) unsigned NOT NULL default '0',
  `balance_amount` decimal(12,4) unsigned NOT NULL DEFAULT 0,
  `balance_delta` decimal(12,4) NOT NULL DEFAULT 0,
  `additional_info` tinytext COLLATE utf8_general_ci NULL,
  PRIMARY KEY (`history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addConstraint(
    'FK_GIFTCARDACCOUNT_HISTORY_ACCOUNT',
    $installer->getTable('enterprise_giftcardaccount/history'), 'giftcardaccount_id',
    $installer->getTable('enterprise_giftcardaccount/giftcardaccount'), 'giftcardaccount_id'
);

$installer->endSetup();
