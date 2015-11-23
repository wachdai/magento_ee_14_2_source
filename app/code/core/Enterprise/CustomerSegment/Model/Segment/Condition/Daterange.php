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
 * Date range combo
 */
class Enterprise_CustomerSegment_Model_Segment_Condition_Daterange
    extends Enterprise_CustomerSegment_Model_Condition_Abstract
{
    /**
     * Input type for operator options
     *
     * @var string
     */
    protected $_inputType = 'select';

    /**
     * Value form element
     *
     * @var Varien_Data_Form_Element_Text
     */
    private $_valueElement = null;

    /**
     * Intialize model
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_daterange');
        $this->setValue(null);
    }

    /**
     * Inherited hierarchy options getter
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        return array(
            'value' => $this->getType(),
            'label' => Mage::helper('enterprise_customersegment')->__('Date Range'),
        );
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
     * Enable chooser selection button
     *
     * @return bool
     */
    public function getExplicitApply()
    {
        return true;
    }

    /**
     * Avoid value distortion by possible options
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        return array();
    }

    /**
     * Chooser button HTML getter
     *
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        return '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="'
            . Mage::getDesign()->getSkinUrl('images/rule_chooser_trigger.gif')
            . '" alt="" class="v-middle rule-chooser-trigger"'
            . 'title="' . Mage::helper('core')->quoteEscape(Mage::helper('rule')->__('Open Chooser')) . '" /></a>';
    }

    /**
     * Chooser URL getter
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/customersegment/chooserDaterange', array(
            'value_element_id' => $this->_valueElement->getId(),
        ));
    }

    /**
     * Render as HTML
     *
     * Chooser div is declared in such a way, that element value will be treated as is
     *
     * @return string
     */
    public function asHtml()
    {
        $this->_valueElement = $this->getValueElement();
        return $this->getTypeElementHtml()
            . Mage::helper('enterprise_customersegment')->__('Date Range %s within %s', $this->getOperatorElementHtml(), $this->_valueElement->getHtml())
            . $this->getRemoveLinkHtml()
            . '<div class="rule-chooser no-split" url="' . $this->getValueElementChooserUrl() . '"></div>';
    }

    /**
     * Get condition subfilter type. Can be used in parent level queries
     *
     * @return string
     */
    public function getSubfilterType()
    {
        return 'date';
    }

    /**
     * Apply date subfilter to parent/base condition query
     *
     * @param string $fieldName base query field name
     * @param bool $requireValid strict validation flag
     * @param $website
     * @return string
     */
    public function getSubfilterSql($fieldName, $requireValid, $website)
    {
        $value = explode('...', $this->getValue());
        if (!isset($value[0]) || !isset($value[1])) {
            return false;
        }

        $regexp = '#^\d{4}-\d{2}-\d{2}$#';
        if (!preg_match($regexp, $value[0]) || !preg_match($regexp, $value[1])) {
            return false;
        }

        $start = $value[0];
        $end = $value[1];

        if (!$start || !$end) {
            return false;
        }

        $inOperator = (($requireValid && $this->getOperator() == '==') ? 'BETWEEN' : 'NOT BETWEEN');
        return sprintf("%s %s '%s' AND '%s'", $fieldName, $inOperator, $start, $end);
    }
}
