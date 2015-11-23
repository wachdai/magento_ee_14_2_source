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

namespace Enterprise\GiftCardAccount\Test\TestStep;

use Mage\Checkout\Test\Page\CheckoutCart;
use Enterprise\GiftCardAccount\Test\Fixture\GiftCardAccount;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Apply gift card before one page checkout.
 */
class ApplyGiftCardStep implements TestStepInterface
{
    /**
     * Checkout cart page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * GiftCardAccount fixtures.
     *
     * @var GiftCardAccount[]
     */
    protected $giftCardAccounts;

    /**
     * @constructor
     * @param CheckoutCart $checkoutCart
     * @param GiftCardAccount|GiftCardAccount[] $giftCardAccount
     */
    public function __construct(CheckoutCart $checkoutCart, $giftCardAccount = null)
    {
        $this->checkoutCart = $checkoutCart;
        $this->giftCardAccounts = is_array($giftCardAccount) ? $giftCardAccount : [$giftCardAccount];
    }

    /**
     * Apply gift card before one page checkout.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->giftCardAccounts as $giftCardAccount) {
            if ($giftCardAccount !== null) {
                $this->checkoutCart->getGiftCardAccountBlock()->addGiftCard($giftCardAccount->getCode());
            }
        }
    }
}
