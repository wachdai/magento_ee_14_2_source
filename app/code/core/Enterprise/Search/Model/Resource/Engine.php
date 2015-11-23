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
 * Search engine resource model
 *
 * @category    Enterprise
 * @package     Enterprise_Search
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Search_Model_Resource_Engine
{
    /**
     * Store search engine adapter model instance
     *
     * @var Enterprise_Search_Model_Adapter_Abstract
     */
    protected $_adapter = null;

    /**
     * Advanced index fields prefix
     *
     * @deprecated after 1.11.2.0
     *
     * @var string
     */
    protected $_advancedIndexFieldsPrefix = '#';

    /**
     * List of static fields for index
     *
     * @deprecated after 1.11.2.0
     *
     * @var array
     */
    protected $_advancedStaticIndexFields = array('#visibility');

    /**
     * List of obligatory dynamic fields for index
     *
     * @deprecated after 1.11.2.0
     *
     * @var array
     */
    protected $_advancedDynamicIndexFields = array(
        '#position_category_',
        '#price_'
    );

    /**
     * Check if hold commit action is possible depending on current commit mode
     *
     * @return bool
     */
    protected function _canHoldCommit()
    {
        $commitMode = Mage::getStoreConfig(
            Enterprise_Search_Model_Indexer_Indexer::SEARCH_ENGINE_INDEXATION_COMMIT_MODE_XML_PATH
        );

        return $commitMode == Enterprise_Search_Model_Indexer_Indexer::SEARCH_ENGINE_INDEXATION_COMMIT_MODE_FINAL
            || $commitMode == Enterprise_Search_Model_Indexer_Indexer::SEARCH_ENGINE_INDEXATION_COMMIT_MODE_ENGINE;
    }

    /**
     * Check if allow commit action is possible depending on current commit mode
     *
     * @return bool
     */
    protected function _canAllowCommit()
    {
        $commitMode = Mage::getStoreConfig(
            Enterprise_Search_Model_Indexer_Indexer::SEARCH_ENGINE_INDEXATION_COMMIT_MODE_XML_PATH
        );

        return $commitMode == Enterprise_Search_Model_Indexer_Indexer::SEARCH_ENGINE_INDEXATION_COMMIT_MODE_FINAL
            || $commitMode == Enterprise_Search_Model_Indexer_Indexer::SEARCH_ENGINE_INDEXATION_COMMIT_MODE_PARTIAL;
    }

    /**
     * Initialize search engine adapter
     *
     * @param array $options
     *
     * @return Enterprise_Search_Model_Resource_Engine
     */
    protected function _initAdapterWithParams(array $options = array())
    {
        $this->_adapter = $this->_getAdapterModelWithParams('solr', $options);

        $this->_adapter->setAdvancedIndexFieldPrefix($this->getFieldsPrefix());
        if (!$this->_canAllowCommit()) {
            $this->_adapter->holdCommit();
        }

        return $this;
    }

    /**
     * Initialize search engine adapter
     *
     * @deprecated
     *
     * @return Enterprise_Search_Model_Resource_Engine
     */
    protected function _initAdapter()
    {
        return $this->_initAdapterWithParams();
    }

    /**
     * Set search engine adapter
     */
    public function __construct(array $options = array())
    {
        $this->_initAdapterWithParams($options);
    }

    /**
     * Set search resource model
     *
     * @return string
     */
    public function getResourceName()
    {
        return 'enterprise_search/advanced';
    }

    /**
     * Retrieve found document ids search index sorted by relevance
     *
     * @param string $query
     * @param array  $params see description in appropriate search adapter
     * @param string $entityType 'product'|'cms'
     * @return array
     */
    public function getIdsByQuery($query, $params = array(), $entityType = 'product')
    {
        return $this->_adapter->getIdsByQuery($query, $params);
    }

    /**
     * Retrieve results for search request
     *
     * @param  string $query
     * @param  array  $params
     * @param  string $entityType 'product'|'cms'
     * @return array
     */
    public function getResultForRequest($query, $params = array(), $entityType = 'product')
    {
        return $this->_adapter->search($query, $params);
    }

    /**
     * Get stat info using engine search stats component
     *
     * @param  string $query
     * @param  array  $params
     * @param  string $entityType 'product'|'cms'
     * @return array
     */
    public function getStats($query, $params = array(), $entityType = 'product')
    {
        return $this->_adapter->getStats($query, $params);
    }

    /**
     * Add entity data to search index
     *
     * @param int $entityId
     * @param int $storeId
     * @param array $index
     * @param string $entityType 'product'|'cms'
     *
     * @return Enterprise_Search_Model_Resource_Engine
     */
    public function saveEntityIndex($entityId, $storeId, $index, $entityType = 'product')
    {
        return $this->saveEntityIndexes($storeId, array($entityId => $index), $entityType);
    }

    /**
     * Add entities data to search index
     *
     * @param int $storeId
     * @param array $entityIndexes
     * @param string $entityType 'product'|'cms'
     *
     * @return Enterprise_Search_Model_Resource_Engine
     */
    public function saveEntityIndexes($storeId, $entityIndexes, $entityType = 'product')
    {
        $docs = $this->_adapter->prepareDocsPerStore($entityIndexes, $storeId);
        $this->_adapter->addDocs($docs);

        return $this;
    }

    /**
     * Remove entity data from search index
     *
     * For deletion of all documents parameters should be null. Empty array will do nothing.
     *
     * @param  int|array|null $storeIds
     * @param  int|array|null $entityIds
     * @param  string $entityType 'product'|'cms'
     * @return Enterprise_Search_Model_Resource_Engine
     */
    public function cleanIndex($storeIds = null, $entityIds = null, $entityType = 'product')
    {
        if ($storeIds === array() || $entityIds === array()) {
            return $this;
        }

        if (is_null($storeIds) || $storeIds == Mage_Core_Model_App::ADMIN_STORE_ID) {
            $storeIds = array_keys(Mage::app()->getStores());
        } else {
            $storeIds = (array) $storeIds;
        }

        $queries = array();
        if (empty($entityIds)) {
            foreach ($storeIds as $storeId) {
                $queries[] = 'store_id:' . $storeId;
            }
        } else {
            $entityIds = (array) $entityIds;
            $uniqueKey = $this->_adapter->getUniqueKey();
            foreach ($storeIds as $storeId) {
                foreach ($entityIds as $entityId) {
                    $queries[] = $uniqueKey . ':' . $entityId . '|' . $storeId;
                }
            }
        }

        $this->_adapter->deleteDocs(array(), $queries);

        return $this;
    }

    /**
     * Retrieve last query number of found results
     *
     * @return int
     */
    public function getLastNumFound()
    {
        return $this->_adapter->getLastNumFound();
    }

    /**
     * Retrieve search result data collection
     *
     * @return Enterprise_Search_Model_Resource_Collection
     */
    public function getResultCollection()
    {
        return Mage::getResourceModel('enterprise_search/collection')->setEngine($this);
    }

    /**
     * Retrieve advanced search result data collection
     *
     * @return Enterprise_Search_Model_Resource_Collection
     */
    public function getAdvancedResultCollection()
    {
        return $this->getResultCollection();
    }

    /**
     * Define if current search engine supports advanced index
     *
     * @return bool
     */
    public function allowAdvancedIndex()
    {
        return true;
    }

    /**
     * Retrieve allowed visibility values for current engine
     *
     * @return array
     */
    public function getAllowedVisibility()
    {
        return Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds();
    }

    /**
     * Prepare index array
     *
     * @param array $index
     * @param string|null $separator
     * @return array
     */
    public function prepareEntityIndex($index, $separator = null)
    {
        return $index;
    }

    /**
     * Define if Layered Navigation is allowed
     *
     * @return bool
     */
    public function isLayeredNavigationAllowed()
    {
        return true;
    }

    /**
     * Retrieve search engine adapter model by adapter name
     *
     * @param string $adapterName
     * @param array $options
     *
     * @return object
     */
    protected function _getAdapterModelWithParams($adapterName, array $options = array())
    {
        switch ($adapterName) {
            case 'solr':
            default:
                if (extension_loaded('solr')) {
                    $modelName = 'enterprise_search/adapter_phpExtension';
                } else {
                    $modelName = 'enterprise_search/adapter_httpStream';
                }
                break;
        }

        $adapter = Mage::getSingleton($modelName, $options);

        return $adapter;
    }

    /**
     * Retrieve search engine adapter model by adapter name
     *
     * @deprecated
     *
     * @param string $adapterName
     *
     * @return object
     */
    protected function _getAdapterModel($adapterName)
    {
        return $this->_getAdapterModelWithParams($adapterName);
    }

    /**
     * Define if selected adapter is available
     *
     * @return bool
     */
    public function test()
    {
        return $this->_adapter->ping();
    }

    /**
     * Optimize search engine index
     *
     * @return Enterprise_Search_Model_Resource_Engine
     */
    public function optimizeIndex()
    {
        $this->_adapter->optimize();
        return $this;
    }

    /**
     * Commit search engine index changes
     *
     * @return Enterprise_Search_Model_Resource_Engine
     */
    public function commitChanges()
    {
        $this->_adapter->commit();
        return $this;
    }

    /**
     * Hold commit of changes for adapter.
     * Can be used for one time commit after full indexation finish.
     *
     * @return bool
     */
    public function holdCommit()
    {
        if ($this->_canHoldCommit()) {
            $this->_adapter->holdCommit();
            return true;
        }

        return false;
    }

    /**
     * Allow commit of changes for adapter
     *
     * @return bool
     */
    public function allowCommit()
    {
        if ($this->_canAllowCommit()) {
            $this->_adapter->allowCommit();
            return true;
        }

        return false;
    }

    /**
     * Define if third party search engine index needs optimization
     *
     * @param  bool $state
     * @return Enterprise_Search_Model_Resource_Engine
     */
    public function setIndexNeedsOptimization($state = true)
    {
        $this->_adapter->setIndexNeedsOptimization($state);
        return $this;
    }

    /**
     * Check if third party search engine index needs optimization
     *
     * @return bool
     */
    public function getIndexNeedsOptimization()
    {
        return $this->_adapter->getIndexNeedsOptimization();
    }

    /**
     * Store searchable attributes
     *
     * @param array $attributes
     * @return Enterprise_Search_Model_Resource_Engine
     */
    public function storeSearchableAttributes(array $attributes)
    {
        $this->_adapter->storeSearchableAttributes($attributes);
        return $this;
    }

    /**
     * Retrieve attribute field name for search engine
     *
     * @param   $attribute
     * @param   string $target
     *
     * @return  string|bool
     */
    public function getSearchEngineFieldName($attribute, $target = 'default')
    {
        return $this->_adapter->getSearchEngineFieldName($attribute, $target);
    }





    /**
     * Retrieve search suggestions
     *
     * @deprecated after 1.9.0.0
     *
     * @param  string $query
     * @param  array $params see description in appropriate search adapter
     * @param  int|bool $limit
     * @param  bool $withResultsCounts
     * @return array
     */
    public function getSuggestionsByQuery($query, $params = array(), $limit = false, $withResultsCounts = false)
    {
        return $this->_adapter->getSuggestionsByQuery($query, $params, $limit, $withResultsCounts);
    }

    /**
     * Define if Layered Navigation is allowed
     *
     * @deprecated after 1.9.1
     * @see $this->isLayeredNavigationAllowed()
     *
     * @return bool
     */
    public function isLeyeredNavigationAllowed()
    {
        $this->isLayeredNavigationAllowed();
    }

    /**
     * Refresh products indexes affected on category update
     *
     * @deprecated after 1.11.0.0
     *
     * @param  array $productIds
     * @param  array $categoryIds
     * @return Enterprise_Search_Model_Resource_Engine
     */
    public function updateCategoryIndex($productIds, $categoryIds)
    {
        if (!is_array($productIds) || empty($productIds)) {
            $productIds = Mage::getResourceSingleton('enterprise_search/index')
                ->getMovedCategoryProductIds($categoryIds[0]);
        }

        if (!empty($productIds)) {
            Mage::getResourceSingleton('catalogsearch/fulltext')->rebuildIndex(null, $productIds);
        }

        return $this;
    }

    /**
     * Returns advanced index fields prefix
     *
     * @deprecated after 1.11.2.0
     *
     * @return string
     */
    public function getFieldsPrefix()
    {
        return $this->_advancedIndexFieldsPrefix;
    }

    /**
     * Prepare advanced index for products
     *
     * @deprecated after 1.11.2.0
     *
     * @see Mage_CatalogSearch_Model_Resource_Fulltext->_getSearchableProducts()
     *
     * @param array $index
     * @param int $storeId
     * @param array | null $productIds
     *
     * @return array
     */
    public function addAdvancedIndex($index, $storeId, $productIds = null)
    {
        return Mage::getResourceSingleton('enterprise_search/index')
            ->addAdvancedIndex($index, $storeId, $productIds);
    }

    /**
     * Add to index fields that allowed in advanced index
     *
     * @deprecated after 1.11.2.0
     *
     * @param array $productData
     *
     * @return array
     */
    public function addAllowedAdvancedIndexField($productData)
    {
        $advancedIndex = array();

        foreach ($productData as $field => $value) {
            if (in_array($field, $this->_advancedStaticIndexFields)
                || $this->_isDynamicField($field)
            ) {
                if (!empty($value)) {
                    $advancedIndex[$field] = $value;
                }
            }
        }

        return $advancedIndex;
    }

    /**
     * Define if field is dynamic index field
     *
     * @deprecated after 1.11.2.0
     *
     * @param string $field
     *
     * @return bool
     */
    protected function _isDynamicField($field)
    {
        foreach ($this->_advancedDynamicIndexFields as $dynamicField) {
            $length = strlen($dynamicField);
            if (substr($field, 0, $length) == $dynamicField) {
                return true;
            }
        }

        return false;
    }
}
