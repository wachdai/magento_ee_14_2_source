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
 * Enterprise search index resource model
 *
 * @category   Enterprise
 * @package    Enterprise_Search
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Enterprise_Search_Model_Resource_Index extends Mage_CatalogSearch_Model_Resource_Fulltext
{
    /**
     * Define product count processed at one iteration
     *
     * @deprecated after 1.11.0.0
     *
     * @var int
     */
    protected $_limit = 100;





    /**
     * Return array of price data per customer and website by products
     *
     * @param   null|array $productIds
     * @return  array
     */
    protected function _getCatalogProductPriceData($productIds = null)
    {
        $adapter = $this->_getWriteAdapter();

        $select = $adapter->select()
            ->from($this->getTable('catalog/product_index_price'),
                array('entity_id', 'customer_group_id', 'website_id', 'min_price'));

        if ($productIds) {
            $select->where('entity_id IN (?)', $productIds);
        }

        $result = array();
        foreach ($adapter->fetchAll($select) as $row) {
            $result[$row['website_id']][$row['entity_id']][$row['customer_group_id']] = round($row['min_price'], 2);
        }

        return $result;
    }

    /**
     * Retrieve price data for product
     *
     * @param   $productIds
     * @param   $storeId
     *
     * @return  array
     */
    public function getPriceIndexData($productIds, $storeId)
    {
        $priceProductsIndexData = $this->_getCatalogProductPriceData($productIds);

        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        if (!isset($priceProductsIndexData[$websiteId])) {
            return array();
        }

        return $priceProductsIndexData[$websiteId];
    }

    /**
     * Prepare system index data for products.
     *
     * @param   int $storeId
     * @param   int|array|null $productIds
     *
     * @return  array
     */
    public function getCategoryProductIndexData($storeId = null, $productIds = null)
    {
        $adapter = $this->_getWriteAdapter();

        $select = $adapter->select()
            ->from(
                array($this->getTable('catalog/category_product_index')),
                array(
                    'category_id',
                    'product_id',
                    'position',
                    'store_id'
                )
            )
            ->where('store_id = ?', $storeId);

        if ($productIds) {
            $select->where('product_id IN (?)', $productIds);
        }

        $result = array();
        foreach ($adapter->fetchAll($select) as $row) {
            $result[$row['product_id']][$row['category_id']] = $row['position'];
        }

        return $result;
    }

    /**
     * Retrieve moved categories product ids
     *
     * @param   int $categoryId
     * @return  array
     */
    public function getMovedCategoryProductIds($categoryId)
    {
        $adapter = $this->_getWriteAdapter();

        $select = $adapter->select()
            ->distinct()
            ->from(
                array('c_p' => $this->getTable('catalog/category_product')),
                array('product_id')
            )
            ->join(
                array('c_e' => $this->getTable('catalog/category')),
                'c_p.category_id = c_e.entity_id',
                array()
            )
            ->where($adapter->quoteInto('c_e.path LIKE ?', '%/' . $categoryId . '/%'))
            ->orWhere('c_p.category_id = ?', $categoryId);

        return $adapter->fetchCol($select);
    }





    // Deprecated methods

    /**
     * Update category products indexes.
     *
     * @deprecated after 1.11.0.0
     *
     * @param   array $productIds
     * @return  Enterprise_Search_Model_Resource_Index
     */
    public function updateCategoryIndexData($productIds)
    {
        return $this;
    }

    /**
     * Update category products price index
     *
     * @deprecated after 1.11.0.0
     *
     * @return Enterprise_Search_Model_Resource_Index
     */
    public function updatePriceIndexData()
    {
        foreach (Mage::app()->getStores(false) as $store) {
            $index = $this->_getCatalogProductPriceData();
            foreach (array_chunk($index, $this->_limit, true) as $indexPart) {
                $this->_engine->saveEntityIndexes($store->getId(), $indexPart, 'product');
            }
        }

        return $this;
    }

    /**
     * Return array of category, position and visibility data (optionally) for products.
     *
     * @deprecated after 1.11.2.0
     * @see $this->getSystemProductIndexData()
     *
     * @param   int $storeId
     * @param   null|array $productIds
     * @param   bool $visibility
     *
     * @return  array
     */
    protected function _getCatalogCategoryData($storeId, $productIds = null, $visibility = true)
    {
        $adapter = $this->_getWriteAdapter();
        $prefix  = $this->_engine->getFieldsPrefix();

        $columns = array(
            'product_id' => 'product_id',
        );

        if ($visibility) {
            $columns[] = 'visibility';
        }

        $select = $adapter->select()
            ->from(array($this->getTable('catalog/category_product_index')), $columns)
            ->where('product_id IN (?)', $productIds)
            ->where('store_id = ?', $storeId)
            ->group('product_id');

        $helper = Mage::getResourceHelper('core');
        $helper->addGroupConcatColumn($select, 'parents', 'category_id', ' ', ',', 'is_parent = 1');
        $helper->addGroupConcatColumn($select, 'anchors', 'category_id', ' ', ',', 'is_parent = 0');
        $helper->addGroupConcatColumn($select, 'positions', array('category_id', 'position'), ' ', '_');
        $select  = $helper->getQueryUsingAnalyticFunction($select);

        $result = array();
        foreach ($adapter->fetchAll($select) as $row) {
            $data = array(
                $prefix . 'categories'          => array_filter(explode(' ', $row['parents'])),
                $prefix . 'show_in_categories'  => array_filter(explode(' ', $row['anchors'])),
            );
            foreach (explode(' ', $row['positions']) as $value) {
                list($categoryId, $position) = explode('_', $value);
                $key = sprintf('%sposition_category_%d', $prefix, $categoryId);
                $data[$key] = $position;
            }
            if ($visibility) {
                $data[$prefix . 'visibility'] = $row['visibility'];
            }

            $result[$row['product_id']] = $data;
        }

        return $result;
    }

    /**
     * Prepare advanced index for products.
     *
     * @deprecated after 1.11.2.0
     *
     * @param   array $index
     * @param   int $storeId
     * @param   array|null $productIds
     *
     * @return  array
     */
    public function addAdvancedIndex($index, $storeId, $productIds = null)
    {
        if (is_null($productIds) || !is_array($productIds)) {
            $productIds = array();
            foreach ($index as $productData) {
                $productIds[] = $productData['entity_id'];
            }
        }

        $prefix         = $this->_engine->getFieldsPrefix();
        $categoryData   = $this->_getCatalogCategoryData($storeId, $productIds, true);
        $priceData      = $this->_getCatalogProductPriceData($productIds);

        foreach ($index as &$productData) {
            $productId = $productData['entity_id'];
            if (isset($categoryData[$productId]) && isset($priceData[$productId])) {
                $productData += $categoryData[$productId];
                $productData += $priceData[$productId];
            } else {
                $productData += array(
                    $prefix . 'categories'          => array(),
                    $prefix . 'show_in_categories'  => array(),
                    $prefix . 'visibility'          => 0
                );
            }
        }

        return $index;
    }
}
