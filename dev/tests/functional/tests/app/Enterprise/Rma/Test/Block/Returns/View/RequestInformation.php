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

namespace Enterprise\Rma\Test\Block\Returns\View;

use Magento\Mtf\Block\Block;

/**
 * Request information for RMA block.
 */
class RequestInformation extends Block
{
    /**
     * Css selector for box content.
     *
     * @var string
     */
    protected $boxContent = '.box-content';

    /**
     * Fields in block.
     *
     * @var array
     */
    protected $fields = [
        'id' => '`ID: (\d+)`',
        'order_id' => '`Order ID: (\d+)`',
        'date' => '`Date Requested: (.*)`',
        'customer_email' => '`Email Address: (.*)`',
        'contact_email' => '`Contact Email Address: (.*)`'
    ];

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        $result = [];
        $content = $this->_rootElement->find($this->boxContent)->getText();
        foreach ($this->fields as $key => $field) {
            preg_match($field, $content, $matches);
            $result[$key] = isset($matches[1]) ? trim($matches[1]) : null;
        }

        return $result;
    }
}
