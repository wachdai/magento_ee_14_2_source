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

namespace Enterprise\Rma\Test\Block\Adminhtml\Rma\NewRma\Tab\Items\Order;

use Mage\Bundle\Test\Fixture\BundleProduct;
use Enterprise\Rma\Test\Block\Adminhtml\Product\Bundle\Items as BundleItems;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Grid for choose order item(bundle product).
 */
class BundleGrid extends Grid
{
    /**
     * Popup block for choose items of returned bundle product.
     *
     * @var string
     */
    protected $bundleItemsPopup = '//ancestor::div[@id="details_container"]';

    /**
     * Select order item.
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function selectItem(FixtureInterface $product)
    {
        /** @var BundleProduct $product */
        $checkoutData = $product->getCheckoutData();
        $bundleSelection = $product->getBundleSelections();
        $bundleOptions = isset($checkoutData['options']['bundle_options'])
            ? $checkoutData['options']['bundle_options']
            : [];

        $labels = [];

        foreach ($bundleOptions as $optionKey => $option) {
            $optionKey = substr($optionKey, -1);
            $productKey = substr($option['value']['name'], -1);
            $labels[] = $bundleSelection[$optionKey]['assigned_products'][$productKey]['name'];
        }

        $this->searchAndSelect(['name' => $product->getName()]);
        $this->getSelectItemsBlock()->fill($labels);
    }

    /**
     * Return popup select bundle items block.
     *
     * @return BundleItems
     */
    protected function getSelectItemsBlock()
    {
        return $this->blockFactory->create(
            'Enterprise\Rma\Test\Block\Adminhtml\Product\Bundle\Items',
            ['element' => $this->_rootElement->find($this->bundleItemsPopup, Locator::SELECTOR_XPATH)]
        );
    }
}
