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
 * 1. Test Categories are created.
 * 2. Products are created (1 product per each category).
 *
 * Steps:
 * 1. Login to the backend.
 * 2. Navigate to Catalog -> Rule-Based Product Relations.
 * 3. Click 'Add Rule' button.
 * 4. Fill in data according to dataSet.
 * 5. Save rule.
 * 6. Perform all assertions.
 *
 * @group Target_Rules_(MX)
 * @ZephyrId MPERF-7217
 */
class CreateTargetRuleEntityTest extends AbstractTargetRuleEntityTest
{
    /**
     * Create Target Rule entity.
     *
     * @param CatalogProductSimple $product
     * @param CatalogProductSimple $promotedProduct
     * @param TargetRule $targetRule
     * @param string $productType
     * @param CustomerSegment|null $customerSegment
     * @return array
     */
    public function test(
        CatalogProductSimple $product,
        CatalogProductSimple $promotedProduct,
        TargetRule $targetRule,
        $productType,
        CustomerSegment $customerSegment = null
    ) {
        // Preconditions
        $this->defaultPreconditions($product, $promotedProduct, $customerSegment);
        $replacementData = $this->getReplaceData($product, $promotedProduct, $customerSegment);

        // Steps
        $this->targetRuleIndex->open();
        $this->targetRuleIndex->getGridPageActions()->addNew();
        $this->targetRuleNew->getTargetRuleForm()->fillForm($targetRule, $replacementData);
        $this->targetRuleNew->getPageActions()->save();

        // Prepare data for tear down
        $this->targetRule = $targetRule;

        return [
            $productType => [$promotedProduct],
            'replacementData' => $replacementData
        ];
    }
}
