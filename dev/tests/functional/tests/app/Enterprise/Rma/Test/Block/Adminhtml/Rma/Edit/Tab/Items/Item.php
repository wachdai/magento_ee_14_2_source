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

namespace Enterprise\Rma\Test\Block\Adminhtml\Rma\Edit\Tab\Items;

use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Item row form on edit rma backend page.
 */
class Item extends \Magento\Mtf\Block\Form
{
    /**
     * Return item row data.
     *
     * @return array
     */
    public function getRowData()
    {
        $mapping = $this->dataMapping();
        $data = [];

        foreach ($mapping as $columnName => $locator) {
            $elementType = isset($locator['input']) ? $locator['input'] : 'input';
            $element = $this->_rootElement->find(
                $locator['selector'] . '/' . $elementType,
                $locator['strategy'],
                $locator['input']
            );
            $value = null;

            if ($element->isVisible() && !$element->isDisabled()) {
                $value = $element->getValue();
            } else {
                $value = $this->_rootElement->find($locator['selector'], $locator['strategy'])->getText();
            }

            $data[$columnName] = trim($value);
        }

        return $data;
    }

    /**
     * Get row element.
     *
     * @param array $locator
     * @param string $elementType
     * @return SimpleElement
     */
    protected function getRowElement(array $locator, $elementType)
    {
        return $this->_rootElement->find(
            $locator['selector'] . ' ' . $elementType,
            $locator['strategy'],
            $locator['input']
        );
    }
}
