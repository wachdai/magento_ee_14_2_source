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
 * General Properties tab of customer segment configuration
 *
 * @category    Enterprise
 * @package     Enterprise_CustomerSegment
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CustomerSegment_Block_Adminhtml_Customersegment_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare general properties form
     *
     * @return Enterprise_CustomerSegment_Block_Adminhtml_Customersegment_Edit_Tab_General
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('current_customer_segment');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('segment_');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('enterprise_customersegment')->__('General Properties')
        ));

        if ($model->getId()) {
            $fieldset->addField('segment_id', 'hidden', array(
                'name' => 'segment_id'
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('enterprise_customersegment')->__('Segment Name'),
            'required' => true
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => Mage::helper('enterprise_customersegment')->__('Description'),
            'style' => 'height: 100px;'
        ));

        if (Mage::app()->isSingleStoreMode()) {
            $websiteId = Mage::app()->getStore(true)->getWebsiteId();
            $fieldset->addField('website_ids', 'hidden', array(
                'name'     => 'website_ids[]',
                'value'    => $websiteId
            ));
            $model->setWebsiteIds($websiteId);
        } else {
            $fieldset->addField('website_ids', 'multiselect', array(
                'name'     => 'website_ids[]',
                'label'    => Mage::helper('enterprise_customersegment')->__('Assigned to Website'),
                'title'    => Mage::helper('enterprise_customersegment')->__('Assigned to Website'),
                'required' => true,
                'values'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(),
                'value'    => $model->getWebsiteIds()
            ));
        }

        $fieldset->addField('is_active', 'select', array(
            'label' => Mage::helper('enterprise_customersegment')->__('Status'),
            'name' => 'is_active',
            'required' => true,
            'options' => array(
                '1' => Mage::helper('enterprise_customersegment')->__('Active'),
                '0' => Mage::helper('enterprise_customersegment')->__('Inactive')
            )
        ));

        $applyToFieldConfig = array(
            'label' => Mage::helper('enterprise_customersegment')->__('Apply To'),
            'name' => 'apply_to',
            'required' => false,
            'disabled' => (boolean)$model->getId(),
            'options' => array(
                Enterprise_CustomerSegment_Model_Segment::APPLY_TO_VISITORS_AND_REGISTERED => Mage::helper('enterprise_customersegment')->__('Visitors and Registered Customers'),
                Enterprise_CustomerSegment_Model_Segment::APPLY_TO_REGISTERED => Mage::helper('enterprise_customersegment')->__('Registered Customers'),
                Enterprise_CustomerSegment_Model_Segment::APPLY_TO_VISITORS => Mage::helper('enterprise_customersegment')->__('Visitors')
            )
        );
        if (!$model->getId()) {
            $applyToFieldConfig['note'] = Mage::helper('enterprise_customersegment')->__('Please save this information in order to specify the conditions for segmentation');
        }

        $fieldset->addField('apply_to', 'select', $applyToFieldConfig);

        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
