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

namespace Mage\Paypal\Test\Block\Express;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Paypal Express Onepage checkout review block.
 */
class Review extends Block
{
    /**
     * 'Place order' button selector.
     *
     * @var string
     */
    protected $placeOrder = '#review_button';

    /**
     * Shipping method selector.
     *
     * @var string
     */
    protected $shippingMethod = '#shipping_method';

    /**
     * Css selector for button waiter.
     *
     * @var string
     */
    protected $loaderForButton = '#review-please-wait img';

    /**
     * Click 'Place Order' button.
     *
     * @return void
     */
    public function placeOrder()
    {
        $this->waitForElementNotVisible($this->loaderForButton);
        $this->_rootElement->find($this->placeOrder)->click();
        $this->waitForElementNotVisible($this->loaderForButton);
    }

    /**
     * Select shipping method.
     *
     * @param string $shippingMethod
     * @return void
     */
    public function selectShippingMethod($shippingMethod)
    {
        $this->waitForElementVisible($this->shippingMethod);
        list($service, $method) = explode('/', $shippingMethod);
        $this->_rootElement->find($this->shippingMethod, Locator::SELECTOR_CSS, 'optgroupselect')
            ->setValue($service . "/" . $method);
    }
}
