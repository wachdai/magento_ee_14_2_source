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

namespace Mage\Catalog\Test\Fixture\CatalogProductVirtual;

/**
 * Preset for virtual product price.
 *
 * Data keys:
 *  - preset (Price verification preset name)
 *  - value (Price value)
 *
 */
class Price extends \Mage\Catalog\Test\Fixture\CatalogProductSimple\Price
{
    /**
     * Get preset.
     *
     * @return array|null
     */
    public function getPreset()
    {
        $presets = [
            'default' => [
                'category_price' => '100.00',
                'category_special_price' => '90.00',
                'product_price' => '100.00',
                'product_special_price' => '90.00',
                'cart_price' => '126.00',
            ],
            'with_tier_price' => [
                [
                    'percent' => 85
                ],
                [
                    'percent' => 76
                ]
            ]
        ];
        if (!isset($presets[$this->currentPreset])) {
            return null;
        }
        return $presets[$this->currentPreset];
    }
}
