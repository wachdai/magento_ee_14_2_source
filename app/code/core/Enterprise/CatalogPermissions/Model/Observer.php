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
 * @package     Enterprise_CatalogPermissions
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Permission model
 *
 * @category   Enterprise
 * @package    Enterprise_CatalogPermissions
 */
class Enterprise_CatalogPermissions_Model_Observer
{
    const XML_PATH_GRANT_CATALOG_CATEGORY_VIEW = 'catalog/enterprise_catalogpermissions/grant_catalog_category_view';
    const XML_PATH_GRANT_CATALOG_PRODUCT_PRICE = 'catalog/enterprise_catalogpermissions/grant_catalog_product_price';
    const XML_PATH_GRANT_CHECKOUT_ITEMS = 'catalog/enterprise_catalogpermissions/grant_checkout_items';

    /**
     * Is in product queue flag
     *
     * @var boolean
     */
    protected $_isProductQueue = false;

    /**
     * Is in category queue flag
     *
     * @var boolean
     */
    protected $_isCategoryQueue = false;

    /**
     * Models queue for permission apling
     *
     * @var array
     */
    protected $_queue = array();

    /**
     * Permissions cache for products in cart
     *
     * @var array
     */
    protected $_permissionsQuoteCache = array();

    /**
     * Catalog permission helper
     *
     * @var Enterprise_CatalogPermissions_Helper_Data
     */
    protected $_helper;

    public function __construct()
    {
        $this->_helper = Mage::helper('enterprise_catalogpermissions');
    }

    /**
     * Apply category permissions for category collection
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function applyCategoryPermissionOnIsActiveFilterToCollection(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $categoryCollection = $observer->getEvent()->getCategoryCollection();

        $this->_getIndexModel()->addIndexToCategoryCollection(
            $categoryCollection,
            $this->_getCustomerGroupId(),
            $this->_getWebsiteId()
        );

        return $this;
    }

    /**
     * Apply category permissions for category collection
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function applyCategoryPermissionOnLoadCollection(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $permissions = array();
        $categoryCollection = $observer->getEvent()->getCategoryCollection();
        $categoryIds = $categoryCollection->getColumnValues('entity_id');

        if ($categoryIds) {
            $permissions = $this->_getIndexModel()->getIndexForCategory(
                $categoryIds,
                $this->_getCustomerGroupId(),
                $this->_getWebsiteId()
            );
        }

        foreach ($permissions as $categoryId => $permission) {
            $categoryCollection->getItemById($categoryId)->setPermissions($permission);
        }

        foreach ($categoryCollection as $category) {
            $this->_applyPermissionsOnCategory($category);
        }

        return $this;
    }

    /**
     * Apply category view for tree
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function applyCategoryInactiveIds(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $categoryIds = $this->_getIndexModel()->getRestrictedCategoryIds(
            $this->_getCustomerGroupId(),
            $this->_getWebsiteId()
        );

        $observer->getEvent()->getTree()->addInactiveCategoryIds($categoryIds);

        return $this;
    }

    /**
     * Apply category view for tree
     *
     * @deprecated after 1.12.0.2
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function applyPriceGrantOnPriceIndex(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $this->_getIndexModel()->applyPriceGrantToPriceIndex(
            $observer->getEvent(),
            $this->_getCustomerGroupId(),
            $this->_getWebsiteId()
        );

        return $this;
    }

    /**
     * Applies permissions on product count for categories
     *
     * @deprecated after 1.12.0.2
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function applyCategoryPermissionOnProductCount(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $collection = $observer->getEvent()->getCollection();
        $this->_getIndexModel()->addIndexToProductCount($collection, $this->_getCustomerGroupId());
        return $this;
    }

    /**
     * Applies category permission on model afterload
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function applyCategoryPermission(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $category = $observer->getEvent()->getCategory();
        $permissions = $this->_getIndexModel()->getIndexForCategory(
            $category->getId(),
            $this->_getCustomerGroupId(),
            $this->_getWebsiteId()
        );

        if (isset($permissions[$category->getId()])) {
            $category->setPermissions($permissions[$category->getId()]);
        }

        $this->_applyPermissionsOnCategory($category);
        if ($observer->getEvent()->getCategory()->getIsHidden()) {

            $observer->getEvent()->getControllerAction()->getResponse()
                ->setRedirect($this->_helper->getLandingPageUrl());

            Mage::throwException(
                Mage::helper('enterprise_catalogpermissions')->__('You have no permissions to access this category')
            );
        }
        return $this;
    }

    /**
     * Set collection limitation condition
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function setCollectionLimitationCondition(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $collection = $observer->getEvent()->getCollection();
        if (is_null($observer->getEvent()->getCategoryId())) {
            $this->_getIndexModel()->setCollectionLimitationCondition($collection);
        }
        return $this;
    }

    /**
     * Apply product permissions for collection
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function applyProductPermissionOnCollection(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $collection = $observer->getEvent()->getCollection();
        $this->_getIndexModel()->addIndexToProductCollection($collection, $this->_getCustomerGroupId());
        return $this;
    }

    /**
     * Apply category permissions for collection on after load
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function applyProductPermissionOnCollectionAfterLoad(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $collection = $observer->getEvent()->getCollection();
        foreach ($collection as $product) {
            if ($collection->hasFlag('product_children')) {
                $product->addData(array(
                    'grant_catalog_category_view'   => -1,
                    'grant_catalog_product_price'   => -1,
                    'grant_checkout_items'          => -1,
                ));
            }
            $this->_applyPermissionsOnProduct($product);
        }
        return $this;
    }

    /**
     * Checks permissions for all quote items
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function checkQuotePermissions(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $quote = $observer->getEvent()->getCart()->getQuote();
        $this->_initPermissionsOnQuoteItems($quote);

        foreach ($quote->getAllItems() as $quoteItem) {
            if ($quoteItem->getParentItem()) {
                $parentItem = $quoteItem->getParentItem();
            } else {
                $parentItem = false;
            }
            /* @var $quoteItem Mage_Sales_Model_Quote_Item */
            if ($quoteItem->getDisableAddToCart() && !$quoteItem->isDeleted()) {
                $quote->removeItem($quoteItem->getId());
                if ($parentItem) {
                    $quote->setHasError(true)
                            ->addMessage(
                                Mage::helper('enterprise_catalogpermissions')->__('The product "%s" cannot be added to cart.', $parentItem->getName())
                            );
                } else {
                     $quote->setHasError(true)
                            ->addMessage(
                                Mage::helper('enterprise_catalogpermissions')->__('The product "%s" cannot be added to cart.', $quoteItem->getName())
                            );
                }
            }
        }

        return $this;
    }

    /**
     * Checks quote item for product permissions
     *
     * @deprecated after 1.11.0.0
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function checkQuoteItem(Varien_Event_Observer $observer)
    {
        return $this;
    }

    /**
     * Checks quote item for product permissions
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function checkQuoteItemSetProduct(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $quoteItem = $observer->getEvent()->getQuoteItem();
        $product = $observer->getEvent()->getProduct();

        if ($quoteItem->getId()) {
            return $this;
        }

        if ($quoteItem->getParentItem()) {
            $parentItem = $quoteItem->getParentItem();
        } else {
            $parentItem = false;
        }

        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        if ($product->getDisableAddToCart() && !$quoteItem->isDeleted()) {
            $quoteItem->getQuote()->removeItem($quoteItem->getId());
            if ($parentItem) {
                Mage::throwException(
                    Mage::helper('enterprise_catalogpermissions')->__('The product "%s" cannot be added to cart.', $parentItem->getName())
                );
            } else {
                Mage::throwException(
                    Mage::helper('enterprise_catalogpermissions')->__('The product "%s" cannot be added to cart.', $quoteItem->getName())
                );
            }
        }

        return $this;
    }

    /**
     * Initialize permissions for quote items
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    protected function _initPermissionsOnQuoteItems($quote)
    {
        $productIds = array();

        foreach ($quote->getAllItems() as $item) {
            if (!isset($this->_permissionsQuoteCache[$item->getProductId()]) &&
                $item->getProductId()) {
                $productIds[] = $item->getProductId();
            }
        }

        if (!empty($productIds)) {
            $this->_permissionsQuoteCache += $this->_getIndexModel()->getIndexForProduct(
                $productIds,
                $this->_getCustomerGroupId(),
                $quote->getStoreId()
            );

            foreach ($productIds as $productId) {
                if (!isset($this->_permissionsQuoteCache[$productId])) {
                    $this->_permissionsQuoteCache[$productId] = false;
                }
            }
        }

        $defaultGrants = array(
            'grant_catalog_category_view' => $this->_helper->isAllowedCategoryView(),
            'grant_catalog_product_price' => $this->_helper->isAllowedProductPrice(),
            'grant_checkout_items' => $this->_helper->isAllowedCheckoutItems()
        );

        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductId()) {
                $permission = $this->_permissionsQuoteCache[$item->getProductId()];
                if (!$permission && in_array(false, $defaultGrants)) {
                    // If no permission found, and no one of default grant is disallowed
                    $item->setDisableAddToCart(true);
                    continue;
                }

                foreach ($defaultGrants as $grant => $defaultPermission) {
                    if ($permission[$grant] == -2 ||
                        ($permission[$grant] != -1 && !$defaultPermission)) {
                        $item->setDisableAddToCart(true);
                        break;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Apply product permissions on model after load
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function applyProductPermission(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $product = $observer->getEvent()->getProduct();
        $this->_getIndexModel()->addIndexToProduct($product, $this->_getCustomerGroupId());
        $this->_applyPermissionsOnProduct($product);
        if ($observer->getEvent()->getProduct()->getIsHidden()) {
            $observer->getEvent()->getControllerAction()->getResponse()
                ->setRedirect($this->_helper->getLandingPageUrl());

            Mage::throwException(
                Mage::helper('enterprise_catalogpermissions')->__('You have no permissions to access this product')
            );
        }

        return $this;
    }

    /**
     * Apply category related permissions on category
     *
     * @param Varien_Data_Tree_Node|Mage_Catalog_Model_Category
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    protected function _applyPermissionsOnCategory($category)
    {
        if ($category->getData('permissions/grant_catalog_category_view') == -2 ||
            ($category->getData('permissions/grant_catalog_category_view')!= -1 &&
                !$this->_helper->isAllowedCategoryView())) {
            $category->setIsActive(0);
            $category->setIsHidden(true);
        }

        return $this;
    }

    /**
     * Apply category related permissions on product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    protected function _applyPermissionsOnProduct($product)
    {
        if ($product->getData('grant_catalog_category_view') == -2 ||
            ($product->getData('grant_catalog_category_view')!= -1 &&
                !$this->_helper->isAllowedCategoryView())) {
            $product->setIsHidden(true);
        }


        if ($product->getData('grant_catalog_product_price') == -2 ||
            ($product->getData('grant_catalog_product_price')!= -1 &&
                !$this->_helper->isAllowedProductPrice())) {
            $product->setCanShowPrice(false);
            $product->setDisableAddToCart(true);
        }

        if ($product->getData('grant_checkout_items') == -2 ||
            ($product->getData('grant_checkout_items')!= -1 &&
                !$this->_helper->isAllowedCheckoutItems())) {
            $product->setDisableAddToCart(true);
        }

        return $this;
    }

    /**
     * Apply is salable to product
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function applyIsSalableToProduct(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getDisableAddToCart()) {
            $observer->getEvent()->getSalable()->setIsSalable(false);
        }
        return $this;
    }


    /**
     * Check catalog search availability on load layout
     *
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function checkCatalogSearchLayout(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        if (!$this->_helper->isAllowedCatalogSearch()) {
            $observer->getEvent()->getLayout()->getUpdate()->addHandle(
                'CATALOGPERMISSIONS_DISABLED_CATALOG_SEARCH'
            );
        }

        return $this;
    }

    /**
     * Check catalog search availability on predispatch
     *
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function checkCatalogSearchPreDispatch(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $action = $observer->getEvent()->getControllerAction();
        if (!$this->_helper->isAllowedCatalogSearch()
            && !$action->getFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH)
            && $action->getRequest()->isDispatched()
        ) {
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $action->getResponse()->setRedirect($this->_helper->getLandingPageUrl());
        }

        return $this;
    }

    /**
     * Retrieve current customer group id
     *
     * @return int
     */
    protected function _getCustomerGroupId()
    {
        return Mage::getSingleton('customer/session')->getCustomerGroupId();
    }

    /**
     * Retrieve permission index model
     *
     * @return Enterprise_CatalogPermissions_Model_Permission_Index
     */
    protected function _getIndexModel()
    {
        return Mage::getSingleton('enterprise_catalogpermissions/permission_index');
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    protected function _getWebsiteId()
    {
        return Mage::app()->getStore()->getWebsiteId();
    }

    /**
     * Apply catalog permissions on product RSS feeds
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Observer
     */
    public function checkIfProductAllowedInRss(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $row = $observer->getEvent()->getRow();
        if (!$row) {
            $row = $observer->getEvent()->getProduct()->getData();
        }

        $observer->getEvent()->getProduct()->setAllowedInRss(
            $this->_checkPermission(
                $row,
                'grant_catalog_category_view',
                'isAllowedCategoryView'
            )
        );

        $observer->getEvent()->getProduct()->setAllowedPriceInRss(
            $this->_checkPermission(
                $row,
                'grant_catalog_product_price',
                'isAllowedProductPrice'
            )
        );

        return $this;
    }

    /**
     * Checks permission in passed product data.
     * For retrieving default configuration value used
     * $method from helper enterprise_catalogpermissions.
     *
     * @param array $data
     * @param string $permission
     * @param string $method method name from Enterprise_CatalogPermissions_Helper_Data class
     * @return bool
     */
    protected function _checkPermission($data, $permission, $method)
    {
        $result = true;

        /*
         * If there is no permissions for this
         * product then we will use configuration default
         */
        if (!array_key_exists($permission, $data)) {
            $data[$permission] = null;
        }

        if (!$this->_helper->$method()) {
            if ($data[$permission] == Enterprise_CatalogPermissions_Model_Permission::PERMISSION_ALLOW) {
                $result = true;
            } else {
                $result = false;
            }
        } else {
            if ($data[$permission] != Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY
                    || is_null($data[$permission])) {
                $result = true;
            } else {
                $result = false;
            }
        }

        return $result;
    }

}
