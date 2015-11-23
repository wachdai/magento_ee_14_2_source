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

use Magento\Mtf\Constraint\AbstractAssertForm;
use Enterprise\GiftCardAccount\Test\Fixture\GiftCardAccount;
use Enterprise\GiftCardAccount\Test\Page\Adminhtml\GiftCardAccountNew;
use Enterprise\GiftCardAccount\Test\Page\Adminhtml\GiftCardAccountIndex;

/**
 * Assert that displayed gift card account data on edit page equals passed from fixture.
 */
class AssertGiftCardAccountForm extends AbstractAssertForm
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that displayed gift card account data on edit page equals passed from fixture.
     *
     * @param GiftCardAccount $giftCardAccount
     * @param GiftCardAccountNew $giftCardAccountNew
     * @param GiftCardAccountIndex $giftCardAccountIndex
     * @param string $code
     * @return void
     */
    public function processAssert(
        GiftCardAccount $giftCardAccount,
        GiftCardAccountNew $giftCardAccountNew,
        GiftCardAccountIndex $giftCardAccountIndex,
        $code
    ) {
        $giftCardAccountIndex->open();
        $giftCardAccountIndex->getGiftCardAccountGrid()->searchAndOpen(['code' => $code], false);
        $formData = $giftCardAccountNew->getGiftCardAccountForm()->getData();
        $dataDiff = $this->verifyData($giftCardAccount->getData(), $formData);
        \PHPUnit_Framework_Assert::assertEmpty(
            $dataDiff,
            "Gift card account form data does not equal to passed from fixture. \n" . $dataDiff
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Gift card account form data equals to passed from fixture.';
    }
}
