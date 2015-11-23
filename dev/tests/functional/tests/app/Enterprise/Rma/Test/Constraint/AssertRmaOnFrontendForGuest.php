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

use Enterprise\Rma\Test\Fixture\Rma;
use Enterprise\Rma\Test\Page\RmaGuestReturn;
use Enterprise\Rma\Test\Page\RmaGuestView;
use Mage\Sales\Test\Fixture\Order;
use Mage\Sales\Test\Page\SalesGuestView;
use Mage\Customer\Test\Fixture\Customer;

/**
 * Assert that rma is correct display for guest on frontend (Orders and Returns).
 */
class AssertRmaOnFrontendForGuest extends AssertRmaOnFrontendForCustomer
{
    /**
     * Rma Guest Return page.
     *
     * @var RmaGuestReturn
     */
    protected $rmaGuestReturn;

    /**
     * Rma guest return view page.
     *
     * @var RmaGuestView
     */
    protected $rmaGuestView;

    /**
     * Assert that rma is correct display for guest on frontend (Orders and Returns):
     * - status on rma history page
     * - details and items on rma view page
     *
     * @param Rma $rma
     * @param SalesGuestView $salesGuestView
     * @param RmaGuestReturn $rmaGuestReturn
     * @param RmaGuestView $rmaGuestView
     * @return void
     */
    public function processAssert(
        Rma $rma,
        SalesGuestView $salesGuestView,
        RmaGuestReturn $rmaGuestReturn,
        RmaGuestView $rmaGuestView
    ) {
        $this->rmaGuestReturn = $rmaGuestReturn;
        $this->rmaGuestView = $rmaGuestView;
        $this->order = $rma->getDataFieldConfig('order_id')['source']->getOrder();
        $this->customer = $this->order->getDataFieldConfig('customer_id')['source']->getCustomer();

        $this->objectManager->create(
            'Mage\Sales\Test\TestStep\OpenSalesOrderOnFrontendForGuestStep',
            ['order' => $this->order]
        )->run();
        $salesGuestView->getViewBlock()->openLinkByName('Returns');
        $this->assertRmaStatus($rma);

        $this->rmaGuestReturn->getReturnsBlock()->getItemRow($rma)->open();
        $this->assertRequestInformation($rma);
        $this->assertItemsData($rma);
    }

    /**
     * Assert request information on RMA guest view page.
     *
     * @param Rma $rma
     * @return void
     */
    protected function assertRequestInformation(Rma $rma)
    {
        $fixtureData = $this->prepareRequestInformationData($rma);
        $pageData = $this->rmaGuestView->getRmaView()->getRequestInformationBlock()->getData();
        $this->assert($fixtureData, $pageData);
    }

    /**
     * Assert RMA items data on RMA guest view page.
     *
     * @param Rma $rma
     * @return void
     */
    protected function assertItemsData(Rma $rma)
    {
        $pageItemsData = $this->sortDataByPath(
            $this->rmaGuestView->getRmaView()->getItemsBlock()->getData(),
            '::sku'
        );
        $fixtureItemsData = $this->sortDataByPath(
            $this->getRmaItems($rma),
            '::sku'
        );
        $this->assert($fixtureItemsData, $pageItemsData);
    }

    /**
     * Assert RMA status on RMA guest return page.
     *
     * @param Rma $rma
     * @return void
     */
    protected function assertRmaStatus(Rma $rma)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $rma->getStatus(),
            $this->rmaGuestReturn->getReturnsBlock()->getItemRow($rma)
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Correct guest's return request is present on frontend (Orders and Returns).";
    }
}
