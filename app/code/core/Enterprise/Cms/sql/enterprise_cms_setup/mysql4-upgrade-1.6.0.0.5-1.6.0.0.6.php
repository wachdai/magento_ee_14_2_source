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
$installer->startSetup();
$nodesTable = $installer->getTable('enterprise_cms/hierarchy_node');
$metadataTable = $installer->getTable('enterprise_cms/hierarchy_metadata');

// Node exclusion flag
$installer->getConnection()->addColumn($metadataTable, 'menu_excluded', "tinyint(4) unsigned NOT NULL DEFAULT '0' AFTER `menu_visibility`");

// Exclude nodes from menu which have "No" in menu_visibility or menu_visibility_self
$sql = "UPDATE `{$nodesTable}` n, `{$metadataTable}` m
        SET m.menu_excluded=1
        WHERE n.node_id=m.node_id
            AND n.level>1
            AND (m.menu_visibility=2 OR m.menu_visibility_self=2)";

$installer->getConnection()->query($sql);

// Update first-level nodes and set menu_visibility equals 0 for other levels
$sql = "UPDATE `{$nodesTable}` n, `{$metadataTable}` m
        SET m.menu_visibility=IF(n.level>1, 0, IF(m.menu_visibility_self=2 OR m.menu_visibility<>1, 0, 1))
        WHERE n.node_id=m.node_id";

$installer->getConnection()->query($sql);

// Remove useless columns
$installer->getConnection()->dropColumn($metadataTable, 'menu_visibility_self');
$installer->getConnection()->dropColumn($metadataTable, 'menu_levels_up');

// Menu Detalization flag
$installer->getConnection()->addColumn($metadataTable, 'menu_brief', "tinyint(4) unsigned NOT NULL DEFAULT '0' AFTER `menu_excluded`");

$installer->endSetup();
