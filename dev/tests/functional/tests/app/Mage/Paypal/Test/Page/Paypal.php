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

namespace Mage\Paypal\Test\Page;

use Magento\Mtf\Page\Page;
use Mage\Paypal\Test\Block;
use Mage\Paypal\Test\Block\Login;
use Mage\Paypal\Test\Block\Review;

/**
 * Pay Pal page.
 */
class Paypal extends Page
{
    /**
     * Page url.
     */
    const MCA = 'paypal';

    /**
     * Blocks' config
     *
     * @var array
     */
    protected $blocks = [
        'loginBlock' => [
            'class' => 'Mage\Paypal\Test\Block\Login',
            'locator' => '.loggingIn',
            'strategy' => 'css selector',
        ],
        'reviewBlock' => [
            'class' => 'Mage\Paypal\Test\Block\Review',
            'locator' => '.outerWrapper',
            'strategy' => 'css selector',
        ],
    ];

    /**
     * Custom initialization.
     *
     * @return void
     */
    protected function _init()
    {
        $this->_url = 'https://www.sandbox.paypal.com/cgi-bin/';
    }

    /**
     * @return Login
     */
    public function getLoginBlock()
    {
        return $this->getBlockInstance('loginBlock');
    }

    /**
     * @return Review
     */
    public function getReviewBlock()
    {
        return $this->getBlockInstance('reviewBlock');
    }
}
