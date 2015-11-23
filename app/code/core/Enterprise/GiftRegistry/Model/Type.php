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
 * Gift registry types processing model
 *
 * @method Enterprise_GiftRegistry_Model_Resource_Type _getResource()
 * @method Enterprise_GiftRegistry_Model_Resource_Type getResource()
 * @method string getCode()
 * @method Enterprise_GiftRegistry_Model_Type setCode(string $value)
 * @method string getMetaXml()
 * @method Enterprise_GiftRegistry_Model_Type setMetaXml(string $value)
 *
 * @category    Enterprise
 * @package     Enterprise_GiftRegistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftRegistry_Model_Type extends Mage_Core_Model_Abstract
{
    protected $_store = null;
    protected $_storeData = null;

    /**
     * Intialize model
     */
    protected function _construct()
    {
        $this->_init('enterprise_giftregistry/type');
    }

    /**
     * Perform actions before object save.
     */
    protected function _beforeSave()
    {
        if (!$this->hasStoreId() && !$this->getStoreId()) {
            $this->_cleanupData();
            $xmlModel = Mage::getModel('enterprise_giftregistry/attribute_processor');
            $this->setMetaXml($xmlModel->processData($this));
        }

        parent::_beforeSave();
    }

    /**
     * Perform actions after object save.
     */
    protected function _afterSave()
    {
        $this->_getResource()->saveTypeStoreData($this);
        if ($this->getStoreId()) {
            $this->_saveAttributeStoreData();
        }
    }

    /**
     * Perform actions after object load
     *
     * @return Enterprise_GiftRegistry_Model_Type
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->assignAttributesStoreData();
        return $this;
    }

    /**
     * Callback function for sorting attributes by sort_order param
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortAttributes($a, $b)
    {
        if ($a['sort_order'] != $b['sort_order']) {
            return ($a['sort_order'] > $b['sort_order']) ? 1 : -1;
        }
        return 0;
    }

    /**
     * Set store id
     *
     * @return Enterprise_GiftRegistry_Model_Type
     */
    public function setStoreId($storeId = null)
    {
        $this->_store = Mage::app()->getStore($storeId);
        return $this;
    }

    /**
     * Retrieve store
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if ($this->_store === null) {
            $this->setStoreId();
        }

        return $this->_store;
    }

    /**
     * Retrieve store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * Save registry type attribute data per store view
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _saveAttributeStoreData()
    {
        if ($groups = $this->getAttributes()) {
            foreach((array)$groups as $attributes) {
                foreach((array)$attributes as $attribute) {
                    $this->_getResource()->saveStoreData($this, $attribute);
                    if (isset($attribute['options']) && is_array($attribute['options'])) {
                        foreach($attribute['options'] as $option) {
                            $optionCode = $option['code'];
                            $option['code'] = $attribute['code'];
                            $this->_getResource()->saveStoreData($this, $option, $optionCode);
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Clear object model from data that should be deleted
     *
     * @return Enterprise_GiftRegistry_Model_Type
     */
    protected function _cleanupData()
    {
        if ($groups = $this->getAttributes()) {
            $attributesToSave = array();
            $config = Mage::getSingleton('enterprise_giftregistry/attribute_config');
            foreach ((array)$groups as $group => $attributes) {
                foreach ((array)$attributes as $attribute) {
                    if ($attribute['is_deleted']) {
                        $this->_getResource()->deleteAttributeStoreData($this->getId(), $attribute['code']);
                        if (in_array($attribute['code'], $config->getStaticTypesCodes())) {
                            $this->_getResource()->deleteAttributeValues(
                                $this->getId(),
                                $attribute['code'],
                                $config->isRegistrantAttribute($attribute['code'])
                            );
                        }
                    } else {
                        if (isset($attribute['options']) && is_array($attribute['options'])) {
                            $optionsToSave = array();
                            foreach ($attribute['options'] as $option) {
                                if ($option['is_deleted']) {
                                  $this->_getResource()->deleteAttributeStoreData(
                                      $this->getId(), $attribute['code'], $option['code']
                                  );
                                } else {
                                    $optionsToSave[] = $option;
                                }
                            }
                            $attribute['options'] = $optionsToSave;
                        }
                        $attributesToSave[$group][] = $attribute;
                    }
                }
                $this->setAttributes($attributesToSave);
            }
        }
        return $this;
    }

    /**
     * Assign attributes store data
     *
     * @return Enterprise_GiftRegistry_Model_Type
     */
    public function assignAttributesStoreData()
    {
        $xmlModel = Mage::getModel('enterprise_giftregistry/attribute_processor');
        $groups = $xmlModel->processXml($this->getMetaXml());
        $storeData = array();

        if (is_array($groups)) {
            foreach ($groups as $group => $attributes) {
                if (!empty($attributes)) {
                    $storeData[$group] = $this->getAttributesStoreData($attributes);
                }
            }
        }
        $this->setAttributes($storeData);
        return $this;
    }

    /**
     * Assign attributes store data
     *
     * @return Enterprise_GiftRegistry_Model_Type
     */
    public function getAttributesStoreData($attributes)
    {
        if (is_array($attributes)) {
            foreach ($attributes as $code => $attribute) {
                if ($storeLabel = $this->getAttributeStoreData($code)) {
                    $attributes[$code]['label'] = $storeLabel;
                    $attributes[$code]['default_label'] = $attribute['label'];
                }
                if (isset($attribute['options']) && is_array($attribute['options'])) {
                    $options = array();
                    foreach ($attribute['options'] as $key => $label) {
                        $data = array('code' => $key, 'label' => $label);
                        if ($storeLabel = $this->getAttributeStoreData($code, $key)) {
                            $data['label'] = $storeLabel;
                            $data['default_label'] = $label;
                        }
                        $options[] = $data;
                    }
                    $attributes[$code]['options'] = $options;
                }
            }
            uasort($attributes, array($this, '_sortAttributes'));
        }
        return $attributes;
    }

    /**
     * Retrieve attribute store label
     *
     * @param string $attributeCode
     * @param string $optionCode
     * @return string
     */
    public function getAttributeStoreData($attributeCode, $optionCode = '')
    {
        if ($this->_storeData === null) {
            $this->_storeData = $this->_getResource()->getAttributesStoreData($this);
        }

        if (is_array($this->_storeData)) {
            foreach ($this->_storeData as $item) {
               if ($item['attribute_code'] == $attributeCode && $item['option_code'] == $optionCode) {
                   return $item['label'];
               }
            }
        }
        return '';
    }

    /**
     * Retrieve attribute by code
     *
     * @param string $code
     * @return null|array
     */
    public function getAttributeByCode($code)
    {
        if (!$this->getId() || empty($code)) {
            return null;
        }
        if ($groups = $this->getAttributes()) {
            foreach ($groups as $group) {
                if (isset($group[$code])) {
                    return $group[$code];
                }
            }
        }
        return null;
    }

    /**
     * Retrieve attribute label by code
     *
     * @param string $attributeCode
     * @return string
     */
    public function getAttributeLabel($attributeCode)
    {
        $attribute = $this->getAttributeByCode($attributeCode);
        if ($attribute && isset($attribute['label'])) {
            return $attribute['label'];
        }
        return '';
    }

    /**
     * Retrieve attribute option label by code
     *
     * @param string $attributeCode
     * @param string $optionCode
     * @return string
     */
    public function getOptionLabel($attributeCode, $optionCode)
    {
        $attribute = $this->getAttributeByCode($attributeCode);
        if ($attribute && isset($attribute['options']) && is_array($attribute['options'])) {
            foreach ($attribute['options'] as $option) {
                if ($option['code'] == $optionCode) {
                    return $option['label'];
                }
            }
        }
        return '';
    }

    /**
     * Retrieve listed static attributes list from type attributes list
     *
     * @return array
     */
    public function getListedAttributes()
    {
        $listedAttributes = array();
        if ($this->getAttributes()) {
            $staticCodes = Mage::getSingleton('enterprise_giftregistry/attribute_config')
                ->getStaticTypesCodes();
            foreach ($this->getAttributes() as $group) {
                foreach ($group as $code => $attribute) {
                    if (in_array($code, $staticCodes) && !empty($attribute['frontend']['is_listed'])) {
                        $listedAttributes[$code] = $attribute['label'];
                    }
                }
            }
        }
        return $listedAttributes;
    }

    /**
     * Custom handler for giftregistry type save action
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchTypeSave($config, $eventModel, $processor)
    {
        $typeData = Mage::app()->getRequest()->getParam('type');
        $typeId = isset($typeData['type_id']) ? $typeData['type_id'] : Mage::helper('enterprise_logging')->__('New');
        return $eventModel->setInfo($typeId);
    }

    /**
     * Filter and load post data to object
     *
     * @param array $data
     * @return Enterprise_GiftRegistry_Model_Type
     */
    public function loadPost(array $data)
    {
        $type = $data['type'];
        $this->setCode($type['code']);

        $attributes = (isset($data['attributes'])) ? $data['attributes'] : null;
        $this->setAttributes($attributes);

        $label = (isset($type['label'])) ? $type['label'] : null;
        $this->setLabel($label);

        $sortOrder = (isset($type['sort_order'])) ? $type['sort_order'] : null;
        $this->setSortOrder($sortOrder);

        $isListed = (isset($type['is_listed'])) ? $type['is_listed'] : null;
        $this->setIsListed($isListed);

        return $this;
    }
}
