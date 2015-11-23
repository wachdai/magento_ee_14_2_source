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
 * @category    Tests
 * @package     Tests_Functional
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

namespace Enterprise\UrlRewrite\Test\Constraint;

use Magento\Mtf\Client\Browser;
use Magento\Mtf\Constraint\AbstractConstraint;
use Enterprise\UrlRewrite\Test\Fixture\UrlRewrite;
use Mage\Catalog\Test\Fixture\CatalogProductSimple;
use Mage\Catalog\Test\Page\Product\CatalogProductView;
use Mage\Cms\Test\Page\CmsIndex;
use Mage\Adminhtml\Test\Page\Adminhtml\Cache;

/**
 * Assert that product available by new URL on the frontend.
 */
class AssertUrlRewriteProductRedirect extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that product available by new URL on the frontend.
     *
     * @param Browser $browser
     * @param UrlRewrite $urlRewrite
     * @param CatalogProductSimple $product
     * @param CatalogProductView $catalogProductView
     * @param CmsIndex $cmsIndex
     * @param Cache $cachePage
     * @return void
     */
    public function processAssert(
        Browser $browser,
        UrlRewrite $urlRewrite,
        CatalogProductSimple $product,
        CatalogProductView $catalogProductView,
        CmsIndex $cmsIndex,
        Cache $cachePage
    ) {
        $cachePage->open()->getPageActions()->flushCacheStorage();
        if ($urlRewrite->hasData('store_id')) {
            $storePath = explode('/', $urlRewrite->getStoreId());
            $cmsIndex->open();
            $cmsIndex->getHeaderBlock()->selectStore($storePath[2]);
        }
        $browser->open($_ENV['app_frontend_url'] . $urlRewrite->getRequestPath());
        \PHPUnit_Framework_Assert::assertEquals(
            $catalogProductView->getViewBlock()->getProductName(),
            strtoupper($product->getName()),
            'URL rewrite product redirect false.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product available by new URL on the frontend.';
    }
}
