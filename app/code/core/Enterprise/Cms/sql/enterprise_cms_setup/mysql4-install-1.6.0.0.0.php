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

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_cms/page_version')}` (
  `version_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(255) DEFAULT NULL,
  `access_level` ENUM('".Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PRIVATE."',
                '".Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PROTECTED."',
                '".Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PUBLIC."') NOT NULL,
  `page_id` SMALLINT(6) NOT NULL,
  `user_id` MEDIUMINT(9) UNSIGNED DEFAULT NULL,
  `revisions_count` INT(11) UNSIGNED DEFAULT NULL,
  `version_number` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`version_id`),
  KEY `IDX_PAGE_ID` (`page_id`),
  KEY `IDX_USER_ID` (`user_id`),
  KEY `IDX_VERSION_NUMBER` (`version_number`),
  CONSTRAINT `FK_CMS_VERSION_PAGE_ID` FOREIGN KEY (`page_id`) REFERENCES `{$installer->getTable('cms/page')}` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CMS_VERSION_USER_ID` FOREIGN KEY (`user_id`) REFERENCES `{$installer->getTable('admin/user')}` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_cms/page_revision')}` (
  `revision_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `version_id` INT(10) UNSIGNED NOT NULL,
  `page_id` SMALLINT(6) NOT NULL,
  `root_template` VARCHAR(255) NOT NULL DEFAULT '',
  `meta_keywords` TEXT NOT NULL,
  `meta_description` TEXT NOT NULL,
  `content` MEDIUMTEXT,
  `created_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `layout_update_xml` TEXT,
  `custom_theme` VARCHAR(100) DEFAULT NULL,
  `custom_root_template` VARCHAR(255) NOT NULL DEFAULT '',
  `custom_layout_update_xml` TEXT,
  `custom_theme_from` DATE DEFAULT NULL,
  `custom_theme_to` DATE DEFAULT NULL,
  `user_id` MEDIUMINT(9) UNSIGNED DEFAULT NULL,
  `revision_number` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`revision_id`),
  KEY `IDX_VERSION_ID` (`version_id`),
  KEY `IDX_PAGE_ID` (`page_id`),
  KEY `IDX_USER_ID` (`user_id`),
  KEY `IDX_REVISION_NUMBER` (`revision_number`),
  CONSTRAINT `FK_CMS_REVISION_PAGE_ID` FOREIGN KEY (`page_id`) REFERENCES `{$installer->getTable('cms/page')}` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CMS_REVISION_USER_ID` FOREIGN KEY (`user_id`) REFERENCES `{$installer->getTable('admin/user')}` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_CMS_REVISION_VERSION_ID` FOREIGN KEY (`version_id`) REFERENCES `{$installer->getTable('enterprise_cms/page_version')}` (`version_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_cms/increment')}` (
  `increment_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` INT(10) NOT NULL,
  `node` INT(10) UNSIGNED NOT NULL,
  `level` INT(10) UNSIGNED NOT NULL,
  `last_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`increment_id`),
  UNIQUE KEY `IDX_TYPE_NODE_LEVEL` (`type`,`node`,`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_cms/hierarchy_metadata')}` (
  `node_id` INT(10) UNSIGNED NOT NULL,
  `meta_first_last` TINYINT(1) NOT NULL DEFAULT '0',
  `meta_next_previous` TINYINT(1) NOT NULL DEFAULT '0',
  `meta_chapter` TINYINT(1) NOT NULL DEFAULT '0',
  `meta_section` TINYINT(1) NOT NULL DEFAULT '0',
  `pager_visibility` TINYINT(4) UNSIGNED NOT NULL,
  `pager_frame` SMALLINT(6) UNSIGNED NOT NULL,
  `pager_jump` SMALLINT(6) UNSIGNED NOT NULL,
  `menu_visibility` TINYINT(4) UNSIGNED NOT NULL,
  `menu_levels_up` TINYINT(4) UNSIGNED NOT NULL,
  `menu_levels_down` TINYINT(4) UNSIGNED NOT NULL,
  `menu_ordered` TINYINT(4) UNSIGNED NOT NULL,
  `menu_list_type` VARCHAR(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`node_id`),
  CONSTRAINT `FK_ENTERPRISE_CMS_HIERARCHY_METADATA_NODE` FOREIGN KEY (`node_id`) REFERENCES `{$installer->getTable('enterprise_cms/hierarchy_node')}` (`node_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_cms/hierarchy_node')}` (
  `node_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_node_id` INT(10) UNSIGNED DEFAULT NULL,
  `page_id` SMALLINT(6) DEFAULT NULL,
  `identifier` VARCHAR(100) DEFAULT NULL,
  `label` VARCHAR(255) DEFAULT NULL,
  `level` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `sort_order` INT(11) NOT NULL,
  `request_url` VARCHAR(255) NOT NULL,
  `xpath` VARCHAR(255) DEFAULT '',
  PRIMARY KEY (`node_id`),
  UNIQUE KEY `UNQ_REQUEST_URL` (`request_url`),
  KEY `IDX_PARENT_NODE` (`parent_node_id`),
  KEY `IDX_PAGE` (`page_id`),
  CONSTRAINT `FK_ENTERPRISE_CMS_HIERARCHY_NODE_PAGE` FOREIGN KEY (`page_id`) REFERENCES `{$installer->getTable('cms/page')}` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ENTERPRISE_CMS_HIERARCHY_NODE_PARENT_NODE` FOREIGN KEY (`parent_node_id`) REFERENCES `{$installer->getTable('enterprise_cms/hierarchy_node')}` (`node_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_cms_widget_instance')}` (
  `instance_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(255) NOT NULL DEFAULT '',
  `package_theme` VARCHAR(255) NOT NULL DEFAULT '',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `store_ids` VARCHAR(255) NOT NULL DEFAULT '0',
  `widget_parameters` TEXT,
  `sort_order` SMALLINT(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`instance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_cms_widget_instance_page')}` (
  `page_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `instance_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `group` VARCHAR(25) NOT NULL DEFAULT '',
  `layout_handle` VARCHAR(255) NOT NULL DEFAULT '',
  `block_reference` VARCHAR(255) NOT NULL DEFAULT '',
  `for` VARCHAR(25) NOT NULL DEFAULT '',
  `entities` TEXT,
  `template` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`page_id`),
  KEY `IDX_WIDGET_INSTANCE_ID` (`instance_id`),
  CONSTRAINT `FK_WIDGET_INSTANCE_ID` FOREIGN KEY (`instance_id`) REFERENCES `{$installer->getTable('enterprise_cms_widget_instance')}` (`instance_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$installer->getTable('enterprise_cms_widget_instance_page_layout')}` (
    `page_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `layout_update_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
    UNIQUE KEY `page_id` (`page_id`,`layout_update_id`),
    KEY `IDX_WIDGET_INSTANCE_PAGE_ID` (`page_id`),
    KEY `IDX_WIDGET_INSTANCE_LAYOUT_UPDATE_ID` (`layout_update_id`),
    CONSTRAINT `FK_WIDGET_INSTANCE_LAYOUT_UPDATE_ID` FOREIGN KEY (`layout_update_id`) REFERENCES `{$installer->getTable('core/layout_update')}` (`layout_update_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_WIDGET_INSTANCE_PAGE_ID` FOREIGN KEY (`page_id`) REFERENCES `{$installer->getTable('enterprise_cms_widget_instance_page')}` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
");

$installer->getConnection()->addColumn($installer->getTable('cms/page'), 'published_revision_id', ' int(10) unsigned default NULL');
$installer->getConnection()->addColumn($installer->getTable('cms/page'), 'website_root', "tinyint(1) NOT NULL default '1'");
$installer->getConnection()->addColumn($installer->getTable('cms/page'), 'under_version_control', 'tinyint(1) unsigned default 0');

$installer->endSetup();

/*
 * Creating initial versions and revisions
 */

$attributes = array(
    'root_template',
    'meta_keywords',
    'meta_description',
    'content',
    'layout_update_xml',
    'custom_theme',
    'custom_theme_from',
    'custom_theme_to'
);

$select = $installer->getConnection()->select();

$select->from(array('p' =>  $installer->getTable('cms/page'), array('*')))
    ->joinLeft(array('v' =>  $installer->getTable('enterprise_cms/page_version')), 'v.page_id = p.page_id', array())
    ->where('v.page_id is NULL');

$resource = $installer->getConnection()->query($select);

try {
    $installer->getConnection()->beginTransaction();
    while($page = $resource->fetch(Zend_Db::FETCH_ASSOC)) {
        $installer->getConnection()->insert($installer->getTable('enterprise_cms/increment'), array(
            'type' => 0,
            'node' => $page['page_id'],
            'level' => 0,
            'last_id' => 1
        ));

        $installer->getConnection()->insert($installer->getTable('enterprise_cms/page_version'), array(
            'version_number' => 1,
            'page_id' => $page['page_id'],
            'access_level' => Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PUBLIC,
            'user_id' => NULL,
            'revisions_count' => 1,
            'label' => $page['title']
        ));

        $versionId = $installer->getConnection()->lastInsertId($installer->getTable('enterprise_cms/page_version'), 'version_id');

        $installer->getConnection()->insert($installer->getTable('enterprise_cms/increment'), array(
            'type' => 0,
            'node' => $versionId,
            'level' => 1,
            'last_id' => 1
        ));

        /*
         * prepare revision data
         */
        $_data = array();

        foreach ($attributes as $attr) {
            $_data[$attr] = $page[$attr];
        }

        $_data['created_at'] = date('Y-m-d');
        $_data['user_id'] = NULL;
        $_data['revision_number'] = 1;
        $_data['version_id'] = $versionId;
        $_data['page_id'] = $page['page_id'];

        $installer->getConnection()->insert($installer->getTable('enterprise_cms/page_revision'), $_data);
    }
    $installer->getConnection()->commit();
} catch (Exception $e) {
    $installer->getConnection()->rollback();
    throw $e;
}

/*
 * Updating new created column with values
 */
$select = 'UPDATE ' . $installer->getTable('cms/page') . ' as p
SET published_revision_id = (SELECT revision_id FROM
        ' . $installer->getTable('enterprise_cms/page_version') . ' as v, ' . $installer->getTable('enterprise_cms/page_revision') . ' as r
    WHERE v.page_id = p.page_id
        AND v.access_level = "' . Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PUBLIC . '"
        AND r.version_id = v.version_id
        AND r.page_id = p.page_id ORDER BY revision_id DESC LIMIT 1)
WHERE p.published_revision_id is NULL';

$installer->getConnection()->query($select);
