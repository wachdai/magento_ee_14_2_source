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

namespace Mage\Sales\Test\Constraint;

use Mage\Customer\Test\Fixture\Customer;
use Mage\Sales\Test\Page\OrderHistory;
use Mage\Sales\Test\Page\OrderView;
use Mage\Customer\Test\Constraint\FrontendActionsForCustomer;

/**
 * Frontend actions for sales asserts.
 */
class FrontendActionsForSalesAssert extends FrontendActionsForCustomer
{
    /**
     * Order history page.
     *
     * @var OrderHistory
     */
    protected $orderHistory;

    /**
     * Order view page.
     *
     * @var OrderView
     */
    protected $customerOrderView;

    /**
     * Pages array.
     *
     * @var array
     */
    protected $pages = [
        'customerAccountIndex' => 'Mage\Customer\Test\Page\CustomerAccountIndex',
        'orderHistory' => 'Mage\Sales\Test\Page\OrderHistory',
        'customerOrderView' => 'Mage\Sales\Test\Page\OrderView'
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
     * Login customer and open Order page.
     *
     * @param Customer $customer
     * @return void
     */
    public function loginCustomerAndOpenOrderPage(Customer $customer)
    {
        $this->loginCustomer($customer);
        $this->customerAccountIndex->open()->getAccountNavigationBlock()->openNavigationItem('My Orders');
    }

    /**
     * Open entity tab.
     *
     * @param string $orderId
     * @param string $entityType
     * @return void
     */
    public function openEntityTab($orderId, $entityType)
    {
        $this->orderHistory->getOrderHistoryBlock()->openOrderById($orderId);
        $this->customerOrderView->getOrderViewBlock()->openLinkByName(ucfirst($entityType) . 's');
    }
}
