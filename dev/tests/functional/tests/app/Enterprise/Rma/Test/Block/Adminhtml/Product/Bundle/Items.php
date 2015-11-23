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

namespace Enterprise\Rma\Test\Block\Adminhtml\Product\Bundle;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Popup block for choose items of returned bundle product.
 */
class Items extends Block
{
    /**
     * Locator for label.
     *
     * @var string
     */
    protected $label = './/label[contains(.,"%s")]';

    /**
     * Locator for "Ok" button.
     *
     * @var string
     */
    protected $buttonOk = './/button[contains(@id,"ok_button")]';

    /**
     * Fill popup block.
     *
     * @param array $labels
     * @return void
     */
    public function fill(array $labels)
    {
        foreach ($labels as $label) {
            $this->_rootElement->find(sprintf($this->label, $label), Locator::SELECTOR_XPATH)->click();
        }
        $this->clickOk();
    }

    /**
     * Click "Ok" button.
     *
     * @return void
     */
    public function clickOk()
    {
        $this->_rootElement->find($this->buttonOk, Locator::SELECTOR_XPATH)->click();
    }
}
