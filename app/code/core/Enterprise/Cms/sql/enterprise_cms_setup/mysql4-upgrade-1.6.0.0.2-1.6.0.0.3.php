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

/* @var $conn Varien_Db_Adapter_Pdo_Mysql */
$conn = $installer->getConnection();

$widgetInstanceOldExists = $installer->tableExists($installer->getTable('enterprise_cms_widget_instance'));
$widgetInstancePageOldExists = $installer->tableExists($installer->getTable('enterprise_cms_widget_instance_page'));
$widgetInstancePageLayoutOldExists = $installer->tableExists($installer->getTable('enterprise_cms_widget_instance_page_layout'));

if ($widgetInstanceOldExists && $widgetInstancePageOldExists && $widgetInstancePageLayoutOldExists) {
    $widgetInstanceData = array();
    $pageData = array();
    $pageLayoutData = array();
    $select = $conn->select()
        ->from($installer->getTable('enterprise_cms_widget_instance'))
        ->order('instance_id ASC');
    foreach ($conn->fetchAll($select) as $instanceRow) {
        $widgetInstanceData[$instanceRow['instance_id']] = $instanceRow;
    }
    if (!empty($widgetInstanceData)) {
        $conn->insertMultiple(
            $installer->getTable('widget/widget_instance'),
            $widgetInstanceData
        );
        $select = $conn->select()
            ->from($installer->getTable('enterprise_cms_widget_instance_page'))
            ->where('instance_id in (?)', array_keys($widgetInstanceData))
            ->order('page_id ASC');
        $pageIds = array();
        foreach ($conn->fetchAll($select) as $pageRow) {
            $pageData[$pageRow['page_id']] = $pageRow;
        }
    }
    if (!empty($pageData)) {
        $conn->insertMultiple(
            $installer->getTable('widget/widget_instance_page'),
            $pageData
        );
        $select = $conn->select()
            ->from($installer->getTable('enterprise_cms_widget_instance_page_layout'))
            ->where('page_id in (?)', array_keys($pageData));
        foreach ($conn->fetchAll($select) as $pageLayoutRow) {
            $pageLayoutData[] = $pageLayoutRow;
        }
    }
    if (!empty($pageLayoutData)) {
        $conn->insertMultiple(
            $installer->getTable('widget/widget_instance_page_layout'),
            $pageLayoutData
        );
    }
    $installer->run("
        DROP TABLE IF EXISTS `{$installer->getTable('enterprise_cms_widget_instance')}`;
        DROP TABLE IF EXISTS `{$installer->getTable('enterprise_cms_widget_instance_page')}`;
        DROP TABLE IF EXISTS `{$installer->getTable('enterprise_cms_widget_instance_page_layout')}`;
    ");
}



$installer->endSetup();
