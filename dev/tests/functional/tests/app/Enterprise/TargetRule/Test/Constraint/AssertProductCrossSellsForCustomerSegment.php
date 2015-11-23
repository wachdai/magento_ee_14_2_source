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

namespace Enterprise\TargetRule\Test\Constraint;

use Magento\Mtf\Client\Browser;
use Mage\Customer\Test\Fixture\Customer;
use Mage\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractConstraint;
use Mage\Customer\Test\Page\CustomerAccountLogout;
use Mage\Catalog\Test\Fixture\CatalogProductSimple;
use Mage\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Assert that product is displayed in cross-sell section for customer segment.
 */
class AssertProductCrossSellsForCustomerSegment extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that product is displayed in cross-sell section for customer segment.
     *
     * @param Browser $browser
     * @param Customer $customer
     * @param CheckoutCart $checkoutCart
     * @param CatalogProductSimple $product
     * @param CatalogProductView $catalogProductView
     * @param CustomerAccountLogout $customerAccountLogout
     * @param InjectableFixture[] $promotedProducts
     * @return void
     */
    public function processAssert(
        Browser $browser,
        Customer $customer,
        CheckoutCart $checkoutCart,
        CatalogProductSimple $product,
        CatalogProductView $catalogProductView,
        CustomerAccountLogout $customerAccountLogout,
        array $promotedProducts
    ) {
        // Create customer, logout and login to frontend
        $customer->persist();
        $customerAccountLogout->open();
        $this->objectManager->create(
            'Mage\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();

        // Clear cart
        $checkoutCart->open();
        $checkoutCart->getCartBlock()->clearShoppingCart();

        // Check display cross sell products
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $catalogProductView->getViewBlock()->addToCart($product);
        $catalogProductView->getMessagesBlock()->waitSuccessMessage();
        $checkoutCart->open();
        $errors = [];
        foreach ($promotedProducts as $promotedProduct) {
            if (!$checkoutCart->getCrosssellBlock()->verifyProductCrosssell($promotedProduct)) {
                $errors[] = "Product '{$promotedProduct->getName()}' is absent in cross-sell section.";
            }
        }

        \PHPUnit_Framework_Assert::assertEmpty($errors, implode(" ", $errors));
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is displayed in cross-sell section.';
    }
}
