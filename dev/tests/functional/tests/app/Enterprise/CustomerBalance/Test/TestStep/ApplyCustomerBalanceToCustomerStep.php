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

namespace Enterprise\CustomerBalance\Test\TestStep;

use Mage\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Apply customer balance to customer.
 */
class ApplyCustomerBalanceToCustomerStep implements TestStepInterface
{
    /**
     * Customer fixture.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Customer Balance dataSet.
     *
     * @var string
     */
    protected $customerBalance;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param Customer $customer
     * @param string $customerBalance [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, Customer $customer, $customerBalance = null)
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->customerBalance = $customerBalance;
        $this->customer = $customer;
    }

    /**
     * Apply customer balance to customer.
     *
     * @return void
     */
    public function run()
    {
        if ($this->customerBalance !== null) {
            $customerBalance = $this->fixtureFactory->createByCode(
                'customerBalance',
                [
                    'dataSet' => $this->customerBalance,
                    'data' => [
                        'customer_id' => ['customer' => $this->customer],
                    ]
                ]
            );
            $customerBalance->persist();
        }
    }
}
