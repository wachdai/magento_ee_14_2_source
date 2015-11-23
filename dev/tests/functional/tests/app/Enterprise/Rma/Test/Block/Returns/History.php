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

namespace Enterprise\Rma\Test\Block\Returns;

use Magento\Mtf\Block\Block;
use Enterprise\Rma\Test\Block\Returns\History\Item;
use Magento\Mtf\Client\Locator;
use Enterprise\Rma\Test\Fixture\Rma;

/**
 * Rma history block.
 */
class History extends Block
{
    /**
     * Selector for RMA item row.
     *
     * @var string
     */
    protected $rmaItemRow = '//tr[td[@class="number" and text()="%d"]]';

    /**
     * Get RMA item row.
     *
     * @param Rma $rma
     * @return Item
     */
    public function getItemRow(Rma $rma)
    {
        $selector = sprintf($this->rmaItemRow, $rma->getEntityId());
        return $this->blockFactory->create(
            'Enterprise\Rma\Test\Block\Returns\History\Item',
            ['element' => $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)]
        );
    }
}
