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

namespace Mage\Catalog\Test\Constraint;

use Mage\Catalog\Test\Fixture\CatalogCategory;
use Mage\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\Browser;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert category is not present on frontend.
 */
class AssertCategoryAbsenceOnFrontend extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Message on the category page 404.
     */
    const NOT_FOUND_MESSAGE = 'WE ARE SORRY, BUT THE PAGE YOU ARE LOOKING FOR CANNOT BE FOUND.';

    /**
     * Assert category is not present on frontend.
     *
     * @param Browser $browser
     * @param CatalogCategoryView $categoryView
     * @param CatalogCategory $category
     * @return void
     */
    public function processAssert(
        Browser $browser,
        CatalogCategoryView $categoryView,
        CatalogCategory $category
    ) {
        $browser->open($_ENV['app_frontend_url'] . $category->getUrlKey() . '.html');
        \PHPUnit_Framework_Assert::assertContains(
            self::NOT_FOUND_MESSAGE,
            $categoryView->getViewBlock()->getText(),
            'Category is present on frontend.'
        );
    }

    /**
     * Returns string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category is absent on frontend.';
    }
}
