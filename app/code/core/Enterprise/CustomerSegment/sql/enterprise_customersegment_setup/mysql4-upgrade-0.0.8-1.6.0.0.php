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
 * @package     Enterprise_CustomerSegment
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
$customerTable = $installer->getTable('enterprise_customersegment/customer');
$segmentTable  = $installer->getTable('enterprise_customersegment/segment');
$websiteTable  = $installer->getTable('enterprise_customersegment/website');
$adapter = $installer->getConnection();

/* @var $installer Enterprise_CustomerSegment_Model_Mysql4_Setup */
$installer->run("CREATE TABLE `{$websiteTable}` (
  `segment_id` int(10) unsigned NOT NULL,
  `website_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`segment_id`,`website_id`),
  KEY `FK_EE_SEGMENT_WEBSITE` (`website_id`),
  CONSTRAINT `FK_EE_SEGMENT_SEFMENT` FOREIGN KEY (`segment_id`)
    REFERENCES `{$this->getTable('enterprise_customersegment/segment')}` (`segment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EE_SEGMENT_WEBSITE` FOREIGN KEY (`website_id`)
    REFERENCES `{$this->getTable('core/website')}` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Segment to website association';
");

$adapter->addColumn($customerTable, 'website_id', 'smallint(5) unsigned NOT NULL');

$installer->run("
UPDATE
    `{$customerTable}` AS sc,
    `{$segmentTable}` AS s
SET sc.website_id=s.website_id
WHERE sc.segment_id=s.segment_id;

INSERT INTO `{$websiteTable}` SELECT segment_id, website_id FROM `{$segmentTable}`;
");

$adapter->dropColumn($segmentTable, 'website_id');
$adapter->addConstraint(
    'FK_EE_CUSTOMER_SEGMENT_WEBSIE',
    $customerTable,
    'website_id',
    $installer->getTable('core/website'),
    'website_id'
);

$adapter->addKey($customerTable, 'FK_CUSTOMER', array('customer_id'));
$adapter->dropKey($customerTable, 'UNQ_ENTERPRISE_CUSTOMERSEGMENT_CUSTOMER');
$adapter->addKey($customerTable, 'UNQ_CUSTOMER', array('segment_id', 'website_id', 'customer_id'), 'unique');
