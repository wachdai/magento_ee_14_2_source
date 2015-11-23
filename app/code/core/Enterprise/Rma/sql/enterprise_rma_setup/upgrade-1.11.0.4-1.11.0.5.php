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
 * @package     Enterprise_Rma
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/** @var $installer Enterprise_Rma_Model_Resource_Setup */
$installer = $this;

/**
 * Create table 'enterprise_rma/rma_shipping_label'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('enterprise_rma/rma_shipping_label'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('rma_entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'RMA Entity Id')
    ->addColumn('shipping_label', Varien_Db_Ddl_Table::TYPE_VARBINARY, '2M', array(
        ), 'Shipping Label Content')
    ->addColumn('packages', Varien_Db_Ddl_Table::TYPE_TEXT, 20000, array(
        ), 'Packed Products in Packages')
    ->addColumn('track_number', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Tracking Number')
    ->addColumn('carrier_title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Carrier Title')
    ->addColumn('method_title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Method Title')
    ->addColumn('carrier_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Carrier Code')
    ->addColumn('method_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Method Code')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price')
    ->addForeignKey(
        $installer->getFkName('enterprise_rma/rma_shipping_label', 'rma_entity_id', 'enterprise_rma/rma', 'entity_id'),
        'rma_entity_id',
        $installer->getTable('enterprise_rma/rma'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('List of RMA Shipping Labels');
$installer->getConnection()->createTable($table);
