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

namespace Enterprise\GiftCard\Test\TestCase;

use Mage\Catalog\Test\Fixture\CatalogCategory;
use Mage\Catalog\Test\Page\Adminhtml\CatalogProduct;
use Mage\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Enterprise\GiftCard\Test\Fixture\GiftCardProduct;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create category.
 *
 * Steps:
 * 1. Login to the backend.
 * 2. Navigate to Catalog > Manage Products.
 * 3. Click "Add Product" button.
 * 4. Start to create Gift Card Product.
 * 5. Fill in data according to data set.
 * 6. Save product.
 * 7. Perform appropriate assertions.
 *
 * @group Gift_Card_(MX)
 * @ZephyrId MPERF-6887
 */
class CreateGiftCardProductEntityTest extends Injectable
{
    /**
     * Product page with a grid.
     *
     * @var CatalogProduct
     */
    protected $catalogProductIndex;

    /**
     * Create product page.
     *
     * @var CatalogProductNew
     */
    protected $catalogProductNew;

    /**
     * Prepare category for test.
     *
     * @param CatalogCategory $category
     * @return array
     */
    public function __prepare(CatalogCategory $category)
    {
        $category->persist();

        return ['category' => $category];
    }

    /**
     * Injection pages.
     *
     * @param CatalogProduct $catalogProductIndex
     * @param CatalogProductNew $catalogProductNew
     * @return void
     */
    public function __inject(CatalogProduct $catalogProductIndex, CatalogProductNew $catalogProductNew)
    {
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductNew = $catalogProductNew;
    }

    /**
     * Create Gift Card product entity.
     *
     * @param GiftCardProduct $product
     * @param CatalogCategory $category
     * @return void
     */
    public function test(GiftCardProduct $product, CatalogCategory $category)
    {
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getGridPageActionBlock()->addNew();
        $this->catalogProductNew->getProductForm()->fill($product, null, $category);
        $this->catalogProductNew->getFormPageActions()->save();
    }
}
