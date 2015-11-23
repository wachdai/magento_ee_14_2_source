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
 * @package     Enterprise_Reminder
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/** @var $installer Enterprise_Reminder_Model_Resource_Setup */
$installer = $this;

$ruleTable  = $installer->getTable('enterprise_reminder/rule');
$ruleWebsiteTable = $installer->getTable('enterprise_reminder/website');
$coreWebsiteTable = $installer->getTable('core/website');
$connection = $installer->getConnection();

$installer->startSetup();

$connection->changeColumn(
    $ruleTable,
    'active_from',
    'from_date',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
        'nullable'  => true,
        'default'   => null
    )
);

$connection->changeColumn(
    $ruleTable,
    'active_to',
    'to_date',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
        'nullable'  => true,
        'default'   => null
    )
);


/**
 * Clean relations with not existing websites
 */
$selectWebsiteIds = $connection->select()
    ->from($coreWebsiteTable, 'website_id');
$websiteIds = $connection->fetchCol($selectWebsiteIds);
if (!empty($websiteIds)) {
    $connection->delete($ruleWebsiteTable, $connection->quoteInto('website_id NOT IN (?)', $websiteIds));
}

/**
 * Add foreign key for rule website table onto core website table
 */
$connection->addForeignKey(
    $installer->getFkName('enterprise_reminder/website', 'website_id', 'core/website', 'website_id'),
    $ruleWebsiteTable,
    'website_id',
    $coreWebsiteTable,
    'website_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
);

$installer->endSetup();
