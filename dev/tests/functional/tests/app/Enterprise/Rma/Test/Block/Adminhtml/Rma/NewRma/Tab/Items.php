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

namespace Enterprise\Rma\Test\Block\Adminhtml\Rma\NewRma\Tab;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;
use Mage\Adminhtml\Test\Block\Template;
use Mage\Catalog\Test\Fixture\CatalogProductSimple;
use Enterprise\Rma\Test\Block\Adminhtml\Rma\NewRma\Tab\Items\Grid as ItemsGrid;
use Enterprise\Rma\Test\Block\Adminhtml\Rma\NewRma\Tab\Items\Order\Grid as OrderItemsGrid;

/**
 * Items product tab.
 */
class Items extends \Enterprise\Rma\Test\Block\Adminhtml\Rma\Edit\Tab\Items
{
    /**
     * Selector for "Add Products" button.
     *
     * @var string
     */
    protected $addProducts = '#rma-items-block .add';

    /**
     * Selector for "Add Selected Product(s) to returns" button.
     *
     * @var string
     */
    protected $addSelectedProducts = '#select-order-items-block .add';

    /**
     * Locator item row by name.
     *
     * @var string
     */
    protected $rowByName = './/tbody/tr[./td[contains(.,"%s")]]';

    /**
     * Locator for order items grid.
     *
     * @var string
     */
    protected $orderItemsGrid = '#select-order-items-block';

    /**
     * Locator for rma items grid.
     *
     * @var string
     */
    protected $rmaItemsGrid = '#rma_items_grid';

    /**
     * Locator for template block.
     *
     * @var string
     */
    protected $templateBlock = './/ancestor::body';

    /**
     * Fill data to fields on tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        $items = isset($fields['items']['value']) ? $fields['items']['value'] : [];
        if (!empty($items)) {
            $this->clickAddProducts();
            foreach ($items as $item) {
                $this->getOrderItemsGrid()->selectItem($item['product']);
            }
            $this->clickAddSelectedProducts();
            foreach ($items as $item) {
                $this->fillItem($item);
            }
            $this->setFields['items'] = $items;
        }

        return $this;
    }

    /**
     * Click "Add Products" button.
     *
     * @return void
     */
    protected function clickAddProducts()
    {
        $this->_rootElement->find($this->addProducts)->click();
        $this->waitForElementVisible($this->orderItemsGrid);
    }

    /**
     * Click "Add Selected Product(s) to returns" button.
     *
     * @return void.
     */
    protected function clickAddSelectedProducts()
    {
        $this->_rootElement->find($this->addSelectedProducts)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Return chooser order items grid.
     *
     * @return OrderItemsGrid
     */
    protected function getOrderItemsGrid()
    {
        return $this->blockFactory->create(
            'Enterprise\Rma\Test\Block\Adminhtml\Rma\NewRma\Tab\Items\Order\Grid',
            ['element' => $this->_rootElement->find($this->orderItemsGrid)]
        );
    }

    /**
     * Return items rma grid.
     *
     * @return ItemsGrid
     */
    protected function getItemsGrid()
    {
        return $this->blockFactory->create(
            'Enterprise\Rma\Test\Block\Adminhtml\Rma\NewRma\Tab\Items\Grid',
            ['element' => $this->_rootElement->find($this->rmaItemsGrid)]
        );
    }

    /**
     * Fill item product in rma items grid.
     *
     * @param array $itemData
     * @return void
     */
    protected function fillItem(array $itemData)
    {
        /** @var CatalogProductSimple $product */
        $product = $itemData['product'];
        $productConfig = $product->getDataConfig();
        $productType = isset($productConfig['type_id']) ? ucfirst($productConfig['type_id']) : '';
        $productItemsClass = 'Enterprise\Rma\Test\Block\Adminhtml\Rma\NewRma\Tab\\' . $productType . 'Items';

        if (class_exists($productItemsClass)) {
            $productGrid = $this->blockFactory->create($productItemsClass, ['element' => $this->_rootElement]);
            $productGrid->fillItem($itemData);
        } else {
            unset($itemData['product']);
            $fields = $this->dataMapping($itemData);
            $itemRow = $this->getItemsGrid()->getItemRow($product->getName());
            $this->_fill($fields, $itemRow);
        }
    }

    /**
     * Return template block.
     *
     * @return Template
     */
    protected function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Mage\Adminhtml\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
