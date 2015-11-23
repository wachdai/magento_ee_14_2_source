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

/**
 * Assert that displayed product data on product page(front-end) equals passed from fixture.
 */
class AssertConfigurableProductPage extends AssertProductPage
{
    /**
     * Verify displayed product data on product page(front-end) equals passed from fixture:
     * 1. Product Name
     * 2. Price
     * 3. SKU
     * 4. Description
     * 5. Short Description
     * 6. Attributes
     *
     * @return array
     */
    protected function verify()
    {
        $errors = parent::verify();
        $errors[] = $this->verifyAttributes();

        return array_filter($errors);
    }

    /**
     * Verify displayed product attributes on product page(front-end) equals passed from fixture.
     *
     * @return string|null
     */
    protected function verifyAttributes()
    {
        $attributesData = $this->product->getConfigurableOptions()['attributes_data'];
        $configurableOptions = [];
        $formOptions = $this->productView->getOptions($this->product)['configurable_options'];

        foreach ($attributesData as $attributeKey => $attributeData) {
            $optionData = [
                'title' => $attributeData['frontend_label'],
                'type' => $attributeData['frontend_input'],
                'is_require' => 'Yes'
            ];

            foreach ($attributeData['options'] as $option) {
                $price = ('Percentage' == $option['price_type'])
                    ? ($this->product->getPrice() * $option['price']) / 100
                    : $option['price'];

                $optionData['options'][] = [
                    'title' => $option['label'],
                    'price' => number_format($price, 2),
                ];
            }

            $configurableOptions[$attributeData['frontend_label']] = $optionData;
        }

        // Sort data for compare
        $configurableOptions = $this->sortDataByPath($configurableOptions, '::title');
        foreach ($configurableOptions as $key => $configurableOption) {
            $configurableOptions[$key] = $this->sortDataByPath($configurableOption, 'options::title');
        }
        $formOptions = $this->sortDataByPath($formOptions, '::title');
        foreach ($formOptions as $key => $formOption) {
            $formOptions[$key] = $this->sortDataByPath($formOption, 'options::title');
        }

        $errors = $this->verifyData($configurableOptions, $formOptions, true, false);
        return empty($errors) ? null : $this->prepareErrors($errors, 'Error configurable options:');
    }
}
