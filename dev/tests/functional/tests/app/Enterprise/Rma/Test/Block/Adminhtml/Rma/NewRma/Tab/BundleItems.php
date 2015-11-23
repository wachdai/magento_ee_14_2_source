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

namespace Enterprise\Rma\Test\Block\Adminhtml\Rma\NewRma\Tab;

use Mage\Bundle\Test\Fixture\BundleProduct;

/**
 * Rma items tab for bundle product.
 */
class BundleItems extends Items
{
    /**
     * Fill item product in rma items grid.
     *
     * @param array $itemData
     * @return void
     */
    protected function fillItem(array $itemData)
    {
        /** @var BundleProduct $product */
        $product = $itemData['product'];
        $bundleSelections = $product->getBundleSelections();
        $checkoutData = $product->getCheckoutData();
        $checkoutOptions = isset($checkoutData['options']['bundle_options'])
            ? $checkoutData['options']['bundle_options']
            : [];

        unset($itemData['product']);
        foreach ($checkoutOptions as $optionKey => $option) {
            $optionKey = substr($optionKey, -1);
            foreach ($bundleSelections[$optionKey]['assigned_products'] as $productKey => $optionProducts) {
                    if ($productKey == substr($option['value']['name'], -1)) {
                        $fields = $this->dataMapping($itemData);
                        $itemRow = $this->getItemsGrid()->getItemRow($optionProducts['name']);
                        $this->_fill($fields, $itemRow);
                    }
                }
            }
        }
}
