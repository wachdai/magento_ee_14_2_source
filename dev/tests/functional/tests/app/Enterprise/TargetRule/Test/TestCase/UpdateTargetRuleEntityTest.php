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

namespace Enterprise\TargetRule\Test\TestCase;

use Mage\Catalog\Test\Fixture\CatalogProductSimple;
use Enterprise\CustomerSegment\Test\Fixture\CustomerSegment;
use Enterprise\TargetRule\Test\Fixture\TargetRule;

/**
 * Preconditions:
 * 1. Create products.
 * 2. Target Rule is created.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to Catalog > Rule-Based Product Relations.
 * 3. Search in grid and open created Target Rule from preconditions.
 * 4. Edit test value(s) according to dataset.
 * 5. Click 'Save' button.
 * 6. Perform all asserts.
 *
 * @group Target_Rules_(MX)
 * @ZephyrId MPERF-7317
 */
class UpdateTargetRuleEntityTest extends AbstractTargetRuleEntityTest
{
    /**
     * Run update TargetRule entity test.
     *
     * @param CatalogProductSimple $product
     * @param CatalogProductSimple $promotedProduct
     * @param TargetRule $initialTargetRule
     * @param TargetRule $targetRule
     * @param CustomerSegment|null $customerSegment
     * @return array
     */
    public function test(
        CatalogProductSimple $product,
        CatalogProductSimple $promotedProduct,
        TargetRule $initialTargetRule,
        TargetRule $targetRule,
        CustomerSegment $customerSegment = null
    ) {
        // Preconditions:
        $this->defaultPreconditions($product, $promotedProduct, $customerSegment);
        $initialTargetRule->persist();
        $replacementData = $this->getReplaceData($product, $promotedProduct, $customerSegment);

        // Steps
        $filter = ['name' => $initialTargetRule->getName()];
        $this->targetRuleIndex->open();
        $this->targetRuleIndex->getTargetRuleGrid()->searchAndOpen($filter);
        $this->targetRuleEdit->getTargetRuleForm()->fillForm($targetRule, $replacementData);
        $this->targetRuleEdit->getPageActions()->save();

        // Prepare data for tear down
        $this->targetRule = $targetRule;

        return [
            'promotedProducts' => [$promotedProduct],
            'replacementData' => $replacementData
        ];
    }
}
