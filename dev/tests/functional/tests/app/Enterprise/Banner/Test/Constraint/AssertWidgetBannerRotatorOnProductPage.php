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

namespace Enterprise\Banner\Test\Constraint;

use Mage\Adminhtml\Test\Page\Adminhtml\Cache;
use Mage\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Constraint\AbstractConstraint;
use Enterprise\Banner\Test\Fixture\BannerWidget;
use Magento\Mtf\Client\Browser;

/**
 * Check that created Banner Rotator widget displayed on frontend on Product page.
 */
class AssertWidgetBannerRotatorOnProductPage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that created Banner Rotator widget displayed on frontend on Product page.
     *
     * @param CatalogProductView $productView
     * @param Browser $browser
     * @param BannerWidget $widget
     * @param Cache $adminCache
     * @return void
     */
    public function processAssert(
        CatalogProductView $productView,
        Browser $browser,
        BannerWidget $widget,
        Cache $adminCache
    ) {
        // Flush cache
        $adminCache->open();
        $adminCache->getPageActions()->flushCacheStorage();
        $adminCache->getMessagesBlock()->waitSuccessMessage();

        $layouts = $widget->getLayout();
        foreach ($layouts as $layout) {
            foreach ($layout['entities'] as $layoutEntity) {
                $urlKey = $layoutEntity['url_key'];
                $browser->open($_ENV['app_frontend_url'] . $urlKey . '.html');
                $errors = $productView->getWidgetView()->isWidgetVisible($widget, $layoutEntity['url_key']);
                \PHPUnit_Framework_Assert::assertEmpty($errors, implode(" ", $errors));
            }
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Widget is present on Product page.";
    }
}
