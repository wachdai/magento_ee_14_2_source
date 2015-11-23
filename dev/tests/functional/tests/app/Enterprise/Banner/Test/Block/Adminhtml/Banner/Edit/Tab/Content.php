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

namespace Enterprise\Banner\Test\Block\Adminhtml\Banner\Edit\Tab;

use Mage\Adminhtml\Test\Block\Widget\Tab;
use Magento\Mtf\Client\Element\SimpleElement as Element;

/**
 * Banner content per store view edit page.
 */
class Content extends Tab
{
    /**
     * Store view content fields.
     *
     * @var array
     */
    protected $storeViewFields = [
        'store_view' => 'No',
        'store_view_content' => ''
    ];

    /**
     * Fill data to content fields on content tab.
     *
     * @param array $fields
     * @param Element|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, Element $element = null)
    {
        if (!isset($fields['store_contents']['value'])) {
            return $this;
        }
        $mappingFields = $this->prepareFields($fields['store_contents']['value']);
        $mapping = $this->dataMapping($mappingFields);
        $data = $this->prepareData($fields['store_contents']['value'], $mapping);
        $this->_fill($data, $element);

        return $this;
    }

    /**
     * Prepare fields for mapping.
     *
     * @param array $fields
     * @return array
     */
    protected function prepareFields(array $fields)
    {
        if (isset($fields['store_views'])) {
            unset($fields['store_views']);
            $fields = array_merge($fields, $this->storeViewFields);
        }

        return $fields;
    }

    /**
     * Prepare data for fill.
     *
     * @param array $fields
     * @param array $mapping
     * @return array
     */
    protected function prepareData(array $fields, array $mapping)
    {
        $result = $mapping;
        if (isset($fields['store_views'])) {
            $storeViews = [];
            foreach ($fields['store_views'] as $key => $storeView) {
                foreach ($this->storeViewFields as $fieldKey => $defaultValue) {
                    $storeViews[$key][$fieldKey] = [
                        'selector' => sprintf($mapping[$fieldKey]['selector'], $key),
                        'strategy' => $mapping[$fieldKey]['strategy'],
                        'input' => $mapping[$fieldKey]['input'],
                        'value' => !empty($defaultValue) ? $defaultValue : $storeView[$fieldKey]
                    ];
                }
            }
            foreach ($this->storeViewFields as $fieldKey => $defaultValue) {
                unset($result[$fieldKey]);
            }
            $result += ['store_views' => $storeViews];
        }


        return $result;
    }

    /**
     * Get data of content tab.
     *
     * @param array|null $fields
     * @param Element|null $element
     * @return array|null
     */
    public function getDataFormTab($fields = null, Element $element = null)
    {
        if (!isset($fields['store_contents']['value'])) {
            return null;
        }
        $mappingFields = $this->prepareFields($fields['store_contents']['value']);
        $mapping = $this->dataMapping($mappingFields);
        $data = $this->prepareData($fields['store_contents']['value'], $mapping);

        return ['store_contents' => $this->_getData($data, $element)];
    }
}
