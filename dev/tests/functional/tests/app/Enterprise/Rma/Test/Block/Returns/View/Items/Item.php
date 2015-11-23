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

namespace Enterprise\Rma\Test\Block\Returns\View\Items;

use Mage\Checkout\Test\Block\AbstractItem;

/**
 * Item row block on RMA items block.
 */
class Item extends AbstractItem
{
    /**
     * Fields on grid.
     *
     * @var array
     */
    protected $fields = [
        'product_name' => 'td:nth-child(1)',
        'sku' => '[data-rwd-label="SKU"]',
        'condition' => '[data-rwd-label="Condition"]',
        'resolution' => '[data-rwd-label="Resolution"]',
        'qty_requested' => '[data-rwd-label="Request Qty"]',
        'qty' => '[data-rwd-label="Qty"]',
        'status' => '[data-rwd-label="Status"]',
    ];

    /**
     * Get data item RMA row.
     *
     * @return array
     */
    public function getRowData()
    {
        $result = [];
        foreach ($this->fields as $key => $field) {
            $value = trim($this->_rootElement->find($field)->getText());
            preg_match('`(.*)`', $value, $matches);
            $result[$key] = isset($matches[1]) ? $matches[1] : null;
        }
        if ($this->_rootElement->find($this->optionsBlock)->isVisible()) {
            $result['options'] = $this->getOptions();
        }

        return $result;
    }
}
