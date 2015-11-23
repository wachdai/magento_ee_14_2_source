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

namespace Enterprise\GiftCardAccount\Test\Block\Adminhtml\Giftcardaccount;

use Magento\Mtf\Client\Element\SimpleElement as Element;
use Magento\Mtf\Client\Locator;

/**
 * Gift card account grid block.
 */
class Grid extends \Mage\Adminhtml\Test\Block\Widget\Grid
{
    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'code' => [
            'selector' => '#giftcardaccountGrid_giftcardaccount_filter_code',
        ],
        'balanceFrom' => [
            'selector' => '#giftcardaccountGrid_giftcardaccount_filter_balance_from',
        ],
        'balanceTo' => [
            'selector' => '#giftcardaccountGrid_giftcardaccount_filter_balance_to',
        ],
    ];

    /**
     * Gift card account code selector.
     *
     * @var string
     */
    protected $giftCardAccountCode = './/td[contains(@class,"a-right")]/following-sibling::td';

    /**
     *  Name for 'Sort' link.
     *
     * @var string
     */
    protected $sortLinkName = 'giftcardaccount_id';

    /**
     * Locator value for link in action column.
     *
     * @var string
     */
    protected $editLink = 'td.last';

    /**
     * Obtain specific row in grid.
     *
     * @param array $filter
     * @param bool $isSearchable [optional]
     * @param bool $isStrict [optional]
     * @throws \Exception
     * @return Element
     */
    protected function getRow(array $filter, $isSearchable = true, $isStrict = true)
    {
        $this->sortGridByField($this->sortLinkName);
        if ($isSearchable) {
            $this->search($filter);
        }
        $rows = [];
        foreach ($filter as $value) {
            $rows[] = 'td[contains(.,"' . $value . '")]';
        }
        $location = '//div[@class="grid"]//tbody/tr[1][' . implode(' and ', $rows) . ']';

        return $this->_rootElement->find($location, Locator::SELECTOR_XPATH);
    }

    /**
     * Search for item and select it.
     *
     * @param array $filter
     * @param bool $isSearchable [optional]
     * @throws \Exception
     * @return void
     */
    public function searchAndOpen(array $filter, $isSearchable = false)
    {
        $selectItem = $this->getRow($filter, $isSearchable);
        if ($selectItem->isVisible()) {
            $selectItem->find($this->editLink)->click();
        } else {
            throw new \Exception('Searched item was not found.');
        }
    }

    /**
     * Get gift card account code.
     *
     * @param array $filter
     * @param bool $isSearchable [optional]
     * @return string
     * @throws \Exception
     */
    public function getCode(array $filter, $isSearchable = false)
    {
        $selectItem = $this->getRow($filter, $isSearchable);
        if ($selectItem->isVisible()) {
            return $selectItem->find($this->giftCardAccountCode, Locator::SELECTOR_XPATH)->getText();
        } else {
            throw new \Exception('Searched item was not found.');
        }
    }
}
