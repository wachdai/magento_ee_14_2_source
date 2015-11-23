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

namespace Enterprise\UrlRewrite\Test\Constraint;

use Mage\Catalog\Test\Fixture\CatalogCategory;
use Enterprise\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that url rewrite category is absent in grid.
 */
class AssertUrlRewriteCategoryNotInGrid extends AbstractConstraint
{
    /**
     * Assert that category url rewrite not in grid.
     *
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param CatalogCategory $category
     * @return void
     */
    public function processAssert(UrlRewriteIndex $urlRewriteIndex, CatalogCategory $category)
    {
        $urlRewriteIndex->open();
        $filter = ['request_path' => $category->getUrlKey()];
        \PHPUnit_Framework_Assert::assertFalse(
            $urlRewriteIndex->getUrlRedirectGrid()->isRowVisible($filter),
            "URL Rewrite with request path '{$filter['request_path']}' is present in grid."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'URL Rewrite is absent in grid.';
    }
}
