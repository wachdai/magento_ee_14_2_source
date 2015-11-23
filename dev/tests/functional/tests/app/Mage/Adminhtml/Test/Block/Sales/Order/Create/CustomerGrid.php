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

namespace Mage\Adminhtml\Test\Block\Sales\Order\Create;

use Mage\Adminhtml\Test\Block\Widget\Grid;
use Mage\Customer\Test\Fixture\Customer;

/**
 * Customer selection grid.
 */
class CustomerGrid extends Grid
{
    /**
     * Selector for 'Create New Customer' button.
     *
     * @var string
     */
    protected $createNewCustomer = '.add';

    /**
     * Locator value for link in action column.
     *
     * @var string
     */
    protected $editLink = 'td';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'email' => [
            'selector' => '#sales_order_create_customer_grid_filter_email',
        ],
    ];

    /**
     * Select customer if it is specified or click create new customer button.
     *
     * @param Customer $customer
     * @return void
     */
    public function selectCustomer(Customer $customer)
    {
        if ($customer === null) {
            $this->_rootElement->find($this->createNewCustomer)->click();
        } else {
            $this->searchAndOpen(['email' => $customer->getEmail()]);
        }
    }
}
