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

namespace Enterprise\GiftCard\Test\Constraint;

use Mage\Catalog\Test\Constraint\AssertProductPage;

/**
 * Assert that displayed gift card product data on product page(front-end) equals passed from fixture.
 */
class AssertGiftCardProductPage extends AssertProductPage
{
    /**
     * Verify displayed gift card product price on product page(front-end) equals passed from fixture.
     *
     * @return string|null
     */
    protected function verifyPrice()
    {
        $productData = $this->product->getData();
        $priceOnPage = $this->productView->getPriceBlock()->getFinalPrice();
        $price = null;
        if (isset($productData['giftcard_amounts'])) {
            $price = array_reverse($productData['giftcard_amounts'])[0];
        }
        if (isset($productData['open_amount_min'])) {
            $price = $productData['open_amount_min'];
        }
        if ($price == $priceOnPage) {
            return null;
        }
        return "Displayed product price on product page(front-end) not equals passed from fixture. "
        . "Actual: {$priceOnPage}, expected: {$price}.";
    }
}
