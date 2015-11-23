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


/* @var $installer Enterprise_Cms_Model_Mysql4_Setup */
$installer = $this;

$hierarchyTable = $installer->getTable('enterprise_cms_hierarchy');
$metadataTable = $installer->getTable('enterprise_cms/hierarchy_metadata');
$nodeTable = $installer->getTable('enterprise_cms/hierarchy_node');

$installer->getConnection()->dropForeignKey($nodeTable, 'FK_ENTERPRISE_CMS_HIERARCHY_NODE_TREE');

if ($installer->tableExists($hierarchyTable)) {
    $installer->run('RENAME TABLE ' . $hierarchyTable . ' TO ' . $metadataTable . ';');
}

if ($installer->getConnection()->tableColumnExists($metadataTable, 'tree_id')) {
    $installer->getConnection()->changeColumn($metadataTable, 'tree_id', 'node_id',
        'int(10) unsigned NOT NULL');

    $installer->run('UPDATE `' . $metadataTable . '` as `metadata`,
    `' . $nodeTable . '` as `node`
    SET `metadata`.`node_id` = `node`.`node_id` WHERE `metadata`.`node_id` = `node`.`tree_id`');
}

$installer->getConnection()->addConstraint('FK_ENTERPRISE_CMS_HIERARCHY_METADATA_NODE',
    $metadataTable, 'node_id', $nodeTable, 'node_id', 'CASCADE', 'CASCADE');

$installer->getConnection()->dropColumn($nodeTable, 'tree_id');

