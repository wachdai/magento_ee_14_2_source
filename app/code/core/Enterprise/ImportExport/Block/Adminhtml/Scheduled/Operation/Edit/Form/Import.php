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
 * @package     Enterprise_ImportExport
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Scheduled import create/edit form
 *
 * @category    Enterprise
 * @package     Enterprise_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Edit_Form_Import
    extends Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Edit_Form
{
    /**
     * Prepare form for import operation
     *
     * @return Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Edit_Form_Import
     */
    protected function _prepareForm()
    {
        $this->setGeneralSettingsLabel(Mage::helper('enterprise_importexport')->__('Import Settings'));
        $this->setFileSettingsLabel(Mage::helper('enterprise_importexport')->__('Import File Information'));
        $this->setEmailSettingsLabel(Mage::helper('enterprise_importexport')->__('Import Failed Emails'));

        parent::_prepareForm();
        $form = $this->getForm();

        $fieldset = $form->getElement('operation_settings');
        $fieldset->addField('behavior', 'select', array(
            'name'      => 'behavior',
            'title'     => Mage::helper('enterprise_importexport')->__('Import Behavior'),
            'label'     => Mage::helper('enterprise_importexport')->__('Import Behavior'),
            'required'  => true,
            'values'    => Mage::getModel('importexport/source_import_behavior')->toOptionArray()
        ), 'entity');

        $fieldset->addField('force_import', 'select', array(
            'name'      => 'force_import',
            'title'     => Mage::helper('enterprise_importexport')->__('On Error'),
            'label'     => Mage::helper('enterprise_importexport')->__('On Error'),
            'required'  => true,
            'values'    => Mage::getSingleton('enterprise_importexport/scheduled_operation_data')
                ->getForcedImportOptionArray()
        ), 'freq');

        $form->getElement('email_template')
            ->setValues(Mage::getModel('adminhtml/system_config_source_email_template')
                ->setPath('enterprise_importexport_import_failed')
                ->toOptionArray()
            );

        $form->getElement('file_settings')->addField('file_name', 'text', array(
            'name'      => 'file_info[file_name]',
            'title'     => Mage::helper('enterprise_importexport')->__('File Name'),
            'label'     => Mage::helper('enterprise_importexport')->__('File Name'),
            'required'  => true
        ), 'file_path');

        $operation = Mage::registry('current_operation');
        $this->_setFormValues($operation->getData());

        return $this;
    }
}
