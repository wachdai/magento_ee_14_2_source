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
 * Permission indexer resource
 *
 * @category    Enterprise
 * @package     Enterprise_CatalogPermissions
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CatalogPermissions_Model_Resource_Permission_Index extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Catalog permissions config path prefix
     *
     * @const
     */
    const XML_PATH_GRANT_BASE = 'catalog/enterprise_catalogpermissions/';

    /**
     * Store ids
     *
     * @var array
     */
    protected $_storeIds           = array();

    /**
     * Data for insert
     *
     * @var array
     */
    protected $_insertData         = array();

    /**
     * Table fields for insert
     *
     * @var array
     */
    protected $_tableFields        = array();

    /**
     * Permission cache
     *
     * @var array
     */
    protected $_permissionCache    = array();

    /**
     * Inheritance of grant appling in categories tree
     *
     * @var array
     */
    protected $_grantsInheritance  = array(
        'grant_catalog_category_view' => 'deny',
        'grant_catalog_product_price' => 'allow',
        'grant_checkout_items' => 'allow'
    );

    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_catalogpermissions/permission_index', 'category_id');
    }

    /**
     * Reindex category permissions
     *
     * @param string|null $categoryPath
     * @return Enterprise_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function reindex($categoryPath = null)
    {
        $readAdapter  = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();

        $select       = $readAdapter->select()
            ->from($this->getTable('catalog/category'), array('entity_id','path'));
        if (!is_null($categoryPath)) {
            $select->where('path LIKE ?', $categoryPath . '/%')
                ->orWhere('entity_id IN(?)', explode('/', $categoryPath));
        }
        $select->order('level ASC');

        $categoryPath = $readAdapter->fetchPairs($select);
        $categoryIds = array_keys($categoryPath);

        $select = $readAdapter->select()
            ->from(array('permission' => $this->getTable('enterprise_catalogpermissions/permission')), array(
                'category_id',
                'website_id',
                'customer_group_id',
                'grant_catalog_category_view',
                'grant_catalog_product_price',
                'grant_checkout_items'
            ))
            ->where('permission.category_id IN (?)', $categoryIds);

        $websiteIds = Mage::getModel('core/website')->getCollection()
            ->addFieldToFilter('website_id', array('neq'=>0))
            ->getAllIds();

        $customerGroupIds = Mage::getModel('customer/group')->getCollection()
            ->getAllIds();

        $notEmptyWhere = array();

        foreach (array_keys($this->_grantsInheritance) as $grant) {
            $notEmptyWhere[] = $readAdapter->quoteInto(
                'permission.' . $grant . ' != ?',
                Enterprise_CatalogPermissions_Model_Permission::PERMISSION_PARENT
            );
        }

        $select->where('(' . implode(' OR ', $notEmptyWhere).  ')');
        $select->order(array('category_id', 'website_id', 'customer_group_id'));

        $permissions = $readAdapter->fetchAll($select);

        // Delete old index
        if (!empty($categoryIds)) {
            $writeAdapter->delete(
                $this->getMainTable(),
                array('category_id IN (?)' => $categoryIds)
            );
        }

        $this->_permissionCache = array();


        foreach ($permissions as $permission) {
            $currentWebsiteIds = is_null($permission['website_id'])
                ? $websiteIds : array($permission['website_id']);

            $currentCustomerGroupIds = is_null($permission['customer_group_id'])
                ? $customerGroupIds : array($permission['customer_group_id']);

            $path = $categoryPath[$permission['category_id']];
            foreach ($currentWebsiteIds as $websiteId) {
                foreach ($currentCustomerGroupIds as $customerGroupId) {
                    $permission['website_id'] = $websiteId;
                    $permission['customer_group_id'] = $customerGroupId;
                    $this->_permissionCache[$path][$websiteId . '_' . $customerGroupId] = $permission;
                }
            }
        }

        $fields =  array_merge(
            array(
                'category_id', 'website_id', 'customer_group_id',
                'grant_catalog_category_view',
                'grant_catalog_product_price',
                'grant_checkout_items'
            )
        );

        $this->_beginInsert('permission_index', $fields);

        $permissionDeny = Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY;
        foreach ($categoryPath as $categoryId => $path) {
            $this->_inheritCategoryPermission($path);
            if (isset($this->_permissionCache[$path])) {
                foreach ($this->_permissionCache[$path] as $permission) {
                    if ($permission['grant_catalog_category_view'] == $permissionDeny) {
                        $permission['grant_catalog_product_price'] = $permissionDeny;
                    }
                    if ($permission['grant_catalog_product_price'] == $permissionDeny) {
                        $permission['grant_checkout_items'] = $permissionDeny;
                    }
                    $this->_insert('permission_index', array(
                        'category_id'                 => $categoryId,
                        'website_id'                  => $permission['website_id'],
                        'customer_group_id'           => $permission['customer_group_id'],
                        'grant_catalog_category_view' => $permission['grant_catalog_category_view'],
                        'grant_catalog_product_price' => $permission['grant_catalog_product_price'],
                        'grant_checkout_items'        => $permission['grant_checkout_items']
                    ));
                }
            }
        }

        $this->_commitInsert('permission_index');

        return $this;
    }

    /**
     * Inherit category permission from it's parent
     *
     * @param string $path
     * @return Enterprise_CatalogPermissions_Model_Resource_Permission_Index
     */
    protected function _inheritCategoryPermission($path)
    {
        if (strpos($path, '/') !== false) {
            $parentPath = substr($path, 0, strrpos($path, '/'));
        } else {
            $parentPath = '';
        }

        $permissionParent = Enterprise_CatalogPermissions_Model_Permission::PERMISSION_PARENT;
        if (isset($this->_permissionCache[$path])) {
            foreach (array_keys($this->_permissionCache[$path]) as $uniqKey) {
                if (isset($this->_permissionCache[$parentPath][$uniqKey])) {
                    foreach ($this->_grantsInheritance as $grant => $inheritance) {

                        $value = $this->_permissionCache[$parentPath][$uniqKey][$grant];

                        if ($this->_permissionCache[$path][$uniqKey][$grant] == $permissionParent) {
                            $this->_permissionCache[$path][$uniqKey][$grant] = $value;
                        } else {
                            if ($inheritance == 'allow') {
                                $value = max(
                                    $this->_permissionCache[$path][$uniqKey][$grant],
                                    $value
                                );
                            }

                            $value = min(
                                $this->_permissionCache[$path][$uniqKey][$grant],
                                $value
                            );

                            $this->_permissionCache[$path][$uniqKey][$grant] = $value;
                        }

                        if ($this->_permissionCache[$path][$uniqKey][$grant] == $permissionParent) {
                            $this->_permissionCache[$path][$uniqKey][$grant] = null;
                        }
                    }
                }
            }
            if (isset($this->_permissionCache[$parentPath])) {
                foreach (array_keys($this->_permissionCache[$parentPath]) as $uniqKey) {
                    if (!isset($this->_permissionCache[$path][$uniqKey])) {
                        $this->_permissionCache[$path][$uniqKey] = $this->_permissionCache[$parentPath][$uniqKey];
                    }
                }
            }
        } elseif (isset($this->_permissionCache[$parentPath])) {
            $this->_permissionCache[$path] = $this->_permissionCache[$parentPath];
        }

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
    public function getIndexForCategory($categoryId, $customerGroupId = null, $websiteId = null)
    {
        $adapter = $this->_getReadAdapter();
        if (!is_array($categoryId)) {
            $categoryId = array($categoryId);
        }

        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('category_id IN(?)', $categoryId);

        if (!is_null($customerGroupId)) {
            $select->where('customer_group_id = ?', $customerGroupId);
        }
        if (!is_null($websiteId)) {
            $select->where('website_id = ?', $websiteId);
        }

        return (!is_null($customerGroupId) && !is_null($websiteId))
            ? $adapter->fetchAssoc($select)
            : $adapter->fetchAll($select);
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
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'category_id')
            ->where('grant_catalog_category_view = :grant_catalog_category_view');
        $bind = array();
        if (is_int($customerGroupId)) {
            $select->where('customer_group_id = :customer_group_id');
            $bind[':customer_group_id'] = $customerGroupId;
        }
        if (is_int($websiteId)) {
            $select->where('website_id = :website_id');
            $bind[':website_id'] = $websiteId;
        }
        if (!Mage::helper('enterprise_catalogpermissions')->isAllowedCategoryView()) {
            $bind[':grant_catalog_category_view'] = Enterprise_CatalogPermissions_Model_Permission::PERMISSION_ALLOW;
        } else {
            $bind[':grant_catalog_category_view'] = Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY;
        }

        $restrictedCatIds = $adapter->fetchCol($select, $bind);

        $select = $adapter->select()
            ->from($this->getTable('catalog/category'), 'entity_id');

        if (!empty($restrictedCatIds) && !Mage::helper('enterprise_catalogpermissions')->isAllowedCategoryView()) {
            $select->where('entity_id NOT IN(?)', $restrictedCatIds);
        } elseif (!empty($restrictedCatIds)
            && Mage::helper('enterprise_catalogpermissions')->isAllowedCategoryView()) {
            $select->where('entity_id IN(?)', $restrictedCatIds);
        } elseif (Mage::helper('enterprise_catalogpermissions')->isAllowedCategoryView()) {
            $select->where('1 = 0'); // category view allowed for all
        }

        return $adapter->fetchCol($select);
    }

    /**
     * Apply price grant on price index select
     *
     * @deprecated after 1.12.0.2
     *
     * @param Varien_Object $data
     * @param int $customerGroupId
     *
     * @return Enterprise_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function applyPriceGrantToPriceIndex($data, $customerGroupId)
    {
        return $this;
    }

    /**
     * Add index to product count select in product collection
     *
     * @deprecated after 1.12.0.2
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @param int $customerGroupId
     *
     * @return Enterprise_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function addIndexToProductCount($collection, $customerGroupId)
    {
        return $this;
    }

    /**
     * Add index to category collection
     *
     * @param Mage_Catalog_Model_Resource_Category_Collection|
     *        Mage_Catalog_Model_Resource_Category_Flat_Collection $collection
     * @param int $customerGroupId
     * @param int $websiteId
     * @return Enterprise_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function addIndexToCategoryCollection($collection, $customerGroupId, $websiteId)
    {
        $adapter = $this->_getReadAdapter();
        if ($collection instanceof Mage_Catalog_Model_Resource_Category_Flat_Collection) {
            $tableAlias = 'main_table';
        } else {
            $tableAlias = 'e';
        }

        $collection->getSelect()->joinLeft(
            array('perm' => $this->getTable('permission_index')),
            'perm.category_id = ' . $tableAlias . '.entity_id'
            . ' AND ' . $adapter->quoteInto('perm.website_id = ?', $websiteId)
            . ' AND ' . $adapter->quoteInto('perm.customer_group_id = ?', $customerGroupId),
            array()
        );

        if (!Mage::helper('enterprise_catalogpermissions')->isAllowedCategoryView()) {
            $collection->getSelect()
                ->where('perm.grant_catalog_category_view = ?',
                    Enterprise_CatalogPermissions_Model_Permission::PERMISSION_ALLOW);
        } else {
            $collection->getSelect()
                ->where('perm.grant_catalog_category_view != ?'
                    . ' OR perm.grant_catalog_category_view IS NULL',
                    Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY);
        }

        return $this;
    }

    /**
     * Set flag for disable root category filter
     *
     * @param $collection
     * @return Enterprise_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function setCollectionLimitationCondition($collection)
    {
        $collection->setFlag('disable_root_category_filter', true);
        return $this;
    }

    /**
     * Add index select in product collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @param int $customerGroupId
     * @return Enterprise_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function addIndexToProductCollection($collection, $customerGroupId)
    {
        $parts = $collection->getSelect()->getPart(Zend_Db_Select::FROM);

        if (isset($parts['perm'])) {
            return $this;
        }

        if ($collection->getFlag('disable_root_category_filter')) {
            $permColumns = $this->_getPermColumns();
        } else {
            $permColumns = array(
                'grant_catalog_category_view',
                'grant_catalog_product_price',
                'grant_checkout_items',
            );
        }

        $collection->getSelect()
            ->joinLeft(
                array(
                    'perm' => $this->getTable('permission_index')),
                    'perm.category_id=cat_index.category_id
                        AND perm.customer_group_id= ' . $customerGroupId .
                        ' AND perm.website_id=' . Mage::app()->getStore()->getWebsiteId(),
                    $permColumns
                );

        if (!Mage::helper('enterprise_catalogpermissions')->isAllowedCategoryView()) {
            $collection->getSelect()
                ->where('perm.grant_catalog_category_view = ?',
                    Enterprise_CatalogPermissions_Model_Permission::PERMISSION_ALLOW);
        } else {
            $collection->getSelect()
                ->where('perm.grant_catalog_category_view != ?'
                    . ' OR perm.grant_catalog_category_view IS NULL',
                    Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY);
        }

        $collection->getSelect()
            ->where('cat_index.store_id=' . $collection->getStoreId());

        if ($collection->getFlag('disable_root_category_filter')) {
            $collection->getSelect()->where('cat_index.is_parent=1');
            $collection->getSelect()->group('cat_index.product_id');
        }

        if ($this->_isLinkCollection($collection)) {
            $collection->getSelect()
                ->where('perm.grant_catalog_product_price!='
                    . Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY
                    . ' OR perm.grant_catalog_product_price IS NULL')
                ->where('perm.grant_checkout_items!='
                    . Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY
                    . ' OR perm.grant_checkout_items IS NULL');
        }

        return $this;
    }

    /**
     * Get permissions columns
     *
     * @return array
     */
    protected function _getPermColumns()
    {
        $helper = Mage::helper('enterprise_catalogpermissions');
        $grantView = $helper->isAllowedCategoryView()
            ? Enterprise_CatalogPermissions_Model_Permission::PERMISSION_ALLOW
            : Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY;
        $grantPrice = $helper->isAllowedProductPrice()
            ? Enterprise_CatalogPermissions_Model_Permission::PERMISSION_ALLOW
            : Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY;
        $grantCheckout = $helper->isAllowedCheckoutItems()
            ? Enterprise_CatalogPermissions_Model_Permission::PERMISSION_ALLOW
            : Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY;

        $adapter = $this->_getWriteAdapter();

        return
            array(
                'grant_catalog_category_view' => $adapter->getCheckSql(
                    'MAX(grant_catalog_category_view) IS NULL',
                    $adapter->quoteInto('?', $grantView),
                    'MAX(grant_catalog_category_view)'
                ),
                'grant_catalog_product_price' => $adapter->getCheckSql(
                    'MAX(grant_catalog_product_price) IS NULL',
                    $adapter->quoteInto('?', $grantPrice),
                    'MAX(grant_catalog_product_price)'
                ),
                'grant_checkout_items' => $adapter->getCheckSql(
                    'MAX(grant_checkout_items) IS NULL',
                    $adapter->quoteInto('?', $grantCheckout),
                    'MAX(grant_checkout_items)'
                ),
            );
    }

    /**
     * Check if its linked collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @return bool
     */
    protected function _isLinkCollection($collection)
    {
        return method_exists($collection, 'getLinkModel') || $collection->getFlag('is_link_collection');
    }

    /**
     * Add permission index to product model
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $customerGroupId
     * @return Enterprise_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function addIndexToProduct($product, $customerGroupId)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from(array('cat_index' => $this->getTable('catalog/category_product_index')), array())
            ->joinLeft(
                array('perm' => $this->getTable('permission_index')),
                    'perm.category_id=cat_index.category_id
                        AND perm.customer_group_id=:customer_group_id
                        AND perm.website_id=:website_id',
                $this->_getPermColumns()
            )
            ->where('product_id = :product_id')
            ->where('customer_group_id = :customer_group_id OR customer_group_id IS NULL')
            ->where('store_id = :store_id')
            ->group('cat_index.product_id');

        $bind = array(
            ':product_id'        => $product->getId(),
            ':customer_group_id' => $customerGroupId,
            ':store_id'          => $product->getStoreId(),
            ':website_id'        => Mage::app()->getStore($product->getStoreId())->getWebsiteId(),
        );

        if ($product->getCategory()) {
            $select->where('perm.category_id = :category_id');
            $bind[':category_id'] = $product->getCategory()->getId();
        }

        $permission = $adapter->fetchRow($select, $bind);

        if ($permission) {
            $product->addData($permission);
        }
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
        $adapter = $this->_getReadAdapter();
        if (!is_array($productId)) {
            $productId = array($productId);
        }

        $select  = $adapter->select()
            ->from(array('cat_index' => $this->getTable('catalog/category_product_index')),
                array('cat_index.product_id')
            )
            ->joinLeft(
                array('perm' => $this->getTable('permission_index')),
                    'perm.category_id=cat_index.category_id
                        AND perm.customer_group_id=:customer_group_id
                        AND perm.website_id=:website_id',
                $this->_getPermColumns()
                )
            ->where('product_id IN(?)', $productId)
            ->where('customer_group_id = :customer_group_id OR customer_group_id IS NULL')
            ->where('store_id = :store_id')
            ->group('cat_index.product_id');

        $bind = array(
            ':customer_group_id' => $customerGroupId,
            ':store_id'          => $storeId,
            ':website_id'        =>  Mage::app()->getStore($storeId)->getWebsiteId(),
        );

        return $adapter->fetchAssoc($select, $bind);
    }

    /**
     * Prepare base information for data insert
     *
     * @param string $table
     * @param array $fields
     * @return Enterprise_CatalogPermissions_Model_Resource_Permission_Index
     */
    protected function _beginInsert($table, $fields)
    {
        $this->_tableFields[$table] = $fields;
        return $this;
    }

    /**
     * Put data into table
     *
     * @param string $table
     * @param bool $forced
     * @return Enterprise_CatalogPermissions_Model_Resource_Permission_Index
     */
    protected function _commitInsert($table, $forced = true)
    {
        $readAdapter  = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();
        if (isset($this->_insertData[$table]) && count($this->_insertData[$table])
            && ($forced || count($this->_insertData[$table]) >= 100)
        ) {

            $writeAdapter->insertMultiple($this->getTable($table), $this->_insertData[$table]);

            $this->_insertData[$table] = array();
        }
        return $this;
    }

    /**
     * Insert data to table
     *
     * @param string $table
     * @param array $data
     * @return Enterprise_CatalogPermissions_Model_Resource_Permission_Index
     */
    protected function _insert($table, $data)
    {
        $this->_insertData[$table][] = $data;
        $this->_commitInsert($table, false);
        return $this;
    }

    /**
     * Reindex all
     *
     * @return Enterprise_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function reindexAll()
    {
        $this->beginTransaction();
        try {
            $this->reindex();
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Reindex products permissions
     *
     * @deprecated after 1.12.0.2
     *
     * @param array|string $productIds
     * @return Enterprise_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function reindexProducts($productIds = null)
    {
        $readAdapter = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();
        /* @var $isActive Mage_Eav_Model_Entity_Attribute */
        $isActive = Mage::getSingleton('eav/config')->getAttribute('catalog_category', 'is_active');

        $selectCategory = $readAdapter->select()
            ->from(
            array('category_product_index' => $this->getTable('catalog/category_product_index')),
            array('product_id', 'store_id'));

        if ($isActive->isScopeGlobal()) {
            $selectCategory
                ->joinLeft(
                array('category_is_active' => $isActive->getBackend()->getTable()),
                'category_product_index.category_id = category_is_active.entity_id'
                    . ' AND category_is_active.store_id = 0'
                    . $readAdapter->quoteInto(' AND category_is_active.attribute_id = ?', $isActive->getAttributeId()),
                array())
                ->where('category_is_active.value = 1');
        } else {
            $whereExpr = $readAdapter->getCheckSql(
                'category_is_active.value_id > 0',
                'category_is_active.value',
                'category_is_active_default.value');

            $table = $isActive->getBackend()->getTable();
            $selectCategory
                ->joinLeft(
                array('category_is_active' => $table),
                'category_product_index.category_id = category_is_active.entity_id'
                    . ' AND category_is_active.store_id = category_product_index.store_id'
                    . $readAdapter->quoteInto(' AND category_is_active.attribute_id = ?', $isActive->getAttributeId()),
                array())
                ->joinLeft(
                array('category_is_active_default' => $table),
                'category_product_index.category_id = category_is_active_default.entity_id'
                    . ' AND category_is_active_default.store_id = 0'
                    . ' AND ' . $readAdapter->quoteInto('category_is_active_default.attribute_id=?',
                    $isActive->getAttributeId()),
                array())
                ->where("{$whereExpr} = 1");
        }

        $exprCatalogCategoryView = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index.grant_catalog_category_view = ?',
                Enterprise_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index.grant_catalog_category_view');

        $exprCatalogProductPrice = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index.grant_catalog_product_price = ?',
                Enterprise_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index.grant_catalog_product_price');

        $exprCheckoutItems = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index.grant_checkout_items = ?',
                Enterprise_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index.grant_checkout_items');

        $selectCategory
            ->join(
            array('store' => $this->getTable('core/store')),
            'category_product_index.store_id = store.store_id',
            array())
            ->group(array(
            'category_product_index.store_id',
            'category_product_index.product_id',
            'permission_index.customer_group_id'
        ))
        // Select for per category product index (without anchor category usage)
            ->columns('category_id', 'category_product_index')
            ->join(
            array('permission_index'=>$this->getTable('permission_index')),
            'category_product_index.category_id = permission_index.category_id'
                . ' AND store.website_id = permission_index.website_id',
            array(
                'customer_group_id',
                'grant_catalog_category_view' => 'MAX(' . $exprCatalogCategoryView . ')',
                'grant_catalog_product_price' => 'MAX(' . $exprCatalogProductPrice . ')',
                'grant_checkout_items'        => 'MAX(' . $exprCheckoutItems . ')'
            ))
            ->group('category_product_index.category_id')
            ->where('category_product_index.is_parent = ?', 1);

        $exprCatalogCategoryView = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index_product.grant_catalog_category_view = ?',
                Enterprise_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index_product.grant_catalog_category_view');

        $exprCatalogProductPrice = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index_product.grant_catalog_product_price = ?',
                Enterprise_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index_product.grant_catalog_product_price');

        $exprCheckoutItems = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index_product.grant_checkout_items = ?',
                Enterprise_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index_product.grant_checkout_items');

        // Select for per category product index (with anchor category)
        $selectAnchorCategory = $readAdapter->select();
        $selectAnchorCategory
            ->from(
            array('permission_index_product'=>$this->getTable('permission_index_product')),
            array('product_id','store_id'))
            ->join(
            array('category_product_index' => $this->getTable('catalog/category_product_index')),
            'permission_index_product.product_id = category_product_index.product_id',
            array('category_id'))
            ->join(
            array('category'=>$this->getTable('catalog/category')),
            'category.entity_id = category_product_index.category_id',
            array())
            ->join(
            array('category_child'=>$this->getTable('catalog/category')),
            $readAdapter->quoteIdentifier('category_child.path') . ' LIKE '
                . $readAdapter->getConcatSql(array(
                $readAdapter->quoteIdentifier('category.path'),
                $readAdapter->quote('/%')))
                . ' AND category_child.entity_id = permission_index_product.category_id',
            array())
            ->columns(
            array(
                'customer_group_id',
                'grant_catalog_category_view' => 'MAX(' . $exprCatalogCategoryView . ')',
                'grant_catalog_product_price' => 'MAX(' . $exprCatalogProductPrice.')',
                'grant_checkout_items'        => 'MAX(' . $exprCheckoutItems . ')'
            ),
            'permission_index_product')
            ->group(array(
            'permission_index_product.store_id',
            'permission_index_product.product_id',
            'permission_index_product.customer_group_id',
            'category_product_index.category_id'))
            ->where('category_product_index.is_parent = 0');


        if ($productIds !== null && !empty($productIds)) {
            if (!is_array($productIds)) {
                $productIds = array($productIds);
            }
            $selectCategory->where('category_product_index.product_id IN(?)', $productIds);
            $selectAnchorCategory->where('permission_index_product.product_id IN(?)', $productIds);
            $condition = array('product_id IN(?)' => $productIds);
        } else {
            $condition = '';
        }

        $fields = array(
            'product_id', 'store_id', 'category_id', 'customer_group_id',
            'grant_catalog_category_view', 'grant_catalog_product_price',
            'grant_checkout_items'
        );

        $writeAdapter->delete($this->getTable('permission_index_product'), $condition);
        $writeAdapter->query($selectCategory->insertFromSelect($this->getTable('permission_index_product'), $fields));
        $writeAdapter->query(
            $selectAnchorCategory->insertFromSelect($this->getTable('permission_index_product'), $fields)
        );

        $this->reindexProductsStandalone($productIds);

        return $this;
    }

    /**
     * Reindex products permissions for standalone mode
     *
     * @deprecated after 1.12.0.2
     *
     * @param array|string $productIds
     * @return Enterprise_CatalogPermissions_Model_Resource_Permission_Index
     */
    public function reindexProductsStandalone($productIds = null)
    {
        $readAdapter  = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();
        $selectConfig = $readAdapter->select();

        //columns expression
        $colCtlgCtgrView = $this->_getConfigGrantDbExpr('grant_catalog_category_view', 'permission_index_product');
        $colCtlgPrdctPrc = $this->_getConfigGrantDbExpr('grant_catalog_product_price', 'permission_index_product');
        $colChcktItms    = $this->_getConfigGrantDbExpr('grant_checkout_items', 'permission_index_product');

        // Config depend index select
        $selectConfig
            ->from(
            array('category_product_index' => $this->getTable('catalog/category_product_index')),
            array())
            ->join(
            array('permission_index_product'=>$this->getTable('permission_index_product')),
            'permission_index_product.product_id = category_product_index.product_id'
                . ' AND permission_index_product.store_id = category_product_index.store_id'
                . ' AND permission_index_product.is_config = 0',
            array('product_id', 'store_id'))
            ->joinLeft(
            array('permission_idx_product_exists'=>$this->getTable('permission_index_product')),
            'permission_idx_product_exists.product_id = permission_index_product.product_id'
                . ' AND permission_idx_product_exists.store_id = permission_index_product.store_id'
                . ' AND permission_idx_product_exists.customer_group_id=permission_index_product.customer_group_id'
                . ' AND permission_idx_product_exists.category_id = category_product_index.category_id',
            array())
            ->columns('category_id')
            ->columns(array(
                'customer_group_id',
                'grant_catalog_category_view' => $colCtlgCtgrView,
                'grant_catalog_product_price' => $colCtlgPrdctPrc,
                'grant_checkout_items'        => $colChcktItms,
                'is_config' => new Zend_Db_Expr('1')),
            'permission_index_product')
            ->group(array(
            'category_product_index.category_id',
            'permission_index_product.product_id',
            'permission_index_product.store_id',
            'permission_index_product.customer_group_id'))
            ->where('permission_idx_product_exists.category_id IS NULL');


        $exprCatalogCategoryView = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index_product.grant_catalog_category_view = ?',
                Enterprise_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index_product.grant_catalog_category_view');


        $exprCatalogProductPrice = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index_product.grant_catalog_product_price = ?',
                Enterprise_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index_product.grant_catalog_product_price');

        $exprCheckoutItems = $readAdapter->getCheckSql(
            $readAdapter->quoteInto(
                'permission_index_product.grant_checkout_items = ?',
                Enterprise_CatalogPermissions_Model_Permission::PERMISSION_PARENT),
            'NULL',
            'permission_index_product.grant_checkout_items');

        // Select for standalone product index
        $selectStandalone = $readAdapter->select();
        $selectStandalone
            ->from(array('permission_index_product'=>$this->getTable('permission_index_product')),
            array(
                'product_id',
                'store_id'
            )
        )->columns(
            array(
                'category_id' => new Zend_Db_Expr('NULL'),
                'customer_group_id',
                'grant_catalog_category_view' => 'MAX(' . $exprCatalogCategoryView . ')',
                'grant_catalog_product_price' => 'MAX(' . $exprCatalogProductPrice . ')',
                'grant_checkout_items'        => 'MAX(' . $exprCheckoutItems . ')',
                'is_config' => new Zend_Db_Expr('1')
            ),
            'permission_index_product'
        )->group(array(
            'permission_index_product.store_id',
            'permission_index_product.product_id',
            'permission_index_product.customer_group_id'
        ));

        $condition = array('is_config = 1');



        if ($productIds !== null && !empty($productIds)) {
            if (!is_array($productIds)) {
                $productIds = array($productIds);
            }
            $selectConfig->where('category_product_index.product_id IN(?)', $productIds);
            $selectStandalone->where('permission_index_product.product_id IN(?)', $productIds);
            $condition['product_id IN(?)'] = $productIds;
        }

        $fields = array(
            'product_id', 'store_id', 'category_id', 'customer_group_id',
            'grant_catalog_category_view', 'grant_catalog_product_price',
            'grant_checkout_items', 'is_config'
        );

        $writeAdapter->delete($this->getTable('permission_index_product'), $condition);
        $writeAdapter->query($selectConfig->insertFromSelect($this->getTable('permission_index_product'), $fields));
        $writeAdapter->query($selectStandalone->insertFromSelect($this->getTable('permission_index_product'), $fields));
        // Fix inherited permissions
        $deny = (int) Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY;

        $data = array(
            'grant_catalog_product_price' => $readAdapter->getCheckSql(
                $readAdapter->quoteInto('grant_catalog_category_view = ?', $deny),
                $deny,
                'grant_catalog_product_price'
            ),
            'grant_checkout_items' => $readAdapter->getCheckSql(
                $readAdapter->quoteInto('grant_catalog_category_view = ?', $deny)
                    . ' OR ' . $readAdapter->quoteInto('grant_catalog_product_price = ?', $deny),
                $deny,
                'grant_checkout_items'
            )
        );
        $writeAdapter->update($this->getTable('permission_index_product'), $data, $condition);

        return $this;
    }

    /**
     * Generates CASE ... WHEN .... THEN expression for grant depends on config
     *
     * @param string $grant
     * @param string $tableAlias
     * @return Zend_Db_Expr
     */
    protected function _getConfigGrantDbExpr($grant, $tableAlias)
    {
        $result      = new Zend_Db_Expr('0');
        $conditions  = array();
        $readAdapter = $this->_getReadAdapter();

        foreach ($this->_getStoreIds() as $storeId) {
            $config = Mage::getStoreConfig(self::XML_PATH_GRANT_BASE . $grant);

            if ($config == 2) {
                $groups = explode(',', trim(Mage::getStoreConfig(
                    self::XML_PATH_GRANT_BASE . $grant . '_groups'
                )));

                foreach ($groups as $groupId) {
                    if (is_numeric($groupId)) {
                        // Case per customer group
                        $condition = $readAdapter->quoteInto($tableAlias . '.store_id = ?', $storeId)
                            . ' AND ' . $readAdapter->quoteInto($tableAlias . '.customer_group_id = ?', (int) $groupId);
                        $conditions[$condition] = Enterprise_CatalogPermissions_Model_Permission::PERMISSION_ALLOW;
                    }
                }

                $condition = $readAdapter->quoteInto($tableAlias . '.store_id = ?', $storeId);
                $conditions[$condition] = Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY;
            } else {
                $condition = $readAdapter->quoteInto($tableAlias . '.store_id = ?', $storeId);
                $conditions[$condition] = (
                $config ?
                    Enterprise_CatalogPermissions_Model_Permission::PERMISSION_ALLOW :
                    Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY
                );
            }
        }

        if (!empty($conditions)) {
            $expr = 'CASE ';
            foreach ($conditions as $condition => $value) {
                $expr .= ' WHEN ' . $condition . ' THEN ' . $this->_getReadAdapter()->quote($value);
            }
            $expr .= ' END';
            $result = new Zend_Db_Expr($expr);
        }

        return $result;
    }

    /**
     * Retrieve store ids
     *
     * @return array
     */
    protected function _getStoreIds()
    {
        if (empty($this->_storeIds)) {
            $this->_storeIds = array();
            $stores = Mage::app()->getConfig()->getNode('stores');
            foreach ($stores->children() as $store) {
                $storeId = (int) $store->descend('system/store/id');
                if ($storeId) {
                    $this->_storeIds[] = $storeId;
                }
            }
        }

        return $this->_storeIds;
    }
}
