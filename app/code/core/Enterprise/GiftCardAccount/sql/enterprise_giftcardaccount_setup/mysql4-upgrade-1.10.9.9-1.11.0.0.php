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
 * @package     Enterprise_GiftCardAccount
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_GiftCardAccount_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftcardaccount/giftcardaccount'),
    'FK_GIFTCARDACCOUNT_WEBSITE_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_giftcardaccount/history'),
    'FK_GIFTCARDACCOUNT_HISTORY_ACCOUNT'
);


/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftcardaccount/giftcardaccount'),
    'FK_GIFTCARDACCOUNT_WEBSITE_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_giftcardaccount/history'),
    'FK_GIFTCARDACCOUNT_HISTORY_ACCOUNT'
);


/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_giftcardaccount/giftcardaccount') => array(
        'columns' => array(
            'giftcardaccount_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Giftcardaccount Id'
            ),
            'code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false,
                'comment'   => 'Code'
            ),
            'status' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Status'
            ),
            'date_created' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
                'nullable'  => false,
                'comment'   => 'Date Created'
            ),
            'date_expires' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
                'comment'   => 'Date Expires'
            ),
            'website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Website Id'
            ),
            'balance' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Balance'
            ),
            'state' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'State'
            ),
            'is_redeemable' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '1',
                'comment'   => 'Is Redeemable'
            )
        ),
        'comment' => 'Enterprise Giftcardaccount'
    ),
    $installer->getTable('enterprise_giftcardaccount/pool') => array(
        'columns' => array(
            'code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Code'
            ),
            'status' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Status'
            )
        ),
        'comment' => 'Enterprise Giftcardaccount Pool'
    ),
    $installer->getTable('enterprise_giftcardaccount/history') => array(
        'columns' => array(
            'history_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'History Id'
            ),
            'giftcardaccount_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Giftcardaccount Id'
            ),
            'updated_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Updated At'
            ),
            'action' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Action'
            ),
            'balance_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Balance Amount'
            ),
            'balance_delta' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Balance Delta'
            ),
            'additional_info' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Additional Info'
            )
        ),
        'comment' => 'Enterprise Giftcardaccount History'
    )
);

$installer->getConnection()->modifyTables($tables);


/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftcardaccount/giftcardaccount'),
    $installer->getIdxName('enterprise_giftcardaccount/giftcardaccount', array('website_id')),
    array('website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_giftcardaccount/history'),
    $installer->getIdxName('enterprise_giftcardaccount/history', array('giftcardaccount_id')),
    array('giftcardaccount_id')
);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftcardaccount/giftcardaccount',
        'website_id',
        'core/website',
        'website_id'
    ),
    $installer->getTable('enterprise_giftcardaccount/giftcardaccount'),
    'website_id',
    $installer->getTable('core/website'),
    'website_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_giftcardaccount/history',
        'giftcardaccount_id',
        'enterprise_giftcardaccount/giftcardaccount',
        'giftcardaccount_id'
    ),
    $installer->getTable('enterprise_giftcardaccount/history'),
    'giftcardaccount_id',
    $installer->getTable('enterprise_giftcardaccount/giftcardaccount'),
    'giftcardaccount_id'
);

$installer->endSetup();
