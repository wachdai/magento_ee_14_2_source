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
 * @package     Enterprise_Reminder
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_Reminder_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_reminder/rule'),
    'FK_EE_REMINDER_SALESRULE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_reminder/coupon'),
    'FK_EE_REMINDER_RULE_COUPON'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_reminder/log'),
    'FK_EE_REMINDER_LOG_RULE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_reminder/website'),
    'FK_EE_REMINDER_RULE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_reminder/template'),
    'FK_EE_REMINDER_TEMPLATE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_reminder/template'),
    'FK_EE_REMINDER_TEMPLATE_RULE'
);


/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reminder/rule'),
    'IDX_EE_REMINDER_SALESRULE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reminder/log'),
    'IDX_EE_REMINDER_LOG_RULE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reminder/log'),
    'IDX_EE_REMINDER_LOG_CUSTOMER'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reminder/coupon'),
    'IDX_EE_REMINDER_RULE_COUPON'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reminder/website'),
    'IDX_EE_REMINDER_WEBSITE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reminder/template'),
    'IDX_EE_REMINDER_TEMPLATE_RULE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_reminder/template'),
    'IDX_EE_REMINDER_TEMPLATE'
);


/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_reminder/rule') => array(
        'columns' => array(
            'rule_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Rule Id'
            ),
            'name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => true,
                'default'   => null,
                'comment'   => 'Name'
            ),
            'description' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Description'
            ),
            'conditions_serialized' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'nullable'  => false,
                'length'    => '2M',
                'comment'   => 'Conditions Serialized'
            ),
            'condition_sql' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '2M',
                'comment'   => 'Condition Sql'
            ),
            'is_active' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Is Active'
            ),
            'salesrule_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Salesrule Id'
            ),
            'schedule' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Schedule'
            ),
            'default_label' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Default Label'
            ),
            'default_description' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Default Description'
            ),
            'active_from' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Active From'
            ),
            'active_to' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'comment'   => 'Active To'
            )
        ),
        'comment' => 'Enterprise Reminder Rule'
    ),
    $installer->getTable('enterprise_reminder/website') => array(
        'columns' => array(
            'rule_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Rule Id'
            ),
            'website_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Website Id'
            )
        ),
        'comment' => 'Enterprise Reminder Rule Website'
    ),
    $installer->getTable('enterprise_reminder/template') => array(
        'columns' => array(
            'rule_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Rule Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Store Id'
            ),
            'template_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Template Id'
            ),
            'label' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Label'
            ),
            'description' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Description'
            )
        ),
        'comment' => 'Enterprise Reminder Template'
    ),
    $installer->getTable('enterprise_reminder/coupon') => array(
        'columns' => array(
            'rule_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Rule Id'
            ),
            'coupon_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Coupon Id'
            ),
            'customer_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Customer Id'
            ),
            'associated_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'nullable'  => false,
                'comment'   => 'Associated At'
            ),
            'emails_failed' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Emails Failed'
            ),
            'is_active' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '1',
                'comment'   => 'Is Active'
            )
        ),
        'comment' => 'Enterprise Reminder Rule Coupon'
    ),
    $installer->getTable('enterprise_reminder/log') => array(
        'columns' => array(
            'log_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Log Id'
            ),
            'rule_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Rule Id'
            ),
            'customer_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Customer Id'
            ),
            'sent_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'nullable'  => false,
                'comment'   => 'Sent At'
            )
        ),
        'comment' => 'Enterprise Reminder Rule Log'
    )
);

$installer->getConnection()->modifyTables($tables);


/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reminder/rule'),
    $installer->getIdxName('enterprise_reminder/rule', array('salesrule_id')),
    array('salesrule_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reminder/coupon'),
    $installer->getIdxName('enterprise_reminder/coupon', array('rule_id')),
    array('rule_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reminder/log'),
    $installer->getIdxName('enterprise_reminder/log', array('rule_id')),
    array('rule_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reminder/log'),
    $installer->getIdxName('enterprise_reminder/log', array('customer_id')),
    array('customer_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reminder/website'),
    $installer->getIdxName('enterprise_reminder/website', array('website_id')),
    array('website_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reminder/template'),
    $installer->getIdxName('enterprise_reminder/template', array('template_id')),
    array('template_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_reminder/template'),
    $installer->getIdxName('enterprise_reminder/template', array('rule_id')),
    array('rule_id')
);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_reminder/rule',
        'salesrule_id',
        'salesrule/rule',
        'rule_id'
    ),
    $installer->getTable('enterprise_reminder/rule'),
    'salesrule_id',
    $installer->getTable('salesrule/rule'),
    'rule_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_reminder/coupon',
        'rule_id',
        'enterprise_reminder/rule',
        'rule_id'
    ),
    $installer->getTable('enterprise_reminder/coupon'),
    'rule_id',
    $installer->getTable('enterprise_reminder/rule'),
    'rule_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_reminder/log',
        'rule_id',
        'enterprise_reminder/rule',
        'rule_id'
    ),
    $installer->getTable('enterprise_reminder/log'),
    'rule_id',
    $installer->getTable('enterprise_reminder/rule'),
    'rule_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_reminder/website',
        'rule_id',
        'enterprise_reminder/rule',
        'rule_id'
    ),
    $installer->getTable('enterprise_reminder/website'),
    'rule_id',
    $installer->getTable('enterprise_reminder/rule'),
    'rule_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_reminder/template',
        'template_id',
        'core/email_template',
        'template_id'
    ),
    $installer->getTable('enterprise_reminder/template'),
    'template_id',
    $installer->getTable('core/email_template'),
    'template_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_reminder/template',
        'rule_id',
        'enterprise_reminder/rule',
        'rule_id'
    ),
    $installer->getTable('enterprise_reminder/template'),
    'rule_id',
    $installer->getTable('enterprise_reminder/rule'),
    'rule_id'
);

$installer->endSetup();
