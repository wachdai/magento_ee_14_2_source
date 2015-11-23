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

namespace Enterprise\CustomerBalance\Test\TestCase;

use Mage\Customer\Test\Fixture\Customer;
use Mage\Customer\Test\Page\Adminhtml\CustomerIndex;
use Mage\Customer\Test\Page\Adminhtml\CustomerEdit;
use Enterprise\CustomerBalance\Test\Fixture\CustomerBalance;
use Magento\Mtf\TestCase\Injectable;

/**
 * Precondition:
 * 1. Default customer is created.
 *
 * Steps:
 * 1. Login to backend as admin.
 * 2. Navigate to Customers -> Manage Customers.
 * 3. Open customer from preconditions.
 * 4. Open "Store Credit" tab.
 * 5. Fill form with test data.
 * 6. Click "Save Customer" button.
 * 7. Preform asserts.
 *
 * @group Customers_(MX)
 * @ZephyrId MPERF-6747
 */
class CreateCustomerBalanceEntityTest extends Injectable
{
    /**
     * Page of all customer grid.
     *
     * @var CustomerIndex
     */
    protected $customerIndex;

    /**
     * Page of edit customer.
     *
     * @var CustomerEdit
     */
    protected $customerEdit;

    /**
     * Prepare customer from preconditions.
     *
     * @param Customer $customer
     * @return array
     */
    public function __prepare(Customer $customer)
    {
        $customer->persist();

        return ['customer' => $customer];
    }

    /**
     * Inject customer pages.
     *
     * @param CustomerIndex $customerIndex
     * @param CustomerEdit $customerEdit
     * @return void
     */
    public function __inject(CustomerIndex $customerIndex, CustomerEdit $customerEdit)
    {
        $this->customerIndex = $customerIndex;
        $this->customerEdit = $customerEdit;
    }

    /**
     * Create customer balance.
     *
     * @param CustomerBalance $customerBalance
     * @param Customer $customer
     * @return void
     */
    public function test(CustomerBalance $customerBalance, Customer $customer)
    {
        $this->customerIndex->open();
        $filter = ['email' => $customer->getEmail()];
        $this->customerIndex->getCustomerGridBlock()->searchAndOpen($filter);
        $this->customerEdit->getCustomerBalanceForm()->fill($customerBalance);
        $this->customerEdit->getPageActionsBlock()->save();
    }
}
