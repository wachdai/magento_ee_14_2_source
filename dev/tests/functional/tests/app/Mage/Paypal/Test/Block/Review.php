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

namespace Mage\Paypal\Test\Block;

use Magento\Mtf\Block\Block;

/**
 * Pay Pal sandbox review block.
 */
class Review extends Block
{
    /**
     * Continue button selector.
     *
     * @var string
     */
    protected $continue = '#confirmButtonTop';

    /**
     * Log out button selector.
     *
     * @var string
     */
    protected $logoutButton = '#reviewUserInfo a';

    /**
     * Change shipping button selector.
     *
     * @var string
     */
    protected $changeShipping = '[name="changeShipping"]';

    /**
     * Addresses block selector.
     *
     * @var string
     */
    protected $addresses = '#selectShipping';

    /**
     * Shipping notification.
     *
     * @var string
     */
    protected $shipNotification = '.shipNotification';

    /**
     * Loader selector.
     *
     * @var string
     */
    protected $loader = '.loader';

    /**
     * Click 'Continue' button.
     *
     * @return void
     */
    public function continueCheckout()
    {
        $this->_rootElement->find($this->continue)->click();
    }

    /**
     * Log out from Pay Pal account.
     *
     * @return void
     */
    public function logOut()
    {
        $logoutButton = $this->_rootElement->find($this->logoutButton);
        if ($logoutButton->isVisible()) {
            $logoutButton->click();
        }
    }

    /**
     * Check change shipping button.
     *
     * @return bool
     */
    public function checkChangeShippingButton()
    {
        $this->waitLoader();
        return $this->_rootElement->find($this->changeShipping)->isVisible();
    }

    /**
     * Check for shipping notification.
     *
     * @return bool
     */
    protected function checkShippingNotification()
    {
        return $this->_rootElement->find($this->shipNotification)->isVisible();
    }

    /**
     * Click change shipping button.
     *
     * @return void
     */
    public function clickChangeShippingButton()
    {
        $this->_rootElement->find($this->changeShipping)->click();
    }

    /**
     * Get addresses block.
     *
     * @return Addresses
     */
    public function getAddressesBlock()
    {
        return $this->blockFactory->create(
            'Mage\Paypal\Test\Block\Addresses',
            ['element' => $this->_rootElement->find($this->addresses)]
        );
    }

    /**
     * Wait loader.
     *
     * @return void
     */
    public function waitLoader()
    {
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Check change address ability.
     *
     * @return bool
     */
    public function checkChangeAddressAbility()
    {
        if ($this->checkChangeShippingButton()) {
            $this->clickChangeShippingButton();
            return !$this->checkShippingNotification();
        } else {
            return false;
        }
    }
}
