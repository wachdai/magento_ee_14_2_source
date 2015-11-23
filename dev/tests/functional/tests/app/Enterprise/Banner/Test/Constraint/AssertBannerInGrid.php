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

namespace Enterprise\Banner\Test\Constraint;

use Enterprise\Banner\Test\Fixture\Banner;
use Enterprise\Banner\Test\Page\Adminhtml\BannerIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that created banner is found by name and has correct banner types, visibility, status.
 */
class AssertBannerInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that created banner is found by name and has correct banner types, visibility, status.
     *
     * @param Banner $banner
     * @param BannerIndex $bannerIndex
     * @return void
     */
    public function processAssert(Banner $banner, BannerIndex $bannerIndex)
    {
        $bannerIndex->open();
        $filter = [
            'name' => $banner->getName(),
            'active' => $banner->getIsEnabled(),
        ];

        $isStoreContents = $banner->getStoreContents();
        if (isset($isStoreContents['store_views'])) {
            $filter['visibility'] = $banner->getDataFieldConfig('store_contents')['source']->getStore();
        }

        $bannerIndex->getGrid()->search($filter);
        if ($banner->hasData('types')) {
            $filter['types'] = implode(', ', $banner->getTypes());
        }
        unset($filter['visibility']);

        \PHPUnit_Framework_Assert::assertTrue(
            $bannerIndex->getGrid()->isRowVisible($filter, false),
            'Banner is absent in grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Banner is present in grid.';
    }
}
