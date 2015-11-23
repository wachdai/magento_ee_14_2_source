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
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Enterprise\Rma\Test\Page\Adminhtml\RmaIndex;
use Enterprise\Rma\Test\Page\Adminhtml\RmaEdit;

/**
 * Assert that displayed rma data on edit page equals passed from fixture.
 */
class AssertRmaForm extends AbstractAssertForm
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Array skipped fields.
     *
     * @var array
     */
    protected $skippedFields = [
        'status',
        'comment',
        'items',
    ];

    /**
     * Assert that displayed rma data on edit page equals passed from fixture.
     *
     * @param Rma $rma
     * @param RmaIndex $rmaIndex
     * @param RmaEdit $rmaEdit
     * @return void
     */
    public function processAssert(Rma $rma, RmaIndex $rmaIndex, RmaEdit $rmaEdit)
    {
        $rmaIndex->open();
        $rmaIndex->getRmaGrid()->searchAndOpen(['rma_id' => $rma->getEntityId()]);
        $fixtureData = $this->getRmaData($rma);
        $pageData = $rmaEdit->getRmaForm()->getData($rma);
        $this->verifyDetails($fixtureData, $pageData);
        $this->verifyComment($fixtureData, $pageData);
        $this->verifyItems($fixtureData, $pageData);
    }

    /**
     * Assert that displayed rma details on edit page equals passed from fixture.
     *
     * @param array $fixtureData
     * @param array $pageData
     * @return void
     */
    protected function verifyDetails(array $fixtureData, array $pageData)
    {
        $fixtureDetails = array_diff_key($fixtureData, array_flip($this->skippedFields));
        $pageDetails = array_diff_key($pageData, array_flip($this->skippedFields));

        \PHPUnit_Framework_Assert::assertEquals(
            $fixtureDetails,
            $pageDetails,
            'Displayed rma details on edit page does not equals passed from fixture'
        );
    }

    /**
     * Assert that displayed rma comment on edit page equals passed from fixture.
     *
     * @param array $fixtureData
     * @param array $pageData
     * @return void
     */
    protected function verifyComment(array $fixtureData, array $pageData)
    {
        $fixtureComment = $fixtureData['comment'];
        $pageComments = $pageData['comment'];
        $isVisibleComment = false;

        foreach ($pageComments as $pageComment) {
            if ($pageComment['comment'] == $fixtureComment['comment']) {
                $isVisibleComment = true;
            }
        }

        \PHPUnit_Framework_Assert::assertTrue(
            $isVisibleComment,
            'Displayed rma comment on edit page does not equals passed from fixture.'
        );
    }

    /**
     * Assert that displayed rma items on edit page equals passed from fixture.
     *
     * @param array $fixtureData
     * @param array $pageData
     * @return void
     */
    protected function verifyItems(array $fixtureData, array $pageData)
    {
        $fixtureItems = $this->sortDataByPath($fixtureData['items'], '::sku');
        $pageItems = $this->sortDataByPath($pageData['items'], '::sku');

        foreach ($pageItems as $key => $pageItem) {
            $pageItem['product'] = preg_replace('/ \(.+\)$/', '', $pageItem['product']);
            $pageItems[$key] = array_intersect_key($pageItem, $fixtureItems[$key]);
        }

        \PHPUnit_Framework_Assert::assertEquals(
            $fixtureItems,
            $pageItems,
            'Displayed rma items on edit page does not equals passed from fixture.'
        );
    }

    /**
     * Return rma data.
     *
     * @param Rma $rma
     * @return array
     */
    protected function getRmaData(Rma $rma)
    {
        /** @var Order $order */
        $order = $rma->getDataFieldConfig('order_id')['source']->getOrder();
        $orderItems = $order->getEntityId();
        /** @var Customer $customer */
        $customer = $order->getDataFieldConfig('customer_id')['source']->getCustomer();

        $data = $rma->getData();
        $data['customer_name'] = sprintf('%s %s', $customer->getFirstname(), $customer->getLastname());
        $data['customer_email'] = $customer->getEmail();

        foreach ($data['items'] as $key => $item) {
            $product = $orderItems['products'][$key];

            $item['product'] = $product->getName();
            $item['sku'] = $this->getItemSku($product);

            $data['items'][$key] = $item;
        }

        return $data;
    }

    /**
     * Return sku of product.
     *
     * @param InjectableFixture $product
     * @return string
     */
    protected function getItemSku(InjectableFixture $product)
    {
        return $product->getSku();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Correct return request is present on backend.';
    }
}
