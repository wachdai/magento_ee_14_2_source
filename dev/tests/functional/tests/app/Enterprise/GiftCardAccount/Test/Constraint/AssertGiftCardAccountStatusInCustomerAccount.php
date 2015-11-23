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

namespace Enterprise\GiftCardAccount\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Mage\Customer\Test\Fixture\Customer;
use Mage\Customer\Test\Page\CustomerAccountIndex;
use Enterprise\GiftCardAccount\Test\Fixture\GiftCardAccount;

/**
 * Assert that created gift card account can be verified on the frontend on My Account page.
 */
class AssertGiftCardAccountStatusInCustomerAccount extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that created gift card account can be verified on the frontend on My Account page.
     *
     * @param Customer $customer
     * @param CustomerAccountIndex $customerAccountIndex
     * @param GiftCardAccount $giftCardAccount
     * @param string $code
     * @return void
     */
    public function processAssert(
        Customer $customer,
        CustomerAccountIndex $customerAccountIndex,
        GiftCardAccount $giftCardAccount,
        $code
    ) {
        $this->objectManager->create(
            'Mage\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();
        $customerAccountIndex->getAccountNavigationBlock()->openNavigationItem('Gift Card');
        $data = $giftCardAccount->getData();
        $customerAccountIndex->getRedeemBlock()->checkStatusAndBalance($code);
        $fixtureData = [
            'code' => $code,
            'balance' => $data['balance'],
            'date_expires' => $data['date_expires'],
        ];
        \PHPUnit_Framework_Assert::assertEquals(
            $fixtureData,
            $customerAccountIndex->getCheckBlock()->getGiftCardAccountData($fixtureData)
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Gift card account data is correct on the frontend on My Account page.';
    }
}
