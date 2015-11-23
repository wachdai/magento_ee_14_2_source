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
 * Gift registry custom attribute config model
 */
class Enterprise_GiftRegistry_Model_Attribute_Config extends Mage_Core_Model_Abstract
{
    protected $_config = null;
    protected $_staticTypes = null;

    /**
     * Pathes to attribute groups and types nodes
     */
    const XML_ATTRIBUTE_GROUPS_PATH = 'prototype/attribute_groups';
    const XML_ATTRIBUTE_TYPES_PATH = 'prototype/attribute_types';

    /**
     * Load config from giftregistry.xml files and try to cache it
     *
     * @return Varien_Simplexml_Config
     */
    public function getXmlConfig()
    {
        if (is_null($this->_config)) {
            if ($cachedXml = Mage::app()->loadCache('giftregistry_config')) {
                $xmlConfig = new Varien_Simplexml_Config($cachedXml);
            } else {
                $xmlConfig = new Varien_Simplexml_Config();
                $xmlConfig->loadString('<?xml version="1.0"?><prototype></prototype>');
                Mage::getConfig()->loadModulesConfiguration('giftregistry.xml', $xmlConfig);

                if (Mage::app()->useCache('config')) {
                    Mage::app()->saveCache($xmlConfig->getXmlString(), 'giftregistry_config',
                        array(Mage_Core_Model_Config::CACHE_TAG));
                }
            }
            $this->_config = $xmlConfig;
        }
        return $this->_config;
    }

    /**
     * Return array of default options
     *
     * @return array
     */
    protected function _getDefaultOption()
    {
        return array(array(
            'value' => '',
            'label' => Mage::helper('enterprise_giftregistry')->__('-- Please select --'))
        );
    }

    /**
     * Return array of attribute types for using as options
     *
     * @return array
     */
    public function getAttributeTypesOptions()
    {
        $options = array_merge($this->_getDefaultOption(), array(
            array(
                'label' => Mage::helper('enterprise_giftregistry')->__('Custom Types'),
                'value' => $this->getAttributeCustomTypesOptions()
            ),
            array(
                'label' => Mage::helper('enterprise_giftregistry')->__('Static Types'),
                'value' => $this->getAttributeStaticTypesOptions()
            )
        ));
        return $options;
    }

    /**
     * Return array of attribute groups for using as options
     *
     * @return array
     */
    public function getAttributeGroupsOptions()
    {
        $options = $this->_getDefaultOption();
        $groups = $this->getAttributeGroups();

        if (is_array($groups)) {
            foreach ($groups as $code => $group) {
                if ($group['visible']) {
                    $options[] = array(
                        'value' => $code,
                        'label' => $group['label']
                    );
                }
            }
        }
        return $options;
    }

    /**
     * Return array of attribute groups
     *
     * @return array
     */
    public function getAttributeGroups()
    {
        if ($groups = $this->getXmlConfig()->getNode(self::XML_ATTRIBUTE_GROUPS_PATH)) {
            return $groups->asCanonicalArray();
        }
    }

    /**
     * Return array of static attribute types for using as options
     *
     * @return array
     */
    public function getStaticTypes()
    {
        if (is_null($this->_staticTypes)) {
            $staticTypes = array();
            foreach (array('registry', 'registrant') as $node) {
                if ($node = $this->getXmlConfig()->getNode('prototype/' . $node . '/attributes/static')) {
                    $staticTypes = array_merge($staticTypes, $node->asCanonicalArray());
                }
            }
            $this->_staticTypes = $staticTypes;
        }
        return $this->_staticTypes;
    }

    /**
     * Return array of codes of static attribute types
     *
     * @return array
     */
    public function getStaticTypesCodes()
    {
        return array_keys($this->getStaticTypes());
    }

    /**
     * Check if attribute is in registrant group
     *
     * @param string $attribute
     * @return bool
     */
    public function isRegistrantAttribute($attribute)
    {
        foreach ($this->getStaticTypes() as $code => $data) {
            if ($attribute == $code && $data['group'] == 'registrant') {
                return true;
            }
        }
        return false;
    }

    /**
     * Return code of static date attribute type
     *
     * @return null|string
     */
    public function getStaticDateType()
    {
        foreach ($this->getStaticTypes() as $code =>$type) {
            if (isset($type['type']) && $type['type'] == 'date') {
                return $code;
            }
        }
        return null;
    }

    /**
     * Return code of static region attribute type
     *
     * @return null|string
     */
    public function getStaticRegionType()
    {
        foreach ($this->getStaticTypes() as $code =>$type) {
            if (isset($type['type']) && $type['type'] == 'region') {
                return $code;
            }
        }
        return null;
    }

    /**
     * Return array of custom attribute types for using as options
     *
     * @return array
     */
    public function getAttributeCustomTypesOptions()
    {
        $types = $this->getXmlConfig()->getNode(self::XML_ATTRIBUTE_TYPES_PATH);
        $options = array();

        foreach ($types->asCanonicalArray() as $code => $type) {
            $options[] = array(
                'value' => $code,
                'label' => $type['label']
            );
        }
        return $options;
    }

    /**
     * Return array of static attribute types for using as options
     *
     * @return array
     */
    public function getAttributeStaticTypesOptions()
    {
        $options = array();
        foreach ($this->getStaticTypes() as $code => $type) {
            if (empty($type['visible'])) {
                continue;
            }
            $valueParts = array($type['type'], $code);
            if (!empty($type['group'])) {
                $valueParts[] = $type['group'];
            }

            $options[] = array(
                'value' => implode(':', $valueParts),
                'label' => $type['label']
            );
        }
        return $options;
    }
}
