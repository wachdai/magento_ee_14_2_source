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

namespace Enterprise\Rma\Test\Block\Returns\History;

use Magento\Mtf\Block\Block;

/**
 * Item rma block on RMA history page.
 */
class Item extends Block
{
    /**
     * Fields on grid.
     *
     * @var array
     */
    protected $fields = [
        'id' => '.number',
        'date' => '.date',
        'ship_from' => '.ship-from',
        'status' => '.status'
    ];

    /**
     * Css selector for link.
     *
     * @var string
     */
    protected $openLink = 'a';

    /**
     * Open RMA view page.
     *
     * @return void
     */
    public function open()
    {
        $this->_rootElement->find($this->openLink)->click();
    }

    /**
     * Get data item RMA row.
     *
     * @return array
     */
    public function getData()
    {
        $result = [];
        foreach ($this->fields as $key => $field) {
            $result[$key] = $this->_rootElement->find($field)->getText();
        }

        return $result;
    }
}
