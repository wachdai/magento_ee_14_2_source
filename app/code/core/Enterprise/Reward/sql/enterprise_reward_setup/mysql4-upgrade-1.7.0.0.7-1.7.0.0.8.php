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
$installer->getConnection()->dropForeignKey($installer->getTable('enterprise_reward/reward'), 'FK_REWARD_WEBSITE_ID');
$installer->getConnection()->changeColumn($installer->getTable('enterprise_reward/reward'), 'website_id',
    'website_id', 'SMALLINT(5) UNSIGNED DEFAULT NULL');
$installer->getConnection()->addColumn($installer->getTable('enterprise_reward/reward'),
    'website_currency_code', 'CHAR(3) DEFAULT NULL AFTER `points_balance`');
$installer->getConnection()->dropForeignKey($installer->getTable('enterprise_reward/reward_history'), 'FK_REWARD_HISTORY_STORE_ID');
$installer->getConnection()->changeColumn($installer->getTable('enterprise_reward/reward_history'), 'store_id',
    'store_id', 'SMALLINT(5) UNSIGNED DEFAULT NULL');
$installer->getConnection()->addConstraint('FK_REWARD_HISTORY_WEBSITE_ID', $installer->getTable('enterprise_reward/reward_history'),
    'website_id', $installer->getTable('core/website'), 'website_id');
$installer->getConnection()->addConstraint('FK_REWARD_HISTORY_STORE_ID', $installer->getTable('enterprise_reward/reward_history'),
    'store_id', $installer->getTable('core/store'), 'store_id', 'SET NULL', 'CASCADE');
$installer->endSetup();
