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

namespace Enterprise\Banner\Test\Fixture\Widget;

use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Prepare Widget options for widget banner rotator.
 */
class WidgetOptions extends \Mage\Widget\Test\Fixture\Widget\WidgetOptions
{
    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        parent::__construct($fixtureFactory, $params, $data);

        $this->data['type_id'] = 'bannerRotator';
    }

    /**
     * Preset for Widget options.
     *
     * @param string $name
     * @return array|null
     */
    protected function getPreset($name)
    {
        $presets = [
            'bannerRotatorShoppingCartRules' => [
                [
                    'display_mode' => 'Specified Banners',
                    'rotate' => 'Do not rotate, display all at once',
                    'entities' => ['banner::banner_rotator_shopping_cart_rules']
                ]
            ],
            'bannerRotatorCatalogRules' => [
                [
                    'display_mode' => 'Specified Banners',
                    'rotate' => 'Do not rotate, display all at once',
                    'entities' => ['banner::banner_rotator_catalog_rules']
                ]
            ]
        ];

        if (!isset($presets[$name])) {
            return null;
        }

        return $presets[$name];
    }
}
