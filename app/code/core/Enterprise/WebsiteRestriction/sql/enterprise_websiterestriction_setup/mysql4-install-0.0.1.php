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
 * @package     Enterprise_WebsiteRestriction
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $installer Enterprise_WebsiteRestriction_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$now = new Zend_Date(time());
$now = $now->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

$installer->getConnection()->insert($installer->getTable('cms/page'), array(
        'title' => '503 Service Unavailable',
        'identifier' => 'service-unavialable',
        'content' => '<div class="page-title"><h1>We\'re Offline...</h1></div>
<p>...but only for just a bit. We\'re working to make the Magento Enterprise Demo a better place for you!</p>',
        'creation_time' => $now,
        'update_time' => $now,
        'is_active' => '1'
));

$pageId = $installer->getConnection()->lastInsertId();
$installer->getConnection()->insert(
    $installer->getTable('cms/page_store'),
    array('page_id'=>$pageId, 'store_id'=>0)
);

$installer->getConnection()->insert($installer->getTable('cms/page'), array(
        'title' => 'Welcome to our Exclusive Online Store',
        'identifier' => 'private-sales',
        'content' => '<div class="private-sales-index">
<div class="box">
<div class="content">
<h1>Welcome to our Exclusive Online Store</h1>
<p>If you are a registered member, please <a href="{{store url="customer/account/login"}}">log in here</a>.</p>
<p class="description">Magento is the leading hub for exclusive specialty items for all your home, apparel and entertainment needs!</p>
</div>
</div>
</div>',
        'creation_time' => $now,
        'update_time' => $now,
        'is_active' => '1'
));

$pageId = $installer->getConnection()->lastInsertId();
$installer->getConnection()->insert(
    $installer->getTable('cms/page_store'),
    array('page_id'=>$pageId, 'store_id'=>0)
);

$installer->endSetup();

