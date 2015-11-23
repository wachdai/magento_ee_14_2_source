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

namespace Enterprise\GiftCardAccount\Test\Block\Account;

use Magento\Mtf\Block\Block;

/**
 * Redeem block on customer account page.
 */
class Redeem extends Block
{
    /**
     * Gift card code input field selector.
     *
     * @var string
     */
    protected $giftCardCode = '[name="giftcard_code"]';

    /**
     * Redeem button selector.
     *
     * @var string
     */
    protected $redeemGiftCard = '[onclick^="giftcardForm"]';

    /**
     * Check status and balance button selector.
     *
     * @var string
     */
    protected $checkStatusAndBalance = "#gca_balance_button";

    /**
     * Waiter block css selector.
     *
     * @var string
     */
    protected $waiter = '#gc-please-wait';

    /**
     * Fill gift card redeem.
     *
     * @param string $value
     * @return void
     */
    public function redeemGiftCard($value)
    {
        $this->enterGiftCardCode($value);
        $this->_rootElement->find($this->redeemGiftCard)->click();
    }

    /**
     * Check status and balance.
     *
     * @param string $value
     * @return void
     */
    public function checkStatusAndBalance($value)
    {
        $this->enterGiftCardCode($value);
        $this->_rootElement->find($this->checkStatusAndBalance)->click();
        $this->waitForElementNotVisible($this->waiter);
    }

    /**
     * Enter gift card code.
     *
     * @param string $value
     * @return void
     */
    protected function enterGiftCardCode($value)
    {
        $this->_rootElement->find($this->giftCardCode)->setValue($value);
    }
}
