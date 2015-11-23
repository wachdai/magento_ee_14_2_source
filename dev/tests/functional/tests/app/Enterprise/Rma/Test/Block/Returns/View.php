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
use Enterprise\Rma\Test\Block\Returns\View\RequestInformation;
use Enterprise\Rma\Test\Block\Returns\View\Items;

/**
 * Rma view block.
 */
class View extends Block
{
    /**
     * Selector for RMA info block.
     *
     * @var string
     */
    protected $rmaInfo = '.col-1.rma-view';

    /**
     * Selector for RMA items block.
     *
     * @var string
     */
    protected $rmaItems = '#my-returns-items-table';

    /**
     * Get request information block.
     *
     * @return RequestInformation
     */
    public function getRequestInformationBlock()
    {
        return $this->blockFactory->create(
            'Enterprise\Rma\Test\Block\Returns\View\RequestInformation',
            ['element' => $this->_rootElement->find($this->rmaInfo)]
        );
    }

    /**
     * Get items block.
     *
     * @return Items
     */
    public function getItemsBlock()
    {
        return $this->blockFactory->create(
            'Enterprise\Rma\Test\Block\Returns\View\Items',
            ['element' => $this->_rootElement->find($this->rmaItems)]
        );
    }
}
