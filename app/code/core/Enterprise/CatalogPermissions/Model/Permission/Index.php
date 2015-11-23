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
 * Permission indexer
 *
 * @method Enterprise_CatalogPermissions_Model_Resource_Permission_Index _getResource()
 * @method Enterprise_CatalogPermissions_Model_Resource_Permission_Index getResource()
 * @method int getCategoryId()
 * @method Enterprise_CatalogPermissions_Model_Permission_Index setCategoryId(int $value)
 * @method int getWebsiteId()
 * @method Enterprise_CatalogPermissions_Model_Permission_Index setWebsiteId(int $value)
 * @method int getCustomerGroupId()
 * @method Enterprise_CatalogPermissions_Model_Permission_Index setCustomerGroupId(int $value)
 * @method int getGrantCatalogCategoryView()
 * @method Enterprise_CatalogPermissions_Model_Permission_Index setGrantCatalogCategoryView(int $value)
 * @method int getGrantCatalogProductPrice()
 * @method Enterprise_CatalogPermissions_Model_Permission_Index setGrantCatalogProductPrice(int $value)
 * @method int getGrantCheckoutItems()
 * @method Enterprise_CatalogPermissions_Model_Permission_Index setGrantCheckoutItems(int $value)
 *
 * @category    Enterprise
 * @package     Enterprise_CatalogPermissions
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CatalogPermissions_Model_Permission_Index extends Mage_Core_Model_Abstract
{
    /**
     * Reindex products permissions event type
     *
     * @deprecated after 1.12.0.2
     */
    const EVENT_TYPE_REINDEX_PRODUCTS = 'reindex_permissions';

    /**
     * Category entity for indexers
     */
    const ENTITY_CATEGORY = 'catalogpermissions_category';

    /**
     * Product entity for indexers
     *
     * @deprecated after 1.12.0.2
     */
    const ENTITY_PRODUCT = 'catalogpermissions_product';

    /**
     * Config entity for indexers
     *
     * @deprecated after 1.12.0.2
     */
    const ENTITY_CONFIG = 'catalogpermissions_config';

    /**
     * Disable visibility of the index
     *
     * @var bool
     */
    protected $_isVisible = false;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('enterprise_catalogpermissions/permission_index');
    }

    /**
     * Reindex category permissions
     *
     * @param string|null $categoryPath
     * @return Enterprise_CatalogPermissions_Model_Permission_Index
     */
    public function reindex($categoryPath = null)
    {
        $this->getResource()->reindex($categoryPath);
        return $this;
    }

    /**
     * Reindex products permissions
     *
     * @deprecated after 1.12.0.2
     *
     * @param array|string $productIds
     * @return Enterprise_CatalogPermissions_Model_Permission_Index
     */
    public function reindexProducts($productIds = null)
    {
        $this->getResource()->reindexProducts($productIds);
        return $this;
    }

    /**
     * Reindex products permissions for standalone mode
     *
     * @deprecated after 1.12.0.2
     *
     * @param array|string $productIds
     * @return Enterprise_CatalogPermissions_Model_Permission_Index
     */
    public function reindexProductsStandalone($productIds = null)
    {
        $this->getResource()->reindexProductsStandalone($productIds);
        return $this;
    }

    /**
     * Retrieve permission index for category or categories with specified customer group and website id
     *
     * @param int|array $categoryId
     * @param int $customerGroupId
     * @param int $websiteId
     * @return array
     */
    public function getIndexForCategory($categoryId, $customerGroupId, $websiteId)
    {
        return $this->getResource()->getIndexForCategory($categoryId, $customerGroupId, $websiteId);
    }

    /**
     * Add index to category collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection|Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Flat_Collection $collection
     * @param int $customerGroupId
     * @param int $websiteId
     * @return Enterprise_CatalogPermissions_Model_Permission_Index
     */
    public function addIndexToCategoryCollection($collection, $customerGroupId, $websiteId)
    {
        $this->getResource()->addIndexToCategoryCollection($collection, $customerGroupId, $websiteId);
        return $this;
    }

    /**
     * Apply price grant on price index select
     *
     * @param Varien_Object $data
     * @param int $customerGroupId
     * @return Enterprise_CatalogPermissions_Model_Permission_Index
     */
    public function applyPriceGrantToPriceIndex($data, $customerGroupId)
    {
        $this->getResource()->applyPriceGrantToPriceIndex($data, $customerGroupId);
        return $this;
    }

    /**
     * Retrieve restricted category ids for customer group and website
     *
     * @param int $customerGroupId
     * @param int $websiteId
     * @return array
     */
    public function getRestrictedCategoryIds($customerGroupId, $websiteId)
    {
        return $this->getResource()->getRestrictedCategoryIds($customerGroupId, $websiteId);
    }

    /**
     * Set collection limitation condition
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return Enterprise_CatalogPermissions_Model_Permission_Index
     */
    public function setCollectionLimitationCondition($collection)
    {
        $this->getResource()->setCollectionLimitationCondition($collection);
        return $this;
    }

    /**
     * Add index select in product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param int $customerGroupId
     * @return Enterprise_CatalogPermissions_Model_Permission_Index
     */
    public function addIndexToProductCollection($collection, $customerGroupId)
    {
        $this->getResource()->addIndexToProductCollection($collection, $customerGroupId);
        return $this;
    }

     /**
     * Add permission index to product model
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $customerGroupId
     * @return Enterprise_CatalogPermissions_Model_Permission_Index
     */
    public function addIndexToProduct($product, $customerGroupId)
    {
        $this->getResource()->addIndexToProduct($product, $customerGroupId);
        return $this;
    }

    /**
     * Get permission index for products
     *
     * @param int|array $productId
     * @param int $customerGroupId
     * @param int $storeId
     * @return array
     */
    public function getIndexForProduct($productId, $customerGroupId, $storeId)
    {
        return $this->getResource()->getIndexForProduct($productId, $customerGroupId, $storeId);
    }

    /**
     * Get name of the index
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('enterprise_catalogpermissions')->__('Catalog Permissions');
    }

    /**
     * Add index to product count select in product collection
     *
     * @deprecated after 1.12.0.2
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param int $customerGroupId
     *
     * @return Enterprise_CatalogPermissions_Model_Permission_Index
     */
    public function addIndexToProductCount($collection, $customerGroupId)
    {
        $this->getResource()->addIndexToProductCount($collection, $customerGroupId);
        return $this;
    }
}
