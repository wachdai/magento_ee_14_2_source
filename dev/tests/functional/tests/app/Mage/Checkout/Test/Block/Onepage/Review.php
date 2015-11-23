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

namespace Mage\Checkout\Test\Block\Onepage;

use Mage\Checkout\Test\Block\Onepage\Review\Items;
use Mage\Checkout\Test\Block\Onepage\Review\Totals;
use Mage\Payment\Test\Fixture\Cc;
use Mage\Paypal\Test\Block\Form\Centinel;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * One page checkout status review block.
 */
class Review extends AbstractOnepage
{
    /**
     * Items block css selector.
     *
     * @var string
     */
    protected $items = 'tbody';

    /**
     * Place order checkout button.
     *
     * @var string
     */
    protected $continue = '#review-buttons-container button';

    /**
     * Css selector for total block.
     *
     * @var string
     */
    protected $total = 'tfoot';

    /**
     * Items block class.
     *
     * @var string
     */
    protected $itemsBlock = 'Mage\Checkout\Test\Block\Onepage\Review\Items';

    /**
     * Centinel form selector.
     *
     * @var string
     */
    protected $centinelForm = '#centinel_authenticate_block .authentication';

    /**
     * Get items product block.
     *
     * @param string|null $productType
     * @return Items
     */
    public function getItemsBlock($productType = null)
    {
        return $this->hasRender($productType)
            ? $this->callRender($productType, 'getItemsBlock')
            : $this->blockFactory->create($this->itemsBlock, ['element' => $this->_rootElement->find($this->items)]);
    }

    /**
     * Get items product block.
     *
     * @return Totals
     */
    public function getTotalBlock()
    {
        return $this->blockFactory->create(
            'Mage\Checkout\Test\Block\Onepage\Review\Totals',
            ['element' => $this->_rootElement->find($this->total)]
        );
    }

    /**
     * Get 3D secure Form.
     *
     * @return Centinel
     */
    public function getCentinelForm()
    {
        return $this->blockFactory->create(
            'Mage\Paypal\Test\Block\Form\Centinel',
            ['element' => $this->_rootElement->find($this->centinelForm)]
        );
    }

    /**
     * Get alert text.
     *
     * @return string
     */
    protected function getAlertText()
    {
        $browser = $this->browser;
        $this->browser->waitUntil(
            function () use ($browser) {
                return $browser->getAlertText() ? true : false;
            });
        $alertText = $this->browser->getAlertText();
        $this->browser->acceptAlert();
        return $alertText;
    }

    /**
     * Click "Place order" button and get alert text.
     *
     * @return string
     */
    public function handleSubmittingOrderInformation()
    {
        $this->_rootElement->find($this->continue)->click();
        return $this->getAlertText();
    }
}
