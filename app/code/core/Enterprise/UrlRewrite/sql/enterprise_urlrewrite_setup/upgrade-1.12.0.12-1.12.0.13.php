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
 * @package     Enterprise_UrlRewrite
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $this Mage_Core_Model_Resource_Setup */
$tableName = $this->getTable('enterprise_urlrewrite/url_rewrite');

$indexes = $this->getConnection()->getIndexList($tableName);
$indexName = $this->getIdxName(
    'enterprise_urlrewrite/url_rewrite',
    array('request_path'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);
$this->getConnection()->dropIndex($tableName, $indexName);
$this->getConnection()->addIndex(
    $tableName,
    $this->getIdxName(
        'enterprise_urlrewrite/url_rewrite',
        array('request_path', 'store_id', 'entity_type'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('request_path', 'store_id', 'entity_type'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$redirectTable = $this->getTable('enterprise_urlrewrite/redirect');
$this->getConnection()->addColumn($redirectTable, 'category_id', array(
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'nullable' => true,
    'unsigned' => true,
    'comment' => 'Category Id',
));
$this->getConnection()->addColumn($redirectTable, 'product_id', array(
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'nullable' => true,
    'unsigned' => true,
    'comment' => 'Product Id',
));

$this->getConnection()->addForeignKey(
    $this->getConnection()->getForeignKeyName(
        $redirectTable, 'category_id', $this->getTable('catalog/category'), 'entity_id'
    ),
    $redirectTable,
    'category_id',
    $this->getTable('catalog/category'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
);
$this->getConnection()->addForeignKey(
    $this->getConnection()->getForeignKeyName(
        $redirectTable, 'product_id', $this->getTable('catalog/product'), 'entity_id'
    ),
    $redirectTable,
    'product_id',
    $this->getTable('catalog/product'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
);
