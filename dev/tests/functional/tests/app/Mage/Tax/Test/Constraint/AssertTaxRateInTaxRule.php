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

use Mage\Tax\Test\Fixture\TaxRate;
use Mage\Tax\Test\Page\Adminhtml\TaxRuleIndex;
use Mage\Tax\Test\Page\Adminhtml\TaxRuleNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that necessary tax rate is present in "Tax Rule Information" on TaxRuleEdit page.
 */
class AssertTaxRateInTaxRule extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that necessary tax rate is present in "Tax Rule Information" on TaxRuleEdit page.
     *
     * @param TaxRuleIndex $taxRuleIndex
     * @param TaxRuleNew $taxRuleNew
     * @param TaxRate $taxRate
     * @return void
     */
    public function processAssert(TaxRuleIndex $taxRuleIndex, TaxRuleNew $taxRuleNew, TaxRate $taxRate)
    {
        $taxRateCode = $taxRate->getCode();
        $taxRuleIndex->open()->getPageActionsBlock()->addNew();
        $availableTaxRates = $taxRuleNew->getTaxRuleForm()->getAvailableTaxRates();

        \PHPUnit_Framework_Assert::assertTrue(
            in_array($taxRateCode, $availableTaxRates),
            "$taxRateCode is not present in Tax Rates multiselect on TaxRuleEdit page."
        );
    }

    /**
     * Returns string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return "Necessary tax rate is present in Tax Rule Information on TaxRuleEdit page.";
    }
}
