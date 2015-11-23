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
 * @package     Enterprise_UrlRewrite
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $this Mage_Core_Model_Resource_Setup */

$this->getConnection()->addColumn($this->getTable('enterprise_urlrewrite/url_rewrite'), 'store_id', array(
    'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'nullable' => false,
    'unsigned' => true,
    'comment' => 'Store Id'
));

$this->getConnection()->addColumn($this->getTable('enterprise_urlrewrite/redirect'), 'store_id', array(
    'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'nullable' => false,
    'unsigned' => true,
    'comment' => 'Store Id'
));

$this->getConnection()->addColumn($this->getTable('enterprise_urlrewrite/url_rewrite'), 'entity_type', array(
    'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'nullable' => false,
    'unsigned' => true,
    'comment' => 'Url Rewrite Entity Type'
));

$this->getConnection()->dropIndex(
    $this->getTable('enterprise_urlrewrite/url_rewrite'),
    $this->getIdxName(
        'enterprise_urlrewrite/url_rewrite',
        array('request_path'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    )
);

$this->getConnection()->addIndex(
    $this->getTable('enterprise_urlrewrite/url_rewrite'),
    $this->getIdxName(
        'enterprise_urlrewrite/url_rewrite',
        array('request_path', 'store_id', 'entity_type'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('request_path', 'store_id', 'entity_type'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

