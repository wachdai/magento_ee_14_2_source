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

/** @var $this Mage_Core_Model_Resource_Setup */

/**
 * Create enterprise_url_rewrite table
 */
$table = $this->getConnection()
    ->newTable($this->getTable('enterprise_urlrewrite/url_rewrite'))
    ->addColumn('url_rewrite_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Url Rewrite Id')
    ->addColumn('request_path', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Request Path')
    ->addColumn('target_path', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Target path')
    ->addColumn('is_system', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'nullable'  => false,
        'unsigned'  => true
    ), 'Is url rewrite System')
    ->addColumn('guid', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'  => false,
    ), 'GUID')
    ->addColumn('identifier', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Unique url identifier')
    ->addColumn('inc', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true,
        'default'   => 1
    ), 'Url increment')
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true
    ), 'Entity table identifier')

    ->addIndex(
        $this->getIdxName('enterprise_urlrewrite/url_rewrite', array('request_path'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('request_path'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addIndex(
        $this->getIdxName('enterprise_urlrewrite/url_rewrite', array('identifier'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX),
        array('identifier'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    )
    ->addIndex(
    $this->getIdxName('enterprise_urlrewrite/url_rewrite', array('value_id', 'guid'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX),
    array('value_id', 'guid'),
    array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
)
    ->setComment('URL Rewrite');
$this->getConnection()->createTable($table);

/**
 * Create enterprise_url_rewrite_redirect table
 */
$table = $this->getConnection()
    ->newTable($this->getTable('enterprise_urlrewrite/redirect'))
    ->addColumn('redirect_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Redirect Id')
    ->addColumn('identifier', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Url identifier')
    ->addColumn('target_path', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Target path')
    ->addColumn('options', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => true,
    ), 'Redirect options')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => true,
    ), 'Description')
    ->addIndex(
        $this->getIdxName('enterprise_urlrewrite/redirect', array('identifier'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('identifier'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
)
    ->setComment('Permanent redirect');
$this->getConnection()->createTable($table);

/**
 * Create enterprise_url_rewrite_redirect_rewrite table
 */
$table = $this->getConnection()
    ->newTable($this->getTable('enterprise_urlrewrite/redirect_rewrite'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Relation Id')
    ->addColumn('redirect_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true,
    ), 'Redirect Id')
    ->addColumn('url_rewrite_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true,
    ), 'Rewrite Id')
    ->addIndex(
        $this->getIdxName(
            'enterprise_urlrewrite/redirect_rewrite', array('url_rewrite_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('url_rewrite_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
)
    ->addIndex(
        $this->getIdxName(
            'enterprise_urlrewrite/redirect_rewrite', array('redirect_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('redirect_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
)
    ->addForeignKey(
        $this->getFkName(
            'enterprise_urlrewrite/redirect_rewrite', 'url_rewrite_id',
            'enterprise_urlrewrite/url_rewrite', 'url_rewrite_id'
        ),
        'url_rewrite_id',
        $this->getTable('enterprise_urlrewrite/url_rewrite'),
        'url_rewrite_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_NO_ACTION
    )
    ->setComment('Relation between rewrites and redirects');

$this->getConnection()->createTable($table);
