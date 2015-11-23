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
 * @package     Enterprise_Banner
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_Banner_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
/**
 * Drop foreign keys
 */
$connection->dropForeignKey(
    $installer->getTable('enterprise_banner/catalogrule'),
    'FK_BANNER_CATALOGRULE_BANNER'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_banner/catalogrule'),
    'FK_BANNER_CATALOGRULE_RULE'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_banner/content'),
    'FK_BANNER_CONTENT_BANNER'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_banner/content'),
    'FK_BANNER_CONTENT_STORE'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_banner/customersegment'),
    'FK_BANNER_CUSTOMER_SEGMENT_BANNER'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_banner/customersegment'),
    'FK_BANNER_CUSTOMER_SEGMENT_SEGMENT'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_banner/salesrule'),
    'FK_BANNER_SALESRULE_BANNER'
);

$connection->dropForeignKey(
    $installer->getTable('enterprise_banner/salesrule'),
    'FK_BANNER_SALESRULE_RULE'
);


/**
 * Drop indexes
 */
$connection->dropIndex(
    $installer->getTable('enterprise_banner/catalogrule'),
    'BANNER_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_banner/catalogrule'),
    'RULE_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_banner/content'),
    'BANNER_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_banner/content'),
    'STORE_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_banner/customersegment'),
    'BANNER_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_banner/customersegment'),
    'SEGMENT_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_banner/salesrule'),
    'BANNER_ID'
);

$connection->dropIndex(
    $installer->getTable('enterprise_banner/salesrule'),
    'RULE_ID'
);

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_banner/banner') => array(
        'columns' => array(
            'banner_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Banner Id'
            ),
            'name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Name'
            ),
            'is_enabled' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'nullable'  => false,
                'comment'   => 'Is Enabled'
            ),
            'types' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Types'
            )
        ),
        'comment' => 'Enterprise Banner'
    ),
    $installer->getTable('enterprise_banner/content') => array(
        'columns' => array(
            'banner_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
                'comment'   => 'Banner Id'
            ),
            'store_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
                'comment'   => 'Store Id'
            ),
            'banner_content' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '2M',
                'comment'   => 'Banner Content'
            )
        ),
        'comment' => 'Enterprise Banner Content'
    ),
    $installer->getTable('enterprise_banner/customersegment') => array(
        'columns' => array(
            'banner_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
                'comment'   => 'Banner Id'
            ),
            'segment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
                'comment'   => 'Segment Id'
            )
        ),
        'comment' => 'Enterprise Banner Customersegment'
    ),
    $installer->getTable('enterprise_banner/catalogrule') => array(
        'columns' => array(
            'banner_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Banner Id'
            ),
            'rule_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Rule Id'
            )
        ),
        'comment' => 'Enterprise Banner Catalogrule'
    ),
    $installer->getTable('enterprise_banner/salesrule') => array(
        'columns' => array(
            'banner_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Banner Id'
            ),
            'rule_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Rule Id'
            )
        ),
        'comment' => 'Enterprise Banner Salesrule'
    )
);

$installer->getConnection()->modifyTables($tables);


/**
 * Add indexes
 */
$connection->addIndex(
    $installer->getTable('enterprise_banner/catalogrule'),
    $installer->getIdxName('enterprise_banner/catalogrule', array('banner_id')),
    array('banner_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_banner/catalogrule'),
    $installer->getIdxName('enterprise_banner/catalogrule', array('rule_id')),
    array('rule_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_banner/content'),
    $installer->getIdxName('enterprise_banner/content', array('banner_id')),
    array('banner_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_banner/content'),
    $installer->getIdxName('enterprise_banner/content', array('store_id')),
    array('store_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_banner/customersegment'),
    $installer->getIdxName('enterprise_banner/customersegment', array('banner_id')),
    array('banner_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_banner/customersegment'),
    $installer->getIdxName('enterprise_banner/customersegment', array('segment_id')),
    array('segment_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_banner/salesrule'),
    $installer->getIdxName('enterprise_banner/salesrule', array('banner_id')),
    array('banner_id')
);

$connection->addIndex(
    $installer->getTable('enterprise_banner/salesrule'),
    $installer->getIdxName('enterprise_banner/salesrule', array('rule_id')),
    array('rule_id')
);


/**
 * Add foreign keys
 */
$connection->addForeignKey(
    $installer->getFkName(
        'enterprise_banner/catalogrule',
        'banner_id',
        'enterprise_banner/banner',
        'banner_id'
    ),
    $installer->getTable('enterprise_banner/catalogrule'),
    'banner_id',
    $installer->getTable('enterprise_banner/banner'),
    'banner_id'
);

$connection->addForeignKey(
    $installer->getFkName(
        'enterprise_banner/catalogrule',
        'rule_id',
        'catalogrule/rule',
        'rule_id'
    ),
    $installer->getTable('enterprise_banner/catalogrule'),
    'rule_id',
    $installer->getTable('catalogrule/rule'),
    'rule_id'
);

$connection->addForeignKey(
    $installer->getFkName(
        'enterprise_banner/content',
        'banner_id',
        'enterprise_banner/banner',
        'banner_id'),
    $installer->getTable('enterprise_banner/content'),
    'banner_id',
    $installer->getTable('enterprise_banner/banner'),
    'banner_id'
);

$connection->addForeignKey(
    $installer->getFkName(
        'enterprise_banner/content',
        'store_id',
        'core/store',
        'store_id'
    ),
    $installer->getTable('enterprise_banner/content'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$connection->addForeignKey(
    $installer->getFkName(
        'enterprise_banner/customersegment',
        'banner_id',
        'enterprise_banner/banner',
        'banner_id'
    ),
    $installer->getTable('enterprise_banner/customersegment'),
    'banner_id',
    $installer->getTable('enterprise_banner/banner'),
    'banner_id'
);

$connection->addForeignKey(
    $installer->getFkName(
        'enterprise_banner/customersegment',
        'segment_id',
        'enterprise_customersegment/segment',
        'segment_id'
    ),
    $installer->getTable('enterprise_banner/customersegment'),
    'segment_id',
    $installer->getTable('enterprise_customersegment/segment'),
    'segment_id'
);

$connection->addForeignKey(
    $installer->getFkName(
        'enterprise_banner/salesrule',
        'banner_id',
        'enterprise_banner/banner',
        'banner_id'
    ),
    $installer->getTable('enterprise_banner/salesrule'),
    'banner_id',
    $installer->getTable('enterprise_banner/banner'),
    'banner_id'
);

$connection->addForeignKey(
    $installer->getFkName(
        'enterprise_banner/salesrule',
        'rule_id',
        'salesrule',
        'rule_id'
    ),
    $installer->getTable('enterprise_banner/salesrule'),
    'rule_id',
    $installer->getTable('salesrule/rule'),
    'rule_id'
);

$installer->endSetup();
