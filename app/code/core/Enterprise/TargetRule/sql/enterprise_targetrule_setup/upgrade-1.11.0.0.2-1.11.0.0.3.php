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
 * @package     Enterprise_TargetRule
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_TargetRule_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('enterprise_targetrule/index'),
    'customer_segment_id',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable' => false,
        'default'  => '0',
        'comment'  => 'Customer Segment Id'
    )
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/index'),
    $installer->getConnection()->getPrimaryKeyName($installer->getTable('enterprise_targetrule/index')),
    array(
        'entity_id',
        'store_id',
        'customer_group_id',
        'type_id',
        'customer_segment_id'
    ),
    Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY
);

$installer->getConnection()->addColumn(
    $installer->getTable('enterprise_targetrule/index_related'),
    'customer_segment_id',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned' => true,
        'nullable' => false,
        'default'  => '0',
        'comment'  => 'Customer Segment Id'
    )
);
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/index_related'),
    $installer->getConnection()->getPrimaryKeyName($installer->getTable('enterprise_targetrule/index_related')),
    array(
        'entity_id',
        'store_id',
        'customer_group_id',
        'customer_segment_id'
    ),
    Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY
);

$installer->getConnection()->addColumn(
    $installer->getTable('enterprise_targetrule/index_upsell'),
    'customer_segment_id',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned' => true,
        'nullable' => false,
        'default'  => '0',
        'comment'  => 'Customer Segment Id'
    )
);
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/index_upsell'),
    $installer->getConnection()->getPrimaryKeyName($installer->getTable('enterprise_targetrule/index_upsell')),
    array(
        'entity_id',
        'store_id',
        'customer_group_id',
        'customer_segment_id'
    ),
    Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY
);

$installer->getConnection()->addColumn(
    $installer->getTable('enterprise_targetrule/index_crosssell'),
    'customer_segment_id',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned' => true,
        'nullable' => false,
        'default'  => '0',
        'comment'  => 'Customer Segment Id'
    )
);
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_targetrule/index_crosssell'),
    $installer->getConnection()->getPrimaryKeyName($installer->getTable('enterprise_targetrule/index_crosssell')),
    array(
        'entity_id',
        'store_id',
        'customer_group_id',
        'customer_segment_id'
    ),
    Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY
);

$installer->endSetup();
