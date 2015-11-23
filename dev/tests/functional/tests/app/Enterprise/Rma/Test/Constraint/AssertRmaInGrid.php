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

namespace Enterprise\Rma\Test\Constraint;

use Mage\Sales\Test\Fixture\Order;
use Enterprise\Rma\Test\Fixture\Rma;
use Mage\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;
use Enterprise\Rma\Test\Page\Adminhtml\RmaIndex;

/**
 * Assert that return request displayed in Returns grid.
 */
class AssertRmaInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that return request displayed in Returns grid:
     * - customer
     * - status (pending)
     * - orderID
     *
     * @param Rma $rma
     * @param RmaIndex $rmaIndex
     * @return void
     */
    public function processAssert(Rma $rma, RmaIndex $rmaIndex)
    {
        /** @var Order $order*/
        $order = $rma->getDataFieldConfig('order_id')['source']->getOrder();
        /** @var Customer $customer */
        $customer = $order->getDataFieldConfig('customer_id')['source']->getCustomer();
        $orderId = $rma->getOrderId();
        $filter = [
            'order_id_from' => $orderId,
            'order_id_to' => $orderId,
            'customer' => sprintf('%s %s', $customer->getFirstname(), $customer->getLastname()),
            'status' => $rma->getStatus(),
        ];

        $rmaIndex->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $rmaIndex->getRmaGrid()->isRowVisible($filter),
            "Rma for order '{$orderId}' is absent in grid."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Rma is present in grid';
    }
}
