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
 * @package     Enterprise_Customer
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Form Type Edit Form Block
 *
 * @category   Enterprise
 * @package    Enterprise_Customer
 */
class Enterprise_Customer_Block_Adminhtml_Customer_Formtype_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Retrieve current form type instance
     *
     * @return Mage_Eav_Model_Form_Type
     */
    protected function _getFormType()
    {
        return Mage::registry('current_form_type');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Enterprise_Customer_Block_Adminhtml_Customer_Formtype_Edit_Form
     */
    protected function _prepareForm()
    {
        $editMode = Mage::registry('edit_mode');
        if ($editMode == 'edit') {
            $saveUrl = $this->getUrl('*/*/save');
            $showNew = false;
        } else {
            $saveUrl = $this->getUrl('*/*/create');
            $showNew = true;
        }
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $saveUrl,
            'method'    => 'post'
        ));

        if ($showNew) {
            $fieldset = $form->addFieldset('base_fieldset', array(
                'legend' => Mage::helper('enterprise_customer')->__('General Information'),
                'class'  => 'fieldset-wide'
            ));

            $options = $this->_getFormType()->getCollection()->toOptionArray();
            array_unshift($options, array(
                'label' => Mage::helper('enterprise_customer')->__('-- Please Select --'),
                'value' => ''
            ));
            $fieldset->addField('type_id', 'select', array(
                'name'      => 'type_id',
                'label'     => Mage::helper('enterprise_customer')->__('Based On'),
                'title'     => Mage::helper('enterprise_customer')->__('Based On'),
                'required'  => true,
                'values'    => $options
            ));

            $fieldset->addField('label', 'text', array(
                'name'      => 'label',
                'label'     => Mage::helper('enterprise_customer')->__('Form Label'),
                'title'     => Mage::helper('enterprise_customer')->__('Form Label'),
                'required'  => true,
            ));

            $options = Mage::getModel('core/design_source_design')->getAllOptions(false);
            array_unshift($options, array(
                'label' => Mage::helper('enterprise_customer')->__('All Themes'),
                'value' => ''
            ));
            $fieldset->addField('theme', 'select', array(
                'name'      => 'theme',
                'label'     => Mage::helper('enterprise_customer')->__('For Theme'),
                'title'     => Mage::helper('enterprise_customer')->__('For Theme'),
                'values'    => $options
            ));

            $fieldset->addField('store_id', 'select', array(
                'name'      => 'store_id',
                'label'     => Mage::helper('enterprise_customer')->__('Store View'),
                'title'     => Mage::helper('enterprise_customer')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
            ));

            $form->setValues($this->_getFormType()->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
