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

namespace Mage\Tax\Test\Constraint;

/**
 * Checks that prices on category, product and cart pages are equal for both customers.
 */
class AssertTaxWithCrossBorderApplied extends AbstractAssertTaxWithCrossBorderApplying
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert prices on category, product and cart pages are equal for both customers.
     *
     * @param array $actualPrices
     * @return void
     */
    public function assert(array $actualPrices)
    {
        //Prices verification
        \PHPUnit_Framework_Assert::assertEmpty(
            array_diff($actualPrices[0], $actualPrices[1]),
            'Prices for customers should be equal. Cross border is not applied.'
        );
    }

    /**
     * Returns string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Cross border trading is applied on front.';
    }
}
