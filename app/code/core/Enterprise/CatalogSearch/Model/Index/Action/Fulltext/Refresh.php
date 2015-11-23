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
 * @package     Enterprise_CatalogSearch
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Fulltext index refresh action class
 *
 * @category    Enterprise
 * @package     Enterprise_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CatalogSearch_Model_Index_Action_Fulltext_Refresh
    implements Enterprise_Mview_Model_Action_Interface
{
    /**
     * Last version ID
     *
     * @var int
     */
    protected $_lastVersionId;

    /**
     * Connection instance
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Mview metadata instance
     *
     * @var Enterprise_Mview_Model_Metadata
     */
    protected $_metadata;

    /**
     * Mview factory instance
     *
     * @var Enterprise_Mview_Model_Factory
     */
    protected $_factory;

    /**
     * Searchable attributes cache
     *
     * @var array
     */
    protected $_searchableAttributes;

    /**
     * Product Type Instances cache
     *
     * @var array
     */
    protected $_productTypes = array();

    /**
     * Index values separator
     *
     * @var string
     */
    protected $_separator = '|';

    /**
     * Array of Zend_Date objects per store
     *
     * @var array
     */
    protected $_dates = array();

    /**
     * Application instance
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * The indexer that does the actual work
     *
     * @var Mage_CatalogSearch_Model_Fulltext
     */
    protected $_indexer;

    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'metadata' Enterprise_Mview_Model_Metadata
     *  - 'connection' Varien_Db_Adapter_Interface
     *  - 'factory' Enterprise_Mview_Model_Factory
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->_connection = $args['connection'];
        $this->_metadata   = $args['metadata'];
        $this->_factory    = $args['factory'];
        $this->_app = !empty($args['app']) ? $args['app'] : Mage::app();
        $this->_indexer = $this->_factory->getSingleton('catalogsearch/fulltext');
    }

    /**
     * Return whether fulltext engine is on
     *
     * @deprecated since version 1.13.2
     * @return bool
     */
    protected function _isFulltextOn()
    {
        return $this->_factory->getHelper('enterprise_catalogsearch')->isFulltextOn();
    }

    /**
     * Run full reindex
     *
     * @return Enterprise_CatalogSearch_Model_Index_Action_Fulltext_Refresh
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        if (!$this->_metadata->isValid()) {
            throw new Enterprise_Index_Model_Action_Exception("Can't perform operation, incomplete metadata!");
        }

        try {
            $this->_getLastVersionId();
            $this->_metadata->setInProgressStatus()->save();
            // Reindex all products
            $this->_indexer->rebuildIndex();
            // Clear search results
            $this->_resetSearchResults();
            $this->_updateMetadata();
            $this->_app->dispatchEvent('after_reindex_process_catalogsearch_index', array());
        } catch (Exception $e) {
            $this->_metadata->setInvalidStatus()->save();
            throw new Enterprise_Index_Model_Action_Exception($e->getMessage(), $e->getCode());
        }

        return $this;
    }

    /**
     * Return last version ID
     *
     * @return string
     */
    protected function _getLastVersionId()
    {
        $changelogName = $this->_metadata->getChangelogName();
        if (empty($changelogName)) {
            return 0;
        }

        if (!$this->_lastVersionId) {
            $select = $this->_connection->select()
                ->from($changelogName, array('version_id'))
                ->order('version_id DESC')
                ->limit(1);

            $this->_lastVersionId = (int)$this->_connection->fetchOne($select);
        }
        return $this->_lastVersionId;
    }

    /**
     * @deprecated since version 1.13.2
     * Regenerate fulltext search index
     */
    protected function _rebuildIndex()
    {
        foreach ($this->_app->getStores() as $store) {
            /** @var $store Mage_Core_Model_Store */
            $this->_rebuildStoreIndex($store->getId());
        }
    }

    /**
     * Proxy for resource getTable()
     *
     * @param string $entityName
     * @return string
     */
    protected function _getTable($entityName)
    {
        return $this->_metadata->getResource()->getTable($entityName);
    }

    /**
     * Return main table name
     *
     * @deprecated since version 1.13.2
     * @return string
     */
    protected function _getMainTable()
    {
        return $this->_getTable('catalogsearch/fulltext');
    }

    /**
     * Return read connection
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getReadAdapter()
    {
        return $this->_metadata->getResource()->getReadConnection();
    }

    /**
     * Return write connection
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getWriteAdapter()
    {
        return $this->_connection;
    }

    /**
     * Regenerate search index for specific store
     *
     * @deprecated since version 1.13.2
     * @param int $storeId Store View Id
     * @return void
     */
    protected function _rebuildStoreIndex($storeId)
    {
        $this->_cleanIndex($storeId);

        // prepare searchable attributes
        $staticFields = array();
        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            /** @var $attribute Mage_Eav_Model_Attribute */
            $staticFields[] = $attribute->getAttributeCode();
        }

        $dynamicFields = array(
            'int'       => array_keys($this->_getSearchableAttributes('int')),
            'varchar'   => array_keys($this->_getSearchableAttributes('varchar')),
            'text'      => array_keys($this->_getSearchableAttributes('text')),
            'decimal'   => array_keys($this->_getSearchableAttributes('decimal')),
            'datetime'  => array_keys($this->_getSearchableAttributes('datetime')),
        );

        // status and visibility filter
        /** @var $visibility Mage_Catalog_Model_Resource_Eav_Attribute */
        $visibility     = $this->_getSearchableAttribute('visibility');
        /** @var $status Mage_Catalog_Model_Resource_Eav_Attribute */
        $status         = $this->_getSearchableAttribute('status');
        $statusValues     = Mage::getSingleton('catalog/product_status')->getVisibleStatusIds();
        $allowedVisibilityValues = Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds();

        $lastProductId = 0;
        $products = $this->_getSearchableProducts($storeId, $staticFields, $lastProductId);
        while (count($products) > 0) {
            $productAttributes = array();
            $productRelations  = array();
            foreach ($products as $productData) {
                $lastProductId = $productData['entity_id'];
                $productAttributes[$productData['entity_id']] = $productData['entity_id'];
                $productChildren = $this->_getProductChildIds($productData['entity_id'], $productData['type_id']);
                $productRelations[$productData['entity_id']] = $productChildren;
                if ($productChildren) {
                    foreach ($productChildren as $productChildId) {
                        $productAttributes[$productChildId] = $productChildId;
                    }
                }
            }

            $productIndexes    = array();
            $productAttributes = $this->_getProductAttributes($storeId, $productAttributes, $dynamicFields);
            foreach ($products as $productData) {
                if (!isset($productAttributes[$productData['entity_id']])) {
                    continue;
                }

                $productAttr = $productAttributes[$productData['entity_id']];
                if (!isset($productAttr[$visibility->getId()])
                    || !in_array($productAttr[$visibility->getId()], $allowedVisibilityValues)
                ) {
                    continue;
                }
                if (!isset($productAttr[$status->getId()])
                    || !in_array($productAttr[$status->getId()], $statusValues)) {
                    continue;
                }

                $productIndex = array(
                    $productData['entity_id'] => $productAttr
                );

                if ($productChildren = $productRelations[$productData['entity_id']]) {
                    foreach ($productChildren as $productChildId) {
                        if (isset($productAttributes[$productChildId])) {
                            $productIndex[$productChildId] = $productAttributes[$productChildId];
                        }
                    }
                }

                $index = $this->_prepareProductIndex($productIndex, $productData, $storeId);

                $productIndexes[$productData['entity_id']] = $index;
            }

            $this->_saveEntityIndexes($storeId, $productIndexes);

            $products = $this->_getSearchableProducts($storeId, $staticFields, $lastProductId);
        }
    }

    /**
     * Get select for removing entity data from fulltext search table
     *
     * @deprecated since version 1.13.2
     * @param int $storeId
     * @return array
     */
    protected function _getCleanIndexConditions($storeId)
    {
        return array(
            $this->_getWriteAdapter()->quoteInto('store_id = ?', $storeId)
        );
    }

    /**
     * Remove entity data from fulltext search table
     *
     * @deprecated since version 1.13.2
     * @param int $storeId
     */
    protected function _cleanIndex($storeId)
    {
        $this->_getWriteAdapter()->delete($this->_getMainTable(), $this->_getCleanIndexConditions($storeId));
    }

    /**
     * Retrieve searchable attributes
     *
     * @deprecated since version 1.13.2
     * @param string $backendType
     * @return array
     */
    protected function _getSearchableAttributes($backendType = null)
    {
        if (is_null($this->_searchableAttributes)) {
            $this->_searchableAttributes = array();

            /** @var $productAttributeCollection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
            $productAttributeCollection = $this->_factory->getResourceModel('catalog/product_attribute_collection');

            $productAttributeCollection->addSearchableAttributeFilter();

            $attributes = $productAttributeCollection->getItems();

            /** @var $entity Mage_Catalog_Model_Resource_Product */
            $entity = $this->_getEavConfig()
                ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
                ->getEntity();

            foreach ($attributes as $attribute) {
                /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                $attribute->setEntity($entity);
            }

            $this->_searchableAttributes = $attributes;
        }

        if (!is_null($backendType)) {
            $attributes = array();
            foreach ($this->_searchableAttributes as $attributeId => $attribute) {
                /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                if ($attribute->getBackendType() == $backendType) {
                    $attributes[$attributeId] = $attribute;
                }
            }

            return $attributes;
        }

        return $this->_searchableAttributes;
    }

    /**
     * Retrieve searchable attribute by Id or code
     *
     * @deprecated since version 1.13.2
     * @param int|string $attribute
     * @return Mage_Eav_Model_Entity_Attribute
     */
    protected function _getSearchableAttribute($attribute)
    {
        $attributes = $this->_getSearchableAttributes();
        if (is_numeric($attribute)) {
            if (isset($attributes[$attribute])) {
                return $attributes[$attribute];
            }
        } elseif (is_string($attribute)) {
            foreach ($attributes as $attributeModel) {
                if ($attributeModel->getAttributeCode() == $attribute) {
                    return $attributeModel;
                }
            }
        }

        return $this->_getEavConfig()->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute);
    }

    /**
     * Retrieve select for getting searchable products per store
     *
     * @deprecated since version 1.13.2
     * @param int $storeId
     * @param array $staticFields
     * @param int $lastProductId
     * @param int $limit
     * @return Varien_Db_Select
     */
    protected function _getSearchableProductsSelect($storeId, array $staticFields, $lastProductId = 0, $limit = 100)
    {
        $websiteId    = $this->_app->getStore($storeId)->getWebsiteId();
        $writeAdapter = $this->_getWriteAdapter();

        /** @var $select Varien_Db_Select */
        $select = $writeAdapter->select()
            ->useStraightJoin(true)
            ->from(
                array('e' => $this->_getTable('catalog/product')),
                array_merge(array('entity_id', 'type_id'), $staticFields)
            )
            ->join(
                array('website' => $this->_getTable('catalog/product_website')),
                $writeAdapter->quoteInto(
                    'website.product_id=e.entity_id AND website.website_id = ?',
                    $websiteId
                ),
                array()
            )
            ->join(
                array('stock_status' => $this->_getTable('cataloginventory/stock_status')),
                $writeAdapter->quoteInto(
                    'stock_status.product_id=e.entity_id AND stock_status.website_id = ?',
                    $websiteId
                ),
                array('in_stock' => 'stock_status')
            )
            ->where('e.entity_id > ?', $lastProductId)
            ->limit($limit)
            ->order('e.entity_id');

        return $select;
    }

    /**
     * Retrieve searchable products per store
     *
     * @deprecated since version 1.13.2
     * @param int $storeId
     * @param array $staticFields
     * @param int $lastProductId
     * @param int $limit
     * @return array
     */
    protected function _getSearchableProducts($storeId, array $staticFields, $lastProductId = 0, $limit = 100)
    {
        $select = $this->_getSearchableProductsSelect($storeId, $staticFields, $lastProductId, $limit);

        $result = $this->_getWriteAdapter()->fetchAll($select);

        return $result;
    }

    /**
     * Return all product children ids
     *
     * @deprecated since version 1.13.2
     * @param int $productId Product Entity Id
     * @param string $typeId Super Product Link Type
     * @return array
     */
    protected function _getProductChildIds($productId, $typeId)
    {
        /** @var $typeInstance Mage_Catalog_Model_Product_Type_Abstract */
        $typeInstance = $this->_getProductTypeInstance($typeId);
        /** @var $relation bool|Varien_Object */
        $relation = $typeInstance->isComposite()
            ? $typeInstance->getRelationInfo()
            : false;

        if ($relation && $relation->getTable() && $relation->getParentFieldName() && $relation->getChildFieldName()) {
            $select = $this->_getReadAdapter()->select()
                ->from(
                    array('main' => $this->_getTable($relation->getTable())),
                    array($relation->getChildFieldName()))
                ->where("{$relation->getParentFieldName()}=?", $productId);
            if (!is_null($relation->getWhere())) {
                $select->where($relation->getWhere());
            }
            return $this->_getReadAdapter()->fetchCol($select);
        }

        return array();
    }

    /**
     * Load product(s) attributes
     *
     * @deprecated since version 1.13.2
     * @param int $storeId
     * @param array $productIds
     * @param array $attributeTypes
     * @return array
     */
    protected function _getProductAttributes($storeId, array $productIds, array $attributeTypes)
    {
        $result  = array();
        $selects = array();
        $adapter = $this->_getWriteAdapter();
        $ifStoreValue = $adapter->getCheckSql('t_store.value_id > 0', 't_store.value', 't_default.value');
        foreach ($attributeTypes as $backendType => $attributeIds) {
            if ($attributeIds) {
                $tableName = $this->_getTable(array('catalog/product', $backendType));
                $selects[] = $adapter->select()
                    ->from(
                        array('t_default' => $tableName),
                        array('entity_id', 'attribute_id'))
                    ->joinLeft(
                        array('t_store' => $tableName),
                        $adapter->quoteInto(
                            't_default.entity_id=t_store.entity_id'
                                . ' AND t_default.attribute_id=t_store.attribute_id'
                                . ' AND t_store.store_id=?',
                            $storeId),
                        array('value' => $this->_unifyField($ifStoreValue, $backendType)))
                    ->where('t_default.store_id=?', 0)
                    ->where('t_default.attribute_id IN (?)', $attributeIds)
                    ->where('t_default.entity_id IN (?)', $productIds);
            }
        }

        if ($selects) {
            $select = $adapter->select()->union($selects, Zend_Db_Select::SQL_UNION_ALL);
            $query = $adapter->query($select);
            while ($row = $query->fetch()) {
                $result[$row['entity_id']][$row['attribute_id']] = $row['value'];
            }
        }

        return $result;
    }

    /**
     * Returns expresion for field unification
     *
     * @deprecated since version 1.13.2
     * @param string $field
     * @param string $backendType
     * @return Zend_Db_Expr
     */
    protected function _unifyField($field, $backendType = 'varchar')
    {
        if ($backendType == 'datetime') {
            /** @var $expr Zend_Db_Expr */
            $expr = $this->_factory->getResourceHelper('catalogsearch')->castField(
                $this->_getReadAdapter()->getDateFormatSql($field, '%Y-%m-%d %H:%i:%s'));
        } else {
            /** @var $expr Zend_Db_Expr */
            $expr = $this->_factory->getResourceHelper('catalogsearch')->castField($field);
        }
        return $expr;
    }

    /**
     * Prepare Fulltext index value for product
     *
     * @deprecated since version 1.13.2
     * @param array $indexData
     * @param array $productData
     * @param int $storeId
     * @return string
     */
    protected function _prepareProductIndex($indexData, $productData, $storeId)
    {
        $index = array();

        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            /** @var $attribute Mage_Eav_Model_Attribute */
            $attributeCode = $attribute->getAttributeCode();

            if (isset($productData[$attributeCode])) {
                $value = $this->_getAttributeValue($attribute->getId(), $productData[$attributeCode], $storeId);
                if ($value) {
                    //For grouped products
                    if (isset($index[$attributeCode])) {
                        if (!is_array($index[$attributeCode])) {
                            $index[$attributeCode] = array($index[$attributeCode]);
                        }
                        $index[$attributeCode][] = $value;
                    }
                    //For other types of products
                    else {
                        $index[$attributeCode] = $value;
                    }
                }
            }
        }

        foreach ($indexData as $entityId => $attributeData) {
            foreach ($attributeData as $attributeId => $attributeValue) {
                $value = $this->_getAttributeValue($attributeId, $attributeValue, $storeId);
                if (!is_null($value) && $value !== false) {
                    $attributeCode = $this->_getSearchableAttribute($attributeId)->getAttributeCode();

                    if (isset($index[$attributeCode])) {
                        $index[$attributeCode][$entityId] = $value;
                    } else {
                        $index[$attributeCode] = array($entityId => $value);
                    }
                }
            }
        }

        /** @var $product Varien_Object */
        $product = $this->_getProductEmulator()
            ->setId($productData['entity_id'])
            ->setTypeId($productData['type_id'])
            ->setStoreId($storeId);
        /** @var $typeInstance Mage_Catalog_Model_Product_Type_Abstract */
        $typeInstance = $this->_getProductTypeInstance($productData['type_id']);
        if ($data = $typeInstance->getSearchableData($product)) {
            $index['options'] = $data;
        }

        if (isset($productData['in_stock'])) {
            $index['in_stock'] = $productData['in_stock'];
        }

        return Mage::helper('catalogsearch')->prepareIndexdata($index, $this->_separator);
    }

    /**
     * Reset search results
     *
     * @return void
     */
    protected function _resetSearchResults()
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->update($this->_getTable('catalogsearch/search_query'), array('is_processed' => 0));
        $adapter->delete($this->_getTable('catalogsearch/result'));

        $this->_app->dispatchEvent('enterprise_catalogsearch_reset_search_result', array());
    }

    /**
     * Retrieve EAV Config Singleton
     *
     * @return Mage_Eav_Model_Config
     */
    protected function _getEavConfig()
    {
        return $this->_factory->getSingleton('eav/config');
    }

    /**
     * Retrieve Product Type Instance
     *
     * @deprecated since version 1.13.2
     * @param string $typeId
     * @return Mage_Catalog_Model_Product_Type_Abstract
     */
    protected function _getProductTypeInstance($typeId)
    {
        if (!isset($this->_productTypes[$typeId])) {
            /** @var $productEmulator Varien_Object */
            $productEmulator = $this->_getProductEmulator();
            $productEmulator->setTypeId($typeId);

            $this->_productTypes[$typeId] = $this->_factory->getSingleton('catalog/product_type')
                ->factory($productEmulator);
        }
        return $this->_productTypes[$typeId];
    }

    /**
     * Retrieve Product Emulator (Varien Object)
     *
     * @deprecated since version 1.13.2
     * @return Varien_Object
     */
    protected function _getProductEmulator()
    {
        $productEmulator = new Varien_Object();
        $productEmulator->setIdFieldName('entity_id');

        return $productEmulator;
    }

    /**
     * Retrieve attribute source value for search
     *
     * @deprecated since version 1.13.2
     * @param int $attributeId
     * @param mixed $value
     * @param int $storeId
     * @return mixed
     */
    protected function _getAttributeValue($attributeId, $value, $storeId)
    {
        /** @var $attribute Mage_Eav_Model_Attribute */
        $attribute = $this->_getSearchableAttribute($attributeId);
        if (!$attribute->getIsSearchable()) {
            return null;
        }

        if ($attribute->usesSource()) {
            $attribute->setStoreId($storeId);
            $value = $attribute->getSource()->getIndexOptionText($value);

            if (is_array($value)) {
                $value = implode($this->_separator, $value);
            } elseif (empty($value)) {
                $inputType = $attribute->getFrontend()->getInputType();
                if ($inputType == 'select' || $inputType == 'multiselect') {
                    return null;
                }
            }
        } elseif ($attribute->getBackendType() == 'datetime') {
            $value = $this->_getStoreDate($storeId, $value);
        } else {
            $inputType = $attribute->getFrontend()->getInputType();
            if ($inputType == 'price') {
                $value = $this->_app->getStore($storeId)->roundPrice($value);
            }
        }

        $value = preg_replace("#\s+#siu", ' ', trim(strip_tags($value)));

        return $value;
    }

    /**
     * Multi add entities data to fulltext search table
     *
     * @deprecated since version 1.13.2
     * @param int $storeId
     * @param array $entityIndexes
     * @return void
     */
    protected function _saveEntityIndexes($storeId, $entityIndexes)
    {
        $data    = array();
        $storeId = (int)$storeId;
        foreach ($entityIndexes as $entityId => $index) {
            $data[] = array(
                'product_id'    => (int)$entityId,
                'store_id'      => $storeId,
                'data_index'    => $index
            );
        }

        if ($data) {
            $this->_factory->getResourceHelper('catalogsearch')
                ->insertOnDuplicate($this->_getMainTable(), $data, array('data_index'));
        }
    }

    /**
     * Retrieve Date value for store
     *
     * @deprecated since version 1.13.2
     * @param int $storeId
     * @param string $date
     * @return string
     */
    protected function _getStoreDate($storeId, $date = null)
    {
        if (!isset($this->_dates[$storeId])) {
            $timezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE, $storeId);
            $locale   = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $storeId);
            $locale   = new Zend_Locale($locale);

            $dateObj = new Zend_Date(null, null, $locale);
            $dateObj->setTimezone($timezone);
            $this->_dates[$storeId] = array($dateObj, $locale->getTranslation(null, 'date', $locale));
        }

        if (!is_empty_date($date)) {
            list($dateObj, $format) = $this->_dates[$storeId];
            $dateObj->setDate($date, Varien_Date::DATETIME_INTERNAL_FORMAT);

            return $dateObj->toString($format);
        }

        return null;
    }

    /**
     * Set changelog valid and update version id into metedata
     *
     * @return Enterprise_Index_Model_Action_Abstract
     */
    protected function _updateMetadata()
    {
        if ($this->_metadata->getStatus() == Enterprise_Mview_Model_Metadata::STATUS_IN_PROGRESS) {
            $this->_metadata->setValidStatus();
        }
        $this->_metadata->setVersionId($this->_getLastVersionId())->save();
        return $this;
    }
}
