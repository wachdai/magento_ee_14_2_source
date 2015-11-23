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

namespace Enterprise\GiftCardAccount\Test\TestCase;

use Mage\Catalog\Test\Page\Product\CatalogProductView;
use Mage\Checkout\Test\Page\CheckoutCart;
use Mage\Cms\Test\Page\CmsIndex;
use Mage\Customer\Test\Fixture\Customer;
use Enterprise\GiftCardAccount\Test\Fixture\GiftCardAccount;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\InjectableFixture;
use Mage\Customer\Test\Page\CustomerAccountLogout;

/**
 * Preconditions:
 * 1. Create Customer.
 * 2. Create GiftCard account.
 *
 * Steps:
 * 1. Go to frontend.
 * 2. Login as a customer if customer name is specified in data set.
 * 3. Add product (according to dataset) to the cart.
 * 4. Expand Gift Cards tab and fill code.
 * 5. Click 'Add Gift Card'.
 * 6. Perform appropriate assertions.
 *
 * @group Gift_Card_Account_(CS)
 * @ZephyrId MPERF-7366
 */
class AddGiftCardInShoppingCartTest extends Injectable
{
    /**
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * CmsIndex page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * CatalogProductView page.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * CheckoutCart page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * CustomerAccountLogout page.
     *
     * @var CustomerAccountLogout
     */
    protected $customerAccountLogout;

    /**
     * Create customer and gift card account.
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $customer = $fixtureFactory->createByCode('customer', ['dataSet' => 'default']);
        $customer->persist();

        $giftCardAccount = $fixtureFactory->createByCode('giftCardAccount', ['dataSet' => 'active_redeemable_account']);
        $giftCardAccount->persist();

        return [
            'customerFixture' => $customer,
            'giftCardAccount' => $giftCardAccount,
        ];
    }

    /**
     * Injection data.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CmsIndex $cmsIndex
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param CustomerAccountLogout $customerAccountLogout
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CmsIndex $cmsIndex,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart,
        CustomerAccountLogout $customerAccountLogout
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->cmsIndex = $cmsIndex;
        $this->catalogProductView = $catalogProductView;
        $this->checkoutCart = $checkoutCart;
        $this->customerAccountLogout = $customerAccountLogout;
    }

    /**
     * Add GiftCard in ShoppingCart.
     *
     * @param Customer $customerFixture
     * @param GiftCardAccount $giftCardAccount
     * @param BrowserInterface $browser
     * @param string $product
     * @param string|null $customer
     * @return array
     */
    public function test(
        Customer $customerFixture,
        GiftCardAccount $giftCardAccount,
        BrowserInterface $browser,
        $product,
        $customer = null
    ) {
        // Preconditions
        $product = $this->createProduct($product);

        // Steps
        if ($customer !== null) {
            $this->loginCustomer($customerFixture);
        }
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $this->catalogProductView->getViewBlock()->addToCart($product);
        $this->checkoutCart->getGiftCardAccountBlock()->addGiftCard($giftCardAccount->getCode());

        return ['giftCardAccount' => $giftCardAccount];
    }

    /**
     * Login customer to frontend.
     *
     * @param Customer $customer
     * @return void
     */
    public function loginCustomer(Customer $customer)
    {
        $this->objectManager->create(
            'Mage\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();
    }

    /**
     * Create product.
     *
     * @param string $product
     * @return InjectableFixture
     */
    protected function createProduct($product)
    {
        return $this->objectManager->create('Mage\Catalog\Test\TestStep\CreateProductStep', ['product' => $product])
            ->run()['product'];
    }

    /**
     * Customer log out after test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->customerAccountLogout->open();
    }
}
