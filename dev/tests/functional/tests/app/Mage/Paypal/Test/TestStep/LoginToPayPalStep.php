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

namespace Mage\Paypal\Test\TestStep;

use Mage\Paypal\Test\Fixture\PaypalCustomer;
use Mage\Paypal\Test\Page\Paypal;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Login to Pay Pal step.
 */
class LoginToPayPalStep implements TestStepInterface
{
    /**
     * Pay Pal page.
     *
     * @var Paypal
     */
    protected $paypalPage;

    /**
     * Pay Pal customer fixture.
     *
     * @var PaypalCustomer
     */
    protected $customer;

    /**
     * @constructor
     * @param Paypal $paypalPage
     * @param PaypalCustomer $paypalCustomer
     */
    public function __construct(Paypal $paypalPage, PaypalCustomer $paypalCustomer)
    {
        $this->paypalPage = $paypalPage;
        $this->customer = $paypalCustomer;
    }

    /**
     * Login to Pay Pal.
     *
     * @return void
     */
    public function run()
    {
        /** Log out from previous session. */
        $this->paypalPage->getReviewBlock()->logOut();

        $payPalLoginBlock = $this->paypalPage->getLoginBlock();
        $payPalLoginBlock->fill($this->customer);
        $payPalLoginBlock->submit();
    }
}
