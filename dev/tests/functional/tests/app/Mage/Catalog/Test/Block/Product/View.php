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

namespace Mage\Catalog\Test\Block\Product;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\InjectableFixture;
use Mage\Catalog\Test\Fixture\CatalogProductSimple;
use Mage\Catalog\Test\Block\AbstractConfigureBlock;

/**
 * Product view block on the product page.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class View extends AbstractConfigureBlock
{
    /**
     * Custom options CSS selector.
     *
     * @var string
     */
    protected $customOptionsSelector = '.product-options-wrapper';

    /**
     * 'Add to Cart' button.
     *
     * @var string
     */
    protected $addToCart = '.button.btn-cart';

    /**
     * Quantity input id.
     *
     * @var string
     */
    protected $qty = '#qty';

    /**
     * Product name element.
     *
     * @var string
     */
    protected $productName = 'div.product-name span.h1';

    /**
     * Product description element.
     *
     * @var string
     */
    protected $productDescription = '.tab-content .std';

    /**
     * Product short-description element.
     *
     * @var string
     */
    protected $productShortDescription = '.short-description .std';

    /**
     * Stock Availability control.
     *
     * @var string
     */
    protected $stockAvailability = '.availability span.value';

    /**
     * Selector for price block.
     *
     * @var string
     */
    protected $priceBlock = "//*[@class='price-info']/*[@class='price-box']";

    /**
     * This member holds the class name of the tier price block.
     *
     * @var string
     */
    protected $tierPricesSelector = "//ul[contains(@class,'tier')]/li[%d]";

    /**
     * "Add to Wishlist" button.
     *
     * @var string
     */
    protected $addToWishlist = '.link-wishlist';

    /**
     * Messages block locator.
     *
     * @var string
     */
    protected $messageBlock = '.messages';

    /**
     * 'Add to Compare' button.
     *
     * @var string
     */
    protected $clickAddToCompare = '.link-compare';

    /**
     * Get block price.
     *
     * @return Price
     */
    public function getPriceBlock()
    {
        return $this->blockFactory->create(
            'Mage\Catalog\Test\Block\Product\Price',
            ['element' => $this->_rootElement->find($this->priceBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Add product to shopping cart.
     *
     * @param InjectableFixture $product
     * @return void
     */
    public function addToCart(InjectableFixture $product)
    {
        /** @var CatalogProductSimple $product */
        $checkoutData = $product->getCheckoutData();
        if (isset($checkoutData['options'])) {
            $this->fillOptions($product);
        }
        if (isset($checkoutData['qty'])) {
            $this->setQty($checkoutData['qty']);
        }
        $this->clickAddToCart();
    }

    /**
     * Fill in the option specified for the product.
     *
     * @param InjectableFixture $product
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function fillOptions(InjectableFixture $product)
    {
        $dataConfig = $product->getDataConfig();
        $typeId = isset($dataConfig['type_id']) ? $dataConfig['type_id'] : null;
        $checkoutData = null;

        /** @var CatalogProductSimple $product */
        if ($this->hasRender($typeId)) {
            $this->callRender($typeId, 'fillOptions', ['product' => $product]);
        }
        /** @var CatalogProductSimple $product */
        $checkoutData = $product->getCheckoutData();
        if (!isset($checkoutData['options']['custom_options'])) {
            return;
        }
        $customOptions = $product->getCustomOptions();
        if (isset($customOptions)) {
            $checkoutCustomOptions = $this->prepareCheckoutData(
                $customOptions,
                $checkoutData['options']['custom_options']
            );
            $this->getCustomOptionsBlock()->fillCustomOptions($checkoutCustomOptions);
        }
    }

    /**
     * Return product options.
     *
     * @param InjectableFixture $product
     * @return array
     */
    public function getOptions(InjectableFixture $product)
    {
        $dataConfig = $product->getDataConfig();
        $typeId = isset($dataConfig['type_id']) ? $dataConfig['type_id'] : null;

        return $this->hasRender($typeId)
            ? $this->callRender($typeId, 'getOptions', ['product' => $product])
            : $this->getCustomOptionsBlock()->getOptions($product);
    }

    /**
     * Click add to card button.
     *
     * @return void
     */
    public function clickAddToCart()
    {
        $this->_rootElement->find($this->addToCart)->click();
    }

    /**
     * Check add to card button.
     *
     * @return bool
     */
    public function checkAddToCartButton()
    {
        return $this->_rootElement->find($this->addToCart)->isVisible();
    }

    /**
     * Set quantity.
     *
     * @param int $qty
     * @return void
     */
    public function setQty($qty)
    {
        $this->browser->selectWindow();
        $this->_rootElement->find($this->qty)->setValue($qty);
        $this->_rootElement->click();
    }

    /**
     * Get product name displayed on page.
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->_rootElement->find($this->productName)->getText();
    }

    /**
     * Return product short description on page.
     *
     * @return string|null
     */
    public function getProductShortDescription()
    {
        if ($this->_rootElement->find($this->productShortDescription)->isVisible()) {
            return $this->_rootElement->find($this->productShortDescription)->getText();
        }
        return null;
    }

    /**
     * Return product description on page.
     *
     * @return string|null
     */
    public function getProductDescription()
    {
        if ($this->_rootElement->find($this->productDescription)->isVisible()) {
            return $this->_rootElement->find($this->productDescription)->getText();
        }
        return null;
    }

    /**
     * Get text of Stock Availability control.
     *
     * @param InjectableFixture $product
     * @return string
     */
    public function getStockAvailability(InjectableFixture $product)
    {
        $dataConfig = $product->getDataConfig();
        $typeId = isset($dataConfig['type_id']) ? $dataConfig['type_id'] : null;

        return $this->hasRender($typeId)
            ? $this->callRender($typeId, 'get' . ucfirst($typeId) . 'StockAvailability')
            : strtolower($this->_rootElement->find($this->stockAvailability)->getText());
    }

    /**
     * This method return array tier prices.
     *
     * @param int $lineNumber [optional]
     * @return array
     */
    public function getTierPrices($lineNumber = 1)
    {
        return $this->_rootElement->find(sprintf($this->tierPricesSelector, $lineNumber), Locator::SELECTOR_XPATH)
            ->getText();
    }

    /**
     * Add product to Wishlist.
     *
     * @param InjectableFixture $product
     * @return void
     */
    public function addToWishlist(InjectableFixture $product)
    {
        $checkoutData = $product->getCheckoutData();
        $this->fillOptions($product);
        if (isset($checkoutData['qty'])) {
            $this->setQty($checkoutData['qty']);
        }
        $this->clickAddToWishlist();
    }

    /**
     * Click "Add to Wishlist" button.
     *
     * @return void
     */
    public function clickAddToWishlist()
    {
        $this->_rootElement->find($this->addToWishlist)->click();
    }

    /**
     * Click "Add to Compare" button.
     *
     * @return void
     */
    public function clickAddToCompare()
    {
        /** @var \Mage\Core\Test\Block\Messages $messageBlock */
        $messageBlock = $this->blockFactory->create(
            'Mage\Core\Test\Block\Messages',
            ['element' => $this->browser->find($this->messageBlock)]
        );
        $this->_rootElement->find($this->clickAddToCompare, Locator::SELECTOR_CSS)->click();
        $messageBlock->waitSuccessMessage();
    }
}
