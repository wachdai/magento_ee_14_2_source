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

namespace Mage\Adminhtml\Test\Block\System;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement as Element;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Currency Symbol form.
 */
class CurrencySymbolForm extends Form
{
    /**
     * Custom Currency locator.
     *
     * @var string
     */
    protected $currencyRow = '//tr[td/label[@for="custom_currency_symbol%s"]]';

    /**
     * Fill the root form.
     *
     * @param InjectableFixture $fixture
     * @param Element|null $element
     * @return $this
     */
    public function fill(InjectableFixture $fixture, Element $element = null)
    {
        $element = $this->_rootElement->find(sprintf($this->currencyRow, $fixture->getCode()), Locator::SELECTOR_XPATH);
        return parent::fill($fixture, $element);
    }
}
