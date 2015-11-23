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

namespace Enterprise\Banner\Test\TestCase;

use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Mage\Cms\Test\Fixture\CmsPage;
use Enterprise\Banner\Test\Fixture\BannerWidget;
use Enterprise\Banner\Test\Fixture\Banner;
use Mage\CatalogRule\Test\Fixture\CatalogRule;
use Enterprise\Banner\Test\Page\Adminhtml\BannerNew;
use Enterprise\Banner\Test\Page\Adminhtml\BannerIndex;
use Mage\Customer\Test\Fixture\Customer;
use Mage\Catalog\Test\Fixture\CatalogProductSimple;
use Mage\SalesRule\Test\Fixture\SalesRule;
use Enterprise\CustomerSegment\Test\Fixture\CustomerSegment;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create CustomerSegment.
 * 3. Create CMS Page.
 * 4. Create widget type - Banner Rotator.
 * 5. Create Shopping Cart Price Rule.
 * 6. Create Catalog Price Rule.
 * 7. Create banner.
 *
 * Steps:
 * 1. Login to the backend.
 * 2. Go to CMS -> Banners.
 * 3. Open created banner from preconditions.
 * 4. Related Cart and Catalog Rules to banner.
 * 5. Perform all assertions.
 *
 * @group Banner_(PS)
 * @ZephyrId MPERF-6988
 */
class AssignRelatedPromotionsToBannerEntityTest extends Injectable
{
    /**
     * BannerIndex page.
     *
     * @var BannerIndex
     */
    protected $bannerIndex;

    /**
     * BannerNew page.
     *
     * @var BannerNew
     */
    protected $bannerNew;

    /**
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Catalog and cart rules data.
     *
     * @var array
     */
    protected $rules;

    /**
     * Product fixture.
     *
     * @var InjectableFixture
     */
    protected $product;

    /**
     * Inject data.
     *
     * @param BannerIndex $bannerIndex
     * @param BannerNew $bannerNew
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        BannerIndex $bannerIndex,
        BannerNew $bannerNew,
        FixtureFactory $fixtureFactory
    ) {
        $this->bannerIndex = $bannerIndex;
        $this->bannerNew = $bannerNew;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Creation for assign Related Cart and Catalog Rules to BannerEntity test.
     *
     * @param Banner $banner
     * @param string $customer
     * @param string $customerSegment
     * @param CmsPage $cmsPage
     * @param string $widget
     * @param string $product
     * @param string $catalogPriceRule [optional]
     * @param string $cartPriceRule [optional]
     * @param bool $isCatalogRuleApplied [optional]
     * @return array
     */
    public function test(
        Banner $banner,
        CmsPage $cmsPage,
        $customer,
        $customerSegment,
        $widget,
        $product,
        $catalogPriceRule = null,
        $cartPriceRule = null,
        $isCatalogRuleApplied = false
    ) {
        // Preconditions
        $customer = $this->createCustomer($customer);
        $customerSegment = $this->createCustomerSegment($customerSegment);
        $cmsPage->persist();
        $this->product = $this->createProduct($product);
        $banner = $this->createBanner($customerSegment, $banner);
        $this->createWidget($widget, $banner);

        $this->createRules($cartPriceRule, $catalogPriceRule);
        $filter = ['name' => $banner->getName()];

        // Steps
        $this->bannerIndex->open();
        $this->bannerIndex->getGrid()->searchAndOpen($filter);
        $this->bannerNew->getBannerForm()->openTab('related_promotions');
        /** @var \Enterprise\Banner\Test\Block\Adminhtml\Banner\Edit\Tab\RelatedPromotions $tab */
        $tab = $this->bannerNew->getBannerForm()->getTabElement('related_promotions');
        if (!empty($this->rules['banner_sales_rules'])) {
            $tab->getCartPriceRulesGrid()->searchAndSelect(['id' => $this->rules['banner_sales_rules']]);
        }
        if (!empty($this->rules['banner_catalog_rules'])) {
            $tab->getCatalogPriceRulesGrid()->searchAndSelect(['id' => $this->rules['banner_catalog_rules']]);
        }
        $this->bannerNew->getFormPageActions()->save();

        // Apply Catalog rule for asserts:
        $this->applyCatalogRule($isCatalogRuleApplied);

        return [
            'product' => $this->product,
            'banner' => $banner,
            'customer' => $customer,
            'customerSegment' => $customerSegment,
        ];
    }

    /**
     * Apply catalog rule for assert proposes.
     *
     * @param boolean $flag
     * @return void
     */
    protected function applyCatalogRule($flag)
    {
        if ($flag)
        {
            $this->objectManager->create(
                'Mage\CatalogRule\Test\TestStep\SaveAndApplyCatalogRuleStep',
                ['catalogRuleId' => $this->rules['banner_catalog_rules']]
            )->run();
        }
    }

    /**
     * Create Cart and Catalog Rules.
     *
     * @param string $cartPriceRule
     * @param string $catalogPriceRule
     * @return void
     */
    protected function createRules($cartPriceRule, $catalogPriceRule)
    {
        $rules = [];
        if ($catalogPriceRule) {
            $catalogPriceRule = $this->prepareCatalogPriceRule($catalogPriceRule);
            $catalogPriceRule->persist();
            $rules['banner_catalog_rules'] = $catalogPriceRule->getId();
        }
        if ($cartPriceRule) {
            $cartPriceRule = $this->fixtureFactory->createByCode('salesRule', ['dataSet' => $cartPriceRule]);
            $cartPriceRule->persist();
            $rules['banner_sales_rules'] = $cartPriceRule->getRuleId();
        }
        $this->rules = $rules;
    }

    /**
     * Prepare catalog price rule.
     *
     * @return CatalogRule
     */
    protected function prepareCatalogPriceRule($dataSet)
    {
        $rule = $this->fixtureFactory->createByCode('catalogRule', ['dataSet' => $dataSet])->getData('rule');
        if ($this->product->hasData('category_ids')) {
            $pattern = '[Category|is|%d]';
            $category = $this->product->getDataFieldConfig('category_ids')['source']->getProductCategory();
            $rule = sprintf($pattern, $category->getId());
        }

        return $this->fixtureFactory->createByCode('catalogRule', ['dataSet' => $dataSet, 'data' => ['rule' => $rule]]);
    }

    /**
     * Create Customer.
     *
     * @param string $customer
     * @return Customer|null
     */
    protected function createCustomer($customer)
    {
        if ($customer !== '-') {
            $customer = $this->fixtureFactory->createByCode('customer', ['dataSet' => $customer]);
            $customer = $this->objectManager->create(
                'Mage\Customer\Test\TestStep\CreateCustomerStep',
                ['customer' => $customer]
            )->run();

            return $customer['customer'];
        }

        return null;
    }

    /**
     * Create Customer Segment.
     *
     * @param string $customerSegment
     * @return CustomerSegment|null
     */
    protected function createCustomerSegment($customerSegment)
    {
        if ($customerSegment !== '-') {
            $customerSegment = $this->fixtureFactory->createByCode('customerSegment', ['dataSet' => $customerSegment]);
            $customerSegment->persist();

            return $customerSegment;
        }

        return null;
    }

    /**
     * Create Product.
     *
     * @param string $products
     * @return CatalogProductSimple
     */
    protected function createProduct($products)
    {
        $products = $this->objectManager->create(
            'Mage\Catalog\Test\TestStep\CreateProductsStep',
            ['products' => $products]
        )->run();

        return $products['products'][0];
    }

    /**
     * Create banner.
     *
     * @param Banner $banner
     * @param string $customerSegment
     * @return Banner
     */
    protected function createBanner($customerSegment, Banner $banner)
    {
        if ($customerSegment !== null) {
            $banner = $this->fixtureFactory->createByCode(
                'banner',
                [
                    'dataSet' => 'default',
                    'data' => [
                        'customer_segment_ids' => [$customerSegment->getSegmentId()],
                    ]
                ]
            );
        }
        $banner->persist();

        return $banner;
    }

    /**
     * Create Widget.
     *
     * @param string $widget
     * @param Banner $banner
     * @return BannerWidget
     */
    protected function createWidget($widget, Banner $banner)
    {
        $widget = $this->fixtureFactory->create(
            'Enterprise\Banner\Test\Fixture\BannerWidget',
            [
                'dataSet' => $widget,
                'data' => [
                    'parameters' => [
                        'banner_ids' => $banner->getBannerId(),
                        'display_mode' => 'fixed',
                    ],
                ]
            ]
        );
        $widget->persist();

        return $widget;
    }

    /**
     * Deleted shopping cart price rules, catalog price rules and widget.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create('Mage\Widget\Test\TestStep\DeleteAllWidgetsStep')->run();
        if (isset($this->rules['banner_sales_rules'])) {
            $this->objectManager->create('Mage\SalesRule\Test\TestStep\DeleteAllSalesRuleStep')->run();
        }
        if (isset($this->rules['banner_catalog_rules'])) {
            $this->objectManager->create('Mage\CatalogRule\Test\TestStep\DeleteAllCatalogRulesStep')->run();
        }
    }
}
