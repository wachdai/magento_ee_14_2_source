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

namespace Enterprise\TargetRule\Test\Constraint;

use Magento\Mtf\Constraint\AbstractAssertForm;
use Enterprise\TargetRule\Test\Fixture\TargetRule;
use Enterprise\TargetRule\Test\Page\Adminhtml\TargetRuleEdit;
use Enterprise\TargetRule\Test\Page\Adminhtml\TargetRuleIndex;

/**
 * Assert that displayed target rule data on edit page equals passed from fixture.
 */
class AssertTargetRuleForm extends AbstractAssertForm
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Skipped fields for verify data.
     *
     * @var array
     */
    protected $skippedFields = ['conditions_serialized', 'actions_serialized'];

    /**
     * Assert that displayed target rule data on edit page equals passed from fixture.
     *
     * @param TargetRuleIndex $targetRuleIndex
     * @param TargetRule $targetRule
     * @param TargetRuleEdit $targetRuleEdit
     * @param array $replacementData
     * @return void
     */
    public function processAssert(
        TargetRuleIndex $targetRuleIndex,
        TargetRule $targetRule,
        TargetRuleEdit $targetRuleEdit,
        array $replacementData
    ) {
        $fixtureData = $this->prepareData($targetRule->getData(), $replacementData['rule_information']);
        $targetRuleIndex->open();
        $targetRuleIndex->getTargetRuleGrid()->searchAndOpen(['name' => $targetRule->getName()]);
        $formData = $targetRuleEdit->getTargetRuleForm()->getData();
        $errors = $this->verifyData($fixtureData, $formData);
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
    }

    /**
     * Prepare target rule data.
     *
     * @param array $data
     * @param array $replace
     * @return array
     */
    protected function prepareData(array $data, array $replace)
    {
        foreach ($replace as $key => $pairs) {
            if (isset($data[$key])) {
                $data[$key] = str_replace(
                    array_keys($pairs),
                    array_values($pairs),
                    $data[$key]
                );
            }
        }

        return $data;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed target rule data on edit page equals to passed from fixture.';
    }
}
