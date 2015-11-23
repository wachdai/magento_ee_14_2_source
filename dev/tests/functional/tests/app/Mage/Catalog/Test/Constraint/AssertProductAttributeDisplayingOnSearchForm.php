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

namespace Mage\Catalog\Test\Constraint;

use Mage\Catalog\Test\Fixture\CatalogProductAttribute;
use Mage\CatalogSearch\Test\Page\CatalogsearchAdvanced;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check whether attribute is displayed in the advanced search form on the frontend.
 */
class AssertProductAttributeDisplayingOnSearchForm extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Check whether attribute is displayed in the advanced search form on the frontend.
     *
     * @param CatalogProductAttribute $attribute
     * @param CatalogsearchAdvanced $advancedSearch
     * @return void
     */
    public function processAssert(CatalogProductAttribute $attribute, CatalogsearchAdvanced $advancedSearch)
    {
        $advancedSearch->open();
        $formLabels = $advancedSearch->getForm()->getFormlabels();
        $label = $attribute->hasData('manage_frontend_label')
            ? $attribute->getManageFrontendLabel()
            : $attribute->getFrontendLabel();
        \PHPUnit_Framework_Assert::assertTrue(
            in_array($label, $formLabels),
            'Attribute is absent on advanced search form.'
        );
    }

    /**
     * Returns string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute is present on advanced search form.';
    }
}
