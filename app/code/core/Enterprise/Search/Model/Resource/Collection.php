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
 * Enterprise search collection resource model
 *
 * @category   Enterprise
 * @package    Enterprise_Search
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Enterprise_Search_Model_Resource_Collection
    extends Mage_Catalog_Model_Resource_Product_Collection
{

    /**
     * Store search query text
     *
     * @var string
     */
    protected $_searchQueryText = '';

    /**
     * Store search query params
     *
     * @var array
     */
    protected $_searchQueryParams = array();

    /**
     * Store search query filters
     *
     * @var array
     */
    protected $_searchQueryFilters = array();

    /**
     * Store found entities ids
     *
     * @var array
     */
    protected $_searchedEntityIds = array();

    /**
     * Store found suggestions
     *
     * @var array
     */
    protected $_searchedSuggestions = array();

    /**
     * Store engine instance
     *
     * @var Enterprise_Search_Model_Resource_Engine
     */
    protected $_engine = null;

    /**
     * Store sort orders
     *
     * @var array
     */
    protected $_sortBy = array();

    /**
     * General default query *:* to disable query limitation
     *
     * @var array
     */
    protected $_generalDefaultQuery = array('*' => '*');

    /**
     * Flag that defines if faceted data needs to be loaded
     *
     * @var bool
     */
    protected $_facetedDataIsLoaded = false;

    /**
     * Faceted search result data
     *
     * @var array
     */
    protected $_facetedData = array();

    /**
     * Suggestions search result data
     *
     * @var array
     */
    protected $_suggestionsData = array();

    /**
     * Conditions for faceted search
     *
     * @var array
     */
    protected $_facetedConditions = array();

    /**
     * Stores original page size, because _pageSize will be unset at _beforeLoad()
     * to disable limitation for collection at load with parent method
     *
     * @var int|bool
     */
    protected $_storedPageSize = false;

    /**
     * Load faceted data if not loaded
     *
     * @return Enterprise_Search_Model_Resource_Collection
     */
    public function loadFacetedData()
    {
        if (empty($this->_facetedConditions)) {
            $this->_facetedData = array();
            return $this;
        }

        list($query, $params) = $this->_prepareBaseParams();
        $params['solr_params']['facet'] = 'on';
        $params['facet'] = $this->_facetedConditions;

        $result = $this->_engine->getResultForRequest($query, $params);
        $this->_facetedData = $result['faceted_data'];
        $this->_facetedDataIsLoaded = true;

        return $this;
    }

    /**
     * Return field faceted data from faceted search result
     *
     * @param string $field
     *
     * @return array
     */
    public function getFacetedData($field)
    {
        if (!$this->_facetedDataIsLoaded) {
            $this->loadFacetedData();
        }

        if (isset($this->_facetedData[$field])) {
            return $this->_facetedData[$field];
        }

        return array();
    }

    /**
     * Return suggestions search result data
     *
     *  @return array
     */
    public function getSuggestionsData()
    {
        return $this->_suggestionsData;
    }

    /**
     * Allow to set faceted search conditions to retrieve result by single query
     *
     * @param string $field
     * @param string | array $condition
     *
     * @return Enterprise_Search_Model_Resource_Collection
     */
    public function setFacetCondition($field, $condition = null)
    {
        if (array_key_exists($field, $this->_facetedConditions)) {
            if (!empty($this->_facetedConditions[$field])) {
                $this->_facetedConditions[$field] = array($this->_facetedConditions[$field]);
            }
            $this->_facetedConditions[$field][] = $condition;
        } else {
            $this->_facetedConditions[$field] = $condition;
        }

        $this->_facetedDataIsLoaded = false;

        return $this;
    }

    /**
     * Add search query filter
     * Set search query
     *
     * @param   string $queryText
     *
     * @return  Enterprise_Search_Model_Resource_Collection
     */
    public function addSearchFilter($queryText)
    {
        /**
         * @var Mage_CatalogSearch_Model_Query $query
         */
        $query = Mage::helper('catalogsearch')->getQuery();
        $this->_searchQueryText = $queryText;
        $synonymFor = $query->getSynonymFor();
        if (!empty($synonymFor)) {
            $this->_searchQueryText .= ' ' . $synonymFor;
        }

        return $this;
    }

    /**
     * Add search query filter
     * Set search query parameters
     *
     * @param   string|array $param
     * @param   string|array $value
     *
     * @return  Enterprise_Search_Model_Resource_Collection
     */
    public function addSearchParam($param, $value = null)
    {
        if (is_array($param)) {
            foreach ($param as $field => $value) {
                $this->addSearchParam($field, $value);
            }
        } elseif (!empty($value)) {
            $this->_searchQueryParams[$param] = $value;
        }

        return $this;
    }

    /**
     * Get extended search parameters
     *
     * @return array
     */
    public function getExtendedSearchParams()
    {
        $result = $this->_searchQueryFilters;
        $result['query_text'] = $this->_searchQueryText;

        return $result;
    }

    /**
     * Add search query filter (fq)
     *
     * @param   array $param
     * @return  Enterprise_Search_Model_Resource_Collection
     */
    public function addFqFilter($param)
    {
        if (is_array($param)) {
            foreach ($param as $field => $value) {
                $this->_searchQueryFilters[$field] = $value;
            }
        }

        return $this;
    }

    /**
     * Add advanced search query filter
     * Set search query
     *
     * @param  string $query
     * @return Enterprise_Search_Model_Resource_Collection
     */
    public function addAdvancedSearchFilter($query)
    {
        return $this->addSearchFilter($query);
    }

    /**
     * Specify category filter for product collection
     *
     * @param   Mage_Catalog_Model_Category $category
     * @return  Enterprise_Search_Model_Resource_Collection
     */
    public function addCategoryFilter(Mage_Catalog_Model_Category $category)
    {
        $this->addFqFilter(array('category_ids' => $category->getId()));
        parent::addCategoryFilter($category);
        return $this;
    }

    /**
     * Add sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Enterprise_Search_Model_Resource_Collection
     */
    public function setOrder($attribute, $dir = 'desc')
    {
        $this->_sortBy[] = array($attribute => $dir);
        return $this;
    }

    /**
     * Prepare base parameters for search adapters
     *
     * @return array
     */
    protected function _prepareBaseParams()
    {
        $store  = Mage::app()->getStore();
        $params = array(
            'store_id'      => $store->getId(),
            'locale_code'   => $store->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE),
            'filters'       => $this->_searchQueryFilters
        );
        $params['filters']     = $this->_searchQueryFilters;

        if (!empty($this->_searchQueryParams)) {
            $params['ignore_handler'] = true;
            $query = $this->_searchQueryParams;
        } else {
            $query = $this->_searchQueryText;
        }

        return array($query, $params);
    }

    /**
     * Search documents by query
     * Set found ids and number of found results
     *
     * @return Enterprise_Search_Model_Resource_Collection
     */
    protected function _beforeLoad()
    {
        $ids = array();
        if ($this->_engine) {
            list($query, $params) = $this->_prepareBaseParams();

            if ($this->_sortBy) {
                $params['sort_by'] = $this->_sortBy;
            }
            if ($this->_pageSize !== false) {
                $page              = ($this->_curPage  > 0) ? (int) $this->_curPage  : 1;
                $rowCount          = ($this->_pageSize > 0) ? (int) $this->_pageSize : 1;
                $params['offset']  = $rowCount * ($page - 1);
                $params['limit']   = $rowCount;
            }

            $needToLoadFacetedData = (!$this->_facetedDataIsLoaded && !empty($this->_facetedConditions));
            if ($needToLoadFacetedData) {
                $params['solr_params']['facet'] = 'on';
                $params['facet'] = $this->_facetedConditions;
            }

            $result = $this->_engine->getIdsByQuery($query, $params);
            $ids    = (array) $result['ids'];

            if ($needToLoadFacetedData) {
                $this->_facetedData = $result['faceted_data'];
                $this->_facetedDataIsLoaded = true;
            }
        }

        $this->_searchedEntityIds = &$ids;
        $this->getSelect()->where('e.entity_id IN (?)', $this->_searchedEntityIds);

        /**
         * To prevent limitations to the collection, because of new data logic.
         * On load collection will be limited by _pageSize and appropriate offset,
         * but third party search engine retrieves already limited ids set
         */
        $this->_storedPageSize = $this->_pageSize;
        $this->_pageSize = false;

        return parent::_beforeLoad();
    }

    /**
     * Sort collection items by sort order of found ids
     *
     * @return Enterprise_Search_Model_Resource_Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $sortedItems = array();
        foreach ($this->_searchedEntityIds as $id) {
            if (isset($this->_items[$id])) {
                $sortedItems[$id] = $this->_items[$id];
            }
        }
        $this->_items = &$sortedItems;

        /**
         * Revert page size for proper paginator ranges
         */
        $this->_pageSize = $this->_storedPageSize;

        return $this;
    }

    /**
     * Retrieve found number of items
     *
     * @return int
     */
    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            list($query, $params) = $this->_prepareBaseParams();
            $params['limit'] = 1;

            $helper = Mage::helper('enterprise_search');
            $searchSuggestionsEnabled = ($this->_searchQueryParams != $this->_generalDefaultQuery
                    && $helper->getSolrConfigData('server_suggestion_enabled'));
            if ($searchSuggestionsEnabled) {
                $params['solr_params']['spellcheck'] = 'true';
                $searchSuggestionsCount = (int) $helper->getSolrConfigData('server_suggestion_count');
                $params['solr_params']['spellcheck.count']  = $searchSuggestionsCount;
                $params['spellcheck_result_counts']         = (bool) $helper->getSolrConfigData(
                    'server_suggestion_count_results_enabled');
            }

            $result = $this->_engine->getIdsByQuery($query, $params);
            if ($searchSuggestionsEnabled && !empty($result['suggestions_data'])) {
                $this->_suggestionsData = $result['suggestions_data'];
            }

            $this->_totalRecords = $this->_engine->getLastNumFound();
        }

        return $this->_totalRecords;
    }

    /**
     * Collect stats per field
     *
     * @param  array $fields
     * @return array
     */
    public function getStats($fields)
    {
        list($query, $params) = $this->_prepareBaseParams();
        $params['limit'] = 0;
        $params['solr_params']['stats'] = 'true';

        if (!is_array($fields)) {
            $fields = array($fields);
        }
        foreach ($fields as $field) {
            $params['solr_params']['stats.field'][] = $field;
        }

        return $this->_engine->getStats($query, $params);
    }

    /**
     * Set query *:* to disable query limitation
     *
     * @return Enterprise_Search_Model_Resource_Collection
     */
    public function setGeneralDefaultQuery()
    {
        $this->_searchQueryParams = $this->_generalDefaultQuery;
        return $this;
    }

    /**
     * Set search engine
     *
     * @param object $engine
     * @return Enterprise_Search_Model_Resource_Collection
     */
    public function setEngine($engine)
    {
        $this->_engine = $engine;
        return $this;
    }

    /**
     * Stub method
     *
     * @param array $fields
     *
     * @return Enterprise_Search_Model_Resource_Collection
     */
    public function addFieldsToFilter($fields)
    {
        return $this;
    }

    /**
     * Adding product count to categories collection
     *
     * @param   Mage_Eav_Model_Entity_Collection_Abstract $categoryCollection
     * @return  Enterprise_Search_Model_Resource_Collection
     */
    public function addCountToCategories($categoryCollection)
    {
        return $this;
    }

    /**
     * Set product visibility filter for enabled products
     *
     * @param   array $visibility
     * @return  Mage_Catalog_Model_Resource_Product_Collection
     */
    public function setVisibility($visibility)
    {
        if (is_array($visibility)) {
            $this->addFqFilter(array('visibility' => $visibility));
        }

        return $this;
    }





    /**
     * Retrieve faceted search results
     *
     * @deprecated after 1.9.0.0 - integrated into $this->getSize()
     *
     * @param  array $params
     * @return array
     */
    public function getFacets($params)
    {
        list($query, $params) = $this->_prepareBaseParams();
        $params['limit'] = 1;

        return (array) $this->_engine->getFacetsByQuery($query, $params);
    }

    /**
     * Add search query filter (qf)
     *
     * @deprecated after 1.9.0.0
     * @see $this->addFqFilter()
     *
     * @param   string|array $param
     * @param   string|array $value
     * @return  Enterprise_Search_Model_Resource_Collection
     */
    public function addSearchQfFilter($param, $value = null)
    {
        if (is_array($param)) {
            foreach ($param as $field => $value) {
                $this->addSearchQfFilter($field, $value);
            }
        } elseif (isset($value)) {
            if (isset($this->_searchQueryFilters[$param]) && !is_array($this->_searchQueryFilters[$param])) {
                $this->_searchQueryFilters[$param] = array($this->_searchQueryFilters[$param]);
                $this->_searchQueryFilters[$param][] = $value;
            } else {
                $this->_searchQueryFilters[$param] = $value;
            }
        }

        return $this;
    }

    /**
     * Get prices from search results
     *
     * @param   null|float $lowerPrice
     * @param   null|float $upperPrice
     * @param   null|int   $limit
     * @param   null|int   $offset
     * @param   boolean    $getCount
     * @param   string     $sort
     * @return  array
     */
    public function getPriceData($lowerPrice = null, $upperPrice = null,
        $limit = null, $offset = null, $getCount = false, $sort = 'asc')
    {
        list($query, $params) = $this->_prepareBaseParams();
        $priceField = $this->_engine->getSearchEngineFieldName('price');
        $conditions = null;
        if (!is_null($lowerPrice) || !is_null($upperPrice)) {
            $conditions = array();
            $conditions['from'] = is_null($lowerPrice) ? 0 : $lowerPrice;
            $conditions['to'] = is_null($upperPrice) ? '' : $upperPrice;
        }
        if (!$getCount) {
            $params['fields'] = $priceField;
            $params['sort_by'] = array(array('price' => $sort));
            if (!is_null($limit)) {
                $params['limit'] = $limit;
            }
            if (!is_null($offset)) {
                $params['offset'] = $offset;
            }
            if (!is_null($conditions)) {
                $params['filters'][$priceField] = $conditions;
            }
        } else {
            $params['solr_params']['facet'] = 'on';
            if (is_null($conditions)) {
                $conditions = array('from' => 0, 'to' => '');
            }
            $params['facet'][$priceField] = array($conditions);
        }

        $data = $this->_engine->getResultForRequest($query, $params);
        if ($getCount) {
            return array_shift($data['faceted_data'][$priceField]);
        }
        $result = array();
        foreach ($data['ids'] as $value) {
            $result[] = (float)$value[$priceField];
        }

        return ($sort == 'asc') ? $result : array_reverse($result);
    }
}
