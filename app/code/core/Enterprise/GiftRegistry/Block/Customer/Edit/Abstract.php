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
 * Customer giftregistry list block
 *
 * @category   Enterprise
 * @package    Enterprise_GiftRegistry
 */
abstract class Enterprise_GiftRegistry_Block_Customer_Edit_Abstract extends Mage_Directory_Block_Data
{

    /**
     * Registry Entity object
     *
     * @var Enterprise_GiftRegistry_Model_Entity
     */
    protected $_entity = null;

    /**
     * Attribute groups array
     *
     * @var array
     */
    protected $_groups = null;

    /**
     * Static types fields holder
     *
     * @var array
     */
    protected $_staticTypes = array();

    /**
     * Scope Selector 'registry/registrant'
     *
     * @var string
     */
    protected $_prefix;

    /**
     * Getter, return entity object , instantiated in controller
     *
     * @return Enterprise_GiftRegistry_Model_Entity
     */
    public function getEntity()
    {
        return Mage::registry('enterprise_giftregistry_entity');
    }

    /**
     * Getter for CustomAttributes Array
     *
     * @return array
     */
    public function getCustomAttributes()
    {
        return $this->getEntity()->getCustomAttributes();
    }

    /**
     * Check if attribute is required
     *
     * @param array $data
     * @return bool
     */
    public function isAttributeRequired($data)
    {
        if (isset($data['frontend']) && is_array($data['frontend']) && !empty($data['frontend']['is_required'])) {
            return true;
        }
        return false;
    }

    /**
     * Check if attribute needs region updater js object
     *
     * @param array $data
     * @return bool
     */
    public function useRegionUpdater($data)
    {
        return ($data['type'] == 'country' && !empty($data['show_region']));
    }

    /**
     * Check if attribute is static
     *
     * @param string $code
     * @return bool
     */
    public function isAttributeStatic($code)
    {
        $types = Mage::getSingleton('enterprise_giftregistry/attribute_config')->getStaticTypesCodes();
        if (in_array($code, $types)) {
            return true;
        }
        return false;
    }

    /**
     * Return array of attribute groups for using as options
     *
     * @return array
     */
    public function getAttributeGroups()
    {
        return Mage::getSingleton('enterprise_giftregistry/attribute_config')->getAttributeGroups();
    }

    /**
     * Return group label
     *
     * @param string $groupId
     * @return string
     */
    public function getGroupLabel($groupId)
    {
        if ($this->_groups === null) {
            $this->_groups = Mage::getSingleton('enterprise_giftregistry/attribute_config')->getAttributeGroups();
        }
        if (is_array($this->_groups) && (!empty($this->_groups[$groupId]))
            && is_array($this->_groups[$groupId]) && !empty($this->_groups[$groupId]['label'])) {
            $label = $this->_groups[$groupId]['label'];
        } else {
            $label = $groupId;
        }
        return $label;
    }

    /**
     * JS Calendar html
     *
     * @param string $name   - DOM name
     * @param string $id     - DOM id
     * @param string $value
     * @param string $format  - full|long|medium|short
     * @param string $class
     *
     * @return string
     */
    public function getCalendarDateHtml($name, $id, $value, $format = false, $class = '')
    {
        if ($format === false) {
            $format = Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM;
        }

        $calendar = $this->getLayout()
            ->createBlock('enterprise_giftregistry/customer_date')
            ->setId($id)
            ->setName($name)
            ->setValue($this->formatDate($value, $format))
            ->setClass($class . ' product-custom-option datetime-picker input-text validate-date')
            ->setImage($this->getSkinUrl('images/calendar.gif'))
            ->setFormat(Mage::app()->getLocale()->getDateStrFormat($format));
        return $calendar->getHtml();
    }

    /**
     * Select element for choosing attribute group
     *
     * @return string
     */
    public function getSelectHtml($options, $name, $id, $value = false, $class = '')
    {
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setData(array(
                'id'    => $id,
                'class' => 'select global-scope '. $class
            ))
            ->setName($name)
            ->setValue($value)
            ->setOptions($options);
        return $select->getHtml();
    }

    /**
     * Reorder attributes array by group
     *
     * @param array $attributes
     * @return array
     */
    protected function _groupAttributes($attributes)
    {
        $grouped = array();
        if (is_array($attributes)) {
            foreach ($attributes as $field => $fdata){
                if (is_array($fdata)) {
                    $grouped[$fdata['group']][$field] = $fdata;
                    $grouped[$fdata['group']][$field]['id'] = $this->_getElementId($field);
                    $grouped[$fdata['group']][$field]['name'] = $this->_getElementName($field);

                    if ($fdata['type'] == 'country' && !empty($fdata['show_region'])) {
                        $regionCode = $field . '_region';
                        $regionAttribute['label'] = $this->__('State/Province');
                        $regionAttribute['group'] = $fdata['group'];
                        $regionAttribute['type'] = 'region';
                        $regionAttribute['id'] = $this->_getElementId($regionCode);
                        $regionAttribute['name'] = $this->_getElementName($regionCode);
                        $grouped[$fdata['group']][$regionCode] = $regionAttribute;
                    }
                }
            }
        }
        return $grouped;
    }

    /**
     * Prepare html element name
     *
     * @param string $code
     * @return string
     */
    protected function _getElementName($code)
    {
        if (!$this->isAttributeStatic($code)) {
            return $this->_prefix . '[' . $code . ']';
        }
        return $code;
    }

    /**
     * Prepare html element id
     *
     * @param string $code
     * @return string
     */
    protected function _getElementId($code)
    {
        return $code;
    }

    /**
     * Get current type Id
     *
     * @return int
     */
    public function getTypeId()
    {
        return $this->getEntity()->getTypeId();
    }

    /**
     * Get current type label
     *
     * @return string
     */
    public function getTypeLabel()
    {
        return $this->getEntity()->getTypeLabel();
    }

    /**
     * Reorder data in group array for internal use
     *
     * @param array $selectOptions
     * @return array
     */
    protected function _convertGroupArray($selectOptions)
    {
        $data = array();
        if (is_array($selectOptions)) {
            $data[] = array('label' => $this->__('Please Select'), 'value' => '');
            foreach ($selectOptions as $option) {
                $data[] = array('label' => $option['label'], 'value' => $option['code']);
            }
        }
        return $data;
    }

    /**
     * Render input field of the specific type : text, select, date, region, country
     *
     * @param array $data
     * @param string $field
     * @param string $value
     * @return string
     */
    public function renderField($data, $field, $value = null)
    {
        $element = '';
        if ($field && is_array($data)) {
            $type  = $data['type'];
            $name  = $data['name'];
            $id    = $data['id'];
            $value = $this->getEntity()->getFieldValue($id);
            $class = ($this->isAttributeRequired($data)) ? 'required-entry' : '';

            switch ($type) {
                case 'country' :
                    $element = $this->getCountryHtmlSelect($value, $name, $id, $class);
                    break;

                case 'region' :
                    $element = $this->getRegionHtmlSelectEmpty($name, $id, $value, $class);
                    $id = $this->_getElementId($id . '_text');
                    $name = $this->_getElementName($id);
                    $value = $this->getEntity()->getFieldValue($id);
                    $element .= $this->_getInputTextHtml($name, $id, $value, $class);
                    break;

               case 'date' :
                   $format = (isset($data['date_format'])) ? $data['date_format'] : '';
                   $element = $this->getCalendarDateHtml($name, $id, $value, $format, $class);
                   break;

               case 'select' :
                   $options = $this->_convertGroupArray($data['options']);
                   if (empty($value)) {
                       $value = (isset($data['default'])) ? $data['default'] : '';
                   }
                   $element = $this->getSelectHtml($options, $name, $id, $value, $class);
                   break;

               default :
                   $element = $this->_getInputTextHtml($name, $id, $value, $class);
            }
        }
        return $element;
    }

    /**
     * Render "input text" field
     * @param string $name
     * @param string $id
     * @param string $value
     * @param string $class
     * @param string $params additional params
     *
     * @return string
     */
    protected function _getInputTextHtml($name, $id, $value = '', $class = '', $params = '')
    {
        $template = $this->getLayout()->getBlock('giftregistry_edit')->getInputTypeTemplate('text');
        $this->setInputName($name)
            ->setInputId($id)
            ->setInputValue($value)
            ->setInputClass($class)
            ->setInputParams($params);
        if ($template) {
            $this->setScriptPath(Mage::getBaseDir('design'));
            return  $this->fetchView($template);
        }
    }

    /**
     * Return region select html element
     * @param string $name
     * @param string $id
     * @param string $value
     * @param string $class
     * @param string $params additional params
     */
    public function getRegionHtmlSelectEmpty($name, $id, $value = '', $class = '', $params = '', $default = '')
    {
        $template = $this->getLayout()->getBlock('giftregistry_edit')->getInputTypeTemplate('region');
        $this->setSelectRegionName($name)
            ->setSelectRegionId($id)
            ->setSelectRegionValue($value)
            ->setSelectRegionClass($class)
            ->setSelectRegionParams($params)
            ->setSelectRegionDefault($default);
        if ($template) {
            $this->setScriptPath(Mage::getBaseDir('design'));
            return  $this->fetchView($template);
        }
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->setCreateActionUrl($this->getUrl('enterprise_giftregistry/index/addPost'));
        return parent::_toHtml();
    }

    /**
     * Return "create giftregistry" form url
     *
     * @return string
     */
    public function getAddGiftRegistryUrl()
    {
        return $this->getUrl('enterprise_giftregistry/index/addselect');
    }

    /**
     * Return "create giftregistry" form url
     *
     * @return string
     */
    public function getSaveActionUrl()
    {
        return $this->getUrl('enterprise_giftregistry/index/save');
    }

    /**
     * Return array of attributes groupped by group
     *
     * @return array
     */
    public function getGroupedAttributes()
    {
        $attributes = $this->getCustomAttributes();
        if (!empty($attributes[$this->_prefix])) {
            return $this->_groupAttributes($attributes[$this->_prefix]);
        }
        return array();
    }
}
