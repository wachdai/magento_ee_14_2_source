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
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_staging/staging'),
    'FK_ENTERPRISE_STAGING_MASTER_WEBSITE_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_staging/staging'),
    'FK_ENTERPRISE_STAGING_STAGING_WEBSITE_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_staging/staging_action'),
    'FK_STAGING_ACTION_MASTER_WEBSITE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_staging/staging_action'),
    'FK_STAGING_ACTION_STAGING_WEBSITE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_staging/staging_item'),
    'FK_ENTERPRISE_STAGING_ITEM_STAGING_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_staging/staging_log'),
    'FK_STAGING_LOG_MASTER_WEBSITE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_staging/staging_log'),
    'FK_STAGING_LOG_STAGING_WEBSITE'
);


/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging'),
    'IDX_ENTERPRISE_STAGING_STATUS'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging'),
    'IDX_ENTERPRISE_STAGING_SORT_ORDER'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging'),
    'FK_ENTERPRISE_STAGING_MASTER_WEBSITE_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging'),
    'FK_ENTERPRISE_STAGING_STAGING_WEBSITE_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging_action'),
    'IDX_STAGING_ACTION_STAGING_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging_action'),
    'IDX_STAGING_ACTION_STATUS'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging_action'),
    'IDX_STAGING_ACTION_VERSION'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging_action'),
    'FK_STAGING_ACTION_MASTER_WEBSITE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging_action'),
    'FK_STAGING_ACTION_STAGING_WEBSITE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging_action'),
    'IDX_STAGING_ACTION_TYPE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging_item'),
    'UNQ_ENTERPRISE_STAGING_ITEM'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging_item'),
    'IDX_ENTERPRISE_STAGING_ITEM_SORT_ORDER'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging_log'),
    'IDX_STAGING_LOG_STAGING_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging_log'),
    'IDX_STAGING_LOG_STATUS'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging_log'),
    'IDX_STAGING_LOG_IS_BACKUPED'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging_log'),
    'IDX_STAGING_LOG_NOTIFY'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging_log'),
    'FK_STAGING_LOG_MASTER_WEBSITE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_staging/staging_log'),
    'FK_STAGING_LOG_STAGING_WEBSITE'
);


/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_staging/staging') => array(
        'columns' => array(
            'staging_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Staging Id'
            ),
            'type' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'comment'   => 'Type'
            ),
            'master_website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Master Website Id'
            ),
            'staging_website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Staging Website Id'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'nullable'  => false,
                'comment'   => 'Created At'
            ),
            'updated_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'nullable'  => false,
                'comment'   => 'Updated At'
            ),
            'status' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 10,
                'comment'   => 'Status'
            ),
            'sort_order' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Sort Order'
            ),
            'merge_scheduling_date' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Merge Scheduling Date'
            ),
            'merge_scheduling_map' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Merge Scheduling Map'
            )
        ),
        'comment' => 'Enterprise Staging'
    ),
    $installer->getTable('enterprise_staging/staging_action') => array(
        'columns' => array(
            'action_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Action Id'
            ),
            'staging_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Staging Id'
            ),
            'type' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Type'
            ),
            'name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Name'
            ),
            'status' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 20,
                'comment'   => 'Status'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'nullable'  => false,
                'comment'   => 'Created At'
            ),
            'updated_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'nullable'  => false,
                'comment'   => 'Updated At'
            ),
            'staging_table_prefix' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Staging Table Prefix'
            ),
            'map' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Map'
            ),
            'mage_version' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'comment'   => 'Mage Version'
            ),
            'mage_modules_version' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Mage Modules Version'
            ),
            'staging_website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Staging Website Id'
            ),
            'master_website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Master Website Id'
            )
        ),
        'comment' => 'Enterprise Staging Action'
    ),
    $installer->getTable('enterprise_staging/staging_item') => array(
        'columns' => array(
            'staging_item_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Staging Item Id'
            ),
            'staging_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Staging Id'
            ),
            'code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'comment'   => 'Code'
            ),
            'sort_order' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Sort Order'
            )
        ),
        'comment' => 'Enterprise Staging Item'
    ),
    $installer->getTable('enterprise_staging/staging_log') => array(
        'columns' => array(
            'log_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Log Id'
            ),
            'staging_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Staging Id'
            ),
            'ip' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_BIGINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Ip'
            ),
            'action' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 20,
                'comment'   => 'Action'
            ),
            'status' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 20,
                'comment'   => 'Status'
            ),
            'is_backuped' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Is Backuped'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'nullable'  => false,
                'comment'   => 'Created At'
            ),
            'user_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'User Id'
            ),
            'username' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Username'
            ),
            'is_admin_notified' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Is Admin Notified'
            ),
            'additional_data' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Additional Data'
            ),
            'map' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Map'
            ),
            'staging_website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Staging Website Id'
            ),
            'staging_website_name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Staging Website Name'
            ),
            'master_website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Master Website Id'
            ),
            'master_website_name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Master Website Name'
            )
        ),
        'comment' => 'Enterprise Staging Log'
    ),
    $installer->getTable('core/website') => array(
        'columns' => array(
            'is_staging' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
                'nullable'  => false,
                'default'   => 0,
                'comment'   => 'Is Staging Flag'
            ),
            'master_login' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 40,
                'comment'   => 'Master Login'
            ),
            'master_password' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 100,
                'comment'   => 'Master Password'
            ),
            'visibility' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 40,
                'comment'   => 'Visibility'
            )
        )
    )
);

$installer->getConnection()->modifyTables($tables);


/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging'),
    $installer->getIdxName('enterprise_staging/staging', array('status')),
    array('status')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging'),
    $installer->getIdxName('enterprise_staging/staging', array('sort_order')),
    array('sort_order')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging'),
    $installer->getIdxName('enterprise_staging/staging', array('master_website_id')),
    array('master_website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging'),
    $installer->getIdxName('enterprise_staging/staging', array('staging_website_id')),
    array('staging_website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging_action'),
    $installer->getIdxName('enterprise_staging/staging_action', array('staging_id')),
    array('staging_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging_action'),
    $installer->getIdxName('enterprise_staging/staging_action', array('status')),
    array('status')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging_action'),
    $installer->getIdxName('enterprise_staging/staging_action', array('mage_version')),
    array('mage_version')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging_action'),
    $installer->getIdxName('enterprise_staging/staging_action', array('master_website_id')),
    array('master_website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging_action'),
    $installer->getIdxName('enterprise_staging/staging_action', array('staging_website_id')),
    array('staging_website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging_action'),
    $installer->getIdxName('enterprise_staging/staging_action', array('type')),
    array('type')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging_item'),
    $installer->getIdxName(
        'enterprise_staging/staging_item',
        array('staging_id', 'code'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('staging_id', 'code'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging_item'),
    $installer->getIdxName('enterprise_staging/staging_item', array('staging_id', 'sort_order')),
    array('staging_id', 'sort_order')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging_log'),
    $installer->getIdxName('enterprise_staging/staging_log', array('staging_id')),
    array('staging_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging_log'),
    $installer->getIdxName('enterprise_staging/staging_log', array('status')),
    array('status')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging_log'),
    $installer->getIdxName('enterprise_staging/staging_log', array('is_backuped')),
    array('is_backuped')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging_log'),
    $installer->getIdxName('enterprise_staging/staging_log', array('is_admin_notified')),
    array('is_admin_notified')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging_log'),
    $installer->getIdxName('enterprise_staging/staging_log', array('master_website_id')),
    array('master_website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_staging/staging_log'),
    $installer->getIdxName('enterprise_staging/staging_log', array('staging_website_id')),
    array('staging_website_id')
);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_staging/staging',
        'master_website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_staging/staging'),
    'master_website_id',
    $installer->getTable('core/website'),
    'website_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_staging/staging',
        'staging_website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_staging/staging'),
    'staging_website_id',
    $installer->getTable('core/website'),
    'website_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_staging/staging_action',
        'staging_website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_staging/staging_action'),
    'staging_website_id',
    $installer->getTable('core/website'),
    'website_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_staging/staging_action',
        'master_website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_staging/staging_action'),
    'master_website_id',
    $installer->getTable('core/website'),
    'website_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_staging/staging_item',
        'staging_id',
        'enterprise_staging/staging',
        'staging_id'
    ),
    $installer->getTable('enterprise_staging/staging_item'),
    'staging_id',
    $installer->getTable('enterprise_staging/staging'),
    'staging_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_staging/staging_log',
        'master_website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_staging/staging_log'),
    'master_website_id',
    $installer->getTable('core/website'),
    'website_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_staging/staging_log',
        'staging_website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_staging/staging_log'),
    'staging_website_id',
    $installer->getTable('core/website'),
    'website_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->endSetup();
