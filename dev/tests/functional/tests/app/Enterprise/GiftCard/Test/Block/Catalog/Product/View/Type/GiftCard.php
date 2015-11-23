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

namespace Enterprise\GiftCard\Test\Block\Catalog\Product\View\Type;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Client\Element\SimpleElement;
use Enterprise\GiftCard\Test\Fixture\GiftCardProduct;

/**
 * Catalog gift card product form.
 */
class GiftCard extends Form
{
    /**
     * Price selector.
     *
     * @var string
     */
    protected $price = '.price-box .price';

    /**
     * Gift card amount field selector.
     *
     * @var string
     */
    protected $amountInput = '[name="custom_giftcard_amount"]';

    /**
     * Gift card amount selector.
     *
     * @var string
     */
    protected $amountSelect = '[name="giftcard_amount"]';

    /**
     * Gift card "Sender Name" field selector.
     *
     * @var string
     */
    protected $senderName = '[name="giftcard_sender_name"]';

    /**
     * Gift card "Sender Email" field selector.
     *
     * @var string
     */
    protected $senderEmail = '[name="giftcard_sender_email"]';

    /**
     * Gift card "Recipient Name" field selector.
     *
     * @var string
     */
    protected $recipientName = '[name="giftcard_recipient_name"]';

    /**
     * Gift card "Recipient Email" field selector.
     *
     * @var string
     */
    protected $recipientEmail = '[name="giftcard_recipient_email"]';

    /**
     * Gift card message selector.
     *
     * @var string
     */
    protected $message = '[name="giftcard_message"]';

    /**
     * Get amount values.
     *
     * @return array
     */
    public function getAmountValues()
    {
        $values = [];
        $giftCardAmount = $this->_rootElement->find($this->amountSelect, Locator::SELECTOR_CSS);
        $priceElement = $this->_rootElement->find($this->price);

        if (!$giftCardAmount->isVisible() && !$priceElement->isVisible()) {
            return $values;
        }

        // Return price if product has one amount
        if (!$giftCardAmount->isVisible()) {
            $price = $priceElement->getText();
            $values[] = floatval(preg_replace('/[^0-9.]/', '', $price));
            return $values;
        }

        $options = $giftCardAmount->getElements('option');
        // Skip option #0("Choose an Amount...")
        array_shift($options);
        foreach ($options as $key => $option) {
            $values[$key] = $option->getValue();
        }

        return array_reverse($values);
    }

    /**
     * Verify that text field for Gift Card amount is present.
     *
     * @return bool
     */
    public function isAmountInputVisible()
    {
        return $this->_rootElement->find($this->amountInput)->isVisible();
    }

    /**
     * Verify that select of Gift Card amount is present.
     *
     * @return bool
     */
    public function isAmountSelectVisible()
    {
        return $this->_rootElement->find($this->amountSelect)->isVisible();
    }

    /**
     * Verify that "Sender Name" field is visible.
     *
     * @return bool
     */
    public function isSenderNameVisible()
    {
        return $this->_rootElement->find($this->senderName)->isVisible();
    }

    /**
     * Verify that "Sender Email" field is visible.
     *
     * @return bool
     */
    public function isSenderEmailVisible()
    {
        return $this->_rootElement->find($this->senderEmail)->isVisible();
    }

    /**
     * Verify that "Recipient Name" field is visible.
     *
     * @return bool
     */
    public function isRecipientNameVisible()
    {
        return $this->_rootElement->find($this->recipientName)->isVisible();
    }

    /**
     * Verify that "Recipient Email" field is present.
     *
     * @return bool
     */
    public function isRecipientEmailVisible()
    {
        return $this->_rootElement->find($this->recipientEmail)->isVisible();
    }

    /**
     * Verify that "Message" field is present.
     *
     * @return bool
     */
    public function isMessageVisible()
    {
        return $this->_rootElement->find($this->message)->isVisible();
    }

    /**
     * Fill the GiftCard options.
     *
     * @param InjectableFixture $product
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(InjectableFixture $product, SimpleElement $element = null)
    {
        $checkoutGiftCardOptions = $this->getCheckoutGiftCardOptions($product);
        $mapping = $this->dataMapping($checkoutGiftCardOptions);
        $this->_fill($mapping, $element);

        return $this;
    }

    /**
     * Get checkout gift card options.
     *
     * @param GiftCardProduct $product
     * @return array
     */
    protected function getCheckoutGiftCardOptions(GiftCardProduct $product)
    {
        $checkoutGiftCardOptions = [];
        $giftcardAmounts = $product->getGiftcardAmounts();
        $checkoutData = $product->getCheckoutData();
        if (isset($checkoutData['options']['giftcard_options'])) {
            $checkoutGiftCardOptions = $checkoutData['options']['giftcard_options'];
            // Replace option key to value
            $amountOptionKey = str_replace('option_key_', '', $checkoutGiftCardOptions['giftcard_amount']);
            $checkoutGiftCardOptions['giftcard_amount'] = $giftcardAmounts[$amountOptionKey]['price'];
        }

        return $checkoutGiftCardOptions;
    }
}
