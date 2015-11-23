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

namespace Mage\Adminhtml\Test\Block\Page;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Header block.
 */
class Header extends Block
{
    /**
     * Logout link selector.
     *
     * @var string
     */
    protected $logOut = '.link-logout';

    /**
     * Top menu item selector.
     *
     * @var string
     */
    protected $topMenuItem = './/div[@class = "nav-bar"]/ul/li/a[contains(., "%s")]';

    /**
     * Log out Admin User.
     *
     * @return void
     */
    public function logOut()
    {
        $this->_rootElement->find($this->logOut)->click();
        $this->waitForElementNotVisible($this->logOut);
    }

    /**
     * Check navigation menu.
     *
     * @param string $name
     * @return bool
     */
    public function checkMenu($name)
    {
        return $this->_rootElement->find(sprintf($this->topMenuItem, $name), Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Wait header block is visible.
     *
     * @return bool
     */
    public function waitVisible()
    {
        try {
            $browser = $this->_rootElement;
            return $browser->waitUntil(
                function () use ($browser) {
                    return $browser->isVisible() ? true : null;
                }
            );
        } catch (\Exception $e) {
            return false;
        }
    }
}
