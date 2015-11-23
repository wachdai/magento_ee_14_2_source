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

namespace Enterprise\Rma\Test\Constraint;

use Enterprise\Rma\Test\Fixture\Rma;
use Mage\Sales\Test\Fixture\Order;
use Mage\Bundle\Test\Fixture\BundleProduct;

/**
 * Assert that rma with item as bundle product is correct display on frontend (MyAccount - My Returns).
 */
class AssertRmaBundleOnFrontendForCustomer extends AssertRmaOnFrontendForCustomer
{
    /**
     * Get assigned products.
     *
     * @param Order $order
     * @return array
     */
    protected function getAssignedProducts(Order $order)
    {
        return $this->prepareBundleItems(parent::getAssignedProducts($order));
    }

    /**
     * Prepare bundle items.
     *
     * @param BundleProduct[] $bundleProducts
     * @return array
     */
    protected function prepareBundleItems(array $bundleProducts)
    {
        $result = [];
        foreach ($bundleProducts as $bundle) {
            $result = array_merge($result, $this->getBundleAssignedItems($bundle));
        }

        return $result;
    }

    /**
     * Get assigned products.
     *
     * @param BundleProduct $bundleProduct
     * @return array
     */
    protected function getBundleAssignedItems(BundleProduct $bundleProduct)
    {
        $result = [];
        $options = $bundleProduct->getCheckoutData()['options']['bundle_options'];
        $productsKeys = $this->getProductsKeys($options);
        $products = $bundleProduct->getDataFieldConfig('bundle_selections')['source']->getProducts();
        foreach ($productsKeys as $optionKey => $optionProduct) {
            $result[] = $products[$optionKey][$optionProduct];
        }

        return $result;
    }

    /**
     * Get products keys.
     *
     * @param array $data
     * @return array
     */
    protected function getProductsKeys(array $data)
    {
        $keys = [];
        foreach ($data as $optionKey => $itemData) {
            $optionKey = str_replace('option_key_', '', $optionKey);
            $keys[$optionKey] = str_replace('product_key_', '', $itemData['value']['name']);
        }

        return $keys;
    }
}
