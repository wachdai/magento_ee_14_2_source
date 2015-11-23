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

$versionTable = $installer->getTable('enterprise_cms_version');

$installer->run("
    CREATE TABLE IF NOT EXISTS `{$versionTable}` (
        `version_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `label` VARCHAR(255),
        `access_level` ENUM('".Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PRIVATE."',
                '".Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PROTECTED."',
                '".Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PUBLIC."') NOT NULL,
        `page_id` SMALLINT(6) NOT NULL,
        `user_id` MEDIUMINT(9) UNSIGNED NOT NULL,
        `revisions_count` INT(11) UNSIGNED,
        PRIMARY KEY (`version_id`),
        KEY `IDX_PAGE_ID` (`page_id`),
        KEY `IDX_USER_ID` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addConstraint('FK_CMS_VERSION_PAGE_ID', $versionTable, 'page_id',
    $installer->getTable('cms/page'), 'page_id');

$revisionTable = $installer->getTable('enterprise_cms_revision');

$installer->run("
    CREATE TABLE IF NOT EXISTS `{$revisionTable}` (
        `revision_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `version_id` INT(10) UNSIGNED NOT NULL,
        `page_id` SMALLINT(6) NOT NULL,
        `root_template` VARCHAR(255) NOT NULL DEFAULT '',
        `meta_keywords` TEXT NOT NULL,
        `meta_description` TEXT NOT NULL,
        `content` TEXT,
        `created_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `updated_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `sort_order` TINYINT(4) NOT NULL DEFAULT '0',
        `layout_update_xml` TEXT,
        `custom_theme` VARCHAR(100) DEFAULT NULL,
        `custom_theme_from` DATE DEFAULT NULL,
        `custom_theme_to` DATE DEFAULT NULL,
        `user_id` MEDIUMINT(9) UNSIGNED NOT NULL,
        PRIMARY KEY (`revision_id`),
        KEY `IDX_VERSION_ID` (`version_id`),
        KEY `IDX_PAGE_ID` (`page_id`),
        KEY `IDX_USER_ID` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addConstraint('FK_CMS_REVISION_VERSION_ID', $revisionTable, 'version_id',
    $versionTable, 'version_id');

$installer->getConnection()->addConstraint('FK_CMS_REVISION_PAGE_ID', $revisionTable, 'page_id',
    $installer->getTable('cms/page'), 'page_id');
