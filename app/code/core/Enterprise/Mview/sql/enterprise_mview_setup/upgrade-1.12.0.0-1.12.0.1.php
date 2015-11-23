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

$this->getConnection()->dropColumn($this->getTable('enterprise_mview/metadata'), 'refreshed_at');

$definition = array(
    'comment'   => 'Version',
    'type'      => Varien_Db_Ddl_Table::TYPE_BIGINT,
    'unsigned'  => true,
    'nullable'  => false,
);
$this->getConnection()->addColumn($this->getTable('enterprise_mview/metadata'), 'version_id', $definition);

///////////////////////////////////////////
// Create table 'mview/event'
///////////////////////////////////////////
$table = $this->getConnection()
    ->newTable($this->getTable('enterprise_mview/event'))
    ->addColumn('mview_event_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Mview event id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
        'nullable'  => false,
    ), 'Event name')
    ->addIndex($this->getIdxName('enterprise_mview/event', array('name'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('name'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->setComment('Mview events');

$this->getConnection()->createTable($table);

///////////////////////////////////////////
// Create table 'mview/metadata_event'
///////////////////////////////////////////
$table = $this->getConnection()
    ->newTable($this->getTable('enterprise_mview/metadata_event'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('mview_event_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Mview event id')
    ->addColumn('metadata_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Metadata id')
    ->addForeignKey($this->getFkName('enterprise_mview/metadata_event', 'metadata_id',
        'enterprise_mview/metadata', 'metadata_id'),
        'metadata_id',
        $this->getTable('enterprise_mview/metadata'),
        'metadata_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey($this->getFkName('enterprise_mview/metadata_event', 'mview_event_id',
        'enterprise_mview/event', 'mview_event_id'),
        'mview_event_id',
        $this->getTable('enterprise_mview/event'),
        'mview_event_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Metadata event');

$this->getConnection()->createTable($table);
