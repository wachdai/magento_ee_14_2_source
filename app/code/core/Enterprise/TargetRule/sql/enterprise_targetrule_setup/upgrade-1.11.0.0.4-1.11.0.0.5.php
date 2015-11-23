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
 * @package     Enterprise_TargetRule
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_TargetRule_Model_Resource_Setup */
$installer = $this;

/**
 * Fill table 'enterprise_targetrule/index_related_product'
 */
$select = $installer->getConnection()->select();
$select->from($installer->getTable('enterprise_targetrule/index_related'), array('targetrule_id', 'product_ids'))
    ->where('`product_ids` <> ""');
$result = $installer->getConnection()->fetchAll($select);
$relatedProducts = array();
if ($result) {
    foreach($result as $row) {
        foreach(explode(',', $row['product_ids']) as $productId) {
            $relatedProducts[] = array('targetrule_id' => $row['targetrule_id'], 'product_id' => trim($productId));
        }
    }
    $installer->getConnection()->insertOnDuplicate(
        $installer->getTable('enterprise_targetrule/index_related_product'),
        $relatedProducts,
        array('targetrule_id', 'product_id')
    );
}
/**
 * Fill table 'enterprise_targetrule/index_crosssell_product'
 */
$select = $installer->getConnection()->select();
$select->from($installer->getTable('enterprise_targetrule/index_crosssell'), array('targetrule_id', 'product_ids'))
    ->where('`product_ids` <> ""');
$result = $installer->getConnection()->fetchAll($select);
$crosssellProducts = array();
if ($result) {
    foreach($result as $row) {
        foreach(explode(',', $row['product_ids']) as $productId) {
            $crosssellProducts[] = array('targetrule_id' => $row['targetrule_id'], 'product_id' => trim($productId));
        }
    }
    $installer->getConnection()->insertOnDuplicate(
        $installer->getTable('enterprise_targetrule/index_crosssell_product'),
        $crosssellProducts,
        array('targetrule_id', 'product_id')
    );
}
/**
 * Fill table 'enterprise_targetrule/index_upsell_product'
 */
$select = $installer->getConnection()->select();
$select->from($installer->getTable('enterprise_targetrule/index_upsell'), array('targetrule_id', 'product_ids'))
    ->where('`product_ids` <> ""');
$result = $installer->getConnection()->fetchAll($select);
$upsellProducts = array();
if ($result) {
    foreach($result as $row) {
        foreach(explode(',', $row['product_ids']) as $productId) {
            $upsellProducts[] = array('targetrule_id' => $row['targetrule_id'], 'product_id' => trim($productId));
        }
    }
    $installer->getConnection()->insertOnDuplicate(
        $installer->getTable('enterprise_targetrule/index_upsell_product'),
        $upsellProducts,
        array('targetrule_id', 'product_id')
    );
}

$installer->getConnection()->modifyColumn(
    $installer->getTable('enterprise_targetrule/index_related'),
    'product_ids',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Deprecated after 1.12.0.2'
    )
);

$installer->getConnection()->modifyColumn(
    $installer->getTable('enterprise_targetrule/index_upsell'),
    'product_ids',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Deprecated after 1.12.0.2'
    )
);

$installer->getConnection()->modifyColumn(
    $installer->getTable('enterprise_targetrule/index_crosssell'),
    'product_ids',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Deprecated after 1.12.0.2'
    )
);
