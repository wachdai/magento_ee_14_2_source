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

namespace Enterprise\GiftCardAccount\Test\Block;

use Magento\Mtf\Block\Block;

/**
 * Check block on customer account page.
 */
class Check extends Block
{
    /**
     * Filter for get data.
     *
     * @var array
     */
    protected $filter = [
        'code' => 'Gift Card: (.*)',
        'balance' => '\nCurrent Balance: \$(.*)',
        'date_expires' => '\nExpires: (\d+\/\d+\/\d+)',
    ];

    /**
     * Info block css selector.
     *
     * @var string
     */
    protected $infoBlock = '.gift-card-info';

    /**
     * Get gift card account data.
     *
     * @param array $filter
     * @return array
     */
    public function getGiftCardAccountData(array $filter)
    {
        $pattern = '';
        $count = 0;
        $result = [];
        foreach ($filter as $key => $value) {
            if (isset($this->filter[$key])) {
                $pattern .= $this->filter[$key];
                $count++;
            }
        }
        $browser = $this->browser;
        $selector = $this->infoBlock;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector);
                return $element->isVisible() ? true : null;
            }
        );
        preg_match('/' . $pattern . '/', $this->_rootElement->getText(), $matches);
        if ($count == count($matches) - 1) {
            $index = 1;
            foreach ($filter as $key => $value) {
                $result[$key] = $matches[$index++];
            }
        }

        return $result;
    }
}
