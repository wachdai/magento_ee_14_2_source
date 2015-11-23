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
 * @package     Enterprise_Search
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
    $installer->getTable('enterprise_search/recommendations'),
    'FK_EE_REMINDER_SEARCH_QUERY'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_search/recommendations'),
    'FK_EE_REMINDER_SEARCH_RELATION'
);


/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_search/recommendations'),
    'FK_EE_REMINDER_SEARCH_QUERY'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_search/recommendations'),
    'FK_EE_REMINDER_SEARCH_RELATION'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('catalogsearch/search_query'),
    'num_results'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('catalogsearch/search_query'),
    'query_text'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('catalogsearch/search_query'),
    'IDX_SEARCH_REC'
);


/**
 * Change columns
 */
$tables = array(
    $installer->getTable('enterprise_search/recommendations') => array(
        'columns' => array(
            'id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Id'
            ),
            'query_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Query Id'
            ),
            'relation_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Relation Id'
            )
        ),
        'comment' => 'Enterprise Search Recommendations'
    ),
    $installer->getTable('catalog/eav_attribute') => array(
        'columns' => array(
            'search_weight' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '1',
                'comment'   => 'Search Weight',
            )
        ),
        'comment' => 'Catalog EAV Attribute Table'
    )
);

$installer->getConnection()->modifyTables($tables);


/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('catalogsearch/search_query'),
    $installer->getIdxName('catalogsearch/search_query', array('num_results')),
    array('num_results')
);

$installer->getConnection()->addIndex(
    $installer->getTable('catalogsearch/search_query'),
    $installer->getIdxName('catalogsearch/search_query', array('query_text')),
    array('query_text')
);

$installer->getConnection()->addIndex(
    $installer->getTable('catalogsearch/search_query'),
    $installer->getIdxName('catalogsearch/search_query', array('query_text', 'store_id', 'num_results')),
    array('query_text', 'store_id', 'num_results')
);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_search/recommendations',
        'query_id',
        'catalogsearch/search_query',
        'query_id'
    ),
    $installer->getTable('enterprise_search/recommendations'),
    'query_id',
    $installer->getTable('catalogsearch/search_query'),
    'query_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_search/recommendations',
        'relation_id',
        'catalogsearch/search_query',
        'query_id'),
    $installer->getTable('enterprise_search/recommendations'),
    'relation_id',
    $installer->getTable('catalogsearch/search_query'),
    'query_id'
);

$installer->endSetup();
