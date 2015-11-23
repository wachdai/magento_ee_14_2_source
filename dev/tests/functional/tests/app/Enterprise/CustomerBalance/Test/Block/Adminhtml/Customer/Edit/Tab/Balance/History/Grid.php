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

namespace Enterprise\CustomerBalance\Test\Block\Adminhtml\Customer\Edit\Tab\Balance\History;

use Enterprise\CustomerBalance\Test\Fixture\CustomerBalance;
use Magento\Mtf\Client\Locator;

/**
 * Balance history grid.
 */
class Grid extends \Mage\Adminhtml\Test\Block\Widget\Grid
{
    /**
     * More information description template.
     *
     * @var string
     */
    protected $moreInformation = "By admin: admin. (%s)";

    /**
     * Customer notified mapping.
     *
     * @var array
     */
    protected $customerNotified = ['Yes' => 'Notified', 'No' => 'No'];

    /**
     * Verify value in balance history grid.
     *
     * @param CustomerBalance $customerBalance
     * @return bool
     */
    public function verifyCustomerBalanceGrid(CustomerBalance $customerBalance)
    {
        $moreInformation = $customerBalance->getComment();
        $gridRowValue = './/tr[td[contains(.,"' . abs($customerBalance->getAmountDelta()) . '")]';
        $customerNotified = $this->customerNotified[$customerBalance->getNotifyByEmail()];
        $gridRowValue .= ' and td[contains(.,"' .  $customerNotified . '")]';
        if ($moreInformation) {
            $gridRowValue .= ' and td["' . sprintf($this->moreInformation, $moreInformation) . '"]';
        }
        $gridRowValue .= ']';
        $this->waitForElementVisible('.headings');
        return $this->_rootElement->find($gridRowValue, Locator::SELECTOR_XPATH)->isVisible();
    }
}
