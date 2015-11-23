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

/**
 * Fix for incorrectly created FK for backup tables
 */

/** @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

/**
 * Collect all existing backup tables
 */
$backupTablesList = array();

$stagingCollection = Mage::getResourceModel('enterprise_staging/staging_collection');
while ($staging = $stagingCollection->fetchItem()) {
    $logCollection = Mage::getResourceModel('enterprise_staging/staging_log_collection')
        ->addFieldToFilter('action', Enterprise_Staging_Model_Staging_Config::ACTION_BACKUP)
        ->addFieldToFilter('status', Enterprise_Staging_Model_Staging_Config::STATUS_STARTED)
        ->addFieldToFilter('staging_id', $staging->getId());

    while ($log = $logCollection->fetchItem()) {
        $staging->getMapperInstance()->unserialize($log->getMap());
        $staging->collectBackupTables($log);

        $backupTables = $staging->getBackupTables();
        if (!is_array($backupTables) || empty($backupTables)) {
            continue;
        }

        $tablePrefix = Mage::getSingleton('enterprise_staging/staging_config')->getTablePrefix($staging)
            . Mage::getSingleton('enterprise_staging/staging_config')->getStagingBackupTablePrefix()
            . $log->getId() . '_';

        foreach ($backupTables as $backupTable) {
            $tableName = $tablePrefix . $backupTable;
            if ($installer->tableExists($tableName)) {
                $backupTablesList[] = $tableName;
            }
        }

        $staging->unsetData('backup_tables');
    }
}

/**
 * Drop backup tables FK and recreate them using correct rules
 */
if (!empty($backupTablesList)) {
    $connection = $installer->getConnection();

    foreach ($backupTablesList as $backupTable) {
        foreach($connection->getForeignKeys($backupTable) as $keyName => $keyInfo) {
            $connection->dropForeignKey($backupTable, $keyName);

            $correctFkName = $connection->getForeignKeyName(
                $keyInfo['TABLE_NAME'],
                $keyInfo['COLUMN_NAME'],
                $keyInfo['REF_TABLE_NAME'],
                $keyInfo['REF_COLUMN_NAME']
            );

            $connection->addForeignKey(
                $correctFkName,
                $keyInfo['TABLE_NAME'],
                $keyInfo['COLUMN_NAME'],
                $keyInfo['REF_TABLE_NAME'],
                $keyInfo['REF_COLUMN_NAME']
            );
        }
    }
}
