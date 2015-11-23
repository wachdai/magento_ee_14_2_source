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

namespace Enterprise\Rma\Test\Block\Adminhtml\Rma\Edit\Tab;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;
use Enterprise\Rma\Test\Block\Adminhtml\Rma\Edit\Tab\Items\Item;

/**
 * Items block on edit rma backend page.
 */
class Items extends \Mage\Adminhtml\Test\Block\Widget\Tab
{
    /**
     * Locator for item row in grid.
     *
     * @var string
     */
    protected $rowItem = './/*[@id="enterprise_rma_item_edit_grid_table"]/tbody/tr';

    /**
     * Locator for search item row by name.
     *
     * @var string
     */
    protected $rowItemByName = "//tr[contains(normalize-space(td/text()),'%s')]";

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
        $context = $element ? $element : $this->_rootElement;

        foreach ($items as $item) {
            $itemElement = $context->find(sprintf($this->rowItemByName, $item['product']));
            $this->getItemRow($itemElement)->fillRow($item);
        }

        $this->setFields['items'] = $fields['items']['value'];
        return $this;
    }

    /**
     * Get data of tab.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     */
    public function getDataFormTab($fields = null, SimpleElement $element = null)
    {
        $data = [];
        if (null === $fields || isset($fields['items'])) {
            $rows = $this->_rootElement->getElements($this->rowItem, Locator::SELECTOR_XPATH);
            $data = [];

            foreach ($rows as $row) {
                $data[] = $this->getItemRow($row)->getRowData();
            };
        }
        return ['items' => $data];
    }

    /**
     * Return item row form.
     *
     * @param SimpleElement $element
     * @return Item
     */
    protected function getItemRow(SimpleElement $element)
    {
        return $this->blockFactory->create(
            'Enterprise\Rma\Test\Block\Adminhtml\Rma\Edit\Tab\Items\Item',
            ['element' => $element]
        );
    }
}
