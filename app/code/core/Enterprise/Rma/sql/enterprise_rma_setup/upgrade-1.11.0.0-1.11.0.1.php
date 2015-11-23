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

/* adding new field = static attribute to rma_item_entity table */
$tableName = $installer->getTable('enterprise_rma/item_entity');
$columnOptions = array(
    'TYPE' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'SCALE' => 4,
    'PRECISION' => 12,
    'COMMENT' => 'Qty of returned items',
);
$installer->getConnection()
    ->addColumn($tableName, 'qty_returned', $columnOptions);

$installer->addAttribute('rma_item', 'qty_returned', array(
            'type'               => 'static',
            'label'              => 'Qty of returned items',
            'input'              => 'text',
            'visible'            => false,
            'sort_order'         => 45,
            'position'           => 45,
));
