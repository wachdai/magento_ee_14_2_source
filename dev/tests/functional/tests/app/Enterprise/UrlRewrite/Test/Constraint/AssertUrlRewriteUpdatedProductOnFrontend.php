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

use Mage\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\Browser;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert that redirect from newCategory/oldProduct is correct to new product.
 */
class AssertUrlRewriteUpdatedProductOnFrontend extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that redirect from newCategory/oldProduct is correct to new product.
     *
     * @param InjectableFixture $product
     * @param InjectableFixture $initialProduct
     * @param Browser $browser
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function processAssert(
        InjectableFixture $product,
        InjectableFixture $initialProduct,
        Browser $browser,
        CatalogProductView $catalogProductView
    ) {
        $category = $product->getDataFieldConfig('category_ids')['source']->getProductCategory();
        $productUrl = $_ENV['app_frontend_url'] . $category->getUrlKey() . '/' . $initialProduct->getUrlKey() . '.html';
        $browser->open($productUrl);

        \PHPUnit_Framework_Assert::assertEquals(
            $catalogProductView->getViewBlock()->getProductName(),
            strtoupper($product->getName())
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Redirect from newCategory/oldProduct is correct to new product.';
    }
}
