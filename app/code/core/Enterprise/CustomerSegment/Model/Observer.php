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
 * CustomerSegment observer
 *
 */
class Enterprise_CustomerSegment_Model_Observer
{
    /**
     * Add Customer Segment condition to the salesrule management
     *
     * @param Varien_Event_Observer $observer
     */
    public function addSegmentsToSalesRuleCombine(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_customersegment')->isEnabled()) {
            return;
        }
        $additional = $observer->getEvent()->getAdditional();
        $additional->setConditions(array(array(
            'label' => Mage::helper('enterprise_customersegment')->__('Customer Segment'),
            'value' => 'enterprise_customersegment/segment_condition_segment'
        )));
    }

    /**
     * Process customer related data changing. Method can process just events with customer object
     *
     * @param   Varien_Event_Observer $observer
     */
    public function processCustomerEvent(Varien_Event_Observer $observer)
    {
        $eventName = $observer->getEvent()->getName();
        $customer  = $observer->getEvent()->getCustomer();
        $dataObject= $observer->getEvent()->getDataObject();
        $customerId= false;

        if ($customer) {
            $customerId = $customer->getId();
        }
        if (!$customerId && $dataObject) {
            $customerId = $dataObject->getCustomerId();
        }

        if ($customerId) {
            Mage::getSingleton('enterprise_customersegment/customer')->processCustomerEvent(
                $eventName,
                $customerId
            );
        }
    }

    /**
     * Match customer segments on supplied event for currently logged in customer or visitor and current website.
     * Can be used for processing just frontend events
     *
     * @param Varien_Event_Observer $observer
     */
    public function processEvent(Varien_Event_Observer $observer)
    {
        $eventName = $observer->getEvent()->getName();
        $customer = Mage::registry('segment_customer');

        // For visitors use customer instance from customer session
        if (!$customer) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
        }

        $website = Mage::app()->getStore()->getWebsite();
        Mage::getSingleton('enterprise_customersegment/customer')->processEvent($eventName, $customer, $website);
    }

    /**
     * Match quote customer to all customer segments.
     * Used before quote recollect in admin
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function processQuote(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $customer = $quote->getCustomer();
        if ($customer && $customer->getId()) {
            $website = $quote->getStore()->getWebsite();
            Mage::getSingleton('enterprise_customersegment/customer')->processCustomer($customer, $website);
        }
    }

    /**
     * Add field "Use in Customer Segment" for Customer and Customer Address attribute edit form
     *
     * @param Varien_Event_Observer $observer
     */
    public function enterpiseCustomerAttributeEditPrepareForm(Varien_Event_Observer $observer)
    {
        $form       = $observer->getEvent()->getForm();
        $fieldset   = $form->getElement('base_fieldset');
        $fieldset->addField('is_used_for_customer_segment', 'select', array(
            'name'      => 'is_used_for_customer_segment',
            'label'     => Mage::helper('enterprise_customersegment')->__('Use in Customer Segment'),
            'title'     => Mage::helper('enterprise_customersegment')->__('Use in Customer Segment'),
            'values'    => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));
    }

    /**
     * Add Customer Segment form fields to Target Rule form
     *
     * Observe  targetrule_edit_tab_main_after_prepare_form event
     *
     * @param Varien_Event_Observer $observer
     */
    public function addFieldsToTargetRuleForm(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_customersegment')->isEnabled()) {
            return;
        }
        /* @var $form Varien_Data_Form */
        $form = $observer->getEvent()->getForm();
        $model = $observer->getEvent()->getModel();
        $block = $observer->getEvent()->getBlock();

        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset = $form->getElement('base_fieldset');

        $model->setUseCustomerSegment(count($model->getCustomerSegmentIds()) > 0);
        $fieldset->addField('use_customer_segment', 'select', array(
            'name' => 'use_customer_segment',
            'label' => Mage::helper('enterprise_customersegment')->__('Customer Segments'),
            'options' => array(
                '0' => Mage::helper('enterprise_customersegment')->__('All'),
                '1' => Mage::helper('enterprise_customersegment')->__('Specified'),
            ),
            'note' => $model->getUseCustomerSegment() ?
                $this->_getSpecificSegmentMessage()  : $this->_getAllSegmentsMessage(),
            'disabled' => $model->getIsReadonly(),
            'after_element_html' => $this->_getChangeNoteMessageScript(
                'rule_use_customer_segment',
                'note_use_customer_segment'
            )
        ));

        $fieldset->addField('customer_segment_ids', 'multiselect', array(
            'name' => 'customer_segment_ids[]',
            'values' => Mage::getResourceSingleton('enterprise_customersegment/segment_collection')
                ->toOptionArray(),
            'can_be_empty' => true,
        ));

        $htmlIdPrefix = $form->getHtmlIdPrefix();
        $block->setChild('form_after', $block->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
            ->addFieldMap("{$htmlIdPrefix}use_customer_segment", 'use_customer_segment')
            ->addFieldMap("{$htmlIdPrefix}customer_segment_ids", 'customer_segment_ids')
            ->addFieldDependence('customer_segment_ids', 'use_customer_segment', '1'));
    }

    /**
     * Add Customer Segment form fields to Banner form
     *
     * Observe  targetrule_edit_tab_main_after_prepare_form event
     *
     * @param Varien_Event_Observer $observer
     */
    public function addFieldsToBannerForm(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_customersegment')->isEnabled()) {
            return;
        }
        /* @var $form Varien_Data_Form */
        $form = $observer->getEvent()->getForm();
        $model = $observer->getEvent()->getModel();
        $block = $observer->getEvent()->getBlock();
        $afterFormBlock = $observer->getEvent()->getAfterFormBlock();
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset = $form->getElement('base_fieldset');
        $model->setUseCustomerSegment(count($model->getCustomerSegmentIds()) > 0);

        // whether to specify customer segments - also for UI design purposes only
        $fieldset->addField('use_customer_segment', 'select', array(
            'name' => 'use_customer_segment',
            'label' => Mage::helper('enterprise_customersegment')->__('Customer Segments'),
            'options' => array(
                '0' => Mage::helper('enterprise_customersegment')->__('All'),
                '1' => Mage::helper('enterprise_customersegment')->__('Specified'),
            ),
            'note' => $model->getUseCustomerSegment() ?
                $this->_getSpecificSegmentMessage() : $this->_getAllSegmentsMessage(),
            'disabled' => (bool)$model->getIsReadonly(),
            'after_element_html' => $this->_getChangeNoteMessageScript(
                'banner_properties_use_customer_segment',
                'note_use_customer_segment'
            )
        ));

        $fieldset->addField('customer_segment_ids', 'multiselect', array(
            'name' => 'customer_segment_ids',
            'values' => Mage::getResourceSingleton('enterprise_customersegment/segment_collection')->toOptionArray(),
            'can_be_empty' => true,
        ));

        $htmlIdPrefix = $form->getHtmlIdPrefix();
        $afterFormBlock->addFieldMap("{$htmlIdPrefix}use_customer_segment", 'use_customer_segment')
            ->addFieldMap("{$htmlIdPrefix}customer_segment_ids", 'customer_segment_ids')
            ->addFieldDependence('customer_segment_ids', 'use_customer_segment', '1');
    }

    /**
     * Get Apply to All Segments Message
     *
     * @return string
     */
    protected function _getAllSegmentsMessage()
    {
        return Mage::helper('enterprise_customersegment')->__('Applies to All of the Specified Customer Segments');
    }

    /**
     * Get apply to specific segment message
     * @return string
     */
    protected function _getSpecificSegmentMessage()
    {
        return Mage::helper('enterprise_customersegment')->__('Apply to the Selected Customer Segments');
    }


    /**
     * Get change note message script
     *
     * @param string $selectBoxId
     * @param string $noteMessageBlockId
     * @return string
     */
    protected function _getChangeNoteMessageScript($selectBoxId, $noteMessageBlockId)
    {
        return "<script type=\"text/javascript\">\r\n"
            . "noteMessages=[\"{$this->_getAllSegmentsMessage()}\", \"{$this->_getSpecificSegmentMessage()}\"];\r\n"
            . "Event.observe('$selectBoxId', 'change', function(event) { \r\n"
            . "noteMessage = window.noteMessages[\$('$selectBoxId').value];\r\n"
            . "\$('$noteMessageBlockId').update('<span>' + noteMessage + '</span>');\r\n"
            . "});\r\n"
            . "</script>\r\n";
    }
}
