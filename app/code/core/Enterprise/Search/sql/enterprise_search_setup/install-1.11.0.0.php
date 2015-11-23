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

$installer = $this;
/** @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('catalog/eav_attribute'), 'search_weight', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        'comment'   => 'Search Weight',
    ));
$installer->getConnection()->addIndex($installer->getTable('catalogsearch/search_query'),
    $installer->getIdxName('catalogsearch/search_query', array('num_results')),
    'num_results');
$installer->getConnection()->addIndex($installer->getTable('catalogsearch/search_query'),
    $installer->getIdxName('catalogsearch/search_query', array('query_text')),
    'query_text');
$installer->getConnection()->addIndex($installer->getTable('catalogsearch/search_query'),
    $installer->getIdxName('catalogsearch/search_query', array('query_text', 'store_id', 'num_results')),
    array('query_text', 'store_id', 'num_results'));

$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_search/recommendations'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('query_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Query Id')
    ->addColumn('relation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Relation Id')
    ->addForeignKey($installer->getFkName('enterprise_search/recommendations', 'query_id', 'catalogsearch/search_query', 'query_id'),
        'query_id', $installer->getTable('catalogsearch/search_query'), 'query_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_search/recommendations', 'relation_id', 'catalogsearch/search_query', 'query_id'),
        'relation_id', $installer->getTable('catalogsearch/search_query'), 'query_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Search Recommendations');
$installer->getConnection()->createTable($table);

$installer->endSetup();
