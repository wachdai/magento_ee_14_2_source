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
 * @package     Enterprise_Support
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'enterprise_support/backup'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_support/backup_item'))
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Item ID')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        'unsigned'  => true,
    ), 'Status')
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        'unsigned'  => true,
    ), 'Type')
    ->addColumn('size', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        'unsigned'  => true,
        ), 'size')
    ->addColumn('backup_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'unsigned' => true,
    ), 'Backup ID')
    ->addIndex($installer->getIdxName('enterprise_support/backup_item', array('status')),
        array('status'))
    ->addIndex($installer->getIdxName('enterprise_support/backup_item', array('type')),
        array('type'))
    ->addForeignKey(
        $installer->getFkName('enterprise_support/backup', 'backup_id', 'enterprise_support/backup_item', 'backup_id'),
        'backup_id', $installer->getTable('enterprise_support/backup'), 'backup_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Support System Backup Items');
$installer->getConnection()->createTable($table);

$installer->endSetup();
