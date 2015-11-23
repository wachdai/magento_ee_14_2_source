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
 * Enterprise search helper
 *
 * @category   Enterprise
 * @package    Enterprise_Search
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Search_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Define if search engine is used for layered navigation
     *
     * @var bool|null
     */
    protected $_useEngineInLayeredNavigation    = null;

    /**
     * Store languag codes for local codes
     *
     * @var array
     */
    protected $_languageCode                    = array();

    /**
     * Store result of third party search engine availability check
     *
     * @var bool|null
     */
    protected $_isThirdPartyEngineAvailable     = null;

    /**
     * Show if taxes have influence on price
     *
     * @var bool|null
     */
    protected $_taxInfluence                    = null;

    /**
     * Define if engine is available for layered navigation
     *
     * @var bool|null
     */
    protected $_isEngineAvailableForNavigation  = null;

    /**
     * Define text type fields
     *
     * @var array
     */
    protected $_textFieldTypes = array(
        'text',
        'varchar'
    );

    /**
     * Retrieve text field types
     *
     * @return array
     */
    public function getTextFieldTypes()
    {
        return $this->_textFieldTypes;
    }

    /**
     * Retrieve supported by Solr languages including locale codes (language codes) that are specified in configuration
     * Array(
     *      'language_code1' => 'locale_code',
     *      'language_code2' => Array('locale_code1', 'locale_code2')
     * )
     *
     * @return array
     */
    public function getSolrSupportedLanguages()
    {
        $default = array(
            /**
             * SnowBall filter based
             */
            //Danish
            'da' => 'da_DK',
            //Dutch
            'nl' => 'nl_NL',
            //English
            'en' => array('en_AU', 'en_CA', 'en_NZ', 'en_GB', 'en_US'),
            //Finnish
            'fi' => 'fi_FI',
            //French
            'fr' => array('fr_CA', 'fr_FR'),
            //German
            'de' => array('de_DE','de_CH','de_AT'),
            //Italian
            'it' => array('it_IT','it_CH'),
            //Norwegian
            'nb' => array('nb_NO', 'nn_NO'),
            //Portuguese
            'pt' => array('pt_BR', 'pt_PT'),
            //Romanian
            'ro' => 'ro_RO',
            //Russian
            'ru' => 'ru_RU',
            //Spanish
            'es' => array('es_AR', 'es_CL', 'es_CO', 'es_CR', 'es_ES', 'es_MX', 'es_PA', 'es_PE', 'es_VE'),
            //Swedish
            'sv' => 'sv_SE',
            //Turkish
            'tr' => 'tr_TR',

            /**
             * Lucene class based
             */
            //Czech
            'cs' => 'cs_CZ',
            //Greek
            'el' => 'el_GR',
            //Thai
            'th' => 'th_TH',
            //Chinese
            'zh' => array('zh_CN', 'zh_HK', 'zh_TW'),
            //Japanese
            'ja' => 'ja_JP',
            //Korean
            'ko' => 'ko_KR'
        );

        /**
         * Merging languages that specified manualy
         */
        $node = Mage::getConfig()->getNode('global/enterprise_search/supported_languages/solr');
        if ($node && $node->children()) {
            foreach ($node->children() as $_node) {
                $localeCode = $_node->getName();
                $langCode   = $_node . '';
                if (isset($default[$langCode])) {
                    if (is_array($default[$langCode])) {
                        if (!in_array($localeCode, $default[$langCode])) {
                            $default[$langCode][] = $localeCode;
                        }
                    } elseif ($default[$langCode] != $localeCode) {
                        $default[$langCode] = array($default[$langCode], $localeCode);
                    }
                } else {
                    $default[$langCode] = $localeCode;
                }
            }
        }

        return $default;
    }

    /**
     * Retrieve information from Solr search engine configuration
     *
     * @param string $field
     * @param int $storeId
     * @return string|int
     */
    public function getSolrConfigData($field, $storeId = null)
    {
        return $this->getSearchConfigData('solr_' . $field, $storeId);
    }

    /**
     * Retrieve information from search engine configuration
     *
     * @param string $field
     * @param int $storeId
     * @return string|int
     */
    public function getSearchConfigData($field, $storeId = null)
    {
        $path = 'catalog/search/' . $field;
        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Return true if third party search engine is used
     *
     * @return bool
     */
    public function isThirdPartSearchEngine()
    {
        $engine = $this->getSearchConfigData('engine');
        if ($engine == 'enterprise_search/engine') {
            return true;
        }

        return false;
    }

    /**
     * Retrieve language code by specified locale code if this locale is supported
     *
     * @param  string $localeCode
     * @return string|false
     */
    public function getLanguageCodeByLocaleCode($localeCode)
    {
        $localeCode = (string)$localeCode;
        if (!$localeCode) {
            return false;
        }

        if (!isset($this->_languageCode[$localeCode])) {
            $languages = $this->getSolrSupportedLanguages();

            $this->_languageCode[$localeCode] = false;
            foreach ($languages as $code => $locales) {
                if (is_array($locales)) {
                    if (in_array($localeCode, $locales)) {
                        $this->_languageCode[$localeCode] = $code;
                    }
                } elseif ($localeCode == $locales) {
                    $this->_languageCode[$localeCode] = $code;
                }
            }
        }

        return $this->_languageCode[$localeCode];
    }

    /**
     * Prepare language suffix for text fields.
     * For not supported languages prefix _def will be returned.
     *
     * @param  string $localeCode
     * @return string
     */
    public function getLanguageSuffix($localeCode)
    {
        $languageCode = $this->getLanguageCodeByLocaleCode($localeCode);
        if (!$languageCode) {
            $languageCode = 'def';
        }
        $languageSuffix = '_' . $languageCode;

        return $languageSuffix;
    }

    /**
     * Retrieve filter array
     *
     * @deprecated since 1.12.0.0
     *
     * @param Enterprise_Search_Model_Resource_Collection $collection
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param string|array $value
     * @return array
     */
    public function getSearchParam($collection, $attribute, $value)
    {
        if (empty($value)
            || (isset($value['from']) && empty($value['from'])
                && isset($value['to']) && empty($value['to'])
            )
        ) {
            return false;
        }

        $locale = Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
        $languageSuffix = $this->getLanguageSuffix($locale);

        $field = $attribute->getAttributeCode();
        $backendType = $attribute->getBackendType();
        $frontendInput = $attribute->getFrontendInput();

        if ($frontendInput == 'multiselect') {
            $field = 'attr_multi_'. $field;
        } elseif ($backendType == 'decimal') {
            $field = 'attr_decimal_'. $field;
        } elseif ($frontendInput == 'select' || $frontendInput == 'boolean') {
            $field = 'attr_select_'. $field;
        } elseif ($backendType == 'datetime') {
            $field = 'attr_datetime_'. $field;

            $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            if (is_array($value)) {
                foreach ($value as &$val) {
                    if (!is_empty_date($val)) {
                        $date = new Zend_Date($val, $format);
                        $val = $date->toString(Zend_Date::ISO_8601) . 'Z';
                    }
                }
            } else {
                if (!is_empty_date($value)) {
                    $date = new Zend_Date($value, $format);
                    $value = $date->toString(Zend_Date::ISO_8601) . 'Z';
                }
            }
        } elseif (in_array($backendType, $this->_textFieldTypes)) {
            $field .= $languageSuffix;
        }

        if ($attribute->usesSource()) {
            $attribute->setStoreId(Mage::app()->getStore()->getId());
        }

        return array($field => $value);
    }

    /**
     * Check if enterprise engine is available
     *
     * @return bool
     */
    public function isActiveEngine()
    {
        $engine = $this->getSearchConfigData('engine');

        if ($engine && Mage::getConfig()->getResourceModelClassName($engine)) {
            $model = Mage::getResourceSingleton($engine);
            if ($model && $model->test() && $model->allowAdvancedIndex()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if third party engine is selected and active
     *
     * @return bool
     */
    public function isThirdPartyEngineAvailable()
    {
        if ($this->_isThirdPartyEngineAvailable === null) {
            $this->_isThirdPartyEngineAvailable = ($this->isThirdPartSearchEngine() && $this->isActiveEngine());
        }

        return $this->_isThirdPartyEngineAvailable;
    }

    /**
     * Check if taxes have influence on price
     *
     * @return bool
     */
    public function getTaxInfluence()
    {
        if (is_null($this->_taxInfluence)) {
            $this->_taxInfluence = (bool) Mage::helper('tax')->getPriceTaxSql('price', 'tax');
        }

        return $this->_taxInfluence;
    }

    /**
     * Check if search engine can be used for catalog navigation
     *
     * @param   bool $isCatalog - define if checking availability for catalog navigation or search result navigation
     * @return  bool
     */
    public function getIsEngineAvailableForNavigation($isCatalog = true)
    {
        if (is_null($this->_isEngineAvailableForNavigation)) {
            $this->_isEngineAvailableForNavigation = false;
            if ($this->isActiveEngine()) {
                if ($isCatalog) {
                    if ($this->getSearchConfigData('solr_server_use_in_catalog_navigation')
                        && !$this->getTaxInfluence()
                    ) {
                        $this->_isEngineAvailableForNavigation = true;
                    }
                } else {
                    $this->_isEngineAvailableForNavigation = true;
                }
            }
        }

        return $this->_isEngineAvailableForNavigation;
    }





    // Deprecated methods

    /**
     * Retrieve attribute field's name
     *
     * @deprecated after 1.11.2.0
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     *
     * @return string
     */
    public function getAttributeSolrFieldName($attribute)
    {
        return '';
    }
}
