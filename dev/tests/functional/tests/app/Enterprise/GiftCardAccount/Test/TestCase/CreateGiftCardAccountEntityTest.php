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

namespace Enterprise\GiftCardAccount\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Mage\Customer\Test\Fixture\Customer;
use Enterprise\GiftCardAccount\Test\Fixture\GiftCardAccount;
use Enterprise\GiftCardAccount\Test\Page\Adminhtml\GiftCardAccountNew;
use Enterprise\GiftCardAccount\Test\Page\Adminhtml\GiftCardAccountIndex;

/**
 * Steps:
 * 1. Login to the backend.
 * 2. Navigate to Customers -> Gift Card Accounts.
 * 3. Generate new code pool if it is needed (if appropriate error message is displayed on the page).
 * 4. Click "Add Gift Card Account" button.
 * 5. Fill in data according to data set.
 * 6. Click "Save" button.
 * 7. Perform appropriate assertions.
 *
 * @group Gift_Card_(CS)
 * @ZephyrId MPERF-6786
 */
class CreateGiftCardAccountEntityTest extends Injectable
{
    /**
     * Page of gift card account.
     *
     * @var GiftCardAccountIndex
     */
    protected $giftCardAccountIndex;

    /**
     * Page of create gift card account.
     *
     * @var GiftCardAccountNew
     */
    protected $giftCardAccountNew;

    /**
     * Prepare data.
     *
     * @param FixtureFactory $fixtureFactory
     * @param Customer $customer
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory, Customer $customer)
    {
        $product = $fixtureFactory->createByCode('catalogProductSimple',['dataSet' => '100_dollar_product']);
        $product->persist();
        $customer->persist();

        return [
            'product' => $product,
            'customer' => $customer
        ];
    }

    /**
     * Inject pages.
     *
     * @param GiftCardAccountIndex $giftCardAccountIndex
     * @param GiftCardAccountNew $giftCardAccountNew
     * @return void
     */
    public function __inject(GiftCardAccountIndex $giftCardAccountIndex, GiftCardAccountNew $giftCardAccountNew)
    {
        $this->giftCardAccountIndex = $giftCardAccountIndex;
        $this->giftCardAccountNew = $giftCardAccountNew;
    }

    /**
     * Create gift card account.
     *
     * @param GiftCardAccount $giftCardAccount
     * @return array
     */
    public function test(GiftCardAccount $giftCardAccount)
    {
        // Steps
        $this->giftCardAccountIndex->open();
        $this->giftCardAccountIndex->getMessagesBlock()->clickLinkInMessages('error', 'here');
        $this->giftCardAccountIndex->getGridPageActions()->addNew();
        $this->giftCardAccountNew->getGiftCardAccountForm()->fill($giftCardAccount);
        $this->giftCardAccountNew->getFormPageActions()->save();

        $code = $this->giftCardAccountIndex->getGiftCardAccountGrid()
            ->getCode(['balance' => $giftCardAccount->getBalance()], false);

        return ['code' => $code];
    }
}
