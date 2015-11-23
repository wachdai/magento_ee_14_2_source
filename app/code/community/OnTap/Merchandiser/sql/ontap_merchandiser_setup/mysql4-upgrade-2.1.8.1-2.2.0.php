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
 * @category    OnTap
 * @package     OnTap_Merchandiser
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
$installer = $this;
$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('merchandiser_category_values')};
CREATE TABLE {$this->getTable('merchandiser_category_values')} (
  `category_id` int(11) NOT NULL,
  `heroproducts` text NOT NULL default '',
  `attribute_codes` varchar(255) NOT NULL default '',
  `smart_attributes` text NOT NULL default '',
  `ruled_only` smallint(4) NOT NULL default 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('merchandiser_vmbuild')};
CREATE TABLE {$this->getTable('merchandiser_vmbuild')} (
  `attribute_code` varchar(255) NOT NULL default ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();
