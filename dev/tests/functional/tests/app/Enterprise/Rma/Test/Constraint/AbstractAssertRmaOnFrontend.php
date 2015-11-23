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
use Mage\Sales\Test\Fixture\Order;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Helper\Constraint\ProductHandler;

/**
 * Assert that rma is correctly displaying on frontend.
 */
abstract class AbstractAssertRmaOnFrontend extends AbstractAssertForm
{
    /**
     * Default status of rma item.
     */
    const ITEM_DEFAULT_STATUS = 'Pending';

    /**
     * Product handler class.
     *
     * @var ProductHandler
     */
    protected $productHandlerClass;

    /**
     * Product handler class path.
     *
     * @var string
     */
    protected $productHandlerPath = 'Magento\Mtf\Helper\Constraint\ProductHandler';

    /**
     * Get rma items.
     *
     * @param Rma $rma
     * @return array
     */
    protected function getRmaItems(Rma $rma)
    {
        $rmaItems = $rma->getItems();
        /** @var Order $order */
        $order = $rma->getDataFieldConfig('order_id')['source']->getOrder();
        $orderItems = $this->getAssignedProducts($order);

        foreach ($rmaItems as $productKey => $productData) {
            $key = str_replace('product_key_', '', $productKey);
            $product = $orderItems[$key];

            $productData['sku'] = $this->productHandlerClass->getProductSku($product);
            $productData['qty'] = $productData['qty_requested'];
            $productData['product_name'] = $product->getName();
            $productOptions = $this->productHandlerClass->getProductOptions($product);
            if ($productOptions != null) {
                $productData['options'] = $productOptions;
            }
            if (!isset($productData['status'])) {
                $productData['status'] = self::ITEM_DEFAULT_STATUS;
            }
            unset($productData['reason']);
            unset($productData['reason_other']);

            $rmaItems[$productKey] = $productData;
        }

        return $rmaItems;
    }

    /**
     * Get assigned products.
     *
     * @param Order $order
     * @return array
     */
    protected function getAssignedProducts(Order $order)
    {
        return $order->getEntityId()['products'];
    }

    /**
     * Get product handler class.
     *
     * @return ProductHandler
     */
    protected function getProductHandlerClass()
    {
        return $this->objectManager->create($this->productHandlerPath);
    }
}
