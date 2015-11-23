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

$cmsPages = array(
    array(
        'title' => '503 Service Unavailable',
        'identifier' => 'service-unavailable',
        'content' => '<div class="page-title"><h1>We\'re Offline...</h1></div>
<p>...but only for just a bit. We\'re working to make the Magento Enterprise Demo a better place for you!</p>',
        'is_active' => '1',
        'stores'        => array(0),
        'sort_order'    => 0
    ),
    array(
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
        'is_active' => '1',
        'stores'        => array(0),
        'sort_order'    => 0
    ),
);

/**
 * Insert default and system pages
 */
foreach ($cmsPages as $data) {
    Mage::getModel('cms/page')->setData($data)->save();
}
