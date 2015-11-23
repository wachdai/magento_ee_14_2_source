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

namespace Enterprise\UrlRewrite\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Mage\Adminhtml\Test\Fixture\Store;
use Mage\Adminhtml\Test\Page\Adminhtml\EditStore;
use Enterprise\UrlRewrite\Test\Fixture\UrlRewrite;
use Mage\Adminhtml\Test\Page\Adminhtml\StoreIndex;
use Mage\Catalog\Test\Fixture\CatalogProductSimple;
use Mage\Adminhtml\Test\Page\Adminhtml\DeleteStore;
use Enterprise\UrlRewrite\Test\Page\Adminhtml\UrlRewriteEdit;
use Enterprise\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Enterprise\UrlRewrite\Test\Page\Adminhtml\UrlRewriteSelect;
use Enterprise\UrlRewrite\Test\Page\Adminhtml\UrlRewriteSelectTypeProduct;
use Enterprise\UrlRewrite\Test\Page\Adminhtml\UrlRewriteSelectTypeProductCategory;

/**
 * Preconditions:
 * 1. Create custom storeView.
 * 2. Create simple product.
 *
 * Steps:
 * 1. Log in to backend.
 * 2. Go to Catalog -> URL Redirects.
 * 3. Click "Add URL Redirect" button.
 * 4. Select "Product" type.
 * 5. Select created early product.
 * 6. Click "Skip Category Selection" button.
 * 7. Fill data according to dataSet.
 * 8. Perform all assertions.
 *
 * @group URL_Rewrites_(MX)
 * @ZephyrId MPERF-6823
 */
class CreateProductUrlRewriteEntityTest extends Injectable
{
    /**
     * Url rewrite index page.
     *
     * @var UrlRewriteIndex
     */
    protected $urlRewriteIndex;

    /**
     * Url rewrite edit page.
     *
     * @var UrlRewriteEdit
     */
    protected $urlRewriteEdit;

    /**
     * Url rewrite select type page.
     *
     * @var UrlRewriteSelect
     */
    protected $urlRewriteSelect;

    /**
     * Url rewrite select type product page.
     *
     * @var UrlRewriteSelectTypeProduct
     */
    protected $urlRewriteSelectTypeProduct;

    /**
     * Url rewrite type product select category page.
     *
     * @var UrlRewriteSelectTypeProductCategory
     */
    protected $urlRewriteSelectTypeProductCategory;

    /**
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * UrlRewrite fixture.
     *
     * @var UrlRewrite
     */
    protected $urlRewrite;

    /**
     * Page StoreIndex.
     *
     * @var StoreIndex
     */
    protected $storeIndex;

    /**
     * Page EditStore.
     *
     * @var EditStore
     */
    protected $editStore;

    /**
     * Page DeleteStore.
     *
     * @var DeleteStore
     */
    protected $deleteStore;

    /**
     * Inject data.
     *
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param UrlRewriteEdit $urlRewriteEdit
     * @param UrlRewriteSelect $urlRewriteSelect
     * @param UrlRewriteSelectTypeProduct $urlRewriteSelectTypeProduct
     * @param UrlRewriteSelectTypeProductCategory $urlRewriteSelectTypeProductCategory
     * @param FixtureFactory $fixtureFactory
     * @param StoreIndex $storeIndex
     * @param EditStore $editStore
     * @param DeleteStore $deleteStore
     * @return void
     */
    public function __inject(
        UrlRewriteIndex $urlRewriteIndex,
        UrlRewriteEdit $urlRewriteEdit,
        UrlRewriteSelect $urlRewriteSelect,
        UrlRewriteSelectTypeProduct $urlRewriteSelectTypeProduct,
        UrlRewriteSelectTypeProductCategory $urlRewriteSelectTypeProductCategory,
        FixtureFactory $fixtureFactory,
        StoreIndex $storeIndex,
        EditStore $editStore,
        DeleteStore $deleteStore
    ) {
        $this->urlRewriteIndex = $urlRewriteIndex;
        $this->urlRewriteEdit = $urlRewriteEdit;
        $this->urlRewriteSelect = $urlRewriteSelect;
        $this->urlRewriteSelectTypeProduct = $urlRewriteSelectTypeProduct;
        $this->urlRewriteSelectTypeProductCategory = $urlRewriteSelectTypeProductCategory;
        $this->fixtureFactory = $fixtureFactory;
        $this->storeIndex = $storeIndex;
        $this->editStore = $editStore;
        $this->deleteStore = $deleteStore;
    }

    /**
     * Create product URL Rewrite.
     *
     * @param CatalogProductSimple $product
     * @param UrlRewrite $urlRewrite
     * @return array
     */
    public function test(CatalogProductSimple $product, UrlRewrite $urlRewrite)
    {
        //Prepare data for tearDown
        $this->urlRewrite = $urlRewrite;
        //Precondition
        $product->persist();
        //Steps
        $filter = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'sku' => $product->getSku()
        ];
        $this->urlRewriteIndex->open();
        $this->urlRewriteIndex->getGridPageActionBlock()->addNew();
        $this->urlRewriteSelect->getSelectTypeForm()->fill($urlRewrite);
        $this->urlRewriteSelectTypeProduct->getProductGridBlock()->searchAndOpen($filter);
        $this->urlRewriteSelectTypeProductCategory->getCategoryTreeBlock()->selectCategory($product->getCategoryIds());
        $this->urlRewriteEdit->getEditForm()->fill($urlRewrite);
        $this->urlRewriteEdit->getFormPageActions()->save();

        return ['product' => $product];
    }

    /**
     * Delete store.
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->urlRewrite !== null && $this->urlRewrite->hasData('store_id')) {
            $store = $this->urlRewrite->getDataFieldConfig('store_id')['source']->getStore();
            if ($store->getName() !== 'Default Store View') {
                $this->storeIndex->open();
                $this->storeIndex->getStoreGrid()->openStore($store);
                $this->editStore->getFormPageActions()->delete();
                $this->deleteStore->getFormPageActions()->delete();
            }
            $this->urlRewrite = null;
        }
    }
}
