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
use Mage\CatalogSearch\Test\Page\CatalogsearchAdvanced;
use Mage\Cms\Test\Page\CmsIndex;
use Mage\Widget\Test\Fixture\Widget;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that created Banner Rotator widget displayed on frontend on Home page and on Advanced Search.
 */
class AssertWidgetBannerRotator extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that created Banner Rotator widget displayed on frontent on Home page and on Advanced Search.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogsearchAdvanced $advancedSearch
     * @param Widget $widget
     * @param Cache $adminCache
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogsearchAdvanced $advancedSearch,
        Widget $widget,
        Cache $adminCache
    ) {
        // Flush cache
        $adminCache->open();
        $adminCache->getPageActions()->flushCacheStorage();
        $adminCache->getMessagesBlock()->waitSuccessMessage();

        $cmsIndex->open();
        $errors = $cmsIndex->getWidgetView()->isWidgetVisible($widget, 'Cms index');
        \PHPUnit_Framework_Assert::assertEmpty($errors, implode(" ", $errors));

        $advancedSearch->open();
        $errors = $cmsIndex->getWidgetView()->isWidgetVisible($widget, 'Advanced search');
        \PHPUnit_Framework_Assert::assertEmpty($errors, implode(" ", $errors));
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Widget is present on Home page and on Advanced Search page.";
    }
}
