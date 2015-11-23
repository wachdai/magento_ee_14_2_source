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

namespace Enterprise\Rma\Test\TestCase;

use Magento\Mtf\ObjectManager;
use Mage\Sales\Test\Fixture\Order;
use Magento\Mtf\TestCase\Injectable;
use Enterprise\Rma\Test\Fixture\Rma;
use Magento\Mtf\Fixture\FixtureFactory;
use Enterprise\Rma\Test\Page\Adminhtml\RmaNew;
use Enterprise\Rma\Test\Page\Adminhtml\RmaIndex;
use Enterprise\Rma\Test\Page\Adminhtml\RmaChooseOrder;
use Enterprise\Rma\Test\Constraint\AssertRmaSuccessSaveMessage;

/**
 * Preconditions:
 * 1. Enable RMA on Frontend (Configuration - Sales - RMA Settings).
 * 2. Create customer.
 * 3. Create product.
 * 4. Create Order.
 * 5. Create invoice and shipping.
 *
 * Steps:
 * 1. Login to the backend.
 * 2. Navigate to Sales -> RMA -> Manage RMA.
 * 3. Create new RMA.
 * 4. Fill data according to dataSet.
 * 5. Submit returns.
 * 6. Perform all assertions.
 *
 * @group RMA_(CS)
 * @ZephyrId MPERF-7343
 */
class CreateRmaEntityOnBackendTest extends Injectable
{
    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Rma index page on backend.
     *
     * @var RmaIndex
     */
    protected $rmaIndex;

    /**
     * Rma choose order page on backend.
     *
     * @var RmaChooseOrder
     */
    protected $rmaChooseOrder;

    /**
     * New Rma page on backend.
     *
     * @var RmaNew
     */
    protected $rmaNew;

    /**
     * Prepare data and setup configuration.
     *
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->objectManager->create(
            'Mage\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'rma_enable_on_frontend']
        )->run();
    }

    /**
     * Inject data.
     *
     * @param RmaIndex $rmaIndex
     * @param RmaChooseOrder $rmaChooseOrder
     * @param RmaNew $rmaNew
     * @return void
     */
    public function __inject(
        RmaIndex $rmaIndex,
        RmaChooseOrder $rmaChooseOrder,
        RmaNew $rmaNew
    ) {
        $this->rmaIndex = $rmaIndex;
        $this->rmaChooseOrder = $rmaChooseOrder;
        $this->rmaNew = $rmaNew;
    }

    /**
     * Run test create Rma Entity.
     *
     * @param Rma $rma
     * @param RmaIndex $rmaIndex
     * @param AssertRmaSuccessSaveMessage $assertRmaSuccessSaveMessage
     * @return array
     */
    public function test(Rma $rma, RmaIndex $rmaIndex, AssertRmaSuccessSaveMessage $assertRmaSuccessSaveMessage)
    {
        // Preconditions
        /** @var Order $order */
        $order = $rma->getDataFieldConfig('order_id')['source']->getOrder();
        $this->objectManager->create('Mage\Sales\Test\TestStep\CreateInvoiceStep', ['order' => $order])->run();
        $this->objectManager->create('Mage\Shipping\Test\TestStep\CreateShipmentStep', ['order' => $order])->run();

        // Steps
        $this->rmaIndex->open();
        $this->rmaIndex->getGridPageActions()->addNew();
        $this->rmaChooseOrder->getOrderGrid()->searchAndOpen(['id' => $rma->getOrderId()]);
        $this->rmaNew->getRmaForm()->fill($rma);
        $this->rmaNew->getPageActions()->save();

        $assertRmaSuccessSaveMessage->processAssert($rmaIndex);

        $rma = $this->createRma($rma, $this->getRmaId($rma));
        return ['rma' => $rma];
    }

    /**
     * Get rma id.
     *
     * @param Rma $rma
     * @return string
     */
    protected function getRmaId(Rma $rma)
    {
        $orderId = $rma->getOrderId();
        $filter = [
            'order_id_from' => $orderId,
            'order_id_to' => $orderId,
        ];
        $this->rmaIndex->getRmaGrid()->search($filter);
        $rowsData = $this->rmaIndex->getRmaGrid()->getRowsData(['rma_id' => '.="RMA #"']);
        return $rowsData[0]['rma_id'];
    }

    /**
     * Create rma entity.
     *
     * @param Rma $rma
     * @param string $rmaId
     * @return Rma
     */
    protected function createRma(Rma $rma, $rmaId)
    {
        $order = $rma->getDataFieldConfig('order_id')['source']->getOrder();
        $rmaData = $rma->getData();
        $data = array_merge(
            $rma->getData(),
            ['entity_id' => $rmaId, 'order_id' => ['order' => $order], 'items' => ['data' => $rmaData['items']]]
        );
        return $this->fixtureFactory->createByCode('rma', ['data' => $data]);
    }
}
