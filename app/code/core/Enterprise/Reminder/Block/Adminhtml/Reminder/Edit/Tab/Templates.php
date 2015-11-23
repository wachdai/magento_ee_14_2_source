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
 * @package     Enterprise_Reminder
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Reminder rules edit form email templates and labels fields
 */
class Enterprise_Reminder_Block_Adminhtml_Reminder_Edit_Tab_Templates
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare general properties form
     *
     * @return Enterprise_Reminder_Block_Adminhtml_Reminder_Edit_Tab_Templates
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $model = Mage::registry('current_reminder_rule');

        $fieldset = $form->addFieldset('email_fieldset', array(
            'legend' => Mage::helper('enterprise_reminder')->__('Email Templates'),
            'table_class'  => 'form-list stores-tree',
            'comment' => Mage::helper('enterprise_reminder')->__('Emails will be sent only for specified store views. Email store view matches the store view customer was registered on.'),
        ));

        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("website_template_{$website->getId()}", 'note', array(
                'label'    => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("group_template_{$group->getId()}", 'note', array(
                    'label'    => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField('store_template_' . $store->getId(), 'select', array(
                        'name'      => 'store_templates[' . $store->getId() . ']',
                        'required'  => false,
                        'label'     => $store->getName(),
                        'values'    => $this->getTemplatesOptionsArray(),
                        'fieldset_html_class' => 'store'
                    ));
                }
            }
        }

        $fieldset = $form->addFieldset('default_label_fieldset', array(
            'legend' => Mage::helper('enterprise_reminder')->__('Default Titles and Description'),
            'comment' => Mage::helper('enterprise_reminder')->__('Rule label and descriptions are accessible in email templates as variables, may be defined per store view.'),
        ));

        $fieldset->addField('default_label', 'text', array(
            'name'      => 'default_label',
            'required'  => false,
            'label'     => Mage::helper('enterprise_reminder')->__('Rule Title for All Store Views')
        ));

        $fieldset->addField('default_description', 'textarea', array(
            'name'     => 'default_description',
            'required' => false,
            'label'    => Mage::helper('enterprise_reminder')->__('Rule Description for All Store Views'),
            'style'    => 'height: 50px;'
        ));

        $fieldset = $form->addFieldset('labels_fieldset', array(
            'legend' => Mage::helper('enterprise_reminder')->__('Titles and Descriptions Per Store View'),
            'comment' => Mage::helper('enterprise_reminder')->__('Overrides default titles and descriptions. Note that if email an template is not specified for this store view, the respective variable values will be deleted.'),
            'table_class'  => 'form-list stores-tree'
        ));

        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("website_label_{$website->getId()}", 'note', array(
                'label'    => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("group_label_{$group->getId()}", 'note', array(
                    'label'    => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField('store_label_' . $store->getId(), 'text', array(
                        'name'      => 'store_labels[' . $store->getId() . ']',
                        'label'     => $store->getName(),
                        'required'  => false,
                        'fieldset_html_class' => 'store'
                    ));
                     $fieldset->addField('store_description_' . $store->getId(), 'textarea', array(
                        'name'      => 'store_descriptions[' . $store->getId() . ']',
                        'required'  => false,
                        'fieldset_html_class' => 'store',
                        'style' => 'height: 50px;'
                    ));
                }
            }
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve array of available email templates
     *
     * @return array
     */
    public function getTemplatesOptionsArray()
    {
        $template = Mage::getModel('adminhtml/system_config_source_email_template');
        $template->setPath(Enterprise_Reminder_Model_Rule::XML_PATH_EMAIL_TEMPLATE);

        $options = $template->toOptionArray();
        array_unshift($options, array('value'=>'',
            'label' => Mage::helper('enterprise_reminder')->__('-- Not Selected --'))
        );
        return $options;
    }
}
