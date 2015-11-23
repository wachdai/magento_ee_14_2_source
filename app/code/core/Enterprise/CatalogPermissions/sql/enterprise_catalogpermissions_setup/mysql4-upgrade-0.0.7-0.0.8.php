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
 * @package     Enterprise_CatalogPermissions
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_CatalogPermissions_Model_Mysql4_Setup */
$installer = $this;

$installer->startSetup();
$coreConfigTable = $installer->getTable('core_config_data');

$connection = $installer->getConnection();
$select = $connection->select();

/**
 * Select old config data
 */
$select->from($coreConfigTable, array('path', 'scope', 'scope_id', 'config_id', 'path2' => 'SUBSTR(path, LENGTH(\'enterprise_catalogpermissions/general/\'))'))
       ->where('`path` LIKE \'enterprise_catalogpermissions/general/%\'');

$resource = $connection->query($select);
try {
    $connection->beginTransaction();
    while($config = $resource->fetch(Zend_Db::FETCH_ASSOC)) {
        $select = $connection->select();

        //If new config data already exists
        $select->from($coreConfigTable, array('config_id'))
            ->where('`path`= ?', 'catalog/enterprise_catalogpermissions' . $config['path2'])
            ->where('`scope`= ?', $config['scope'])
            ->where('`scope_id`= ?', $config['scope_id']);
        $newConfig = $connection->fetchOne($select);

        //Then delete this data
        if ($newConfig) {
            $connection->delete($coreConfigTable, array('`config_id`= ?' => $newConfig));
        }

        $connection->update($coreConfigTable, array('path' => 'catalog/enterprise_catalogpermissions' . $config['path2']), array('config_id =?' => $config['config_id']));
    }
    $connection->commit();
}
catch (Exception $e) {
    $connection->rollback();
    throw $e;
}

$installer->endSetup();
