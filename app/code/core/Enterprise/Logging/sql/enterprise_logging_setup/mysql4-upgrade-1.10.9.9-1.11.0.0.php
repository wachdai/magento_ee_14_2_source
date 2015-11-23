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
 * @package     Enterprise_Logging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_logging/event'),
    'FK_LOGGING_EVENT_USER'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_logging/event_changes'),
    'FK_LOGGING_EVENT_CHANGES_EVENT_ID'
);


/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_logging/event'),
    'FK_LOGGING_EVENT_USER'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_logging/event'),
    'IDX_LOGGING_EVENT_USERNAME'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_logging/event_changes'),
    'EVENT_ID'
);


/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_logging/event') => array(
        'columns' => array(
            'log_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Log Id'
            ),
            'ip' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_BIGINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Ip address'
            ),
            'x_forwarded_ip' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_BIGINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Real ip address if visitor used proxy'
            ),
            'event_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 100,
                'comment'   => 'Event Code'
            ),
            'time' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Even date'
            ),
            'action' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 20,
                'comment'   => 'Event action'
            ),
            'info' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Additional information'
            ),
            'status' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 15,
                'comment'   => 'Status'
            ),
            'user' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 40,
                'comment'   => 'User name'
            ),
            'user_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'User Id'
            ),
            'fullaction' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 200,
                'comment'   => 'Full action description'
            ),
            'error_message' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Error Message'
            )
        ),
        'comment' => 'Enterprise Logging Event'
    ),
    $installer->getTable('enterprise_logging/event_changes') => array(
        'columns' => array(
            'id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Enterprise logging id'
            ),
            'source_name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 150,
                'comment'   => 'Logged Source Name'
            ),
            'event_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Logged event id'
            ),
            'source_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'comment'   => 'Logged Source Id'
            ),
            'original_data' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Logged Original Data'
            ),
            'result_data' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Logged Result Data'
            )
        ),
        'comment' => 'Enterprise Logging Event Changes'
    )
);

$installer->getConnection()->modifyTables($tables);


/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_logging/event'),
    $installer->getIdxName('enterprise_logging/event', array('user_id')),
    array('user_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_logging/event'),
    $installer->getIdxName('enterprise_logging/event', array('user')),
    array('user')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_logging/event_changes'),
    $installer->getIdxName('enterprise_logging/event_changes', array('event_id')),
    array('event_id')
);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_logging/event',
        'user_id',
        'admin/user',
        'user_id'
    ),
    $installer->getTable('enterprise_logging/event'),
    'user_id',
    $installer->getTable('admin/user'),
    'user_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_logging/event_changes',
        'event_id',
        'enterprise_logging/event',
        'log_id'
    ),
    $installer->getTable('enterprise_logging/event_changes'),
    'event_id',
    $installer->getTable('enterprise_logging/event'),
    'log_id'
);

$installer->endSetup();
