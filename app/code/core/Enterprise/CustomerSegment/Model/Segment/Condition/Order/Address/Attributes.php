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
 * @package     Enterprise_CustomerSegment
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Order address attribute condition
 */
class Enterprise_CustomerSegment_Model_Segment_Condition_Order_Address_Attributes
    extends Enterprise_CustomerSegment_Model_Condition_Abstract
{
    /**
     * Array of Customer Address attributes used for customer segment
     *
     * @var array
     */
    protected $_attributes;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_order_address_attributes');
        $this->setValue(null);
    }

    /**
     * Get array of event names where segment with such conditions combine can be matched
     *
     * @return array
     */
    public function getMatchedEvents()
    {
        return array('sales_order_save_commit_after');
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $attributes = $this->loadAttributeOptions()->getAttributeOption();
        $conditions = array();
        foreach ($attributes as $code => $label) {
            $conditions[] = array(
                'value' => $this->getType() . '|' . $code,
                'label' => $label
            );
        }

        return array(
            'value' => $conditions,
            'label' => Mage::helper('enterprise_customersegment')->__('Order Address Attributes')
        );
    }

    /**
     * Load attribute options
     *
     * @return Enterprise_CustomerSegment_Model_Segment_Condition_Order_Address_Attributes
     */
    public function loadAttributeOptions()
    {
        if (is_null($this->_attributes)) {
            $this->_attributes  = array();

            /* @var $config Mage_Eav_Model_Config */
            $config     = Mage::getSingleton('eav/config');
            $attributes = array();

            foreach ($config->getEntityAttributeCodes('customer_address') as $attributeCode) {
                $attribute = $config->getAttribute('customer_address', $attributeCode);
                if (!$attribute || !$attribute->getIsUsedForCustomerSegment()) {
                    continue;
                }
                // skip "binary" attributes
                if (in_array($attribute->getFrontendInput(), array('file', 'image'))) {
                    continue;
                }
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
                $this->_attributes[$attribute->getAttributeCode()] = $attribute;
            }

            $this->setAttributeOption($attributes);
        }

        return $this;
    }

    /**
     * Retrieve select option values
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = Mage::getModel('adminhtml/system_config_source_country')
                        ->toOptionArray();
                    break;

                case 'region_id':
                    $options = Mage::getModel('adminhtml/system_config_source_allregion')
                        ->toOptionArray();
                    break;

                default:
                    $options = array();
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Retrieve attribute element
     *
     * @return Varien_Form_Element_Abstract
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Get input type for attribute operators.
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'country_id': case 'region_id':
                return 'select';
        }
        return 'string';
    }

    /**
     * Get input type for attribute value.
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'country_id': case 'region_id':
                return 'select';
        }
        return 'text';
    }

    /**
     * Get HTML of condition string
     *
     * @return string
     */
    public function asHtml()
    {
        return Mage::helper('enterprise_customersegment')->__('Order Address %s', parent::asHtml());
    }

    /**
     * Get order address attribute
     *
     * @return Eav_Model_Entity_Attribute_Abstract
     */
    public function getAttributeObject()
    {
        $this->loadAttributeOptions();
        return $this->_attributes[$this->getAttribute()];
    }

    /**
     * Get condition query for order address attribute
     *
     * @param $customer
     * @param $website
     * @return Varien_Db_Select
     */
    public function getConditionsSql($customer, $website)
    {
        if ($this->getAttributeObject()->getIsUserDefined()) {
            $tableAlias = 'extra_order_address';
        } else {
            $tableAlias = 'order_address';
        }

        return $this->getResource()->createConditionSql(
            sprintf('%s.%s', $tableAlias, $this->getAttribute()),
            $this->getOperator(),
            $this->getValue()
        );
    }
}
