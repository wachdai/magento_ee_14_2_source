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

namespace Enterprise\Banner\Test\Fixture\Banner;

use Mage\CatalogRule\Test\Fixture\CatalogRule;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Preset for catalog rules.
 */
class CatalogRules implements FixtureInterface
{
    /**
     * Resource data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Return catalog rules.
     *
     * @var CatalogRule[]
     */
    protected $catalogRules = [];

    /**
     * Data set configuration settings.
     *
     * @var array
     */
    protected $params;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['presets'])) {
            $presets = explode(',', $data['presets']);
            foreach ($presets as $preset) {
                /** @var CatalogRule $catalogRule */
                $catalogRule = $fixtureFactory->createByCode('catalogRule', ['dataSet' => $preset]);
                if (!$catalogRule->getId()) {
                    $catalogRule->persist();
                }

                $this->data[] = $catalogRule->getId();
                $this->catalogRule[] = $catalogRule;
            }
        } else {
            $this->data[] = $data;
        }
    }

    /**
     * Persist catalog rule.
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data.
     *
     * @param string|null $key
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings.
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Return catalog rules fixture.
     *
     * @return array
     */
    public function getCatalogRules()
    {
        return $this->catalogRules;
    }
}
