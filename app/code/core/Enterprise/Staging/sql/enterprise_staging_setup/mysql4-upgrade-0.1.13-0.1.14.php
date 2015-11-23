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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer->startSetup();
$connection      = $installer->getConnection();
$stagingTable    = $this->getTable('enterprise_staging/staging');
$stagingLogTable = $this->getTable('enterprise_staging/staging_log');
$actionTable     = $this->getTable('enterprise_staging/staging_action');

$connection->dropForeignKey($actionTable, 'FK_STAGING_LOG_MASTER_WEBSITE');
$connection->dropForeignKey($actionTable, 'FK_STAGING_LOG_STAGING_WEBSITE');

$connection->addConstraint('FK_STAGING_LOG_MASTER_WEBSITE', $stagingLogTable, 'master_website_id', $installer->getTable('core/website'), 'website_id' ,'SET NULL');
$connection->addConstraint('FK_STAGING_LOG_STAGING_WEBSITE', $stagingLogTable, 'staging_website_id', $installer->getTable('core/website'), 'website_id' ,'SET NULL');

$connection->changeColumn($stagingTable, 'merge_scheduling_date', 'merge_scheduling_date', 'DATETIME NULL DEFAULT NULL');

$connection->dropColumn($stagingLogTable, 'comment');
$connection->dropColumn($stagingLogTable, 'log');

$connection->addColumn($stagingLogTable, 'staging_website_name', 'VARCHAR( 255 ) NULL DEFAULT NULL AFTER `staging_website_id`');
$connection->addColumn($stagingLogTable, 'master_website_name', 'VARCHAR( 255 ) NULL DEFAULT NULL AFTER `master_website_id`');
$connection->addColumn($stagingLogTable, 'additional_data', 'TEXT NULL DEFAULT NULL AFTER `is_admin_notified`');

$installer->endSetup();
