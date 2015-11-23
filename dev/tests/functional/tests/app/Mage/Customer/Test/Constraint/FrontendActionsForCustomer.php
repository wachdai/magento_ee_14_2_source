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

namespace Mage\Customer\Test\Constraint;

use Mage\Customer\Test\Page\CustomerAccountIndex;
use Mage\Customer\Test\Fixture\Customer;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\Page\PageInterface;

/**
 * Frontend actions for customer.
 */
class FrontendActionsForCustomer
{
    /**
     * Customer account index page
     *
     * @var CustomerAccountIndex
     */
    protected $customerAccountIndex;

    /**
     * Pages array.
     *
     * @var array
     */
    protected $pages = [
        'customerAccountIndex' => 'Mage\Customer\Test\Page\CustomerAccountIndex'
    ];

    /**
     * @constructor
     */
    public function __construct()
    {
        foreach ($this->pages as $key => $page) {
            $this->$key = $this->createPage($page);
        }
    }

    /**
     * Create page.
     *
     * @param string $page
     * @return PageInterface
     */
    protected function createPage($page)
    {
        return ObjectManager::getInstance()->create($page);
    }

    /**
     * Login customer to frontend.
     *
     * @param Customer $customer
     * @return void
     */
    public function loginCustomer(Customer $customer)
    {
        $loginCustomerOnFrontendStep = ObjectManager::getInstance()->create(
            'Mage\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        );
        $loginCustomerOnFrontendStep->run();
    }

    /**
     * Open customer tab.
     *
     * @param string $tabName
     * @return void
     */
    public function openCustomerTab($tabName)
    {
        $this->customerAccountIndex->getAccountNavigationBlock()->openNavigationItem($tabName);
    }
}
