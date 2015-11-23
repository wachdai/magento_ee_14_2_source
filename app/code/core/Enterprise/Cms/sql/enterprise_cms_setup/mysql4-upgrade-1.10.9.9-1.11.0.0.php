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
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_cms/hierarchy_metadata'),
    'FK_ENTERPRISE_CMS_HIERARCHY_METADATA_NODE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_cms/hierarchy_node'),
    'FK_ENTERPRISE_CMS_HIERARCHY_NODE_PAGE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_cms/hierarchy_node'),
    'FK_ENTERPRISE_CMS_HIERARCHY_NODE_PARENT_NODE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_cms/page_revision'),
    'FK_CMS_REVISION_PAGE_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_cms/page_revision'),
    'FK_CMS_REVISION_USER_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_cms/page_revision'),
    'FK_CMS_REVISION_VERSION_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_cms/page_version'),
    'FK_CMS_VERSION_PAGE_ID'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('enterprise_cms/page_version'),
    'FK_CMS_VERSION_USER_ID'
);


/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_cms/hierarchy_node'),
    'UNQ_REQUEST_URL'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_cms/hierarchy_node'),
    'IDX_PARENT_NODE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_cms/hierarchy_node'),
    'IDX_PAGE'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_cms/increment'),
    'IDX_TYPE_NODE_LEVEL'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_cms/page_version'),
    'IDX_PAGE_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_cms/page_version'),
    'IDX_USER_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_cms/page_version'),
    'IDX_VERSION_NUMBER'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_cms/page_revision'),
    'IDX_VERSION_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_cms/page_revision'),
    'IDX_PAGE_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_cms/page_revision'),
    'IDX_USER_ID'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('enterprise_cms/page_revision'),
    'IDX_REVISION_NUMBER'
);


/**
 * Change tables columns
 */
$tables = array(
    $installer->getTable('enterprise_cms/page_version') => array(
        'columns' => array(
            'version_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Version Id'
            ),
            'label' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Label'
            ),
            'access_level' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 9,
                'comment'   => 'Access Level'
            ),
            'page_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Page Id'
            ),
            'user_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'User Id'
            ),
            'revisions_count' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Revisions Count'
            ),
            'version_number' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Version Number'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'nullable'  => false,
                'comment'   => 'Created At'
            )
        ),
        'comment' => 'Enterprise Cms Page Version'
    ),
    $installer->getTable('enterprise_cms/page_revision') => array(
        'columns' => array(
            'revision_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Revision Id'
            ),
            'version_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Version Id'
            ),
            'page_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'comment'   => 'Page Id'
            ),
            'root_template' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Root Template'
            ),
            'meta_keywords' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Meta Keywords'
            ),
            'meta_description' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Meta Description'
            ),
            'content_heading' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Content Heading'
            ),
            'content' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '2M',
                'comment'   => 'Content'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
                'nullable'  => false,
                'comment'   => 'Created At'
            ),
            'layout_update_xml' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Layout Update Xml'
            ),
            'custom_theme' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 100,
                'comment'   => 'Custom Theme'
            ),
            'custom_root_template' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Custom Root Template'
            ),
            'custom_layout_update_xml' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '64K',
                'comment'   => 'Custom Layout Update Xml'
            ),
            'custom_theme_from' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
                'comment'   => 'Custom Theme From'
            ),
            'custom_theme_to' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
                'comment'   => 'Custom Theme To'
            ),
            'user_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'User Id'
            ),
            'revision_number' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Revision Number'
            )
        ),
        'comment' => 'Enterprise Cms Page Revision'
    ),
    $installer->getTable('enterprise_cms/increment') => array(
        'columns' => array(
            'increment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Increment Id'
            ),
            'last_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Last Id'
            )
        ),
        'comment' => 'Enterprise Cms Increment'
    ),
    $installer->getTable('enterprise_cms/hierarchy_metadata') => array(
        'columns' => array(
            'node_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Node Id'
            ),
            'meta_first_last' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Meta First Last'
            ),
            'meta_next_previous' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Meta Next Previous'
            ),
            'meta_chapter' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Meta Chapter'
            ),
            'meta_section' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Meta Section'
            ),
            'meta_cs_enabled' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Meta Cs Enabled'
            ),
            'pager_visibility' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Pager Visibility'
            ),
            'pager_frame' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Pager Frame'
            ),
            'pager_jump' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Pager Jump'
            ),
            'menu_visibility' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Menu Visibility'
            ),
            'menu_excluded' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Menu Excluded'
            ),
            'menu_layout' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'comment'   => 'Menu Layout'
            ),
            'menu_brief' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Menu Brief'
            ),
            'menu_levels_down' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Menu Levels Down'
            ),
            'menu_ordered' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Menu Ordered'
            ),
            'menu_list_type' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'comment'   => 'Menu List Type'
            )
        ),
        'comment' => 'Enterprise Cms Hierarchy Metadata'
    ),
    $installer->getTable('enterprise_cms/hierarchy_node') => array(
        'columns' => array(
            'node_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Node Id'
            ),
            'parent_node_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Parent Node Id'
            ),
            'page_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'comment'   => 'Page Id'
            ),
            'identifier' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 100,
                'comment'   => 'Identifier'
            ),
            'label' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Label'
            ),
            'level' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Level'
            ),
            'sort_order' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Sort Order'
            ),
            'request_url' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Request Url'
            ),
            'xpath' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Xpath'
            )
        ),
        'comment' => 'Enterprise Cms Hierarchy Node'
    ),
    $installer->getTable('enterprise_cms/hierarchy_lock') => array(
        'columns' => array(
            'lock_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Lock Id'
            ),
            'user_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'User Id'
            ),
            'user_name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'comment'   => 'User Name'
            ),
            'session_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'comment'   => 'Session Id'
            ),
            'started_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Started At'
            )
        ),
        'comment' => 'Enterprise Cms Hierarchy Lock'
    ),
    $installer->getTable('cms/page') => array(
        'columns' => array(
            'published_revision_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Published Revision Id'
            ),
            'website_root' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '1',
                'comment'   => 'Website Root'
            ),
            'under_version_control' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Under Version Control Flag'
            )
        )
    )
);

$installer->getConnection()->modifyTables($tables);

$installer->getConnection()->changeColumn(
    $installer->getTable('enterprise_cms/increment'),
    'type',
    'increment_type',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable'  => false,
        'comment'   => 'Increment Type'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('enterprise_cms/increment'),
    'node',
    'increment_node',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => false,
        'comment'   => 'Increment Node'
    )
);

$installer->getConnection()->changeColumn(
    $installer->getTable('enterprise_cms/increment'),
    'level',
    'increment_level',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => false,
        'comment'   => 'Increment Level'
    )
);


/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_cms/hierarchy_node'),
    $installer->getIdxName(
        'enterprise_cms/hierarchy_node',
        array('request_url'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('request_url'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_cms/hierarchy_node'),
    $installer->getIdxName('enterprise_cms/hierarchy_node', array('parent_node_id')),
    array('parent_node_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_cms/hierarchy_node'),
    $installer->getIdxName('enterprise_cms/hierarchy_node', array('page_id')),
    array('page_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_cms/increment'),
    $installer->getIdxName(
        'enterprise_cms/increment',
        array('increment_type', 'increment_node', 'increment_level'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('increment_type', 'increment_node', 'increment_level'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_cms/page_revision'),
    $installer->getIdxName('enterprise_cms/page_revision', array('version_id')),
    array('version_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_cms/page_revision'),
    $installer->getIdxName('enterprise_cms/page_revision', array('page_id')),
    array('page_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_cms/page_revision'),
    $installer->getIdxName('enterprise_cms/page_revision', array('user_id')),
    array('user_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_cms/page_revision'),
    $installer->getIdxName('enterprise_cms/page_revision', array('revision_number')),
    array('revision_number')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_cms/page_version'),
    $installer->getIdxName('enterprise_cms/page_version', array('page_id')),
    array('page_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_cms/page_version'),
    $installer->getIdxName('enterprise_cms/page_version', array('user_id')),
    array('user_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('enterprise_cms/page_version'),
    $installer->getIdxName('enterprise_cms/page_version', array('version_number')),
    array('version_number')
);


/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_cms/hierarchy_metadata',
        'node_id',
        'enterprise_cms/hierarchy_node',
        'node_id'
    ),
    $installer->getTable('enterprise_cms/hierarchy_metadata'),
    'node_id',
    $installer->getTable('enterprise_cms/hierarchy_node'),
    'node_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_cms/hierarchy_node',
        'page_id',
        'cms_page',
        'page_id'
    ),
    $installer->getTable('enterprise_cms/hierarchy_node'),
    'page_id',
    $installer->getTable('cms_page'),
    'page_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_cms/hierarchy_node',
        'parent_node_id',
        'enterprise_cms/hierarchy_node',
        'node_id'
    ),
    $installer->getTable('enterprise_cms/hierarchy_node'),
    'parent_node_id',
    $installer->getTable('enterprise_cms/hierarchy_node'),
    'node_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_cms/page_revision',
        'page_id',
        'cms/page',
        'page_id'
    ),
    $installer->getTable('enterprise_cms/page_revision'),
    'page_id',
    $installer->getTable('cms/page'),
    'page_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_cms/page_revision',
        'user_id',
        'admin/user',
        'user_id'
    ),
    $installer->getTable('enterprise_cms/page_revision'),
    'user_id',
    $installer->getTable('admin/user'),
    'user_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_cms/page_revision',
        'version_id',
        'enterprise_cms/page_version',
        'version_id'),
    $installer->getTable('enterprise_cms/page_revision'),
    'version_id',
    $installer->getTable('enterprise_cms/page_version'),
    'version_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_cms/page_version',
        'page_id',
        'cms/page',
        'page_id'
    ),
    $installer->getTable('enterprise_cms/page_version'),
    'page_id',
    $installer->getTable('cms/page'),
    'page_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'enterprise_cms/page_version',
        'user_id',
        'admin/user',
        'user_id'
    ),
    $installer->getTable('enterprise_cms/page_version'),
    'user_id',
    $installer->getTable('admin/user'),
    'user_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL
);

$installer->endSetup();
