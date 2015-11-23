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
 * @package     Enterprise_GoogleAnalyticsUniversal
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Class Enterprise_GoogleAnalyticsUniversal_Block_List_Json
 * @method string getStepName()
 * @method Mage_Sales_Model_Quote setStepName(string $value)
 * @method string getListType()
 * @method Mage_Sales_Model_Quote setListType(string $value)
 * @method string getBlockName()
 * @method Mage_Sales_Model_Quote setBlockName(string $value)
 * @method boolean getShowCategory()
 * @method Mage_Sales_Model_Quote setShowCategory(boolean $value)
 * @method Enterprise_TargetRule_Block_Catalog_Product_Item getFpcBlock()
 * @method Mage_Sales_Model_Quote setFpcBlock(Enterprise_TargetRule_Block_Catalog_Product_Item $value)
 */
class Enterprise_GoogleAnalyticsUniversal_Block_List_Json extends Mage_Core_Block_Template
{
    /**
     * Catalog Product collection
     *
     * @var Mage_Catalog_Model_Resource_Collection_Abstract
     */
    protected $_productCollection;

    /**
     * Array of blocks of Enterprise_Banner_Block_Widget_Banner type once they are created
     *
     * @var array
     */
    protected $_bannerBlocks = array();

    /**
     * Variable is used to turn on/off the output of _getProductCollection for cross-sells
     *
     * @var bool
     */
    protected $_showCrossSells = true;

    /**
     * Keeps collection of banners with GA related data included
     * null|Enterprise_Banner_Model_Resource_Banner_Collection
     * @var null
     */
    protected $_bannerCollection = null;

    /**
     * Render GA tracking scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('enterprise_googleanalyticsuniversal')->isTagManagerAvailable()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Returns an instance of an assigned block via a layout update file
     *
     * @return Mage_Catalog_Block_Product_List | Enterprise_TargetRule_Block_Checkout_Cart_Crosssell | Mage_Checkout_Block_Cart | Mage_Catalog_Block_Product_List_Related
     */
    public function getListBlock()
    {
        return $this->getLayout()->getBlock($this->getBlockName());
    }

    /**
     * Set a variable to false to hide cross-sell items for an empty cart
     */
    public function checkCartItems()
    {
        if (!Mage::helper('checkout/cart')->getItemsCount()) {
            $this->_showCrossSells = false;
        }
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Catalog_Model_Resource_Collection_Abstract | null
     */
    protected function _getProducts()
    {
        /** @var Mage_Catalog_Model_Category $category */
        $category = $this->getCurrentCategory();
        if ($category && ($category->getDisplayMode() == Mage_Catalog_Model_Category::DM_MIXED ||
            $category->getDisplayMode() == Mage_Catalog_Model_Category::DM_PRODUCT)) {
            return $this->_getProductCollection();
        }
        return null;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Catalog_Model_Resource_Collection_Abstract | null
     */
    protected function _getProductCollection()
    {
        /* For catalog list and search results
         * Expects getListBlock as Mage_Catalog_Block_Product_List
         */
        if (is_null($this->_productCollection)) {
            $this->_productCollection = $this->getListBlock()->getLoadedProductCollection();
        }

        /* For collections of cross/up-sells and related
         * Expects getListBlock as one of the following:
         * Enterprise_TargetRule_Block_Catalog_Product_List_Upsell | _linkCollection
         * Enterprise_TargetRule_Block_Catalog_Product_List_Related | _items
         * Enterprise_TargetRule_Block_Checkout_Cart_Crosssell | _items
         * Mage_Catalog_Block_Product_List_Related | _itemCollection
         * Mage_Catalog_Block_Product_List_Upsell | _itemCollection
         * Mage_Checkout_Block_Cart_Crosssell, | setter items
         */
        if ($this->_showCrossSells && is_null($this->_productCollection)) {
            $this->_productCollection = $this->getListBlock()->getItemCollection();
        }

        // Support for CE
        if (is_null($this->_productCollection)
            && ($this->getBlockName() == 'catalog.product.related'
                || $this->getBlockName() == 'checkout.cart.crosssell'))
        {
            $this->_productCollection = $this->getListBlock()->getItems();
        }

        return $this->_productCollection;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Catalog_Model_Resource_Collection_Abstract | null
     */
    public function getLoadedProductCollection()
    {
        //return $this->_getProductsWithReflection();
        return $this->_getProducts();
    }

    /**
     * Retrieves a current category
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory()
    {
        /** @var Mage_Catalog_Model_Category $category */
        $category = null;

        if (Mage::getSingleton('catalog/layer')) {
            $category = Mage::getSingleton('catalog/layer')->getCurrentCategory();
        } else if(Mage::registry('current_category')){
            $category = Mage::registry('current_category');
        }
        return $category;
    }

    /**
     * Retrieves name of the current category
     *
     * @return string
     */
    public function getCurrentCategoryName()
    {
        if (!$this->getShowCategory()) {
            return '';
        }
        /** @var Mage_Catalog_Model_Category $category */
        $category = $this->getCurrentCategory();

        if ($category && Mage::app()->getStore()->getRootCategoryId() != $category->getId()) {
            return $category->getName();
        }
        return '';
    }

    /**
     * Retrieves name of the current list assigned via layout update
     *
     * @return string
     */
    public function getCurrentListName()
    {
        $listName = '';
        if (strlen($this->getListType())) {
            switch ($this->getListType()) {
                case 'catalog' :
                    $listName = Mage::getStoreConfig(
                        Enterprise_GoogleAnalyticsUniversal_Helper_Data::XML_PATH_LIST_CATALOG_PAGE
                    );
                    break;
                case 'search' :
                    $listName = Mage::getStoreConfig(
                        Enterprise_GoogleAnalyticsUniversal_Helper_Data::XML_PATH_LIST_SEARCH_PAGE
                    );
                    break;
                case 'related' :
                    $listName = Mage::getStoreConfig(
                        Enterprise_GoogleAnalyticsUniversal_Helper_Data::XML_PATH_LIST_RELATED_BLOCK
                    );
                    break;
                case 'upsell' :
                    $listName = Mage::getStoreConfig(
                        Enterprise_GoogleAnalyticsUniversal_Helper_Data::XML_PATH_LIST_UPSELL_BLOCK
                    );
                    break;
                case 'crosssell' :
                    $listName = Mage::getStoreConfig(
                        Enterprise_GoogleAnalyticsUniversal_Helper_Data::XML_PATH_LIST_CROSSSELL_BLOCK
                    );
                    break;
            }
        }
        return $listName;
    }

    /**
     * @param $block Enterprise_Banner_Block_Widget_Banner
     * @return $this
     */
    public function appendBannerBlock($block)
    {
        $this->_bannerBlocks[] = $block;
        return $this;
    }

    /**
     * Returns a collection of banners by rendered ids
     *
     * @return null|Enterprise_Banner_Model_Resource_Banner_Collection
     */
    public function getBannerCollection()
    {
        if ($this->_bannerCollection != null) {
            return $this->_bannerCollection;
        }
        $bannerIds = array();
        foreach($this->_bannerBlocks as $block) {
            /** @var  $block Enterprise_Banner_Block_Widget_Banner*/
            $params = $block->renderAndGetInfo();
            if (count($params['params']['renderedBannerIds'])) {
                $bannerIds = array_merge($bannerIds, $params['params']['renderedBannerIds']);
                $bannerIds = array_unique($bannerIds);
            }
        }
        if (count($bannerIds)) {
            $this->_bannerCollection = Mage::getResourceModel('enterprise_banner/banner_collection')
                ->addBannerIdsFilter($bannerIds);
        }
        return $this->_bannerCollection;
    }

    /**
     * Returns banner position defined by SPEC as Current page (controller handler)
     * @return string
     */
    public function getBannerPosition()
    {
        /** @var Mage_Core_Controller_Varien_Action $action */
        $action = Mage::app()->getFrontController()->getAction();
        if ($action) {
            return $action->getFullActionName();
        }
        return '';
    }

    /**
     * Mapping of checkout steps to numbers for both simple and multishipping checkout
     *
     * @return int
     */
    protected function getStepNumber()
    {
        $steps = array(
            'login'     => 1,

            'billing'   => 2,
            'shipping'  => 3,
            'shipping_method' => 4,
            'payment'   => 5,
            'review'    => 6,

            'addresses' => 2,
            'multishipping' => 3,
            'multibilling'  => 4,
            'multireview' => 5
        );

        /** stepName is set in layout file */
        if ($this->getStepName() && array_key_exists($this->getStepName(), $steps)) {
            return $steps[$this->getStepName()];
        }
        return 0;
    }

    public function detectStepName()
    {
        $stepName = $this->isCustomerLoggedIn() ? 'billing' : 'login';
        $this->setStepName($stepName);
    }

    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Generates json array of all products in the cart for javascript on each checkout step
     *
     * @return string
     */
    public function getCartContent()
    {
        $cart = array();
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            $cart[]= $this->_formatProduct($item);
        }
        return Mage::helper('core')->jsonEncode($cart);
    }

    /**
     * Generates json array of all products in the cart for javascript on each checkout step
     *
     * @return string
     */
    public function getCartContentForUpdate()
    {
        $cart = array();
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            $cart[$item->getSku()]= $this->_formatProduct($item);
        }
        return Mage::helper('core')->jsonEncode($cart);
    }

    /**
     * Format product item for output to json
     *
     * @param $item Mage_Sales_Model_Quote_Item
     * @return array
     */
    protected function _formatProduct($item)
    {
        $product = array();
        $product['id'] = $item->getSku();
        $product['name'] = $item->getName();
        $product['price'] = $item->getPrice();
        $product['qty'] = $item->getQty();
        return $product;
    }

    /**
     * Retrieve loaded category collection checking via reflection if the block's collection was populated
     * Was abandoned in behalf of $category->getDisplayMode()
     *
     * @return Mage_Catalog_Model_Resource_Collection_Abstract | null
     */
    protected function _getProductsWithReflection()
    {
        $properties = array(
            'search_result_list' => '_productCollection',
            'product_list'       => '_productCollection',
        );

        if (Mage::helper('core')->isModuleEnabled('Enterprise_TargetRule')) {
            $properties['product.info.upsell']     = '_linkCollection';
            $properties['catalog.product.related'] = '_items';
            $properties['checkout.cart.crosssell'] = '_items';
        } else {
            $properties['product.info.upsell']     = '_itemCollection';
            $properties['catalog.product.related'] = '_itemCollection';
            $properties['checkout.cart.crosssell'] = array('items', false);
        }

        $reflection = true;
        $property = $properties[$this->getBlockName()];
        if (is_array($property)) {
            list($property, $reflection) = $property;
        }

        if (!$this->_isCollectionLoaded($this->getListBlock(), $property, $reflection)) {
            return null;
        }

        return $this->_getProductCollection();
    }

    /**
     * Verify a protected property of an object via reflection or magic getter
     * @param $classInstance
     * @param $propertyName
     * @param $useReflection
     * @return bool
     */
    protected function _isCollectionLoaded($classInstance, $propertyName, $useReflection)
    {
        if (!$useReflection) {
            return $classInstance->hasData($propertyName);
        }

        $visibility = ReflectionProperty::IS_PROTECTED;

        if (!is_object($classInstance)) {
            return false;
        }
        $reflection = new ReflectionClass($classInstance);
        $properties = $reflection->getProperties($visibility);
        foreach ($properties as $property) {
            $property->setAccessible(true);
            if ($property->name == $propertyName) {
                $value = $property->getValue($classInstance);
                if (isset($value) && !empty($value)) {
                    return true;
                }
            }
        }
        return false;
    }
}
