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
 * TargetRule Checkout Cart Cross-Sell Products Block
 *
 * @category   Enterprise
 * @package    Enterprise_TargetRule
 */
class Enterprise_TargetRule_Block_Checkout_Cart_Crosssell extends Enterprise_TargetRule_Block_Product_Abstract
{
    /**
     * Default MAP renderer type
     *
     * @var string
     */
    protected $_mapRenderer = 'msrp_item';

    /**
     * Array of product objects in cart
     *
     * @var array
     */
    protected $_products;

    /**
     * object of just added product to cart
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_lastAddedProduct;

    /**
     * Whether get products by last added
     *
     * @var bool
     */
    protected $_byLastAddedProduct = false;

    /**
     * Retrieve Catalog Product List Type identifier
     *
     * @return int
     */
    public function getProductListType()
    {
        return Enterprise_TargetRule_Model_Rule::CROSS_SELLS;
    }

    /**
     * Retrieve just added to cart product id
     *
     * @return int|false
     */
    public function getLastAddedProductId()
    {
        return Mage::getSingleton('checkout/session')->getLastAddedProductId(true);
    }

    /**
     * Retrieve just added to cart product object
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getLastAddedProduct()
    {
        if (is_null($this->_lastAddedProduct)) {
            $productId = $this->getLastAddedProductId();
            if ($productId) {
                $this->_lastAddedProduct = Mage::getModel('catalog/product')
                    ->load($productId);
            } else {
                $this->_lastAddedProduct = false;
            }
        }
        return $this->_lastAddedProduct;
    }

    /**
     * Retrieve quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Retrieve Array of Product instances in Cart
     *
     * @return array
     */
    protected function _getCartProducts()
    {
        if (is_null($this->_products)) {
            $this->_products = array();
            foreach ($this->getQuote()->getAllItems() as $quoteItem) {
                /* @var $quoteItem Mage_Sales_Model_Quote_Item */
                $product = $quoteItem->getProduct();
                $this->_products[$product->getEntityId()] = $product;
            }
        }

        return $this->_products;
    }

    /**
     * Retrieve Array of product ids in Cart
     *
     * @return array
     */
    protected function _getCartProductIds()
    {
        $products = $this->_getCartProducts();
        return array_keys($products);
    }

    /**
     * Retrieve Array of product ids which have special relation with products in Cart
     * For example simple product as part of Grouped product
     *
     * @return array
     */
    protected function _getCartProductIdsRel()
    {
        $productIds = array();
        foreach ($this->getQuote()->getAllItems() as $quoteItem) {
            $productTypeOpt = $quoteItem->getOptionByCode('product_type');
            if ($productTypeOpt instanceof Mage_Sales_Model_Quote_Item_Option
                && $productTypeOpt->getValue() == Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE
                && $productTypeOpt->getProductId()
            ) {
                $productIds[] = $productTypeOpt->getProductId();
            }
        }

        return $productIds;
    }

    /**
     * Retrieve TargetRule data helper
     *
     * @return Enterprise_TargetRule_Helper_Data
     */
    public function getTargetRuleHelper()
    {
        return Mage::helper('enterprise_targetrule');
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
     * Retrieve Maximum Number Of Product
     *
     * @return int
     */
    public function getPositionLimit()
    {
        return $this->getTargetRuleHelper()->getMaximumNumberOfProduct(Enterprise_TargetRule_Model_Rule::CROSS_SELLS);
    }

    /**
     * Retrieve Position Behavior
     *
     * @return int
     */
    public function getPositionBehavior()
    {
        return $this->getTargetRuleHelper()->getShowProducts(Enterprise_TargetRule_Model_Rule::CROSS_SELLS);
    }

    /**
     * Get link collection for cross-sell
     *
     * @throws Mage_Core_Exception
     * @return Mage_Catalog_Model_Resource_Product_Collection|null
     */
    protected function _getTargetLinkCollection()
    {
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection */
        $collection = Mage::getModel('catalog/product_link')
            ->useCrossSellLinks()
            ->getProductCollection()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setGroupBy();
        $this->_addProductAttributesAndPrices($collection);

        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInSiteFilterToCollection($collection);

        Mage::getSingleton('cataloginventory/stock_status')
            ->addIsInStockFilterToCollection($collection);

        return $collection;
    }

    /**
     * Retrieve array of cross-sell products for just added product to cart
     *
     * @return array
     */
    protected function _getProductsByLastAddedProduct()
    {
        $product = $this->getLastAddedProduct();
        if (!$product) {
            return array();
        }
        $this->_byLastAddedProduct = true;
        $items = parent::getItemCollection();
        $this->_byLastAddedProduct = false;
        $this->_items = null;
        return $items;
    }

    /**
     * Retrieve Product Ids from Cross-sell rules based products index by product object
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $limit
     * @param array $excludeProductIds
     * @return array
     */
    protected function _getProductIdsFromIndexByProduct($product, $count, $excludeProductIds = array())
    {
        return $this->_getTargetRuleIndex()
            ->setType(Enterprise_TargetRule_Model_Rule::CROSS_SELLS)
            ->setLimit($count)
            ->setProduct($product)
            ->setExcludeProductIds($excludeProductIds)
            ->getProductIds();
    }

    /**
     * Retrieve Product Collection by Product Ids
     *
     * @param array $productIds
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected function _getProductCollectionByIds($productIds)
    {
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addFieldToFilter('entity_id', array('in' => $productIds));
        $this->_addProductAttributesAndPrices($collection);

        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInCatalogFilterToCollection($collection);

        Mage::getSingleton('cataloginventory/stock_status')
            ->addIsInStockFilterToCollection($collection);

        return $collection;
    }

    /**
     * Retrieve Product Ids from Cross-sell rules based products index by products in shopping cart
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $limit
     * @param array $excludeProductIds
     * @return array
     */
    protected function _getProductIdsFromIndexForCartProducts($limit, $excludeProductIds = array())
    {
        $resultIds = array();

        foreach ($this->_getCartProducts() as $product) {
            if ($product->getEntityId() == $this->getLastAddedProductId()) {
                continue;
            }

            $productIds = $this
                ->_getProductIdsFromIndexByProduct($product, $this->getPositionLimit(), $excludeProductIds);
            $resultIds = array_merge($resultIds, $productIds);
        }

        $resultIds = array_unique($resultIds);
        shuffle($resultIds);

        return array_slice($resultIds, 0, $limit);
    }

    /**
     * Get exclude product ids
     *
     * @return array
     */
    protected function _getExcludeProductIds()
    {
        $excludeProductIds = $this->_getCartProductIds();
        if (!is_null($this->_items)) {
            $excludeProductIds = array_merge(array_keys($this->_items), $excludeProductIds);
        }
        return $excludeProductIds;
    }

    /**
     * Get target rule based products for cross-sell
     *
     * @return array
     */
    protected function _getTargetRuleProducts()
    {
        $excludeProductIds = $this->_getExcludeProductIds();
        $limit = $this->getPositionLimit();
        $productIds = $this->_byLastAddedProduct
            ? $this->_getProductIdsFromIndexByProduct($this->getLastAddedProduct(), $limit, $excludeProductIds)
            : $this->_getProductIdsFromIndexForCartProducts($limit, $excludeProductIds);

        $items = array();
        if ($productIds) {
            $collection = $this->_getProductCollectionByIds($productIds);
            foreach ($collection as $product) {
                $items[$product->getEntityId()] = $product;
            }
        }

        return $items;
    }

    /**
     * Get linked products
     *
     * @return array
     */
    protected function _getLinkProducts()
    {
        $items = array();
        $collection = $this->getLinkCollection();
        if ($collection) {
            if ($this->_byLastAddedProduct) {
                $collection->addProductFilter($this->getLastAddedProduct()->getEntityId());
            } else {
                $filterProductIds = array_merge($this->_getCartProductIds(), $this->_getCartProductIdsRel());
                $collection->addProductFilter($filterProductIds);
            }
            $collection->addExcludeProductFilter($this->_getExcludeProductIds());

            foreach ($collection as $product) {
                $items[$product->getEntityId()] = $product;
            }
        }
        return $items;
    }

    /**
     * Retrieve array of cross-sell products
     *
     * @return array
     */
    public function getItemCollection()
    {
        if (is_null($this->_items)) {
            // if has just added product to cart - load cross-sell products for it
            $productsByLastAdded = $this->_getProductsByLastAddedProduct();
            $limit = $this->getPositionLimit();
            if (count($productsByLastAdded) < $limit) {
                // reset collection
                $this->_linkCollection = null;
                parent::getItemCollection();
                // products by last added are preferable
                $this->_items = $productsByLastAdded + $this->_items;
                $this->_sliceItems();
            } else {
                $this->_items = $productsByLastAdded;
            }
            $this->_orderProductItems();
        }
        return $this->_items;
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
     * Retrieve linked as cross-sell product collection
     *
     * @deprecated after 1.11.0.2 use getLinkCollection() instead
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection
     */
    protected function _getLinkCollection()
    {
        return $this->getLinkCollection();
    }
}
