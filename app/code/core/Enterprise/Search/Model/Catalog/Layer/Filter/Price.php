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
 * Layer price filter
 *
 * @category    Enterprise
 * @package     Enterprise_Search
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Search_Model_Catalog_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'MAXPRICE';

    /**
     * Whether current price interval is divisible
     *
     * @var bool
     */
    protected $_divisible = true;

    /**
     * Ranges faceted data
     *
     * @var array
     */
    protected $_facets = array();

    /**
     * Return cache tag for layered price filter
     *
     * @return string
     */
    public function getCacheTag()
    {
        return self::CACHE_TAG;
    }

    /**
     * Get facet field name based on current website and customer group
     *
     * @return string
     */
    protected function _getFilterField()
    {
        $engine = Mage::getResourceSingleton('enterprise_search/engine');
        $priceField = $engine->getSearchEngineFieldName('price');

        return $priceField;
    }

    /**
     * Get data for build price filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        if (!$this->_divisible) {
            return array();
        }

        $isAuto = (Mage::app()->getStore()
            ->getConfig(self::XML_PATH_RANGE_CALCULATION) == self::RANGE_CALCULATION_IMPROVED);
        if (!$isAuto && $this->getInterval()) {
            return array();
        }

        $facets = $this->getLayer()->getProductCollection()->getFacetedData($this->_getFilterField());
        $data = array();
        if (!empty($facets)) {
            foreach ($facets as $k => $count) {
                if ($count <= 0) {
                    unset($facets[$k]);
                }
            }

            if (!$isAuto && !empty($facets)) {
                $range = $this->getPriceRange();
            }

            $i = 0;
            $maxIntervalsNumber = $this->getMaxIntervalsNumber();
            $lastSeparator = null;
            foreach ($facets as $key => $count) {
                if (!preg_match('/\[([\d\.\*]+) TO ([\d\.\*]+)\]$/', $key, $separator)) {
                    continue;
                }

                ++$i;
                $label = null;
                $value = null;
                if (isset($this->_facets[$separator[1] . '_' . $separator[2]])) {
                    $separatorLabelValues = $this->_facets[$separator[1] . '_' . $separator[2]];
                    if ($i <= max(1, $maxIntervalsNumber)) {
                        $lastSeparator = $separatorLabelValues[0];
                    }
                    $label = $this->_renderRangeLabel($separatorLabelValues[0], $separatorLabelValues[1]);
                    $value = (empty($separatorLabelValues[0]) ? '' : $separatorLabelValues[0])
                        . '-' . $separatorLabelValues[1];
                }

                if ($isAuto) {
                    if ($separator[1] == '*') {
                        $separator[1] = '';
                    }
                    if ($separator[2] == '*') {
                        $separator[2] = '';
                    }
                } else {
                    $rangeKey = $separator[2] / $range;

                    $rangeKey = round($rangeKey, 2);
                    $separator[1] = ($rangeKey == 1) ? '' : (($rangeKey - 1) * $range);
                    $separator[2] = ($key == null) ? '' : ($rangeKey * $range);
                    // checking max number of intervals
                    if ($i > 1 && $i > $maxIntervalsNumber) {
                        --$i;
                        $count += $data[$i - 1]['count'];
                        $separator[1] = $data[$i - 1]['from'];
                        $label = $value = null;
                    } elseif (!empty($separator[2]) && $separator[2] > $this->getMaxPriceInt()) {
                        $label = $value = null;
                        $separator[2] = '';
                    }
                }

                $data[$i - 1] = array(
                    'label' => is_null($label) ? $this->_renderRangeLabel(
                            empty($separator[1]) ? 0 : ($separator[1] * $this->getCurrencyRate()),
                            empty($separator[2]) ? $separator[2] : $separator[2]  * $this->getCurrencyRate()
                        ) : $label,
                    'value' => (is_null($value) ? ($separator[1] . '-' . $separator[2]) : $value)
                        . $this->_getAdditionalRequestData(),
                    'count' => $count,
                    'from'  => $separator[1],
                    'to'    => $separator[2],
                );
            }

            if (isset($data[$i - 1]) && $data[$i - 1]['from'] != $data[$i - 1]['to']) {
                $upperIntervalLimit = '';
                $appliedInterval = $this->getInterval();
                if ($appliedInterval) {
                    $upperIntervalLimit = $appliedInterval[1];
                }
                if (is_null($value)) {
                    $data[$i - 1]['value'] = $lastSeparator . '-' . $upperIntervalLimit
                        . $this->_getAdditionalRequestData();
                }
                if (is_null($label)) {
                    $data[$i - 1]['label'] = $this->_renderRangeLabel(
                        empty($lastSeparator) ? 0 : $lastSeparator,
                        $upperIntervalLimit
                    );
                }
            }
        }

        return $data;
    }

    /**
     * Prepare unique cache key
     *
     * @param string $cachePrefix
     * @param array  $additionalParams
     *
     * @return string
     */
    protected function _getUniqueCacheKey($cachePrefix, array $additionalParams = array())
    {
        $uniqueParams = $this->getLayer()->getProductCollection()->getExtendedSearchParams();
        $uniqueParams['currency_rate'] = $this->getCurrencyRate();
        if (!empty($additionalParams)) {
            $additionalParams = array_filter($additionalParams, 'strlen');
            sort($additionalParams);
            $uniqueParams = array_merge($uniqueParams, $additionalParams);
        }
        $uniqueParams = strtoupper(md5(serialize($uniqueParams)));

        $cacheKey = $cachePrefix . '_' . $this->getLayer()->getStateKey() . '_' . $uniqueParams;

        return $cacheKey;
    }

    /**
     * Get maximum price from layer products set using cache
     *
     * @return float
     */
    public function getMaxPriceInt()
    {
        $cacheKey = $this->_getUniqueCacheKey('MAXPRICE');

        $cachedData = Mage::app()->loadCache($cacheKey);
        if (!$cachedData) {
            $stats = $this->getLayer()->getProductCollection()->getStats($this->_getFilterField());

            $max = $stats[$this->_getFilterField()]['max'];
            if (!is_numeric($max)) {
                $max = parent::getMaxPriceInt();
            } else {
                $max = floor($max * $this->getCurrencyRate());
            }

            $cachedData = $max;
            $tags = $this->getLayer()->getStateTags();
            $tags[] = self::CACHE_TAG;
            Mage::app()->saveCache($cachedData, $cacheKey, $tags);
        }

        return $cachedData;
    }

    /**
     * Get data with price separators
     *
     * @return array
     */
    protected function _getSeparators()
    {
        $intervalParams = $this->getInterval();
        $additionalParams = ($intervalParams) ? array($intervalParams[0] . '-' . $intervalParams[1]) : array();
        $cacheKey = $this->_getUniqueCacheKey('PRICE_SEPARATORS', $additionalParams);

        $cachedData = Mage::app()->loadCache($cacheKey);
        if (!$cachedData) {
            /** @var $algorithmModel Mage_Catalog_Model_Layer_Filter_Price_Algorithm */
            $algorithmModel = Mage::getSingleton('catalog/layer_filter_price_algorithm');
            $statistics = $this->getLayer()->getProductCollection()->getStats($this->_getFilterField());
            $statistics = $statistics[$this->_getFilterField()];

            $appliedInterval = $this->getInterval();
            if ($appliedInterval
                && ($statistics['count'] <= $this->getIntervalDivisionLimit()
                    || $appliedInterval[0] == $appliedInterval[1]
                    || $appliedInterval[1] === '0'
                )
            ) {
                $algorithmModel->setPricesModel($this)->setStatistics(0, 0, 0, 0);
                $this->_divisible = false;
            } else {
                if ($appliedInterval) {
                    $algorithmModel->setLimits($appliedInterval[0], $appliedInterval[1]);
                }
                $algorithmModel->setPricesModel($this)->setStatistics(
                    round($statistics['min'] * $this->getCurrencyRate(), 2),
                    round($statistics['max'] * $this->getCurrencyRate(), 2),
                    $statistics['stddev'] * $this->getCurrencyRate(),
                    $statistics['count']
                );
            }

            $cachedData = array();
            foreach ($algorithmModel->calculateSeparators() as $separator) {
                $cachedData[] = $separator['from'] . '-' . $separator['to'];
            }
            $cachedData = implode(',', $cachedData);

            $tags = $this->getLayer()->getStateTags();
            $tags[] = self::CACHE_TAG;
            Mage::app()->saveCache($cachedData, $cacheKey, $tags);
        }

        if (!$cachedData) {
            return array();
        }

        $cachedData = explode(',', $cachedData);
        foreach ($cachedData as $k => $v) {
            $cachedData[$k] = explode('-', $v);
        }

        return $cachedData;
    }

    /**
     * Prepare faceted value
     *
     * @param float $value
     * @param bool $decrease
     * @return float
     */
    protected function _prepareFacetedValue($value, $decrease = true) {
        // rounding issue
        if ($this->getCurrencyRate() > 1) {
            if ($decrease) {
                $value -= Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
            } else {
                $value += Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
            }
            $value /= $this->getCurrencyRate();
        } else {
            $value /= $this->getCurrencyRate();
            if ($decrease) {
                $value -= Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
            } else {
                $value += Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
            }
        }
        return round($value, 3);
    }

    /**
     * Prepare price range to be added to facet conditions
     *
     * @param string|float $from
     * @param string|float $to
     * @return array
     */
    protected function _prepareFacetRange($from, $to)
    {
        if (empty($from)) {
            $from = '*';
        }

        if ($to === '') {
            $to = '*';
        } else {
            if ($to == $from || ($to == 0 && $from == '*')) {
                $to = $this->_prepareFacetedValue($to, false);
            } else {
                $to = $this->_prepareFacetedValue($to);
            }
        }

        if ($from != '*') {
            $from = $this->_prepareFacetedValue($from);
        }
        return array('from' => $from, 'to' => $to);
    }

    /**
     * Add params to faceted search generated by algorithm
     *
     * @return Enterprise_Search_Model_Catalog_Layer_Filter_Price
     */
    protected function _addCalculatedFacetCondition()
    {
        $priceFacets = array();
        $this->_facets = array();
        foreach ($this->_getSeparators() as $separator) {
            $facetedRange = $this->_prepareFacetRange($separator[0], $separator[1]);
            $this->_facets[$facetedRange['from'] . '_' . $facetedRange['to']] = $separator;
            $priceFacets[] = $facetedRange;
        }
        $this->getLayer()->getProductCollection()->setFacetCondition($this->_getFilterField(), $priceFacets);
    }

    /**
     * Add params to faceted search
     *
     * @return Enterprise_Search_Model_Catalog_Layer_Filter_Price
     */
    public function addFacetCondition()
    {
        $calculation = Mage::app()->getStore()->getConfig(self::XML_PATH_RANGE_CALCULATION);
        if ($calculation == self::RANGE_CALCULATION_IMPROVED) {
            return $this->_addCalculatedFacetCondition();
        }

        $this->_facets = array();
        $range    = $this->getPriceRange();
        $maxPrice = $this->getMaxPriceInt();
        $priceFacets = array();
        /** @var $productCollection Enterprise_Search_Model_Resource_Collection */
        $productCollection = $this->getLayer()->getProductCollection();
        if ($maxPrice > 0) {
            if ($calculation == self::RANGE_CALCULATION_MANUAL
                || $range == $this->getLayer()->getCurrentCategory()->getFilterPriceRange()
            ) {
                $startPriceInterval = 0;
                $currentMaxPrice = 0;
                $facetCount = $this->getMaxIntervalsNumber();
                do {
                    for ($i = 0; $i < $facetCount; $i++) {
                        $separator = array(
                            $startPriceInterval + $i * $range,
                            $startPriceInterval + ($i + 1) * $range
                        );
                        $facetedRange = $this->_prepareFacetRange($separator[0], $separator[1]);
                        $this->_facets[$facetedRange['from'] . '_' . $facetedRange['to']] = $separator;
                        $priceFacets[] = $facetedRange;
                    }

                    $currentMaxPrice = $priceFacets[count($priceFacets) - 1]['to'];
                    $result = $productCollection->getPriceData($currentMaxPrice, null, 1);
                    $startPriceInterval = count($result) ? floor($result[0] / $range) * $range : $maxPrice;
                    if (($startPriceInterval && --$facetCount == 0) || $startPriceInterval == $maxPrice) {
                        $separator = array(
                            $startPriceInterval,
                            $maxPrice + $range
                        );
                        $facetedRange = $this->_prepareFacetRange($separator[0], $separator[1]);
                        $this->_facets[$facetedRange['from'] . '_' . $facetedRange['to']] = $separator;
                        $priceFacets[] = $facetedRange;
                        break;
                    }
                } while ($maxPrice > $currentMaxPrice);
            } else {
                $facetCount = ceil($maxPrice / $range);
                for ($i = 0; $i < $facetCount + 1; $i++) {
                    $separator = array($i * $range, ($i + 1) * $range);
                    $facetedRange = $this->_prepareFacetRange($separator[0], $separator[1]);
                    $this->_facets[$facetedRange['from'] . '_' . $facetedRange['to']] = $separator;
                    $priceFacets[] = $facetedRange;
                }
            }

            $productCollection->setFacetCondition($this->_getFilterField(), $priceFacets);
        } elseif ($maxPrice == 0) {
            $separator = array(0,1);
            $facetedRange = $this->_prepareFacetRange($separator[0], $separator[1]);
            $this->_facets[$facetedRange['from'] . '_' . $facetedRange['to']] = $separator;
            $priceFacets[] = $facetedRange;
            $productCollection->setFacetCondition($this->_getFilterField(), $priceFacets);
        }

        return $this;
    }

    /**
     * Apply filter value to product collection based on filter range and selected value
     *
     * @deprecated since 1.12.0.0
     * @param int $range
     * @param int $index
     * @return Mage_Catalog_Model_Layer_Filter_Price
     */
    protected function _applyToCollection($range, $index)
    {
        $to = $range * $index;
        if ($to < $this->getMaxPriceInt()) {
            $to -= Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
        }

        $value = array(
            $this->_getFilterField() => array(
                'from' => ($range * ($index - 1)),
                'to'   => $to
            )
        );

        $this->getLayer()->getProductCollection()->addFqFilter($value);

        return $this;
    }

    /**
     * Apply price range filter to collection
     *
     * @return Mage_Catalog_Model_Layer_Filter_Price
     */
    protected function _applyPriceRange()
    {
        list($from, $to) = $this->getInterval();
        $this->getLayer()->getProductCollection()->addFqFilter(array(
            $this->_getFilterField() => $this->_prepareFacetRange($from, $to)
        ));

        return $this;
    }

    /**
     * Get comparing value according to currency rate
     *
     * @param float|null $value
     * @param bool $decrease
     * @return float|null
     */
    protected function _prepareComparingValue($value, $decrease = true)
    {
        if (is_null($value)) {
            return $value;
        }

        if ($decrease) {
            $value -= Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 2;
        } else {
            $value += Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 2;
        }

        $value /= $this->getCurrencyRate();
        if ($value < 0) {
            $value = null;
        }

        return $value;
    }

    /**
     * Load range of product prices
     *
     * @param int $limit
     * @param null|int $offset
     * @param null|int $lowerPrice
     * @param null|int $upperPrice
     * @return array|false
     */
    public function loadPrices($limit, $offset = null, $lowerPrice = null, $upperPrice = null)
    {
        $lowerPrice = $this->_prepareComparingValue($lowerPrice);
        $upperPrice = $this->_prepareComparingValue($upperPrice);
        if (!is_null($upperPrice)) {
            $upperPrice -= Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
        }
        $result = $this->getLayer()->getProductCollection()->getPriceData($lowerPrice, $upperPrice, $limit, $offset);
        if (!$result) {
            return $result;
        }
        foreach ($result as &$v) {
            $v = round((float)$v * $this->getCurrencyRate(), 2);
        }
        return $result;
    }

    /**
     * Load range of product prices, preceding the price
     *
     * @param float $price
     * @param int $index
     * @param null|int $lowerPrice
     * @return array|false
     */
    public function loadPreviousPrices($price, $index, $lowerPrice = null)
    {
        $originLowerPrice = $lowerPrice;
        $lowerPrice = $this->_prepareComparingValue($lowerPrice);
        $price = $this->_prepareComparingValue($price);
        if (!is_null($price)) {
            $price -= Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
        }
        $countLess = $this->getLayer()->getProductCollection()->getPriceData($lowerPrice, $price, null, null, true);
        if (!$countLess) {
            return false;
        }

        return $this->loadPrices($index - $countLess + 1, $countLess - 1, $originLowerPrice);
    }

    /**
     * Load range of product prices, next to the price
     *
     * @param float $price
     * @param int $rightIndex
     * @param null|int $upperPrice
     * @return array|false
     */
    public function loadNextPrices($price, $rightIndex, $upperPrice = null)
    {
        $lowerPrice = $this->_prepareComparingValue($price);
        $price = $this->_prepareComparingValue($price, false);
        $upperPrice = $this->_prepareComparingValue($upperPrice);
        if (!is_null($price)) {
            $price += Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
        }
        if (!is_null($upperPrice)) {
            $upperPrice -= Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
        }
        $countGreater = $this->getLayer()->getProductCollection()->getPriceData($price, $upperPrice, null, null, true);
        if (!$countGreater) {
            return false;
        }

        $result = $this->getLayer()->getProductCollection()->getPriceData(
            $lowerPrice,
            $upperPrice,
            $rightIndex - $countGreater + 1,
            $countGreater - 1,
            false,
            'desc'
        );
        if (!$result) {
            return $result;
        }
        foreach ($result as &$v) {
            $v = round((float)$v * $this->getCurrencyRate(), 2);
        }
        return $result;
    }
}
