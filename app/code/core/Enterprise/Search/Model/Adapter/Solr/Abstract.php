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
 * Solr search engine abstract adapter
 *
 * @category   Enterprise
 * @package    Enterprise_Search
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_Search_Model_Adapter_Solr_Abstract extends Enterprise_Search_Model_Adapter_Abstract
{
    /**
     * Default number of rows to select
     */
    const DEFAULT_ROWS_LIMIT        = 9999;

    /**
     * Default suggestions count
     */
    const DEFAULT_SPELLCHECK_COUNT  = 1;

    /**
     * Default timeout in seconds
     */
    const DEFAULT_TIMEOUT = 15;



    /**
     * Define ping status
     *
     * @var float | bool
     */
    protected $_ping = null;

    /**
     * Array of Zend_Date objects per store
     *
     * @var array
     */
    protected $_dateFormats = array();

    /**
     * Advanced index fields prefix
     *
     * @var string
     */
    protected $_advancedIndexFieldsPrefix = '';



    /**
     * Prepare connect options list and load default values for not set params
     *
     * @param array $options
     *
     * @return array
     */
    protected function _initConnectOptions(array $options = array())
    {
        $helper = Mage::helper('enterprise_search');
        $defaultOptions = array(
            'hostname' => $helper->getSolrConfigData('server_hostname'),
            'login'    => $helper->getSolrConfigData('server_username'),
            'password' => $helper->getSolrConfigData('server_password'),
            'port'     => $helper->getSolrConfigData('server_port'),
            'timeout'  => $helper->getSolrConfigData('server_timeout'),
            'path'     => $helper->getSolrConfigData('server_path')
        );

        return array_merge($defaultOptions, $options);
    }

    /**
     * Set advanced index fields prefix
     *
     * @param string $prefix
     */
    public function setAdvancedIndexFieldPrefix($prefix)
    {
        $this->_advancedIndexFieldsPrefix = $prefix;
    }

    /**
     * Retrieve language code by specified locale code if this locale is supported by Solr
     *
     * @param string $localeCode
     * @return false|string
     */
    protected function _getLanguageCodeByLocaleCode($localeCode)
    {
        return Mage::helper('enterprise_search')->getLanguageCodeByLocaleCode($localeCode);
    }

    /**
     * Prepare language suffix for text fields.
     * For not supported languages prefix _def will be returned.
     *
     * @param  string $localeCode
     * @return string
     */
    protected function _getLanguageSuffix($localeCode)
    {
        return Mage::helper('enterprise_search')->getLanguageSuffix($localeCode);
    }

    /**
     * Retrieve date value in solr format (ISO 8601) with Z
     * Example: 1995-12-31T23:59:59Z
     *
     * @param int $storeId
     * @param string $date
     *
     * @return string|null
     */
    protected function _getSolrDate($storeId, $date = null)
    {
        if (!isset($this->_dateFormats[$storeId])) {
            $timezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE, $storeId);
            $locale   = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $storeId);
            $locale   = new Zend_Locale($locale);

            $dateObj  = new Zend_Date(null, null, $locale);
            $dateObj->setTimezone($timezone);
            $this->_dateFormats[$storeId] = array($dateObj, $locale->getTranslation(null, 'date', $locale));
        }

        if (is_empty_date($date)) {
            return null;
        }

        list($dateObj, $localeDateFormat) = $this->_dateFormats[$storeId];
        $dateObj->setDate($date, $localeDateFormat);

        return $dateObj->toString(Zend_Date::ISO_8601) . 'Z';
    }

    /**
     * Prepare search conditions from query
     *
     * @param string|array $query
     *
     * @return string
     */
    protected function prepareSearchConditions($query)
    {
        if (is_array($query)) {
            $searchConditions = array();
            foreach ($query as $field => $value) {
                if (is_array($value)) {
                    if ($field == 'price' || isset($value['from']) || isset($value['to'])) {
                        $from = (isset($value['from']) && strlen(trim($value['from'])))
                            ? $this->_prepareQueryText($value['from'])
                            : '*';
                        $to = (isset($value['to']) && strlen(trim($value['to'])))
                            ? $this->_prepareQueryText($value['to'])
                            : '*';
                        $fieldCondition = "$field:[$from TO $to]";
                    } else {
                        $fieldCondition = array();
                        foreach ($value as $part) {
                            $part = $this->_prepareFilterQueryText($part);
                            $fieldCondition[] = $field . ':' . $part;
                            if ($field == 'sku') {
                                $fieldCondition[] = 'partial_' . $field . ':' . $part;
                            }
                        }
                        $fieldCondition = '(' . implode(' OR ', $fieldCondition) . ')';
                    }
                } else {
                    if ($value != '*') {
                        $value = $this->_prepareQueryText($value);
                    }
                    $fieldCondition = $field . ':' . $value;
                }

                $searchConditions[] = $fieldCondition;
            }

            $searchConditions = implode(' AND ', $searchConditions);
        } else {
            $searchConditions = $this->_prepareQueryText($query);
        }

        return $searchConditions;
    }

    /**
     * Prepare facet fields conditions
     *
     * @param array $facetFields
     * @return array
     */
    protected function _prepareFacetConditions($facetFields)
    {
        $result = array();

        if (is_array($facetFields)) {
            $result['facet'] = 'on';
            foreach ($facetFields as $facetField => $facetFieldConditions) {
                if (empty($facetFieldConditions)) {
                    $result['facet.field'][] = $facetField;
                } else {
                    foreach ($facetFieldConditions as $facetCondition) {
                        if (is_array($facetCondition) && isset($facetCondition['from'])
                            && isset($facetCondition['to'])
                        ) {
                            $from = strlen(trim($facetCondition['from']))
                                ? $facetCondition['from']
                                : '*';
                            $to = strlen(trim($facetCondition['to']))
                                ? $facetCondition['to']
                                : '*';
                            $fieldCondition = "$facetField:[$from TO $to]";
                        } else {
                            $facetCondition = $this->_prepareQueryText($facetCondition);
                            $fieldCondition = $this->_prepareFieldCondition($facetField, $facetCondition);
                        }

                        $result['facet.query'][] = $fieldCondition;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Prepare fq filter conditions
     *
     * @param array $filters
     * @return array
     */
    protected function _prepareFilters($filters)
    {
        $result = array();

        if (is_array($filters) && !empty($filters)) {
            foreach ($filters as $field => $value) {
                if (is_array($value)) {
                    if (isset($value['from']) || isset($value['to'])) {
                        $from = (isset($value['from']) && !empty($value['from']))
                            ? $value['from']
                            : '*';
                        $to = (isset($value['to']) && !empty($value['to']))
                            ? $value['to']
                            : '*';
                        $fieldCondition = "$field:[$from TO $to]";
                    } else {
                        $fieldCondition = array();
                        foreach ($value as $part) {
                            $part = $this->_prepareFilterQueryText($part);
                            $fieldCondition[] = $this->_prepareFieldCondition($field, $part);
                        }
                        $fieldCondition = '(' . implode(' OR ', $fieldCondition) . ')';
                    }
                } else {
                    $value = $this->_prepareFilterQueryText($value);
                    $fieldCondition = $this->_prepareFieldCondition($field, $value);
                }

                $result[] = $fieldCondition;
            }
        }

        return $result;
    }

    /**
     * Prepare sort fields
     *
     * @param array $sortBy
     * @return array
     */
    protected function _prepareSortFields($sortBy)
    {
        $result = array();

        $localeCode = Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
        $languageSuffix = $this->_getLanguageSuffix($localeCode);

        /**
         * Support specifying sort by field as only string name of field
         */
        if (!empty($sortBy) && !is_array($sortBy)) {
            if ($sortBy == 'relevance') {
                $sortBy = 'score';
            } elseif ($sortBy == 'name') {
                $sortBy = 'alphaNameSort' . $languageSuffix;
            } elseif ($sortBy == 'position') {
                $sortBy = 'position_category_' . Mage::registry('current_category')->getId();
            } elseif ($sortBy == 'price') {
                $websiteId       = Mage::app()->getStore()->getWebsiteId();
                $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

                $sortBy = 'price_'. $customerGroupId .'_'. $websiteId;
            }

            $sortBy = array(array($sortBy => 'asc'));
        }

        foreach ($sortBy as $sort) {
            $_sort = each($sort);
            $sortField = $_sort['key'];
            $sortType = $_sort['value'];
            if ($sortField == 'relevance' || $sortField == 'score') {
                $sortField = 'score';
            } elseif ($sortField == 'position') {
                $sortField = 'position_category_' . Mage::registry('current_category')->getId();
            } elseif ($sortField == 'price') {
                $sortField = $this->getPriceFieldName();
            } else {
                $sortField = $this->getSearchEngineFieldName($sortField, 'sort');
            }

            $result[] = array('sortField' => $sortField, 'sortType' => trim(strtolower($sortType)));
        }

        return $result;
    }

    /**
     * Retrieve Solr server status
     *
     * @return  float|bool Actual time taken to ping the server, FALSE if timeout or HTTP error status occurs
     */
    public function ping()
    {
        if (is_null($this->_ping)) {
            try {
                $this->_ping = $this->_client->ping();
            } catch (Exception $e) {
                $this->_ping = false;
            }
        }

        return $this->_ping;
    }

    /**
     * Prepare name for system text fields.
     *
     * @param   string $filed
     * @param   string $suffix
     * @param   null|int $storeId
     *
     * @return  string
     */
    public function getAdvancedTextFieldName($filed, $suffix = '', $storeId = null)
    {
        if ($storeId !== null) {
            $localeCode = Mage::app()->getStore($storeId)->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
            $languageSuffix = Mage::helper('enterprise_search')->getLanguageSuffix($localeCode);
        } else {
            $languageSuffix = '';
        }

        if ($suffix) {
            $suffix = '_' . $suffix;
        }

        return $filed . $suffix . $languageSuffix;
    }

    /**
     * Retrieve attribute solr field name
     *
     * @param   Mage_Catalog_Model_Resource_Eav_Attribute|string $attribute
     * @param   string $target - default|sort|nav
     *
     * @return  string|bool
     */
    public function getSearchEngineFieldName($attribute, $target = 'default')
    {
        if (is_string($attribute)) {
            if ($attribute == 'price') {
                return $this->getPriceFieldName();
            }

            $eavConfig  = Mage::getSingleton('eav/config');
            $entityType = $eavConfig->getEntityType('catalog_product');
            $attribute  = $eavConfig->getAttribute($entityType, $attribute);
        }

        // Field type defining
        $attributeCode = $attribute->getAttributeCode();
        if ($attributeCode == 'sku') {
            if ($target == 'sort') {
                return 'attr_sort_sku';
            } else {
                return 'sku';
            }
        }

        if ($attributeCode == 'price') {
            return $this->getPriceFieldName();
        }

        $backendType    = $attribute->getBackendType();
        $frontendInput  = $attribute->getFrontendInput();

        if ($frontendInput == 'multiselect') {
            $fieldType = 'multi';
        } elseif ($frontendInput == 'select' || $frontendInput == 'boolean') {
            $fieldType = 'select';
        } elseif ($backendType == 'decimal' || $backendType == 'datetime') {
            $fieldType = $backendType;
        } else {
            $fieldType = 'text';
        }

        // Field prefix construction. Depends on field usage purpose - default, sort, navigation
        $fieldPrefix = 'attr_';
        if ($target == 'sort') {
            $fieldPrefix .= $target . '_';
        } elseif ($target == 'nav') {
            if ($attribute->getIsFilterable() || $attribute->getIsFilterableInSearch() || $attribute->usesSource()) {
                $fieldPrefix .= $target . '_';
            }
        }

        if ($fieldType == 'text') {
            $localeCode     = Mage::app()->getStore($attribute->getStoreId())
                ->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
            $languageSuffix = Mage::helper('enterprise_search')->getLanguageSuffix($localeCode);
            $fieldName      = $fieldPrefix . $attributeCode . $languageSuffix;
        } else {
            $fieldName      = $fieldPrefix . $fieldType . '_' . $attributeCode;
        }

        return $fieldName;
    }





    // Deprecated methods

    /**
     * Prepare index data for using in Solr metadata.
     * Add language code suffix to text fields and type suffix for not text dynamic fields.
     * Prepare sorting fields.
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
        $productId  = $data['id'];
        $storeId    = $data['store_id'];

        if ($productId && $storeId) {
            return $this->_prepareIndexProductData($data, $productId, $storeId);
        }

        return array();
    }

    /**
     * Retrieve attribute field's name for sorting
     *
     * @deprecated after 1.11.2.0
     *
     * @param string $attributeCode
     *
     * @return string
     */
    public function getAttributeSolrFieldName($attributeCode)
    {
        return $this->getSearchEngineFieldName($attributeCode, 'sort');
    }
}
