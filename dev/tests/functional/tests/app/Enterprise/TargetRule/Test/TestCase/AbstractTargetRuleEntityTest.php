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

use Mage\Catalog\Test\Fixture\CatalogCategory;
use Mage\Catalog\Test\Fixture\CatalogProductSimple;
use Enterprise\CustomerSegment\Test\Fixture\CustomerSegment;
use Enterprise\TargetRule\Test\Fixture\TargetRule;
use Enterprise\TargetRule\Test\Page\Adminhtml\TargetRuleEdit;
use Enterprise\TargetRule\Test\Page\Adminhtml\TargetRuleIndex;
use Enterprise\TargetRule\Test\Page\Adminhtml\TargetRuleNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Parent class for TargetRule tests.
 */
abstract class AbstractTargetRuleEntityTest extends Injectable
{
    /**
     * Target rule index page.
     *
     * @var TargetRuleIndex
     */
    protected $targetRuleIndex;

    /**
     * New target rule page.
     *
     * @var TargetRuleNew
     */
    protected $targetRuleNew;

    /**
     * Target rule edit page.
     *
     * @var TargetRuleEdit
     */
    protected $targetRuleEdit;

    /**
     * Current target rule.
     *
     * @var TargetRule
     */
    protected $targetRule;

    /**
     * Injection data
     *
     * @param TargetRuleIndex $targetRuleIndex
     * @param TargetRuleNew $targetRuleNew
     * @param TargetRuleEdit $targetRuleEdit
     * @return void
     */
    public function __inject(
        TargetRuleIndex $targetRuleIndex,
        TargetRuleNew $targetRuleNew,
        TargetRuleEdit $targetRuleEdit
    ) {
        $this->targetRuleIndex = $targetRuleIndex;
        $this->targetRuleNew = $targetRuleNew;
        $this->targetRuleEdit = $targetRuleEdit;
    }

    /**
     * Get data for replace in variations.
     *
     * @param CatalogProductSimple $product
     * @param CatalogProductSimple $promotedProduct
     * @param CustomerSegment|null $customerSegment
     * @return array
     */
    protected function getReplaceData(
        CatalogProductSimple $product,
        CatalogProductSimple $promotedProduct,
        CustomerSegment $customerSegment = null
    ) {
        $customerSegmentName = ($customerSegment instanceof CustomerSegment) ? $customerSegment->getName() : '';

        return [
            'rule_information' => [
                'customer_segment_ids' => [
                    '%customer_segment%' => $customerSegmentName,
                ],
            ],
            'products_to_match' => [
                'conditions_serialized' => [
                    '%category_1%' => $this->getProductCategory($product)->getId(),
                ],
            ],
            'products_to_display' => [
                'actions_serialized' => [
                    '%category_2%' => $this->getProductCategory($promotedProduct)->getId(),
                ],
            ],
        ];
    }

    /**
     * Get product category.
     *
     * @param CatalogProductSimple $product
     * @return CatalogCategory
     */
    protected function getProductCategory(CatalogProductSimple $product)
    {
        return $product->getDataFieldConfig('category_ids')['source']->getProductCategory();
    }

    /**
     * Default preconditions for target rules tests.
     *
     * @param CatalogProductSimple $product
     * @param CatalogProductSimple $promotedProduct
     * @param CustomerSegment $customerSegment
     * @return void
     */
    protected function defaultPreconditions(
        CatalogProductSimple $product,
        CatalogProductSimple $promotedProduct,
        CustomerSegment $customerSegment = null
    ) {
        $product->persist();
        $promotedProduct->persist();
        if ($customerSegment) {
            $customerSegment->persist();
        }
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        if (!$this->targetRule instanceof TargetRule) {
            return;
        }
        $this->targetRuleIndex->open();
        $this->targetRuleIndex->getTargetRuleGrid()->searchAndOpen(['name' => $this->targetRule->getName()]);
        $this->targetRuleEdit->getPageActions()->deleteAndAcceptAlert();
    }
}
