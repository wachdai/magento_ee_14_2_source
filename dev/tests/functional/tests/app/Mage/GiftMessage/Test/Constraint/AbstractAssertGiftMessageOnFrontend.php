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

namespace Mage\GiftMessage\Test\Constraint;

use Mage\Customer\Test\Fixture\Customer;
use Mage\GiftMessage\Test\Fixture\GiftMessage;
use Mage\Sales\Test\Page\OrderHistory;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Abstract assert gift message on frontend.
 */
abstract class AbstractAssertGiftMessageOnFrontend extends AbstractAssertForm
{
    /**
     * Open order page.
     *
     * @param OrderHistory $orderHistory
     * @param string $orderId
     */
    protected function openOrderPage(OrderHistory $orderHistory, $orderId)
    {
        $orderHistory->open();
        $orderHistory->getOrderHistoryBlock()->openOrderById($orderId);
    }

    /**
     * Prepare expected data.
     *
     * @param GiftMessage $giftMessage
     * @return array
     */
    protected function prepareExpectedData(GiftMessage $giftMessage)
    {
        return [
            'sender' => $giftMessage->getSender(),
            'recipient' => $giftMessage->getRecipient(),
            'message' => $giftMessage->getMessage(),
        ];
    }

    /**
     * Login customer on frontend.
     *
     * @param Customer $customer
     * @return void
     */
    protected function loginOnFrontend(Customer $customer)
    {
        $this->objectManager->create(
            'Mage\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();
    }
}
