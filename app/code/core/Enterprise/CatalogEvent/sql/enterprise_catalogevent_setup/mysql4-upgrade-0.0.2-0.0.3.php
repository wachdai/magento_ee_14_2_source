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
 * @package     Enterprise_CatalogEvent
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_CatalogEvent_Model_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$blockId = $installer->getConnection()->fetchOne($installer->getConnection()->select()
    ->from($this->getTable('cms/block'), 'block_id')
    ->where('identifier = ?', 'catalog_events_lister'));

if ($blockId) {
    $installer->getConnection()->delete(
        $this->getTable('cms/block_store'),
        $installer->getConnection()->quoteInto('block_id = ?', $blockId)
    );

    $installer->getConnection()->insert(
        $this->getTable('cms/block_store'),
        array(
            'block_id' => $blockId,
            'store_id' => 0
        )
    );
}

$installer->endSetup();
