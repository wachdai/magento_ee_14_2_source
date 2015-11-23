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

/** @var $installer Enterprise_Cms_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

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
$adapter = $installer->getConnection();
$select  = $adapter->select();

$select->from(array('p' => $installer->getTable('cms/page')))
    ->joinLeft(array('v' => $installer->getTable('enterprise_cms/page_version')), 'v.page_id = p.page_id', array())
    ->where('v.page_id IS NULL');

$resource = $adapter->query($select);

while ($page = $resource->fetch(Zend_Db::FETCH_ASSOC)) {
    $adapter->insert($installer->getTable('enterprise_cms/increment'), array(
        'increment_type'    => 0,
        'increment_node'    => $page['page_id'],
        'increment_level'   => 0,
        'last_id'           => 1
    ));

    $adapter->insert($installer->getTable('enterprise_cms/page_version'), array(
        'version_number'  => 1,
        'page_id'         => $page['page_id'],
        'access_level'    => Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PUBLIC,
        'user_id'         => new Zend_Db_Expr('NULL'),
        'revisions_count' => 1,
        'label'           => $page['title'],
        'created_at'      => Mage::getSingleton('core/date')->gmtDate()
    ));

    $versionId = $adapter->lastInsertId($installer->getTable('enterprise_cms/page_version'), 'version_id');

    $adapter->insert($installer->getTable('enterprise_cms/increment'), array(
        'increment_type'    => 0,
        'increment_node'    => $versionId,
        'increment_level'   => 1,
        'last_id'           => 1
    ));

    /**
     * Prepare revision data
     */
    $_data = array();

    foreach ($attributes as $attr) {
        $_data[$attr] = $page[$attr];
    }

    $_data['created_at']      = Mage::getSingleton('core/date')->gmtDate();
    $_data['user_id']         = new Zend_Db_Expr('NULL');
    $_data['revision_number'] = 1;
    $_data['version_id']      = $versionId;
    $_data['page_id']         = $page['page_id'];

    $adapter->insert($installer->getTable('enterprise_cms/page_revision'), $_data);
}

$adapter->query($select);

$installer->endSetup();
