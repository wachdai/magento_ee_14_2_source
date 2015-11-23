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

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'enterprise_support/sysreport'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_support/sysreport'))
    ->addColumn('report_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Report ID')
    ->addColumn('created_at',       Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Creation Time')
    ->addColumn('client_host',      Varien_Db_Ddl_Table::TYPE_TEXT, 255,   array('nullable' => false), 'Client Host')
    ->addColumn('magento_version',  Varien_Db_Ddl_Table::TYPE_TEXT, 25,    array('nullable' => false), 'Magento')
    ->addColumn('report_version',   Varien_Db_Ddl_Table::TYPE_TEXT, 15,    array('nullable' => false), 'Report Version')
    ->addColumn('report_types',     Varien_Db_Ddl_Table::TYPE_TEXT, '4k',    array('nullable' => false), 'Report Types')
    ->addColumn('report_flags',     Varien_Db_Ddl_Table::TYPE_TEXT, '64k',   array('nullable' => false), 'Report Flags')
    ->addColumn('report_data',      Varien_Db_Ddl_Table::TYPE_TEXT, '1536k', array('nullable' => false), 'Report Data')
    ->addIndex($installer->getIdxName('enterprise_support/sysreport', array('report_version')),
        array('report_version'))
    ->setComment('Support System Reports');
$installer->getConnection()->createTable($table);

$installer->endSetup();
