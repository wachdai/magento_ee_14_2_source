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

use Mage\Cms\Test\Page\CmsIndex;
use Mage\Customer\Test\Fixture\Customer;
use Enterprise\Banner\Test\Fixture\Banner;
use Mage\Adminhtml\Test\Page\Adminhtml\Cache;
use Magento\Mtf\Constraint\AbstractConstraint;
use Mage\Catalog\Test\Fixture\CatalogProductSimple;
use Mage\Catalog\Test\Page\Category\CatalogCategoryView;

/**
 *Assert that banner is absent on specific category page.
 */
class AssertBannerNotOnCategoryPage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that banner is absent on specific category page.
     *
     * @param CatalogProductSimple $product
     * @param CmsIndex $cmsIndex
     * @param Banner $banner
     * @param CatalogCategoryView $catalogCategoryView
     * @param Customer $customer[optional]
     * @param Cache $adminCache
     * @return void
     */
    public function processAssert(
        CatalogProductSimple $product,
        CmsIndex $cmsIndex,
        Banner $banner,
        CatalogCategoryView $catalogCategoryView,
        Customer $customer = null,
        Cache $adminCache
    ) {
        // Flush cache
        $adminCache->open();
        $adminCache->getPageActions()->flushCacheStorage();
        $adminCache->getMessagesBlock()->waitSuccessMessage();

        if ($customer !== null) {
            $this->objectManager->create(
                'Mage\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
                ['customer' => $customer]
            )->run();
        } else {
            $cmsIndex->open();
        }
        $cmsIndex->getTopmenu()->selectCategory($product->getCategoryIds()[0]);
        \PHPUnit_Framework_Assert::assertFalse(
            $catalogCategoryView->getBannerViewBlock()->checkWidgetBanners($banner),
            'Banner is present on Category page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Banner is absent on Category page.";
    }
}
