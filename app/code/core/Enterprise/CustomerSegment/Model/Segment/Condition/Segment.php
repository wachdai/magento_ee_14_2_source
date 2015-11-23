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
 * Segment condition for sales rules
 */
class Enterprise_CustomerSegment_Model_Segment_Condition_Segment extends Mage_Rule_Model_Condition_Abstract
{
    /**
     * @var string
     */
    protected $_inputType = 'multiselect';

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = array(
                'multiselect' => array('==', '!=', '()', '!()'),
            );
            $this->_arrayInputTypes = array('multiselect');
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * Render chooser trigger
     *
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        return '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="'
            . Mage::getDesign()->getSkinUrl('images/rule_chooser_trigger.gif')
            . '" alt="" class="v-middle rule-chooser-trigger" title="'
            . Mage::helper('core')->quoteEscape(Mage::helper('rule')->__('Open Chooser')) . '" /></a>';
    }

    /**
     * Value element type getter
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Chooser URL getter
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/customersegment/chooserGrid', array(
            'value_element_id' => $this->_valueElement->getId(),
            'form' => $this->getJsFormObject(),
        ));
    }

    /**
     * Enable chooser selection button
     *
     * @return bool
     */
    public function getExplicitApply()
    {
        return true;
    }

    /**
     * Render element HTML
     *
     * @return string
     */
    public function asHtml()
    {
        $this->_valueElement = $this->getValueElement();
        return $this->getTypeElementHtml()
            . Mage::helper('enterprise_customersegment')->__('If Customer Segment %s %s', $this->getOperatorElementHtml(), $this->_valueElement->getHtml())
            . $this->getRemoveLinkHtml()
            . '<div class="rule-chooser" url="' . $this->getValueElementChooserUrl() . '"></div>';
    }

    /**
     * Specify allowed comparison operators
     *
     * @return Enterprise_CustomerSegment_Model_Segment_Condition_Segment
     */
    public function loadOperatorOptions()
    {
        parent::loadOperatorOptions();
        $this->setOperatorOption(array(
            '=='  => Mage::helper('enterprise_customersegment')->__('matches'),
            '!='  => Mage::helper('enterprise_customersegment')->__('does not match'),
            '()'  => Mage::helper('enterprise_customersegment')->__('is one of'),
            '!()' => Mage::helper('enterprise_customersegment')->__('is not one of'),
        ));
        return $this;
    }

    /**
     * Present selected values as array
     *
     * @return array
     */
    public function getValueParsed()
    {
        $value = $this->getData('value');
        $value = array_map('trim', explode(',',$value));
        return $value;
    }

    /**
     * Validate if qoute customer is assigned to role segments
     *
     * @param   Mage_Sales_Model_Quote_Address $object
     * @return  bool
     */
    public function validate(Varien_Object $object)
    {
        if (!Mage::helper('enterprise_customersegment')->isEnabled()) {
            return false;
        }
        $customer = null;
        if ($object->getQuote()) {
            $customer = $object->getQuote()->getCustomer();
        }
        if (!$customer) {
            return false;
        }

        $quoteWebsiteId = $object->getQuote()->getStore()->getWebsite()->getId();
        if (!$customer->getId()) {
            $visitorSegmentIds = Mage::getSingleton('customer/session')->getCustomerSegmentIds();
            if (is_array($visitorSegmentIds) && isset($visitorSegmentIds[$quoteWebsiteId])) {
                $segments = $visitorSegmentIds[$quoteWebsiteId];
            } else {
                $segments = array();
            }
        } else {
            $segments = Mage::getSingleton('enterprise_customersegment/customer')->getCustomerSegmentIdsForWebsite(
                $customer->getId(),
                $quoteWebsiteId
            );
        }
        return $this->validateAttribute($segments);
    }
}
