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
 * Gift registry advanced search block
 *
 * @category   Enterprise
 * @package    Enterprise_GiftRegistry
 */
class Enterprise_GiftRegistry_Block_Search_Advanced extends Enterprise_GiftRegistry_Block_Form_Element
{
    protected $_attributes = null;
    protected $_formData = null;

    /**
     * Block constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setFieldIdPrefix('params_');
        $this->setFieldNameContainer('params');
    }

    /**
     * Retrieve by key saved in session form data
     *
     * @param string $key
     * @return mixed
     */
    public function getFormData($key)
    {
        if (is_null($this->_formData)) {
            $this->_formData = Mage::getSingleton('customer/session')->getRegistrySearchData();
        }
        if (!$this->_formData || !isset($this->_formData[$key])) {
            return null;
        }
        return $this->escapeHtml($this->_formData[$key]);
    }

    /**
     * Prepare array of searcheable attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        if (is_null($this->_attributes)) {
            $type = Mage::registry('current_giftregistry_type');
            $config = Mage::getSingleton('enterprise_giftregistry/attribute_config');
            $staticTypes = $config->getStaticTypesCodes();

            $attributes = array();
            foreach ($type->getAttributes() as $group) {
                $attributes = array_merge($attributes, $group);
            }

            $isDate = false;
            $isCountry = false;

            foreach ($attributes as $code => $attribute) {
                if (!in_array($code, $staticTypes) || empty($attribute['frontend']['is_searcheable'])) {
                    unset($attributes[$code]);
                    continue;
                }
                switch ($attribute['type']) {
                    case 'date' : $isDate = $code; break;
                    case 'country' : $isCountry = $code; break;
                }
            }

            /*
             * Create date range html elements instead of date select element
             */
            if ($isDate) {
                $fromDate = $isDate . '_from';
                $attributes[$fromDate] = $attributes[$isDate];
                $attributes[$fromDate]['label'] .= ' ' . $this->__('From');

                $toDate = $isDate . '_to';
                $attributes[$toDate] = $attributes[$isDate];
                $attributes[$toDate]['label'] .= ' ' . $this->__('To');

                unset($attributes[$isDate]);
            }

            /*
             * Add region updater js object to form
             */
            if ($isCountry && !empty($attributes[$isCountry]['show_region'])) {
                $region = $config->getStaticRegionType();
                $this->setRegionJsVisible(true)
                    ->setElementCountry($isCountry)
                    ->setElementRegion($region)
                    ->setElementRegionText($region . '_text');

                $regionAttribute['label'] = $this->__('State/Province');
                $regionAttribute['code'] = $region;
                $regionAttribute['type'] = 'region';

                if ($formValue = $this->getFormData($isCountry)) {
                    $regionAttribute['country'] = $formValue;
                }
                $attributes[$region] = $regionAttribute;
            }

            $this->_attributes = $attributes;
        }
        return $this->_attributes;
    }

    /**
     * Render gift registry attribute as html element
     * @param string $code
     * @return string
     */
    public function renderField($code)
    {
        $attributes = $this->getAttributes();
        $element = '';
        $value = $this->getFormData($code);

        if (empty($attributes[$code])) {
            return $element;
        } else {
            $attribute = $attributes[$code];
        }

        switch ($attribute['type']) {
            case 'text' :
                $element = $this->getInputTextHtml($code, $code, $value);
                break;
            case 'select' :
                $options = $this->convertArrayToOptions($attribute['options'], true);
                $element = $this->getSelectHtml($code, $code, $options, $value);
                break;
            case 'date' :
                $element = $this->getCalendarDateHtml($code, $code, $value, $attribute['date_format']);
                break;
            case 'region' :
                $regionCountry = (isset($attribute['country'])) ? $attribute['country'] : null;
                $element = $this->getRegionHtmlSelect($code, $code, $value, $regionCountry);
                if ($this->getRegionJsVisible()) {
                    $code = $this->getElementRegionText();
                    $value = $this->getFormData($code);
                    $element .= $this->getInputTextHtml($code, $code, $value, '', 'display:none');
                }
                break;
            case 'country' :
                $element = $this->getCountryHtmlSelect($code, $code, $value);
                break;
        }
        return $element;
    }
}
