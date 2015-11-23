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

class Enterprise_GiftRegistry_Block_Adminhtml_Giftregistry_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Return current gift registry type instance
     *
     * @return Enterprise_GiftRegistry_Model_Type
     */
    public function getType()
    {
        return Mage::registry('current_giftregistry_type');
    }

    /**
     * Prepares layout and set element renderer
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('enterprise_giftregistry/adminhtml_giftregistry_form_renderer_element')
        );
    }

    /**
     * Prepare general properties form
     *
     * @return Enterprise_GiftRegistry_Block_Adminhtml_Giftregistry_Edit_Tab_General
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('type');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'  => Mage::helper('enterprise_giftregistry')->__('General Information')
        ));

        if ($this->getType()->getId()) {
            $fieldset->addField('type_id', 'hidden', array(
                'name' => 'type_id'
            ));
        }

        $fieldset->addField('code', 'text', array(
            'name'     => 'code',
            'label'    => Mage::helper('enterprise_giftregistry')->__('Code'),
            'required' => true,
            'class'    => 'validate-code'
        ));

        $element = $fieldset->addField('label', 'text', array(
            'name'     => 'label',
            'label'    => Mage::helper('enterprise_giftregistry')->__('Label'),
            'required' => true,
            'scope'    => 'store'
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name'     => 'sort_order',
            'label'    => Mage::helper('enterprise_giftregistry')->__('Sort Order'),
            'scope'    => 'store'
        ));

        $fieldset->addField('is_listed', 'select', array(
            'label'    => Mage::helper('enterprise_giftregistry')->__('Is Listed'),
            'name'     => 'is_listed',
            'values'   => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'scope'    => 'store'
        ));

        $form->setValues($this->getType()->getData());
        $this->setForm($form);
        $form->setDataObject($this->getType());

        return parent::_prepareForm();
    }
}
