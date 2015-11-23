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

namespace Mage\Catalog\Test\Block\Product\ProductList;

use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Element\SimpleElement as Element;
use Magento\Mtf\Client\Locator;

/**
 * Crosssell product block on the page.
 */
class Crosssell extends Block
{
    /**
     * Crosssell product locator on the page.
     *
     * @var string
     */
    protected $crosssellProduct = "//div[normalize-space(h3//a)='%s']";

    /**
     * Verify cross-sell item.
     *
     * @param InjectableFixture $product
     * @return bool
     */
    public function verifyProductCrosssell(InjectableFixture $product)
    {
        return $this->_rootElement->find(sprintf($this->crosssellProduct, $product->getName()), Locator::SELECTOR_XPATH)
            ->isVisible();
    }
}
