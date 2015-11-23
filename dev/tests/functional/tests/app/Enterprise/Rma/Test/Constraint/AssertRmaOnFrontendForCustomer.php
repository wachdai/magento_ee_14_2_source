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

use Mage\Cms\Test\Page\CmsIndex;
use Mage\Customer\Test\Fixture\Customer;
use Mage\Customer\Test\Page\CustomerAccountIndex;
use Enterprise\Rma\Test\Fixture\Rma;
use Enterprise\Rma\Test\Page\RmaReturnHistory;
use Enterprise\Rma\Test\Page\RmaReturnView;
use Mage\Sales\Test\Fixture\Order;

/**
 * Assert that rma is correct display on frontend (MyAccount - My Returns).
 */
class AssertRmaOnFrontendForCustomer extends AbstractAssertRmaOnFrontend
{
    /**
     * Rma return history page.
     *
     * @var RmaReturnHistory
     */
    protected $rmaReturnHistory;

    /**
     * Rma return view page.
     *
     * @var RmaReturnView
     */
    protected $rmaReturnView;

    /**
     * Order fixture.
     *
     * @var Order
     */
    protected $order;

    /**
     * Customer fixture.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Assert that rma is correct display on frontend (MyAccount - My Returns):
     * - status on rma history page
     * - details and items on rma view page
     *
     * @param Rma $rma
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountIndex $customerAccountIndex
     * @param RmaReturnHistory $rmaReturnHistory
     * @param RmaReturnView $rmaReturnView
     * @return void
     */
    public function processAssert(
        Rma $rma,
        CmsIndex $cmsIndex,
        CustomerAccountIndex $customerAccountIndex,
        RmaReturnHistory $rmaReturnHistory,
        RmaReturnView $rmaReturnView
    ) {
        $this->rmaReturnHistory = $rmaReturnHistory;
        $this->rmaReturnView = $rmaReturnView;
        $this->order = $rma->getDataFieldConfig('order_id')['source']->getOrder();
        $this->customer = $this->order->getDataFieldConfig('customer_id')['source']->getCustomer();
        $this->productHandlerClass = $this->getProductHandlerClass();
        $this->login();
        $cmsIndex->getTopLinksBlock()->openAccount();
        $customerAccountIndex->getAccountNavigationBlock()->openNavigationItem('My Returns');
        $this->assertRmaStatus($rma);
        $rmaReturnHistory->getRmaHistory()->getItemRow($rma)->open();
        $this->assertRequestInformation($rma);
        $this->assertItemsData($rma);
    }

    /**
     * Assert RMA items data on RMA view page.
     *
     * @param Rma $rma
     * @return void
     */
    protected function assertItemsData(Rma $rma)
    {
        $pageItemsData = $this->sortDataByPath(
            $this->rmaReturnView->getRmaView()->getItemsBlock()->getData(),
            '::sku'
        );
        $fixtureItemsData = $this->sortDataByPath(
            $this->getRmaItems($rma),
            '::sku'
        );
        $this->assert($fixtureItemsData, $pageItemsData);
    }

    /**
     * Assert request information on RMA view page.
     *
     * @param Rma $rma
     * @return void
     */
    protected function assertRequestInformation(Rma $rma)
    {
        $fixtureData = $this->prepareRequestInformationData($rma);
        $pageData = $this->rmaReturnView->getRmaView()->getRequestInformationBlock()->getData();
        $this->assert($fixtureData, $pageData);
    }

    /**
     * Prepare request information fixture data.
     *
     * @param Rma $rma
     * @return array
     */
    protected function prepareRequestInformationData(Rma $rma)
    {
        return [
            'id' => $rma->getEntityId(),
            'order_id' => $this->order->getId(),
            'date' => date('n/j/Y'),
            'customer_email' => $this->customer->getEmail(),
            'contact_email' => $rma->getContactEmail()
        ];
    }

    /**
     * Assert RMA status on history page.
     *
     * @param Rma $rma
     * @return void
     */
    protected function assertRmaStatus(Rma $rma)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $rma->getStatus(),
            $this->rmaReturnHistory->getRmaHistory()->getItemRow($rma)->getData()['status']
        );
    }

    /**
     * Login customer.
     *
     * @return void
     */
    protected function login()
    {
        $this->objectManager->create(
            'Mage\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $this->customer]
        )->run();
    }

    /**
     * Assert data is equals.
     *
     * @param array $fixtureData
     * @param array $pageData
     * @return void
     */
    protected function assert(array $fixtureData, array $pageData)
    {
        $errors = $this->verifyData($fixtureData, $pageData);
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'RMA is correct display on frontend (MyAccount - My Returns).';
    }
}
