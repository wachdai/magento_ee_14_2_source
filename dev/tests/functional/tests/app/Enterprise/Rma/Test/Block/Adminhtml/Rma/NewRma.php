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

namespace Enterprise\Rma\Test\Block\Adminhtml\Rma;

use Enterprise\Rma\Test\Fixture\Rma;
use Magento\Mtf\Fixture\InjectableFixture;
use Enterprise\Rma\Test\Fixture\Rma\OrderId;
use Magento\Mtf\Client\Element\SimpleElement;
use Mage\Adminhtml\Test\Block\Widget\FormTabs;

/**
 * Rma new page tabs.
 */
class NewRma extends FormTabs
{
    /**
     * Fill form with tabs.
     *
     * @param InjectableFixture $fixture
     * @param SimpleElement|null $element
     * @return FormTabs
     */
    public function fill(InjectableFixture $fixture, SimpleElement $element = null)
    {
        $tabs = $this->getFieldsByTabs($fixture);
        if (isset($tabs['items']['items']['value'])) {
            $orderItems = $this->getOrderItems($fixture);
            $tabs['items']['items']['value'] = $this->prepareItems($orderItems, $tabs['items']['items']['value']);
        }

        return $this->fillTabs($tabs, $element);
    }

    /**
     * Get order items from rma fixture.
     *
     * @param InjectableFixture $fixture
     * @return array
     */
    protected function getOrderItems(InjectableFixture $fixture)
    {
        /** @var OrderId $sourceOrderId */
        $sourceOrderId = $fixture->getDataFieldConfig('order_id')['source'];
        return $sourceOrderId->getOrder()->getEntityId()['products'];
    }

    /**
     * Prepare items data.
     *
     * @param array $orderItems
     * @param array $items
     * @return array
     */
    protected function prepareItems(array $orderItems, array $items)
    {
        foreach ($items as $productKey => $productData) {
            $key = str_replace('product_key_', '', $productKey);
            $items[$productKey]['product'] = $orderItems[$key];
        }
        return $items;
    }
}
