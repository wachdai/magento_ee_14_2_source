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

namespace Enterprise\GiftCardAccount\Test\Block\Checkout\Cart;

use Magento\Mtf\Block\Form;

/**
 * Gift card account block in cart.
 */
class Giftcardaccount extends Form
{
    /**
     * Add gift cards button selector.
     *
     * @var string
     */
    protected $addGiftCardButton = '.button-wrapper .button2';

    /**
     * Check status and balance button selector.
     *
     * @var string
     */
    protected $checkStatusAndBalance = ".check-gc-status";

    /**
     * Fill gift card in cart.
     *
     * @param string $code
     * @return void
     */
    public function addGiftCard($code)
    {
        $this->enterGiftCardCode($code);
        $this->_rootElement->find($this->addGiftCardButton)->click();
    }

    /**
     * Enter gift card code.
     *
     * @param string $code
     * @return void
     */
    protected function enterGiftCardCode($code)
    {
        $this->_fill($this->dataMapping(['code' => $code]));
    }

    /**
     * Check status and balance.
     *
     * @param string $code
     * @return void
     */
    public function checkStatusAndBalance($code)
    {
        $this->enterGiftCardCode($code);
        $this->_rootElement->find($this->checkStatusAndBalance)->click();
    }
}
