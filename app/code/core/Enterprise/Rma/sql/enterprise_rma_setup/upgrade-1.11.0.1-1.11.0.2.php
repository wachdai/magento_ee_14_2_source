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

$tableName = $installer->getTable('enterprise_rma/item_entity');

$installer->getConnection()
    ->addColumn(
        $tableName,
        'product_admin_name',
        array(
            'TYPE' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'LENGTH' => 255,
            'COMMENT' => 'Product Name For Backend',
        )
    );
$installer->getConnection()
    ->addColumn(
        $tableName,
        'product_admin_sku',
        array(
            'TYPE' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'LENGTH' => 255,
            'COMMENT' => 'Product Sku For Backend',
        )
    );
$installer->getConnection()
    ->addColumn(
        $tableName,
        'product_options',
        array(
            'TYPE' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'COMMENT' => 'Product Options',
        )
    );

$installer->addAttribute('rma_item', 'product_admin_name',
        array(
            'type'               => 'static',
            'label'              => 'Product Name For Backend',
            'input'              => 'text',
            'visible'            => false,
            'sort_order'         => 46,
            'position'           => 46,
        ));
$installer->addAttribute('rma_item', 'product_admin_sku',
        array(
            'type'               => 'static',
            'label'              => 'Product Sku For Backend',
            'input'              => 'text',
            'visible'            => false,
            'sort_order'         => 47,
            'position'           => 47,
        ));
$installer->addAttribute('rma_item', 'product_options',
        array(
            'type'               => 'static',
            'label'              => 'Product Options',
            'input'              => 'text',
            'visible'            => false,
            'sort_order'         => 48,
            'position'           => 48,
        ));
