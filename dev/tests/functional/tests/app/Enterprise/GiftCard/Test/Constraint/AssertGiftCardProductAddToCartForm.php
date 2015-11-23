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

use Magento\Mtf\Client\Browser;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Enterprise\GiftCard\Test\Fixture\GiftCardProduct;
use Mage\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Assert that displayed gift card data on product page(front-end) equals passed from fixture.
 */
class AssertGiftCardProductAddToCartForm extends AbstractAssertForm
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that displayed gift card data on product page(front-end) equals passed from fixture.
     *
     * @param Browser $browser
     * @param GiftCardProduct $product
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function processAssert(Browser $browser, GiftCardProduct $product, CatalogProductView $catalogProductView)
    {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $fixtureAmounts = $product->hasData('giftcard_amounts') ? $product->getGiftcardAmounts() : [];
        $formAmounts = $catalogProductView->getGiftCardBlock()->getAmountValues();
        $errors = $this->verifyData($fixtureAmounts, $formAmounts);
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);

        $errors = $this->verifyFields($catalogProductView, $product, $fixtureAmounts);
        \PHPUnit_Framework_Assert::assertEmpty($errors, "\nErrors fields: \n" . implode("\n", $errors));
    }

    /**
     * Verify fields for "Add to cart" form.
     *
     * @param CatalogProductView $catalogProductView
     * @param GiftCardProduct $product
     * @param array $fixtureAmounts
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function verifyFields(
        CatalogProductView $catalogProductView,
        GiftCardProduct $product,
        array $fixtureAmounts
    ) {
        $giftCard = $catalogProductView->getGiftCardBlock();
        $isAmountSelectVisible = $giftCard->isAmountSelectVisible();
        $isAmountInputVisible = $giftCard->isAmountInputVisible();
        $isAllowOpenAmount = $product->hasData('allow_open_amount') && 'Yes' === $product->getAllowOpenAmount();
        $isShowSelectAmount = $product->hasData('giftcard_amounts')
            && ($isAllowOpenAmount || 1 < count($product->getGiftcardAmounts()));

        return array_filter(array_merge(
            $this->getSelectAmountVisibleErrors($isAmountSelectVisible, $isShowSelectAmount),
            $this->getInputAmountVisibleErrors($fixtureAmounts, $isAllowOpenAmount, $isAmountInputVisible),
            $this->getSenderAndRecipientFieldsVisibleErrors($product, $catalogProductView)
        ));
    }

    /**
     * Get input amount visible errors.
     *
     * @param array $fixtureAmounts
     * @param bool $isAllowOpenAmount
     * @param bool $isAmountInputVisible
     * @return array
     */
    protected function getInputAmountVisibleErrors(array $fixtureAmounts, $isAllowOpenAmount, $isAmountInputVisible)
    {
        $errors = [];
        $errors[] = (count($fixtureAmounts) === 0 && $isAllowOpenAmount && !$isAmountInputVisible)
            ? '- input amount is not displayed.'
            : null;
        $errors[] = (!$isAllowOpenAmount && $isAmountInputVisible) ? '- input amount is displayed.' : null;

        return $errors;
    }

    /**
     * Get select amount visible errors.
     *
     * @param bool $isAmountSelectVisible
     * @param bool $isShowSelectAmount
     * @return array
     */
    protected function getSelectAmountVisibleErrors($isAmountSelectVisible, $isShowSelectAmount)
    {
        $errors = [];
        $errors[] = (!$isAmountSelectVisible && $isShowSelectAmount) ? '- select amount is not displayed.' : null;
        $errors[] = ($isAmountSelectVisible && !$isShowSelectAmount) ? '- select amount is displayed.' : null;

        return $errors;
    }

    /**
     * Get sender and recipient fields visible errors.
     *
     * @param GiftCardProduct $product
     * @param CatalogProductView $catalogProductView
     * @return array
     */
    protected function getSenderAndRecipientFieldsVisibleErrors(
        GiftCardProduct $product,
        CatalogProductView $catalogProductView
    ) {
        $giftCard = $catalogProductView->getGiftCardBlock();
        $errors = [];
        $errors[] = !$giftCard->isSenderNameVisible() ? '- "Sender Name" is not displayed.' : null;
        $errors[] = !$giftCard->isRecipientNameVisible() ? '- "Recipient Name" is not displayed.' : null;
        $errors[] = !$giftCard->isMessageVisible() ? '- "Message" is not displayed.' : null;
        if ('Physical' !== $product->getGiftcardType()) {
            $errors[] = !$giftCard->isSenderEmailVisible() ? '- "Sender Email" is not displayed.' : null;
            $errors[] = !$giftCard->isRecipientEmailVisible() ? '- "Recipient Email" is not displayed.' : null;
        }

        return $errors;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Displayed gift card data on product page(front-end) equals passed from fixture.";
    }
}
