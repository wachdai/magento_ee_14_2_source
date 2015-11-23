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

namespace Enterprise\CustomerBalance\Test\Constraint;

use Mage\Customer\Test\Fixture\Customer;
use Enterprise\CustomerBalance\Test\Page\StoreCreditInfo;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Mage\Customer\Test\Constraint\FrontendActionsForCustomer;

/**
 * Check that customer balance amount is changed on frontend.
 */
class AssertStoreCreditOnFrontend extends AbstractAssertForm
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Verify fields.
     *
     * @var array
     */
    protected $verifyFields = [
        'item_action',
        'item_balance_change',
        'item_balance'
    ];

    /**
     * Assert that customer balance amount is changed on frontend.
     *
     * @param Customer $customer
     * @param StoreCreditInfo $storeCreditInfo
     * @param array|null $verifyData
     * @return void
     */
    public function processAssert(Customer $customer, StoreCreditInfo $storeCreditInfo, array $verifyData)
    {
        $frontendActions = new FrontendActionsForCustomer();
        $frontendActions->loginCustomer($customer);
        $frontendActions->openCustomerTab('Store Credit');
        $fixtureData = $this->prepareStoreCreditData($verifyData);
        $formData = $storeCreditInfo->getBalanceHistoryGrid()->getFieldsData();
        $error = $this->verifyData($fixtureData, $formData);
        \PHPUnit_Framework_Assert::assertEmpty($error, $error);
    }

    /**
     * Prepare store credit data.
     *
     * @param array $verifyData
     * @return array
     */
    protected function prepareStoreCreditData(array $verifyData)
    {
        $result = [];
        foreach ($this->verifyFields as $field) {
            if (isset($verifyData[$field])) {
                $result[$field] = $verifyData[$field];
            }
        }

        return $result;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer balance amount has been updated on frontend.';
    }
}
