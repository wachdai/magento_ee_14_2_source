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
 * @package     Enterprise_Cms
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
/* @var $installer Enterprise_Cms_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'enterprise_cms/page_version'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_cms/page_version'))
    ->addColumn('version_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Version Id')
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Label')
    ->addColumn('access_level', Varien_Db_Ddl_Table::TYPE_TEXT, 9, array(
        ), 'Access Level')
    ->addColumn('page_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        ), 'Page Id')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'User Id')
    ->addColumn('revisions_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Revisions Count')
    ->addColumn('version_number', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Version Number')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addIndex($installer->getIdxName('enterprise_cms/page_version', array('page_id')),
        array('page_id'))
    ->addIndex($installer->getIdxName('enterprise_cms/page_version', array('user_id')),
        array('user_id'))
    ->addIndex($installer->getIdxName('enterprise_cms/page_version', array('version_number')),
        array('version_number'))
    ->addForeignKey($installer->getFkName('enterprise_cms/page_version', 'page_id', 'cms/page', 'page_id'),
        'page_id', $installer->getTable('cms/page'), 'page_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_cms/page_version', 'user_id', 'admin/user', 'user_id'),
        'user_id', $installer->getTable('admin/user'), 'user_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Cms Page Version');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_cms/page_revision'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_cms/page_revision'))
    ->addColumn('revision_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Revision Id')
    ->addColumn('version_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Version Id')
    ->addColumn('page_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        ), 'Page Id')
    ->addColumn('root_template', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Root Template')
    ->addColumn('meta_keywords', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Meta Keywords')
    ->addColumn('meta_description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Meta Description')
    ->addColumn('content_heading', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Content Heading')
    ->addColumn('content', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'Content')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('layout_update_xml', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Layout Update Xml')
    ->addColumn('custom_theme', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        ), 'Custom Theme')
    ->addColumn('custom_root_template', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Custom Root Template')
    ->addColumn('custom_layout_update_xml', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Custom Layout Update Xml')
    ->addColumn('custom_theme_from', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Custom Theme From')
    ->addColumn('custom_theme_to', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Custom Theme To')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'User Id')
    ->addColumn('revision_number', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Revision Number')
    ->addIndex($installer->getIdxName('enterprise_cms/page_revision', array('version_id')),
        array('version_id'))
    ->addIndex($installer->getIdxName('enterprise_cms/page_revision', array('page_id')),
        array('page_id'))
    ->addIndex($installer->getIdxName('enterprise_cms/page_revision', array('user_id')),
        array('user_id'))
    ->addIndex($installer->getIdxName('enterprise_cms/page_revision', array('revision_number')),
        array('revision_number'))
    ->addForeignKey($installer->getFkName('enterprise_cms/page_revision', 'page_id', 'cms/page', 'page_id'),
        'page_id', $installer->getTable('cms/page'), 'page_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('enterprise_cms/page_revision', 'user_id', 'admin/user', 'user_id'),
        'user_id', $installer->getTable('admin/user'), 'user_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_cms/page_revision',
            'version_id',
            'enterprise_cms/page_version',
            'version_id'
        ),
        'version_id', $installer->getTable('enterprise_cms/page_version'), 'version_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Cms Page Revision');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_cms/increment'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_cms/increment'))
    ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Increment Id')
    ->addColumn('increment_type', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Increment Type')
    ->addColumn('increment_node', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Increment Node')
    ->addColumn('increment_level', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Increment Level')
    ->addColumn('last_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Last Id')
    ->addIndex($installer->getIdxName('enterprise_cms/increment',
        array('increment_type', 'increment_node', 'increment_level'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('increment_type', 'increment_node', 'increment_level'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->setComment('Enterprise Cms Increment');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_cms/hierarchy_node'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_cms/hierarchy_node'))
    ->addColumn('node_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Node Id')
    ->addColumn('parent_node_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Parent Node Id')
    ->addColumn('page_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        ), 'Page Id')
    ->addColumn('identifier', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        ), 'Identifier')
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Label')
    ->addColumn('level', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Level')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Sort Order')
    ->addColumn('request_url', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Request Url')
    ->addColumn('xpath', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Xpath')
    ->addIndex(
        $installer->getIdxName(
            'enterprise_cms/hierarchy_node',
            array('request_url'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('request_url'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('enterprise_cms/hierarchy_node', array('parent_node_id')),
        array('parent_node_id'))
    ->addIndex($installer->getIdxName('enterprise_cms/hierarchy_node', array('page_id')),
        array('page_id'))
    ->addForeignKey($installer->getFkName('enterprise_cms/hierarchy_node', 'page_id', 'cms/page', 'page_id'),
        'page_id', $installer->getTable('cms/page'), 'page_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_cms/hierarchy_node',
            'parent_node_id',
            'enterprise_cms/hierarchy_node',
            'node_id'
        ),
        'parent_node_id', $installer->getTable('enterprise_cms/hierarchy_node'), 'node_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Cms Hierarchy Node');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_cms/hierarchy_metadata'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_cms/hierarchy_metadata'))
    ->addColumn('node_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Node Id')
    ->addColumn('meta_first_last', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Meta First Last')
    ->addColumn('meta_next_previous', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Meta Next Previous')
    ->addColumn('meta_chapter', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Meta Chapter')
    ->addColumn('meta_section', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Meta Section')
    ->addColumn('meta_cs_enabled', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Meta Cs Enabled')
    ->addColumn('pager_visibility', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Pager Visibility')
    ->addColumn('pager_frame', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Pager Frame')
    ->addColumn('pager_jump', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Pager Jump')
    ->addColumn('menu_visibility', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Menu Visibility')
    ->addColumn('menu_excluded', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Menu Excluded')
    ->addColumn('menu_layout', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Menu Layout')
    ->addColumn('menu_brief', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Menu Brief')
    ->addColumn('menu_levels_down', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Menu Levels Down')
    ->addColumn('menu_ordered', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Menu Ordered')
    ->addColumn('menu_list_type', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Menu List Type')
    ->addForeignKey(
        $installer->getFkName(
            'enterprise_cms/hierarchy_metadata',
            'node_id',
            'enterprise_cms/hierarchy_node',
            'node_id'
        ),
        'node_id', $installer->getTable('enterprise_cms/hierarchy_node'), 'node_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Enterprise Cms Hierarchy Metadata');
$installer->getConnection()->createTable($table);

/**
 * Create table 'enterprise_cms/hierarchy_lock'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_cms/hierarchy_lock'))
    ->addColumn('lock_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Lock Id')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'User Id')
    ->addColumn('user_name', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'User Name')
    ->addColumn('session_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Session Id')
    ->addColumn('started_at', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Started At')
    ->setComment('Enterprise Cms Hierarchy Lock');
$installer->getConnection()->createTable($table);

// Add fields for cms/page table
$installer->getConnection()
    ->addColumn($installer->getTable('cms/page'), 'published_revision_id', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        'comment'   => 'Published Revision Id'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('cms/page'), 'website_root', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        'comment'   => 'Website Root'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('cms/page'), 'under_version_control', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        'comment'   => 'Under Version Control Flag'
    ));

$installer->endSetup();
