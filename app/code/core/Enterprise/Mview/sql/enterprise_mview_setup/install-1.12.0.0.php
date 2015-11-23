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
 * @package     Enterprise_Mview
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $this Mage_Core_Model_Resource_Setup */


///////////////////////////////////////////
// Create table 'mview/metadata'
///////////////////////////////////////////
$table = $this->getConnection()
    ->newTable($this->getTable('enterprise_mview/metadata'))
    ->addColumn('metadata_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Metadata Id')
    ->addColumn('view_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
        'nullable'  => false,
    ), 'View name')
    ->addColumn('table_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
        'nullable'  => false,
    ), 'Table name')
    ->addColumn('key_column', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(), 'Key column name')
    ->addColumn('changelog_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(), 'Changelog table name')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
        'nullable'  => false,
    ), 'Materialized view status')
    ->addColumn('refreshed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Date of last view table refresh')
    ->addIndex($this->getIdxName('enterprise_mview/metadata', array('view_name'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('view_name'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addIndex($this->getIdxName('enterprise_mview/metadata', array('table_name'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('table_name'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->setComment('Materialized view metadata');

$this->getConnection()->createTable($table);

///////////////////////////////////////////
// Create table 'mview/subscriber'
///////////////////////////////////////////
$table = $this->getConnection()
    ->newTable($this->getTable('enterprise_mview/subscriber'))
    ->addColumn('subscriber_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Subscriber Id')
    ->addColumn('metadata_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Metadata Id')
    ->addColumn('target_table', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
        'nullable'  => false,
    ), 'Target table name')
    ->addColumn('target_column', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
        'nullable'  => false,
    ), 'Target column name')
    ->addIndex($this->getIdxName('enterprise_mview/subscriber', array('metadata_id', 'target_table', 'target_column'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('metadata_id', 'target_table', 'target_column'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey($this->getFkName('enterprise_mview/subscriber', 'metadata_id',
        'enterprise_mview/metadata', 'metadata_id'),
        'metadata_id',
        $this->getTable('enterprise_mview/metadata'),
        'metadata_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_NO_ACTION
    )
    ->setComment('Materialized view subscribers');

$this->getConnection()->createTable($table);
