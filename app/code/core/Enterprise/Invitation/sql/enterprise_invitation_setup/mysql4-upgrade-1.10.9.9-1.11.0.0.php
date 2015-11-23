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
 * @package     Enterprise_Invitation
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$connection = $installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_invitation/invitation'),
    'FK_INVITATION_CUSTOMER'
);

$connection = $installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_invitation/invitation'),
    'FK_INVITATION_CUSTOMER_GROUP'
);

$connection = $installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_invitation/invitation'),
    'FK_INVITATION_REFERRAL'
);

$connection = $installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_invitation/invitation'),
    'FK_INVITATION_STORE'
);

$connection = $installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_invitation/invitation_history'),
    'FK_INVITATION_HISTORY_INVITATION'
);

$connection = $installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_invitation/invitation_history'),
    'FK_ENTERPRISE_INVITATION_STATUS_HISTORY_INVITATION'
);

$connection = $installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_invitation/invitation_track'),
    'FK_INVITATION_TRACK_INVITER'
);

$connection = $installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_invitation/invitation_track'),
    'FK_INVITATION_TRACK_REFERRAL'
);


/**
 * Drop indexes
 */
$connection = $installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_invitation/invitation'),
    'IDX_CUSTOMER_ID'
);

$connection = $installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_invitation/invitation'),
    'IDX_REFERRAL_ID'
);

$connection = $installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_invitation/invitation'),
    'FK_INVITATION_STORE'
);

$connection = $installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_invitation/invitation'),
    'FK_INVITATION_CUSTOMER_GROUP'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_invitation/invitation_history'),
    'IDX_INVITATION_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_invitation/invitation_track'),
    'UNQ_INVITATION_TRACK_IDS'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_invitation/invitation_track'),
    'FK_INVITATION_TRACK_REFERRAL'
);


/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_invitation/invitation') => array(
        'columns' => array(
            'invitation_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Invitation Id'
            ),
            'customer_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Customer Id'
            ),
            'email' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Email'
            ),
            'referral_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Referral Id'
            ),
            'protection_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 32,
                'comment'   => 'Protection Code'
            ),
            'signup_date' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Signup Date'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Store Id'
            ),
            'group_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Group Id'
            ),
            'message' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Message'
            ),
            'status' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 8,
                'nullable'  => false,
                'default'   => 'new',
                'comment'   => 'Status'
            )
        ),
        'comment' => 'Enterprise Invitation'
    ),
    $installer->getTable('enterprise_invitation/invitation_history') => array(
        'columns' => array(
            'history_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'History Id'
            ),
            'invitation_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Invitation Id'
            ),
            'status' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 8,
                'nullable'  => false,
                'default'   => 'new',
                'comment'   => 'Invitation Status'
            )
        ),
        'comment' => 'Enterprise Invitation Status History'
    ),
    $installer->getTable('enterprise_invitation/invitation_track') => array(
        'columns' => array(
            'track_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Track Id'
            ),
            'inviter_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Inviter Id'
            ),
            'referral_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Referral Id'
            )
        ),
        'comment' => 'Enterprise Invitation Track'
    )
);

$installer->getConnection()->modifyTables($tables);

$installer->getConnection()->changeColumn(
    $installer->getTable('enterprise_invitation/invitation'),
    'date',
    'invitation_date',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'comment'   => 'Invitation Date'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('enterprise_invitation/invitation_history'),
    'date',
    'invitation_date',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'comment'   => 'Invitation Date'
    )
);

/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_invitation/invitation'),
    $installer->getIdxName('enterprise_invitation/invitation', array('customer_id')),
    array('customer_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_invitation/invitation'),
    $installer->getIdxName('enterprise_invitation/invitation', array('referral_id')),
    array('referral_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_invitation/invitation'),
    $installer->getIdxName('enterprise_invitation/invitation', array('store_id')),
    array('store_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_invitation/invitation'),
    $installer->getIdxName('enterprise_invitation/invitation', array('group_id')),
    array('group_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_invitation/invitation_history'),
    $installer->getIdxName('enterprise_invitation/invitation_history', array('invitation_id')),
    array('invitation_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_invitation/invitation_track'),
    $installer->getIdxName(
        'enterprise_invitation/invitation_track',
        array('inviter_id', 'referral_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('inviter_id', 'referral_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_invitation/invitation_track'),
    $installer->getIdxName('enterprise_invitation/invitation_track', array('referral_id')),
    array('referral_id')
);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_invitation/invitation', 'group_id', 'customer/customer_group', 'customer_group_id'
    ),
    $installer->getTable('enterprise_invitation/invitation'),
    'group_id',
    $installer->getTable('customer/customer_group'),
    'customer_group_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('enterprise_invitation/invitation', 'customer_id', 'customer/entity', 'entity_id'),
    $installer->getTable('enterprise_invitation/invitation'),
    'customer_id',
    $installer->getTable('customer/entity'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('enterprise_invitation/invitation', 'referral_id', 'customer/entity', 'entity_id'),
    $installer->getTable('enterprise_invitation/invitation'),
    'referral_id',
    $installer->getTable('customer/entity'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('enterprise_invitation/invitation', 'store_id', 'core/store', 'store_id'),
    $installer->getTable('enterprise_invitation/invitation'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_invitation/invitation_history', 'invitation_id', 'enterprise_invitation/invitation', 'invitation_id'
    ),
    $installer->getTable('enterprise_invitation/invitation_history'),
    'invitation_id',
    $installer->getTable('enterprise_invitation/invitation'),
    'invitation_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('enterprise_invitation/invitation_track', 'inviter_id', 'customer/entity', 'entity_id'),
    $installer->getTable('enterprise_invitation/invitation_track'),
    'inviter_id',
    $installer->getTable('customer/entity'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('enterprise_invitation/invitation_track', 'referral_id', 'customer/entity', 'entity_id'),
    $installer->getTable('enterprise_invitation/invitation_track'),
    'referral_id',
    $installer->getTable('customer/entity'),
    'entity_id'
);

$installer->endSetup();
