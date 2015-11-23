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

namespace Enterprise\Banner\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\WidgetOptionsType;

use Enterprise\Banner\Test\Block\Adminhtml\Banner\Grid;
use Mage\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\WidgetOptionsType\WidgetOptionsForm;
use Magento\Mtf\Block\BlockInterface;
use Magento\Mtf\Client\Element\SimpleElement as Element;

/**
 * Filling Widget Options that have banner rotator type.
 */
class BannerRotator extends WidgetOptionsForm
{
    /**
     * Banner Rotator grid block.
     *
     * @var string
     */
    protected $gridBlock = '#bannerGrid';

    /**
     * Select node on widget options tab.
     *
     * @param array $entities
     * @return void
     */
    protected function selectEntities(array $entities)
    {
        foreach ($entities['value'] as $entity) {
            /** @var Grid $bannerRotatorGrid */
            $bannerRotatorGrid = $this->getBannerGrid();
            $bannerRotatorGrid->searchAndSelect(['name' => $entity->getName()]);
        }
    }

    /**
     * Get banner grid.
     *
     * @return BlockInterface
     */
    protected function getBannerGrid()
    {
        return $this->blockFactory->create(
            'Enterprise\Banner\Test\Block\Adminhtml\Banner\Grid',
            ['element' => $this->_rootElement->find($this->gridBlock)]
        );

    }

    /**
     * Get data of tab.
     *
     * @param array|null $fields
     * @param Element|null $element
     * @return array
     */
    public function getDataFormTab($fields = null, Element $element = null)
    {
        $data = [];
        foreach ($fields as $key => $field) {
            $dataMapping = $this->dataMapping($field);
            $data[$key] = $this->_getData($dataMapping, $element);
            if (isset($dataMapping['entities'])) {
                $data[$key]['entities'] = $this->getBanners($dataMapping['entities']['value']);
            }
        }
        $result['widgetOptions'] = $data;

        return $result;
    }

    /**
     * Get banners from tab.
     *
     * @param array $banners
     * @return array
     */
    protected function getBanners(array $banners)
    {
        $result = [];
        /** @var Grid $bannerGrid */
        $bannerGrid = $this->getBannerGrid();
        foreach ($banners as $key => $banner) {
            if ($bannerGrid->isSelect(['name' => $banner->getName()])) {
                $result[$key]['name'] = $banner->getName();
            }
        }
        return $result;
    }
}
