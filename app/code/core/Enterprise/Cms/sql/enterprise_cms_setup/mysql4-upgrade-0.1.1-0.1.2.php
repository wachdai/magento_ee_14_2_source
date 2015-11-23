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

$versionTableOld = $installer->getTable('enterprise_cms_version');
$revisionTableOld = $installer->getTable('enterprise_cms_revision');

$versionTableNew = $installer->getTable('enterprise_cms/page_version');
$revisionTableNew = $installer->getTable('enterprise_cms/page_revision');

$incrementTable = $installer->getTable('enterprise_cms/increment');

if ($installer->tableExists($versionTableOld)) {
    $installer->run('RENAME TABLE ' . $revisionTableOld . ' TO ' . $revisionTableNew . ';');
}

if ($installer->tableExists($versionTableOld)) {
    $installer->run('RENAME TABLE ' . $versionTableOld . ' TO ' . $versionTableNew . ';');
}

$installer->run('
CREATE TABLE IF NOT EXISTS `' . $incrementTable . '` (
  `increment_id` int(10) unsigned NOT NULL auto_increment,
  `type` int(10) NOT NULL,
  `node` int(10) unsigned NOT NULL,
  `level` int(10) unsigned NOT NULL,
  `last_id` varchar(255) character set latin1 NOT NULL,
  PRIMARY KEY  (`increment_id`),
  UNIQUE KEY `IDX_TYPE_NODE_LEVEL` (`type`,`node`,`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
');

$installer->getConnection()->addColumn($revisionTableNew, 'revision_number', 'varchar(255) NOT NULL');
$installer->getConnection()->dropColumn($revisionTableNew, 'updated_at');
$installer->getConnection()->addColumn($versionTableNew, 'version_number', 'varchar(255) NOT NULL');

/*
 * Creating initial versions and revisions
 */

$attributes = array(
            'root_template',
            'meta_keywords',
            'meta_description',
            'content',
            'sort_order',
            'layout_update_xml',
            'custom_theme',
            'custom_theme_from',
            'custom_theme_to'
        );

$select = 'SELECT `' . implode('`,`', $attributes) . '`
    FROM `' . $installer->getTable('cms/page') . '` as p
    LEFT JOIN ' . $versionTableNew . ' as v on v.page_id = p.page_id WHERE v.page_id is NULL';

$select = $installer->getConnection()->select();

$select->from(array('p' =>  $installer->getTable('cms/page'), array('*')))
    ->joinLeft(array('v' =>  $versionTableNew), 'v.page_id = p.page_id', array())
    ->where('v.page_id is NULL');

$resource = $installer->getConnection()->query($select);

try {
    $installer->getConnection()->beginTransaction();
    while($page = $resource->fetch(Zend_Db::FETCH_ASSOC)) {
        $installer->getConnection()->insert($incrementTable, array(
            'type' => 0,
            'node' => $page['page_id'],
            'level' => 0,
            'last_id' => 1
        ));

        $installer->getConnection()->insert($versionTableNew, array(
            'version_number' => 1,
            'page_id' => $page['page_id'],
            'access_level' => Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PUBLIC,
            'user_id' => 0,
            'revisions_count' => 1,
            'label' => $page['title']
        ));

        $versionId = $installer->getConnection()->lastInsertId($versionTableNew, 'version_id');

        $installer->getConnection()->insert($incrementTable, array(
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
        $_data['user_id'] = 0;
        $_data['revision_number'] = 1;
        $_data['version_id'] = $versionId;
        $_data['page_id'] = $page['page_id'];

        $installer->getConnection()->insert($revisionTableNew, $_data);
    }
    $installer->getConnection()->commit();
} catch (Exception $e) {
    $installer->getConnection()->rollback();
    throw $e;
}

$installer->endSetup();

