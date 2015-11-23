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
 * @category    Enterprise
 * @package     Enterprise_Search
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Layer decimal attribute filter
 *
 * @category    Enterprise
 * @package     Enterprise_Search
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Search_Model_Catalog_Layer_Filter_Decimal extends Mage_Catalog_Model_Layer_Filter_Decimal
{
    /**
     * Get data for build decimal filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $range = $this->getRange();
        $attribute_code = $this->getAttributeModel()->getAttributeCode();
        $facets = $this->getLayer()->getProductCollection()->getFacetedData('attr_decimal_' . $attribute_code);

        $data = array();
        if (!empty($facets)) {
            foreach ($facets as $key => $count) {
                preg_match('/TO ([\d\.]+)\]$/', $key, $rangeKey);
                $rangeKey = $rangeKey[1] / $range;
                if ($count > 0) {
                    $rangeKey = round($rangeKey);
                    $data[] = array(
                        'label' => $this->_renderItemLabel($range, $rangeKey),
                        'value' => $rangeKey . ',' . $range,
                        'count' => $count,
                    );
                }
            }
        }

        return $data;
    }

    /**
     * Apply decimal range filter to product collection
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Mage_Catalog_Block_Layer_Filter_Decimal $filterBlock
     * @return Mage_Catalog_Model_Layer_Filter_Decimal
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        /**
         * Filter must be string: $index, $range
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }

        $filter = explode(',', $filter);
        if (count($filter) != 2) {
            return $this;
        }

        list($index, $range) = $filter;
        if ((int)$index && (int)$range) {
            $this->setRange((int)$range);

            $this->applyFilterToCollection($this, $range, $index);
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_renderItemLabel($range, $index), $filter)
            );

            $this->_items = array();
        }

        return $this;
    }

    /**
     * Add params to faceted search
     *
     * @return Enterprise_Search_Model_Catalog_Layer_Filter_Decimal
     */
    public function addFacetCondition()
    {
        $range    = $this->getRange();
        $maxValue = $this->getMaxValue();
        if ($maxValue > 0) {
            $facets = array();
            $facetCount = ceil($maxValue / $range);
            for ($i = 0; $i < $facetCount; $i++) {
                $facets[] = array(
                    'from' => $i * $range,
                    'to'   => ($i + 1) * $range - 0.001
                );
            }

            $attributeCode = $this->getAttributeModel()->getAttributeCode();
            $field         = 'attr_decimal_' . $attributeCode;

            $this->getLayer()->getProductCollection()->setFacetCondition($field, $facets);
        }

        return $this;
    }

    /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @param int $range
     * @param int $index    the range factor
     *
     * @return Enterprise_Search_Model_Catalog_Layer_Filter_Decimal
     */
    public function applyFilterToCollection($filter, $range, $index)
    {
        $productCollection = $filter->getLayer()->getProductCollection();
        $attributeCode     = $filter->getAttributeModel()->getAttributeCode();
        $field             = 'attr_decimal_'. $attributeCode;

        $value = array(
            $field => array(
                'from' => ($range * ($index - 1)),
                'to'   => $range * $index - 0.001
            )
        );

        $productCollection->addFqFilter($value);
        return $this;
    }
}
