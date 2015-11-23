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
 * @package     Enterprise_TargetRule
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * TargetRule Catalog Product List Abstract Block
 *
 * @category   Enterprise
 * @package    Enterprise_TargetRule
 */
abstract class Enterprise_TargetRule_Block_Catalog_Product_List_Abstract
    extends Enterprise_TargetRule_Block_Product_Abstract
{
    /**
     * TargetRule Index instance
     *
     * @var Enterprise_TargetRule_Model_Index
     */
    protected $_index;

    /**
     * Array of exclude Product Ids
     *
     * @var array
     */
    protected $_excludeProductIds;

    /**
     * Array of all product ids in list
     *
     * @var null|array
     */
    protected $_allProductIds = null;

    /**
     * Retrieve current product instance (if actual and available)
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }

    /**
     * Retrieve Catalog Product List Type prefix
     * without last underscore
     *
     * @return string
     */
    protected function _getTypePrefix()
    {
        switch ($this->getProductListType()) {
            case Enterprise_TargetRule_Model_Rule::RELATED_PRODUCTS:
                $prefix = 'related';
                break;

            case Enterprise_TargetRule_Model_Rule::UP_SELLS:
                $prefix = 'upsell';
                break;

            default:
                Mage::throwException(
                    Mage::helper('enterprise_targetrule')->__('Undefined Catalog Product List Type')
                );
        }
        return $prefix;
    }

    /**
     * Retrieve Target Rule Index instance
     *
     * @return Enterprise_TargetRule_Model_Index
     */
    protected function _getTargetRuleIndex()
    {
        if (is_null($this->_index)) {
            $this->_index = Mage::getModel('enterprise_targetrule/index');
        }
        return $this->_index;
    }

    /**
     * Retrieve position limit product attribute name
     *
     * @return string
     */
    protected function _getPositionLimitField()
    {
        return sprintf('%s_tgtr_position_limit', $this->_getTypePrefix());
    }

    /**
     * Retrieve position behavior product attribute name
     *
     * @return string
     */
    protected function _getPositionBehaviorField()
    {
        return sprintf('%s_tgtr_position_behavior', $this->_getTypePrefix());
    }

    /**
     * Retrieve Maximum Number Of Product
     *
     * @return int
     */
    public function getPositionLimit()
    {
        $limit = $this->getProduct()->getData($this->_getPositionLimitField());
        if (is_null($limit)) { // use configuration settings
            $limit = $this->getTargetRuleHelper()->getMaximumNumberOfProduct($this->getProductListType());
            $this->getProduct()->setData($this->_getPositionLimitField(), $limit);
        }
        return $this->getTargetRuleHelper()->getMaxProductsListResult($limit);
    }

    /**
     * Retrieve Position Behavior
     *
     * @return int
     */
    public function getPositionBehavior()
    {
        $behavior = $this->getProduct()->getData($this->_getPositionBehaviorField());
        if (is_null($behavior)) { // use configuration settings
            $behavior = $this->getTargetRuleHelper()->getShowProducts($this->getProductListType());
            $this->getProduct()->setData($this->_getPositionBehaviorField(), $behavior);
        }
        return $behavior;
    }

    /**
     * Retrieve array of exclude product ids
     *
     * @return array
     */
    public function getExcludeProductIds()
    {
        if (is_null($this->_excludeProductIds)) {
            $this->_excludeProductIds = array($this->getProduct()->getEntityId());
        }
        return $this->_excludeProductIds;
    }

    /**
     * Get link collection with limit parameter
     *
     * @throws Mage_Core_Exception
     * @param null|int $limit
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection|null
     */
    protected function _getPreparedTargetLinkCollection($limit = null)
    {
        $linkCollection = null;
        switch ($this->getProductListType()) {
            case Enterprise_TargetRule_Model_Rule::RELATED_PRODUCTS:
                $linkCollection = $this->getProduct()
                    ->getRelatedProductCollection();
                break;

            case Enterprise_TargetRule_Model_Rule::UP_SELLS:
                $linkCollection = $this->getProduct()
                    ->getUpSellProductCollection();
                break;

            default:
                Mage::throwException(
                    Mage::helper('enterprise_targetrule')->__('Undefined Catalog Product List Type')
                );
        }

        if (!is_null($limit)) {
            $this->_addProductAttributesAndPrices($linkCollection);
            $linkCollection->setPageSize($limit);
        }

        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInCatalogFilterToCollection($linkCollection);
        $linkCollection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $linkCollection->setFlag('do_not_use_category_id', true);

        $excludeProductIds = $this->getExcludeProductIds();
        if ($excludeProductIds) {
            $linkCollection->addAttributeToFilter('entity_id', array('nin' => $excludeProductIds));
        }

        return $linkCollection;
    }

    /**
     * Get link collection for related and up-sell
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection|null
     */
    protected function _getTargetLinkCollection()
    {
        return $this->_getPreparedTargetLinkCollection($this->getPositionLimit());
    }

    /**
     * Retrieve count of related linked products assigned to product
     *
     * @return int
     */
    public function getLinkCollectionCount()
    {
        return count($this->getLinkCollection()->getItems());
    }

    /**
     * Get target rule collection ids
     *
     * @param null|int $limit
     * @return array
     */
    protected function _getTargetRuleProductIds($limit = null)
    {
        $excludeProductIds = $this->getExcludeProductIds();
        if (!is_null($this->_items)) {
            $excludeProductIds = array_merge(array_keys($this->_items), $excludeProductIds);
        }
        $indexModel = $this->_getTargetRuleIndex()
            ->setType($this->getProductListType())
            ->setProduct($this->getProduct())
            ->setExcludeProductIds($excludeProductIds);
        if (!is_null($limit)) {
            $indexModel->setLimit($limit);
        }

        return $indexModel->getProductIds();
    }

    /**
     * Get target rule collection for related and up-sell
     *
     * @return array
     */
    protected function _getTargetRuleProducts()
    {
        $limit = $this->getPositionLimit();

        $productIds = $this->_getTargetRuleProductIds($limit);

        $items = array();
        if ($productIds) {
            $items = $this->_getTargetRuleProductsByIds($productIds, $limit);
        }

        return $items;
    }

    /**
     * Get target rule products by ids
     *
     * @param array $ids
     * @param int $limit
     * @return array
     */
    protected function _getTargetRuleProductsByIds(array $ids, $limit)
    {
        /** @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addFieldToFilter('entity_id', array('in' => $ids));
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addProductAttributesAndPrices($collection);

        $collection->setFlag('is_link_collection', true);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        $collection->setPageSize($limit)->setFlag('do_not_use_category_id', true);
        $items = array();
        foreach ($collection as $item) {
            $items[$item->getEntityId()] = $item;
        }
        return $items;
    }

    /**
     * Check is has items
     *
     * @return bool
     */
    public function hasItems()
    {
        return $this->getItemsCount() > 0;
    }

    /**
     * Retrieve count of product in collection
     *
     * @return int
     */
    public function getItemsCount()
    {
        return count($this->getItemCollection());
    }

    /**
     * Get ids of all assigned products
     *
     * @return array
     */
    public function getAllIds()
    {
        if (is_null($this->_allProductIds)) {
            if (!$this->isShuffled()) {
                $this->_allProductIds = array_keys($this->getItemCollection());
                return $this->_allProductIds;
            }

            $limit = $this->getTargetRuleHelper()->getMaxProductsListResult();
            $productIds = $this->_getTargetRuleProductIds($limit);
            $targetRuleProducts = $this->_getTargetRuleProductsByIds($productIds, $limit);
            $targetRuleProductIds = array();
            foreach ($targetRuleProducts as $product) {
                $targetRuleProductIds[] = $product->getId();
            }

            $linkProductCollection = $this->_getPreparedTargetLinkCollection($limit);
            $linkProductIds = array();
            foreach ($linkProductCollection as $item) {
                $linkProductIds[] = $item->getEntityId();
            }

            // to ensure that all displayed items are included to ids for shuffle
            $currentItemCollectionIds = array();
            foreach ($this->getItemCollection() as $item) {
                $currentItemCollectionIds[] = $item->getId();
            }

            $this->_allProductIds = array_unique(array_merge(
                $targetRuleProductIds,
                $linkProductIds,
                $currentItemCollectionIds
            ));
            shuffle($this->_allProductIds);
        }

        return $this->_allProductIds;
    }

    /**
     * Retrieve block cache tags
     *
     * @return array
     */
    public function getCacheTags()
    {
        $tags = parent::getCacheTags();
        $ids = $this->getAllIds();
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $tags[] = Mage_Catalog_Model_Product::CACHE_TAG . '_' . $id;
            }
        }
        return $tags;
    }
}
