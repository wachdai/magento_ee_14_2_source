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

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'enterprise_staging/staging'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_staging/staging'))
    ->addColumn('staging_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Staging Id')
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Type')
    ->addColumn('master_website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Master Website Id')
    ->addColumn('staging_website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Staging Website Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated At')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
        ), 'Status')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Sort Order')
    ->addColumn('merge_scheduling_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Merge Scheduling Date')
    ->addColumn('merge_scheduling_map', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Merge Scheduling Map')
    ->addIndex($installer->getIdxName('enterprise_staging/staging', array('status')),
        array('status'))
    ->addIndex($installer->getIdxName('enterprise_staging/staging', array('sort_order')),
        array('sort_order'))
    ->addIndex($installer->getIdxName('enterprise_staging/staging', array('master_website_id')),
        array('master_website_id'))
    ->addIndex($installer->getIdxName('enterprise_staging/staging', array('staging_website_id')),
        array('staging_website_id'))
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_staging/staging',
            'master_website_id',
            'core/website',
            'website_id'
        ),
        'master_website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_staging/staging',
            'staging_website_id',
            'core/website',
            'website_id'
        ),
        'staging_website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Staging');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_staging/staging_item'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_staging/staging_item'))
    ->addColumn('staging_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Staging Item Id')
    ->addColumn('staging_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Staging Id')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Code')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Sort Order')
    ->addIndex(
        $installer->getIdxName(
            'enterprise_staging/staging_item',
            array('staging_id', 'code'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('staging_id', 'code'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('enterprise_staging/staging_item', array('staging_id', 'sort_order')),
        array('staging_id', 'sort_order'))
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_staging/staging_item',
            'staging_id',
            'enterprise_staging/staging',
            'staging_id'
        ),
        'staging_id', $installer->getTable('enterprise_staging/staging'), 'staging_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Staging Item');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_staging/staging_action'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_staging/staging_action'))
    ->addColumn('action_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Action Id')
    ->addColumn('staging_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Staging Id')
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Type')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        ), 'Status')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated At')
    ->addColumn('staging_table_prefix', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Staging Table Prefix')
    ->addColumn('map', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Map')
    ->addColumn('mage_version', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Mage Version')
    ->addColumn('mage_modules_version', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Mage Modules Version')
    ->addColumn('staging_website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Staging Website Id')
    ->addColumn('master_website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Master Website Id')
    ->addIndex($installer->getIdxName('enterprise_staging/staging_action', array('staging_id')),
        array('staging_id'))
    ->addIndex($installer->getIdxName('enterprise_staging/staging_action', array('status')),
        array('status'))
    ->addIndex($installer->getIdxName('enterprise_staging/staging_action', array('mage_version')),
        array('mage_version'))
    ->addIndex($installer->getIdxName('enterprise_staging/staging_action', array('master_website_id')),
        array('master_website_id'))
    ->addIndex($installer->getIdxName('enterprise_staging/staging_action', array('staging_website_id')),
        array('staging_website_id'))
    ->addIndex($installer->getIdxName('enterprise_staging/staging_action', array('type')),
        array('type'))
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_staging/staging_action',
            'staging_website_id',
            'core/website',
            'website_id'
        ),
        'staging_website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_staging/staging_action',
            'master_website_id',
            'core/website',
            'website_id'
        ),
        'master_website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Staging Action');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_staging/staging_log'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_staging/staging_log'))
    ->addColumn('log_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Log Id')
    ->addColumn('staging_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Staging Id')
    ->addColumn('ip', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Ip')
    ->addColumn('action', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        ), 'Action')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        ), 'Status')
    ->addColumn('is_backuped', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Backuped')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'User Id')
    ->addColumn('username', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Username')
    ->addColumn('is_admin_notified', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Admin Notified')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Data')
    ->addColumn('map', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Map')
    ->addColumn('staging_website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Staging Website Id')
    ->addColumn('staging_website_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Staging Website Name')
    ->addColumn('master_website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Master Website Id')
    ->addColumn('master_website_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Master Website Name')
    ->addIndex($installer->getIdxName('enterprise_staging/staging_log', array('staging_id')),
        array('staging_id'))
    ->addIndex($installer->getIdxName('enterprise_staging/staging_log', array('status')),
        array('status'))
    ->addIndex($installer->getIdxName('enterprise_staging/staging_log', array('is_backuped')),
        array('is_backuped'))
    ->addIndex($installer->getIdxName('enterprise_staging/staging_log', array('is_admin_notified')),
        array('is_admin_notified'))
    ->addIndex($installer->getIdxName('enterprise_staging/staging_log', array('master_website_id')),
        array('master_website_id'))
    ->addIndex($installer->getIdxName('enterprise_staging/staging_log', array('staging_website_id')),
        array('staging_website_id'))
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_staging/staging_log',
            'master_website_id',
            'core/website',
            'website_id'
        ),
        'master_website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_staging/staging_log',
            'staging_website_id',
            'core/website',
            'website_id'
        ),
        'staging_website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Staging Log');
$installer->getConnection()->createTable($table);

$installer->getConnection()->addColumn($installer->getTable('core/website'), 'is_staging', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'nullable'  => false,
    'default'   => 0,
    'comment'   => 'Is Staging Flag'
));
$installer->getConnection()->addColumn($installer->getTable('core/website'), 'master_login', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 40,
    'comment'   => 'Master Login'
));
$installer->getConnection()->addColumn($installer->getTable('core/website'), 'master_password', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 100,
    'comment'   => 'Master Password'
));
$installer->getConnection()->addColumn($installer->getTable('core/website'), 'visibility', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 40,
    'comment'   => 'Visibility'
));

$installer->endSetup();
