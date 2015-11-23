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

namespace Mage\Checkout\Test\Block\GroupedProductCart;

use Mage\Checkout\Test\Block\Cart\CartItem as CheckoutCartItem;
use Magento\Mtf\Client\ElementInterface;

/**
 * Product item block on checkout page.
 */
class CartItem extends CheckoutCartItem
{
    /**
     * Get product price.
     *
     * @return string
     */
    public function getPrice()
    {
        $result = [];
        foreach ($this->config['associated_cart_items'] as $productSku => $cartItem) {
            /** @var CheckoutCartItem $cartItem */
            $result[$productSku] = $cartItem->getPrice();
        }

        return $result;
    }

    /**
     * Set product quantity.
     *
     * @param array $data
     * @return void
     */
    public function setQty(array $data)
    {
        foreach ($data as $productSku => $qty) {
            /** @var CheckoutCartItem $cartItem */
            $cartItem = $this->config['associated_cart_items'][$productSku];
            $cartItem->setQty($qty);
        }
    }

    /**
     * Get product quantity.
     *
     * @return string
     */
    public function getQty()
    {
        $result = [];
        foreach ($this->config['associated_cart_items'] as $productSku => $cartItem) {
            /** @var CheckoutCartItem $cartItem */
            $result[$productSku] = $cartItem->getQty();
        }

        return $result;
    }

    /**
     * Get sub-total for the specified item in the cart.
     *
     * @return string
     */
    public function getSubtotalPrice()
    {
        $result = [];
        foreach ($this->config['associated_cart_items'] as $productSku => $cartItem) {
            /** @var CheckoutCartItem $cartItem */
            $result[$productSku] = $cartItem->getSubtotalPrice();
        }

        return $result;
    }

    /**
     * Get product options in the cart.
     *
     * @param ElementInterface $element
     * @return string
     */
    public function getOptions(ElementInterface $element = null)
    {
        $result = [];
        foreach ($this->config['associated_cart_items'] as $cartItem) {
            /** @var CheckoutCartItem $cartItem */
            $result[] = [
                'title' => $cartItem->getProductName(),
                'value' => $cartItem->getQty(),
            ];
        }

        return $result;
    }
}
