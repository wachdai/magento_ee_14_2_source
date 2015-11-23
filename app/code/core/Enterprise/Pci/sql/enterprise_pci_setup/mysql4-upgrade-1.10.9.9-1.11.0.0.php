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
 * @package     Enterprise_Pci
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_Pci_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign key
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_pci/admin_passwords'),
    'FK_ADMIN_PASSWORDS_USER'
);

/**
 * Drop index
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_pci/admin_passwords'),
    'FK_ADMIN_PASSWORDS_USER'
);

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_pci/admin_passwords') => array(
        'columns' => array(
            'password_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Password Id'
            ),
            'user_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'User Id'
            ),
            'password_hash' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 100,
                'comment'   => 'Password Hash'
            ),
            'expires' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Expires'
            ),
            'last_updated' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Last Updated'
            )
        ),
        'comment' => 'Enterprise Admin Passwords'
    ),
    $installer->getTable('api/user') => array(
        'columns' => array(
            'api_key' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 100,
                'comment'   => 'Api key'
            )
        )
    ),
    $installer->getTable('admin/user') => array(
        'columns' => array(
            'password' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 100,
                'comment'   => 'User Password'
            ),
            'failures_num' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => true,
                'default'   => 0,
                'comment'   => 'Failure Number'
            ),
            'first_failure' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'First Failure'
            ),
            'lock_expires' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Expiration Lock Dates'
            )
        )
    )
);

$installer->getConnection()->modifyTables($tables);


/**
 * Add index
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_pci/admin_passwords'),
    $installer->getIdxName('enterprise_pci/admin_passwords', array('user_id')),
    array('user_id')
);

/**
 * Add foreign key
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_pci/admin_passwords',
        'user_id',
        'admin/user',
        'user_id'
    ),
    $installer->getTable('enterprise_pci/admin_passwords'),
    'user_id',
    $installer->getTable('admin/user'),
    'user_id'
);

$installer->endSetup();
