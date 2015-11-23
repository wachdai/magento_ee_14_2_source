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
 * Scheduled export create/edit form
 *
 * @category    Enterprise
 * @package     Enterprise_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Edit_Form_Export
    extends Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Edit_Form
{
    /**
     * Prepare form for export operation
     *
     * @return Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Edit_Form_Export
     */
    protected function _prepareForm()
    {
        $this->setGeneralSettingsLabel(Mage::helper('enterprise_importexport')->__('Export Settings'));
        $this->setFileSettingsLabel(Mage::helper('enterprise_importexport')->__('Export File Information'));
        $this->setEmailSettingsLabel(Mage::helper('enterprise_importexport')->__('Export Failed Emails'));

        parent::_prepareForm();
        $form = $this->getForm();
        $operation = Mage::registry('current_operation');

        $fieldset = $form->getElement('operation_settings');
        $fieldset->addField('file_format', 'select', array(
            'name'      => 'file_info[file_format]',
            'title'     => Mage::helper('enterprise_importexport')->__('File Format'),
            'label'     => Mage::helper('enterprise_importexport')->__('File Format'),
            'required'  => true,
            'values'    => Mage::getModel('importexport/source_export_format')->toOptionArray()
        ), 'entity');

        $form->getElement('email_template')
            ->setValues(Mage::getModel('adminhtml/system_config_source_email_template')
                ->setPath('enterprise_importexport_export_failed')
                ->toOptionArray()
            );

        $form->getElement('entity')
            ->setData('onchange', 'editForm.getFilter();');

        $fieldset = $form->addFieldset('export_filter_grid_container', array(
            'legend' => Mage::helper('enterprise_importexport')->__('Entity Attributes'),
            'fieldset_container_id' => 'export_filter_container'
        ));

        if ($operation->getId()) {
            $fieldset->setData('html_content', $this->_getFilterBlock($operation)->toHtml());
        }

        $this->_setFormValues($operation->getData());

        return $this;
    }

    /**
     * Return block instance with specific attribute fields
     *
     * @param Enterprise_ImportExport_Model_Scheduled_Operation $operation
     * @return Enterprise_ImportExport_Block_Adminhtml_Export_Filter
     */
    protected function _getFilterBlock($operation)
    {
        $export = $operation->getInstance();
        $block = $this->getLayout()
            ->createBlock('enterprise_importexport/adminhtml_export_filter')
            ->setOperation($export);

        $export->filterAttributeCollection($block->prepareCollection($export->getEntityAttributeCollection()));
        return $block;
    }
}
