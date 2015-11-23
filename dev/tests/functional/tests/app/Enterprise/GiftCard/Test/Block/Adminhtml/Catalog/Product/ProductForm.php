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

namespace Enterprise\GiftCard\Test\Block\Adminhtml\Catalog\Product;

use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Client\Element\SimpleElement as Element;
use Mage\Catalog\Test\Fixture\CatalogCategory;

/**
 * Gift card product form on backend product page.
 */
class ProductForm extends \Mage\Adminhtml\Test\Block\Catalog\Product\ProductForm
{
    /**
     * Get gift card product type.
     *
     * @return string
     */
    protected function convertProductType()
    {
        return "Gift Card";
    }

    /**
     * Fill the product form.
     *
     * @param InjectableFixture $product
     * @param Element|null $element [optional]
     * @param CatalogCategory|null $category [optional]
     * @return void
     */
    public function fill(InjectableFixture $product, Element $element = null, CatalogCategory $category = null)
    {
        $this->fillDefaultFields($product, $element, $category);
    }
}
