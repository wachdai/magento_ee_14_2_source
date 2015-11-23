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

use Mage\Adminhtml\Test\Fixture\Store;
use Mage\Adminhtml\Test\Fixture\StoreGroup;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Preset for store contents.
 */
class StoreContents implements FixtureInterface
{
    /**
     * Resource data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Data set configuration settings.
     *
     * @var array
     */
    protected $params;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Store name.
     *
     * @var string
     */
    protected $store;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->params = $params;
        $this->data = $this->prepareData($data);
    }

    /**
     * Prepare data.
     *
     * @param array $data
     * @return array
     */
    protected function prepareData(array $data)
    {
        $preset = [];
        if (isset($data['preset'])) {
            $preset = $this->getPreset($data['preset']);
            unset($data['preset']);
        }
        $data = empty($preset) ? $data : array_replace_recursive($preset, $data);
        if (isset($data['store_views'])) {
            $data['store_views'] = $this->prepareStoreViewContent($data['store_views']);
        }

        return $data;
    }

    /**
     * Prepare store view content.
     *
     * @param array $storeViews
     * @return array
     */
    protected function prepareStoreViewContent(array $storeViews)
    {
        $result = [];
        foreach ($storeViews as $storeView) {
            if (isset($storeView['store_view']['dataSet'])) {
                /** @var Store $storeViewFixture */
                $storeViewFixture = $this->fixtureFactory->createByCode('store', $storeView['store_view']);
                if (!$storeViewFixture->hasData('store_id')) {
                    $storeViewFixture->persist();
                }
                $result[$storeViewFixture->getStoreId()] = [
                    'store_view' => 'No',
                    'store_view_content' => $storeView['store_view_content']
                ];
                $this->store = $storeViewFixture->getGroupId() . '/' . $storeViewFixture->getName();
            }
        }

        return $result;
    }

    /**
     * Return array preset.
     *
     * @param string $name
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getPreset($name)
    {
        $isolation = mt_rand();
        $presets = [
            'default' => [
                'store_contents_not_use' => 'No',
                'store_content' => 'banner_content_' . $isolation
            ],
            'custom' => [
                'store_contents_not_use' => 'Yes',
                'store_views' => [
                    [
                        'store_view' => ['dataSet' => 'custom_with_custom_store_group'],
                        'store_view_content' => 'banner_content_' . $isolation
                    ]
                ]
            ]
        ];
        return isset($presets[$name]) ? $presets[$name] : [];
    }

    /**
     * Persist store content.
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
     * Get store.
     *
     * @return string|null
     */
    public function getStore()
    {
        return $this->store;
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
}
