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
 * Search engine abstract adapter
 *
 * @category   Enterprise
 * @package    Enterprise_Search
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_Search_Model_Adapter_Abstract
{
    /**
     * Field to use to determine and enforce document uniqueness
     *
     */
    const UNIQUE_KEY = 'unique';

    /**
     * Store Solr Client instance
     *
     * @var object
     */
    protected $_client = null;

    /**
     * Object name used to create solr document object
     *
     * @var string
     */
    protected $_clientDocObjectName = 'Apache_Solr_Document';

    /**
     * Store last search query number of found results
     *
     * @var int
     */
    protected $_lastNumFound = 0;

    /**
     * Search query filters
     *
     * @var array
     */
    protected $_filters = array();

    /**
     * Store common Solr metadata fields
     * All fields, that come up from search engine will be filtered by these keys
     *
     * @var array
     */
    protected $_usedFields = array('sku', 'visibility', 'in_stock');

    /**
     * Defines text type fields
     *
     * @var array
     */
    protected $_textFieldTypes = array('text', 'varchar');

    /**
     * Search query params with their default values
     *
     * @var array
     */
    protected $_defaultQueryParams = array(
        'offset' => 0,
        'limit' => Enterprise_Search_Model_Adapter_Solr_Abstract::DEFAULT_ROWS_LIMIT,
        'sort_by' => array(array('score' => 'desc')),
        'store_id' => null,
        'locale_code' => null,
        'fields' => array(),
        'solr_params' => array(),
        'ignore_handler' => false,
        'filters' => array()
    );

    /**
     * Index values separator
     *
     * @var string
     */
    protected $_separator = ' ';

    /**
     * Searchable attribute params
     *
     * @var array | null
     */
    protected $_indexableAttributeParams = null;

    /**
     * Define if automatic commit on changes for adapter is allowed
     *
     * @var bool
     */
    protected $_holdCommit = false;

    /**
     * Define if search engine index needs optimization
     *
     * @var bool
     */
    protected $_indexNeedsOptimization = false;


    // Deprecated properties

    /**
     * Text fields which can store data differ in different languages
     *
     * @deprecated after 1.11.0.0
     *
     * @var array
     */
    protected $_searchTextFields = array('name', 'alphaNameSort');

    /**
     * Fields which must be are not included in fulltext field
     *
     * @deprecated after 1.11.2.0
     *
     * @var array
     */
    protected $_notInFulltextField = array(
        self::UNIQUE_KEY,
        'id',
        'store_id',
        'in_stock',
        'category_ids',
        'visibility'
    );


    /**
     * Retrieve attribute field name
     *
     * @abstract
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute|string $attribute
     * @param string $target
     *
     * @return string|bool
     */
    abstract public function getSearchEngineFieldName($attribute, $target = 'default');

    /**
     * Before commit action
     *
     * @return Enterprise_Search_Model_Adapter_Abstract
     */
    protected function _beforeCommit()
    {
        return $this;
    }

    /**
     * After commit action
     *
     * @return Enterprise_Search_Model_Adapter_Abstract
     */
    protected function _afterCommit()
    {
        $this->_indexNeedsOptimization = true;

        return $this;
    }

    /**
     * Before optimize action.
     * _beforeCommit method is called because optimize includes commit in itself
     *
     * @return Enterprise_Search_Model_Adapter_Abstract
     */
    protected function _beforeOptimize()
    {
        $this->_beforeCommit();

        return $this;
    }

    /**
     * After commit action
     * _afterCommit method is called because optimize includes commit in itself
     *
     * @return Enterprise_Search_Model_Adapter_Abstract
     */
    protected function _afterOptimize()
    {
        $this->_afterCommit();

        $this->_indexNeedsOptimization = false;

        return $this;
    }

    /**
     * Store searchable attributes to prevent additional collection load
     *
     * @param   array $attributes
     * @return  Enterprise_Search_Model_Adapter_Abstract
     */
    public function storeSearchableAttributes(array $attributes)
    {
        $result = array();
        foreach ($attributes as $attribute) {
            $result[$attribute->getAttributeCode()] = $attribute;
        }

        $this->_indexableAttributeParams = $result;
        return $this;
    }

    /**
     * Prepare name for system text fields.
     *
     * @param   string $field
     * @param   string $suffix
     *
     * @return  string
     */
    public function getAdvancedTextFieldName($field, $suffix = '', $storeId = null)
    {
        return $field;
    }

    /**
     * Prepare price field name for search engine
     *
     * @param   null|int $customerGroupId
     * @param   null|int $websiteId
     *
     * @return  bool|string
     */
    public function getPriceFieldName($customerGroupId = null, $websiteId = null)
    {
        if ($customerGroupId === null) {
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
        if ($websiteId === null) {
            $websiteId = Mage::app()->getStore()->getWebsiteId();
        }

        if ($customerGroupId === null || !$websiteId) {
            return false;
        }

        return 'price_' . $customerGroupId . '_' . $websiteId;
    }


    /**
     * Prepare category index data for product
     *
     * @param   $productId
     * @param   $storeId
     *
     * @return  array
     */
    protected function _prepareProductCategoryIndexData($productId, $storeId)
    {
        $result = array();

        $categoryProductData = Mage::getResourceSingleton('enterprise_search/index')
                ->getCategoryProductIndexData($storeId, $productId);

        if (isset($categoryProductData[$productId])) {
            $categoryProductData = $categoryProductData[$productId];

            $categoryIds = array_keys($categoryProductData);
            if (!empty($categoryIds)) {
                $result = array('category_ids' => $categoryIds);

                foreach ($categoryProductData as $categoryId => $position) {
                    $result['position_category_' . $categoryId] = $position;
                }
            }
        }

        return $result;
    }

    /**
     * Prepare price index for product
     *
     * @param   $productId
     * @param   $storeId
     *
     * @return  array
     */
    protected function _preparePriceIndexData($productId, $storeId)
    {
        $result = array();

        $productPriceIndexData = Mage::getResourceSingleton('enterprise_search/index')
            ->getPriceIndexData($productId, $storeId);

        if (isset($productPriceIndexData[$productId])) {
            $productPriceIndexData = $productPriceIndexData[$productId];

            $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
            foreach ($productPriceIndexData as $customerGroupId => $price) {
                $fieldName = $this->getPriceFieldName($customerGroupId, $websiteId);
                $result[$fieldName] = sprintf('%F', $price);
            }
        }

        return $result;
    }


    /**
     * Is data available in index
     *
     * @param array $productIndexData
     * @param int $productId
     * @return bool
     */
    protected function isAvailableInIndex($productIndexData, $productId)
    {
        if (!is_array($productIndexData) || empty($productIndexData)) {
            return false;
        }

        if (!isset($productIndexData['visibility'][$productId])) {
            return false;
        }

        return true;
    }

    /**
     * Prepare index data for using in search engine metadata.
     * Prepare fields for advanced search, navigation, sorting and fulltext fields for each search weight for
     * quick search and spell.
     *
     * @param array $productIndexData
     * @param int $productId
     * @param int $storeId
     *
     * @return  array|bool
     */
    protected function _prepareIndexProductData($productIndexData, $productId, $storeId)
    {
        if (!$this->isAvailableInIndex($productIndexData, $productId)) {
            return false;
        }


        $fulltextData = array();
        foreach ($productIndexData as $attributeCode => $value) {
            if ($attributeCode == 'visibility') {
                $productIndexData[$attributeCode] = $value[$productId];
                continue;
            }

            // Prepare processing attribute info
            if (isset($this->_indexableAttributeParams[$attributeCode])) {
                /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                $attribute = $this->_indexableAttributeParams[$attributeCode];
            } else {
                $attribute = null;
            }

            // Prepare values for required fields
            if (!in_array($attributeCode, $this->_usedFields)) {
                unset($productIndexData[$attributeCode]);
            }

            if (!$attribute || $attributeCode == 'price' || empty($value)) {
                continue;
            }

            $attribute->setStoreId($storeId);
            $preparedValue = '';

            // Preparing data for solr fields
            if ($attribute->getIsSearchable() || $attribute->getIsVisibleInAdvancedSearch()
                || $attribute->getIsFilterable() || $attribute->getIsFilterableInSearch()
                || $attribute->getUsedForSortBy()
            ) {
                $backendType = $attribute->getBackendType();
                $frontendInput = $attribute->getFrontendInput();

                if ($attribute->usesSource()) {
                    if ($frontendInput == 'multiselect') {
                        $preparedValue = array();
                        foreach ($value as $val) {
                            $preparedValue = array_merge($preparedValue, array_filter(explode(',', $val)));
                        }
                        $preparedNavValue = $preparedValue;
                    } else {
                        // safe condition
                        if (!is_array($value)) {
                            $preparedValue = array($value);
                        } else {
                            $preparedValue = array_unique($value);
                        }

                        $preparedNavValue = $preparedValue;
                        // Ensure that self product value will be saved after array_unique function for sorting purpose
                        if (isset($value[$productId])) {
                            if (!isset($preparedNavValue[$productId])) {
                                $selfValueKey = array_search($value[$productId], $preparedNavValue);
                                unset($preparedNavValue[$selfValueKey]);
                                $preparedNavValue[$productId] = $value[$productId];
                            }
                        }
                    }

                    foreach ($preparedValue as $id => $val) {
                        $preparedValue[$id] = $attribute->getSource()->getIndexOptionText($val);
                    }
                } else {
                    $preparedValue = $value;
                    if ($backendType == 'datetime') {
                        if (is_array($value)) {
                            $preparedValue = array();
                            foreach ($value as $id => &$val) {
                                $val = $this->_getSolrDate($storeId, $val);
                                if (!empty($val)) {
                                     $preparedValue[$id] = $val;
                                }
                            }
                            unset($val); //clear link to value
                            $preparedValue = array_unique($preparedValue);
                        } else {
                            $preparedValue[$productId] = $this->_getSolrDate($storeId, $value);
                        }
                    }
                }
            }

            // Preparing data for sorting field
            if ($attribute->getUsedForSortBy()) {
                $sortValue = null;
                if (is_array($preparedValue)) {
                    if (isset($preparedValue[$productId])) {
                        $sortValue = $preparedValue[$productId];
                    } else {
                        $sortValue = null;
                    }
                }

                if (!empty($sortValue)) {
                    $fieldName = $this->getSearchEngineFieldName($attribute, 'sort');

                    if ($fieldName) {
                        $productIndexData[$fieldName] = $sortValue;
                    }
                }
            }

            // Adding data for advanced search field (without additional prefix)
            if (($attribute->getIsVisibleInAdvancedSearch() ||  $attribute->getIsFilterable()
                || $attribute->getIsFilterableInSearch())
            ) {
                if ($attribute->usesSource()) {
                    $fieldName = $this->getSearchEngineFieldName($attribute, 'nav');
                    if ($fieldName && !empty($preparedNavValue)) {
                        $productIndexData[$fieldName] = $preparedNavValue;
                    }
                } else {
                    $fieldName = $this->getSearchEngineFieldName($attribute);
                    if ($fieldName && !empty($preparedValue)) {
                        $productIndexData[$fieldName] = in_array($backendType, $this->_textFieldTypes)
                            ? implode(' ', (array)$preparedValue)
                            : $preparedValue ;
                    }
                }
            }

            // Adding data for fulltext search field
            if ($attribute->getIsSearchable() && !empty($preparedValue)) {
                $searchWeight = $attribute->getSearchWeight();
                if ($searchWeight) {
                    $fulltextData[$searchWeight][] = is_array($preparedValue)
                        ? implode(' ', $preparedValue)
                        : $preparedValue;
                }
            }

            unset($preparedNavValue, $preparedValue, $fieldName, $attribute);
        }

        // Preparing fulltext search fields
        $fulltextSpell = array();
        foreach ($fulltextData as $searchWeight => $data) {
            $fieldName = $this->getAdvancedTextFieldName('fulltext', $searchWeight, $storeId);
            $productIndexData[$fieldName] = $this->_implodeIndexData($data);
            $fulltextSpell = array_merge($fulltextSpell, $data);
        }
        unset($fulltextData);

        // Preparing field with spell info
        $fulltextSpell = array_unique($fulltextSpell);
        $fieldName = $this->getAdvancedTextFieldName('spell', '', $storeId);
        $productIndexData[$fieldName] = $this->_implodeIndexData($fulltextSpell);
        unset($fulltextSpell);

        // Getting index data for price
        if (isset($this->_indexableAttributeParams['price'])) {
            $priceEntityIndexData = $this->_preparePriceIndexData($productId, $storeId);
            $productIndexData = array_merge($productIndexData, $priceEntityIndexData);
        }

        // Product category index data definition
        $productCategoryIndexData = $this->_prepareProductCategoryIndexData($productId, $storeId);
        $productIndexData = array_merge($productIndexData, $productCategoryIndexData);

        // Define system data for engine internal usage
        $productIndexData['id'] = $productId;
        $productIndexData['store_id'] = $storeId;
        $productIndexData[self::UNIQUE_KEY] = $productId . '|' . $storeId;

        return $productIndexData;
    }

    /**
     * Create Solr Input Documents by specified data
     *
     * @param   array $docData
     * @param   int $storeId
     *
     * @return  array
     */
    public function prepareDocsPerStore($docData, $storeId)
    {
        if (!is_array($docData) || empty($docData)) {
            return array();
        }

        $this->_separator = Mage::getResourceSingleton('catalogsearch/fulltext')->getSeparator();

        $docs = array();
        foreach ($docData as $productId => $productIndexData) {
            $doc = new $this->_clientDocObjectName;

            $productIndexData = $this->_prepareIndexProductData($productIndexData, $productId, $storeId);
            if (!$productIndexData) {
                continue;
            }

            foreach ($productIndexData as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        if (!is_array($val)) {
                            $doc->addField($name, $val);
                        }
                    }
                } else {
                    $doc->addField($name, $value);
                }
            }
            $docs[] = $doc;
        }

        return $docs;
    }

    /**
     * Add prepared Solr Input documents to Solr index
     *
     * @param array $docs
     * @return Enterprise_Search_Model_Adapter_Solr
     */
    public function addDocs($docs)
    {
        if (empty($docs)) {
            return $this;
        }
        if (!is_array($docs)) {
            $docs = array($docs);
        }

        $_docs = array();
        foreach ($docs as $doc) {
            if ($doc instanceof $this->_clientDocObjectName) {
                $_docs[] = $doc;
            }
        }

        if (empty($_docs)) {
            return $this;
        }

        try {
            $this->_client->addDocuments($_docs);
        } catch (Exception $e) {
            $this->rollback();
            Mage::logException($e);
        }

        $this->commit();

        return $this;
    }

    /**
     * Remove documents from Solr index
     *
     * @param  int|string|array $docIDs
     * @param  string|array|null $queries if "all" specified and $docIDs are empty, then all documents will be removed
     * @return Enterprise_Search_Model_Adapter_Abstract
     */
    public function deleteDocs($docIDs = array(), $queries = null)
    {
        $_deleteBySuffix = 'Ids';
        $params = array();
        if (!empty($docIDs)) {
            if (!is_array($docIDs)) {
                $docIDs = array($docIDs);
            }
            $params = $docIDs;
        } elseif (!empty($queries)) {
            if ($queries == 'all') {
                $queries = array('*:*');
            }
            if (!is_array($queries)) {
                $queries = array($queries);
            }
            $_deleteBySuffix = 'Queries';
            $params = $queries;
        }

        if ($params) {
            $deleteMethod = sprintf('deleteBy%s', $_deleteBySuffix);

            try {
                $this->_client->$deleteMethod($params);
            } catch (Exception $e) {
                $this->rollback();
                Mage::logException($e);
            }

            $this->commit();
        }

        return $this;
    }

    /**
     * Retrieve found document ids from Solr index sorted by relevance
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function getIdsByQuery($query, $params = array())
    {
        $params['fields'] = array('id');

        $result = $this->_search($query, $params);

        if (!isset($result['ids'])) {
            $result['ids'] = array();
        }

        if (!empty($result['ids'])) {
            foreach ($result['ids'] as &$id) {
                $id = $id['id'];
            }
        }

        return $result;
    }

    /**
     * Collect statistics about specified fields
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function getStats($query, $params = array())
    {
        return $this->_search($query, $params);
    }

    /**
     * Retrieve search suggestions by query
     *
     * @depracated after 1.9.0.0
     *
     * @param string $query
     * @param array $params
     * @param int $limit
     * @param bool $withResultsCounts
     * @return array
     */
    public function getSuggestionsByQuery($query, $params = array(), $limit = false, $withResultsCounts = false)
    {
        return $this->_searchSuggestions($query, $params, $limit, $withResultsCounts);
    }

    /**
     * Search documents in Solr index sorted by relevance
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function search($query, $params = array())
    {
        return $this->_search($query, $params);
    }

    /**
     * Finalizes all add/deletes made to the index
     *
     * @return object|bool
     */
    public function commit()
    {
        if ($this->_holdCommit) {
            return false;
        }

        $this->_beforeCommit();
        $result = $this->_client->commit();
        $this->_afterCommit();

        return $result;
    }

    /**
     * Perform optimize operation
     * Same as commit operation, but also defragment the index for faster search performance
     *
     * @return object|bool
     */
    public function optimize()
    {
        if ($this->_holdCommit) {
            return false;
        }

        $this->_beforeOptimize();
        $result = $this->_client->optimize();
        $this->_afterOptimize();

        return $result;
    }

    /**
     * Rollbacks all add/deletes made to the index since the last commit
     *
     * @return object
     */
    public function rollback()
    {
        return $this->_client->rollback();
    }

    /**
     * Getter for field to use to determine and enforce document uniqueness
     *
     * @return string
     */
    public function getUniqueKey()
    {
        return self::UNIQUE_KEY;
    }

    /**
     * Retrieve last query number of found results
     *
     * @return int
     */
    public function getLastNumFound()
    {
        return $this->_lastNumFound;
    }

    /**
     * Connect to Search Engine Client by specified options.
     * Should initialize _client
     *
     * @param array $options
     */
    abstract protected function _connect($options = array());

    /**
     * Simple Search interface
     *
     * @param string $query
     * @param array $params
     */
    abstract protected function _search($query, $params = array());

    /**
     * Checks if Solr server is still up
     */
    abstract public function ping();

    /**
     * Retrieve language code by specified locale code if this locale is supported
     *
     * @param string $localeCode
     */
    abstract protected function _getLanguageCodeByLocaleCode($localeCode);

    /**
     * Convert Solr Query Response found documents to an array
     *
     * @param object $response
     * @return array
     */
    protected function _prepareQueryResponse($response)
    {
        $realResponse = $response->response;
        $_docs = $realResponse->docs;
        if (!$_docs) {
            return array();
        }
        $this->_lastNumFound = (int)$realResponse->numFound;
        $result = array();
        foreach ($_docs as $doc) {
            $result[] = $this->_objectToArray($doc);
        }

        return $result;
    }

    /**
     * Convert Solr Query Response found suggestions to string
     *
     * @param object $response
     * @return array
     */
    protected function _prepareSuggestionsQueryResponse($response)
    {
        $suggestions = array();

        if (array_key_exists('spellcheck', $response) && array_key_exists('suggestions', $response->spellcheck)) {
            $arrayResponse = $this->_objectToArray($response->spellcheck->suggestions);
            if (is_array($arrayResponse)) {
                foreach ($arrayResponse as $item) {
                    if (isset($item['suggestion']) && is_array($item['suggestion']) && !empty($item['suggestion'])) {
                        $suggestions = array_merge($suggestions, $item['suggestion']);
                    }
                }
            }

            // It is assumed that the frequency corresponds to the number of results
            if (count($suggestions)) {
                usort($suggestions, array(get_class($this), 'sortSuggestions'));
            }
        }

        return $suggestions;
    }

    /**
     * Convert Solr Query Response found facets to array
     *
     * @param object $response
     * @return array
     */
    protected function _prepareFacetsQueryResponse($response)
    {
        return $this->_facetObjectToArray($response->facet_counts);
    }

    /**
     * Convert Solr Query Response collected statistics to array
     *
     * @param object $response
     * @return array
     */
    protected function _prepateStatsQueryResponce($response)
    {
        return $this->_objectToArray($response->stats->stats_fields);
    }

    /**
     * Callback function for sort search suggestions
     *
     * @param   array $a
     * @param   array $b
     * @return  int
     */
    public static function sortSuggestions($a, $b)
    {
        return $a['freq'] > $b['freq'] ? -1 : ($a['freq'] < $b['freq'] ? 1 : 0);
    }

    /**
     * Escape query text
     *
     * @param string $text
     * @return string
     */
    protected function _prepareQueryText($text)
    {
        $words = explode(' ', $text);
        if (count($words) > 1) {
            foreach ($words as $key => &$val) {
                if (!empty($val)) {
                    $val = $this->_escape($val);
                } else {
                    unset($words[$key]);
                }
            }
            $text = '(' . implode(' ', $words) . ')';
        } else {
            $text = $this->_escape($text);
        }

        return $text;
    }

    /**
     * Escape filter query text
     *
     * @param string $text
     * @return string
     */
    protected function _prepareFilterQueryText($text)
    {
        $words = explode(' ', trim($text));
        if (count($words) > 1) {
            $text = $this->_phrase($text);
        } else {
            $text = $this->_escape($text);
        }

        return $text;
    }

    /**
     * Implode index array to string by separator
     * Support 2 level array gluing
     *
     * @param array $indexData
     * @param string $separator
     * @return string
     */
    protected function _implodeIndexData($indexData, $separator = ' ')
    {
        if (!$indexData) {
            return '';
        }
        if (is_string($indexData)) {
            return $indexData;
        }

        $_index = array();
        if (!is_array($indexData)) {
            $indexData = array($indexData);
        }

        foreach ($indexData as $value) {
            if (!is_array($value)) {
                $_index[] = $value;
            } else {
                $_index = array_merge($_index, $value);
            }
        }
        $_index = array_unique($_index);

        return implode($separator, $_index);
    }

    /**
     * Escape a value for special query characters such as ':', '(', ')', '*', '?', etc.
     *
     * @link http://lucene.apache.org/java/docs/queryparsersyntax.html#Escaping%20Special%20Characters
     *
     * @param string $value
     * @return string
     */
    public function _escape($value)
    {
        $pattern = '/(\+|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }

    /**
     * Escape a value meant to be contained in a phrase for special query characters
     *
     * @param string $value
     * @return string
     */
    public function _escapePhrase($value)
    {
        $pattern = '/("|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }

    /**
     * Convenience function for creating phrase syntax from a value
     *
     * @param string $value
     * @return string
     */
    public function _phrase($value)
    {
        return '"' . $this->_escapePhrase($value) . '"';
    }

    /**
     * Prepare solr field condition
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    protected function _prepareFieldCondition($field, $value)
    {
        $fieldCondition = $field . ':' . $value;

        return $fieldCondition;
    }

    /**
     * Convert an object to an array
     *
     * @param object $object The object to convert
     * @return array
     */
    protected function _objectToArray($object)
    {
        if (!is_object($object) && !is_array($object)) {
            return $object;
        }
        if (is_object($object)) {
            $object = get_object_vars($object);
        }

        return array_map(array($this, '_objectToArray'), $object);
    }

    /**
     * Convert facet results object to an array
     *
     * @param   object|array $object
     * @return  array
     */
    protected function _facetObjectToArray($object)
    {
        if (!is_object($object) && !is_array($object)) {
            return $object;
        }

        if (is_object($object)) {
            $object = get_object_vars($object);
        }

        $res = array();
        foreach ($object['facet_fields'] as $attr => $val) {
            foreach ($val as $key => $value) {
                $res[$attr][$key] = $value;
            }
        }

        foreach ($object['facet_queries'] as $attr => $val) {
            $attrArray = explode(':', $attr);
            $res[$attrArray[0]][$attrArray[1]] = $val;
        }

        return $res;
    }

    /**
     * Hold commit of changes for adapter
     *
     * @return Enterprise_Search_Model_Adapter_Abstract
     */
    public function holdCommit()
    {
        $this->_holdCommit = true;
        return $this;
    }

    /**
     * Allow changes commit for adapter
     *
     * @return Enterprise_Search_Model_Adapter_Abstract
     */
    public function allowCommit()
    {
        $this->_holdCommit = false;
        return $this;
    }

    /**
     * Define if third party search engine index needs optimization
     *
     * @param  bool $state
     * @return Enterprise_Search_Model_Adapter_Abstract
     */
    public function setIndexNeedsOptimization($state = true)
    {
        $this->_indexNeedsOptimization = (bool)$state;
        return $this;
    }

    /**
     * Check if third party search engine index needs optimization
     *
     * @return bool
     */
    public function getIndexNeedsOptimization()
    {
        return $this->_indexNeedsOptimization;
    }

    // Deprecated methods

    /**
     * Filter index data by common Solr metadata fields
     * Add language code suffix to text fields
     *
     * @deprecated after 1.8.0.0 - use $this->_prepareIndexData()
     * @see $this->_usedFields, $this->_searchTextFields
     *
     * @param  array $data
     * @param  string|null $localeCode
     * @return array
     */
    protected function _filterIndexData($data, $localeCode = null)
    {
        if (empty($data) || !is_array($data)) {
            return array();
        }

        foreach ($data as $code => $value) {
            if (!in_array($code, $this->_usedFields) && strpos($code, 'fulltext') !== 0) {
                unset($data[$code]);
            }
        }

        $languageCode = $this->_getLanguageCodeByLocaleCode($localeCode);
        if ($languageCode) {
            foreach ($data as $key => $value) {
                if (in_array($key, $this->_searchTextFields) || strpos($key, 'fulltext') === 0) {
                    $data[$key . '_' . $languageCode] = $value;
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }

    /**
     * Retrieve default searchable fields
     *
     * @deprecated after 1.11.0.0
     *
     * @return array
     */
    public function getSearchTextFields()
    {
        return $this->_searchTextFields;
    }

    /**
     * Create Solr Input Documents by specified data
     *
     * @deprecated after 1.11.2.0
     *
     * @param  array $docData
     * @param  string|null $localeCode
     * @return array
     */
    public function prepareDocs($docData, $localeCode)
    {
        return array();
    }

    /**
     * Retrieve attributes selected parameters
     *
     * @deprecated after 1.11.2.0
     *
     * @return  array
     */
    protected function _getIndexableAttributeParams()
    {
        if ($this->_indexableAttributeParams === null) {
            $attributeCollection = Mage::getResourceSingleton('catalog/product_attribute_collection')
                    ->addToIndexFilter()
                    ->getItems();

            $this->_indexableAttributeParams = array();
            foreach ($attributeCollection as $item) {
                $this->_indexableAttributeParams[$item->getAttributeCode()] = array(
                    'backendType' => $item->getBackendType(),
                    'frontendInput' => $item->getFrontendInput(),
                    'searchWeight' => $item->getSearchWeight(),
                    'isSearchable' => (bool)$item->getIsSearchable()
                );
            }
        }

        return $this->_indexableAttributeParams;
    }

    /**
     * Ability extend document index data.
     *
     * @deprecated after 1.11.2.0
     *
     * @param   array $data
     * @param   array $attributesParams
     * @param   string|null $localeCode
     *
     * @return  array
     */
    protected function _prepareIndexData($data, $attributesParams = array(), $localeCode = null)
    {
        return $data;
    }
}
