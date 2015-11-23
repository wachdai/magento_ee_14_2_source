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
 * @package     Enterprise_Search
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Enterprise_Reminder_Model_Mysql4_Setup */

// Create search recommendations table
$installer->run("
CREATE TABLE `{$this->getTable('enterprise_search/recommendations')}` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `query_id` int(10) unsigned NOT NULL,
    `relation_id` int(10) unsigned NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addConstraint(
    'FK_EE_REMINDER_SEARCH_QUERY',
    $this->getTable('enterprise_search/recommendations'),
    'query_id',
    $this->getTable('catalogsearch_query'),
    'query_id'
);

$installer->getConnection()->addConstraint(
    'FK_EE_REMINDER_SEARCH_RELATION',
    $this->getTable('enterprise_search/recommendations'),
    'relation_id',
    $this->getTable('catalogsearch_query'),
    'query_id'
);

$installer->getConnection()->query("ALTER TABLE {$this->getTable('catalogsearch_query')} ADD KEY (num_results)");
$installer->getConnection()->query("ALTER TABLE {$this->getTable('catalogsearch_query')} ADD KEY (query_text)");



