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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Abstract block to render form elements
 */
class Enterprise_GiftRegistry_Block_Form_Element extends Mage_Core_Block_Template
{
    protected $_countryCollection;
    protected $_regionCollection;

    /**
     * Load country collection
     *
     * @param null|string $country
     * @return Mage_Directory_Model_Mysql4_Country_Collection
     */
    protected function _getCountryCollection()
    {
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getSingleton('directory/country')->getResourceCollection()
                ->loadByStore();
        }
        return $this->_countryCollection;
    }

    /**
     * Load region collection by specified country code
     *
     * @param null|string $country
     * @return Mage_Directory_Model_Mysql4_Region_Collection
     */
    protected function _getRegionCollection($country = null)
    {
        if (!$this->_regionCollection) {
            $this->_regionCollection = Mage::getModel('directory/region')->getResourceCollection()
                ->addCountryFilter($country)
                ->load();
        }
        return $this->_regionCollection;
    }

    /**
     * Try to load country options from cache
     * If it is not exist load options from country collection and save to cache
     *
     * @return array
     */
    protected function _getCountryOptions()
    {
        $options  = false;
        $useCache = Mage::app()->useCache('config');
        if ($useCache) {
            $cacheId = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }

        if ($options == false) {
            $options = $this->_getCountryCollection()->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
            }
        }
        return $options;
    }

    /** Get field name
     *
     * @return string
     */
    protected function _getFieldName($name)
    {
        $name = $this->getFieldNamePrefix() . $name;
        $container = $this->getFieldNameContainer();
        if ($container) {
            $name = $container . '[' . $name .']';
        }
        return $name;
    }

    /** Get field name
     *
     * @return string
     */
    protected function _getFieldId($id)
    {
        return $this->getFieldIdPrefix() . $id;
    }

    /** Get field id prefix
     *
     * @return string
     */
    public function getFieldIdPrefix()
    {
        return $this->getData('field_id_prefix');
    }

    /** Get field name prefix
     *
     * @return string
     */
    public function getFieldNamePrefix()
    {
        return $this->getData('field_name_prefix');
    }

    /** Get field name container
     *
     * @return string
     */
    public function getFieldNameContainer()
    {
        return $this->getData('field_name_container');
    }

    /**
     * Create select html element
     *
     * @param string $name
     * @param string $id
     * @param array $options
     * @param mixed $value
     * @param string $class
     * @return string
     */
    public function getSelectHtml($name, $id, $options = array(), $value = null, $class = '')
    {
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($this->_getFieldName($name))
            ->setId($this->_getFieldId($id))
            ->setClass('select ' . $class)
            ->setValue($value)
            ->setOptions($options);
        return $select->getHtml();
    }

    /**
     * Create country select html element
     *
     * @param string $name
     * @param string $id
     * @param null|string $value
     * @param string $class
     * @return string
     */
    public function getCountryHtmlSelect($name, $id, $value = null, $class = '')
    {
        $options = $this->_getCountryOptions();
        return $this->getSelectHtml($name, $id, $options, $value, $class);
    }

    /**
     * Create region select html element
     *
     * @param string $name
     * @param string $id
     * @param null|int $value
     * @param null|string $country
     * @param string $class
     * @return string
     */
    public function getRegionHtmlSelect($name, $id, $value = null, $country = null, $class = '')
    {
        $options = $this->_getRegionCollection($country)
            ->toOptionArray();
        return $this->getSelectHtml($name, $id, $options, $value, $class);
    }

    /**
     * Create js calendar html
     *
     * @param string $name
     * @param string $id
     * @param string $value
     * @param null|string $format
     * @param string $class
     * @return string
     */
    public function getCalendarDateHtml($name, $id, $value = null, $format = null, $class = '')
    {
        if (is_null($format)) {
            $format = Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM;
        }

        $calendar = $this->getLayout()->createBlock('core/html_date')
            ->setName($this->_getFieldName($name))
            ->setId($this->_getFieldId($id))
            ->setValue($value)
            ->setClass('datetime-picker input-text' . $class)
            ->setImage($this->getSkinUrl('images/calendar.gif'))
            ->setFormat(Mage::app()->getLocale()->getDateStrFormat($format));
        return $calendar->getHtml();
    }

    /**
     * Create input text html element
     *
     * @param string $name
     * @param string $id
     * @param string $value
     * @param string $class
     * @param string $style
     * @return string
     */
    public function getInputTextHtml($name, $id, $value = '', $class = '', $style='')
    {
        $name = $this->_getFieldName($name);
        $id = $this->_getFieldId($id);
        $class = 'input-text ' . $class;

        return '<input class="' . $class  . '" type="text" name="' . $name . '" id="' . $id .
            '" value="' . $value . '" style="' . $style . '"/>';
    }

    /**
     * Convert array to options array for select html element
     *
     * @param array $selectOptions
     * @param bool $withEmpty
     * @return array
     */
    public function convertArrayToOptions($selectOptions, $withEmpty = false) {
        $options = array();
        if ($withEmpty) {
            $options[] = array('value' => '', 'label' => Mage::helper('enterprise_giftregistry')->__('-- Please select --'));
        }
        if (is_array($selectOptions)) {
            foreach ($selectOptions as $code => $option) {
                $options[] = array('label' => $option['label'], 'value' => $option['code']);
            }
        }
        return $options;
    }
}
