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
 * Scheduled operation create/edit form
 *
 * @category    Enterprise
 * @package     Enterprise_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare general form for scheduled operation
     *
     * @return Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Edit_Form
     */
    protected function _prepareForm()
    {
        $operation = Mage::registry('current_operation');
        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form',
            'name'   => 'scheduled_operation'
        ));
        // settings information
        $this->_addGeneralSettings($form, $operation);

        // file information
        $this->_addFileSettings($form, $operation);

        // email notifications
        $this->_addEmailSettings($form, $operation);

        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setAction($this->getUrl('*/*/save'));

        $this->setForm($form);
        if (is_array($operation->getStartTime())) {
            $operation->setStartTime(join(',', $operation->getStartTime()));
        }
        $operation->setStartTime(str_replace(':', ',', $operation->getStartTime()));

        return $this;
    }

    /**
     * Add general information fieldset to form
     *
     * @param Varien_Data_Form $form
     * @param Enterprise_ImportExport_Model_Scheduled_Operation $operation
     * @return Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Edit_Form
     */
    protected function _addGeneralSettings($form, $operation)
    {
        $fieldset = $form->addFieldset('operation_settings', array(
            'legend' => $this->getGeneralSettingsLabel()
        ));

        if ($operation->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name'      => 'id',
                'required'  => true
            ));
        }
        $fieldset->addField('operation_type', 'hidden', array(
            'name'     => 'operation_type',
            'required' => true
        ));

        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'title'     => Mage::helper('enterprise_importexport')->__('Name'),
            'label'     => Mage::helper('enterprise_importexport')->__('Name'),
            'required'  => true
        ));

        $fieldset->addField('details', 'textarea', array(
            'name'      => 'details',
            'title'     => Mage::helper('enterprise_importexport')->__('Description'),
            'label'     => Mage::helper('enterprise_importexport')->__('Description'),
            'required'  => false
        ));

        $entities = Mage::getModel('importexport/source_' . $operation->getOperationType() . '_entity')
            ->toOptionArray();

        $fieldset->addField('entity', 'select', array(
            'name'      => 'entity_type',
            'title'     => Mage::helper('enterprise_importexport')->__('Entity Type'),
            'label'     => Mage::helper('enterprise_importexport')->__('Entity Type'),
            'required'  => true,
            'values'    => $entities
        ));

        $fieldset->addField('start_time', 'time', array(
            'name'      => 'start_time',
            'title'     => Mage::helper('enterprise_importexport')->__('Start Time'),
            'label'     => Mage::helper('enterprise_importexport')->__('Start Time'),
            'required'  => true,
        ));

        $fieldset->addField('freq', 'select', array(
            'name'      => 'freq',
            'title'     => Mage::helper('enterprise_importexport')->__('Frequency'),
            'label'     => Mage::helper('enterprise_importexport')->__('Frequency'),
            'required'  => true,
            'values'    => Mage::getSingleton('enterprise_importexport/scheduled_operation_data')
                ->getFrequencyOptionArray()
        ));

        $fieldset->addField('status', 'select', array(
            'name'      => 'status',
            'title'     => Mage::helper('enterprise_importexport')->__('Status'),
            'label'     => Mage::helper('enterprise_importexport')->__('Status'),
            'required'  => true,
            'values'    => Mage::getSingleton('enterprise_importexport/scheduled_operation_data')
                ->getStatusesOptionArray()
        ));

        return $this;
    }

    /**
     * Add file information fieldset to form
     *
     * @param Varien_Data_Form $form
     * @param Enterprise_ImportExport_Model_Scheduled_Operation $operation
     * @return Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Edit_Form
     */
    protected function _addFileSettings($form, $operation)
    {
        $fieldset = $form->addFieldset('file_settings', array(
            'legend' => $this->getFileSettingsLabel()
        ));

        $fieldset->addField('server_type', 'select', array(
            'name'      => 'file_info[server_type]',
            'title'     => Mage::helper('enterprise_importexport')->__('Server Type'),
            'label'     => Mage::helper('enterprise_importexport')->__('Server Type'),
            'required'  => true,
            'values'    => Mage::getSingleton('enterprise_importexport/scheduled_operation_data')
                ->getServerTypesOptionArray(),
        ));

        $fieldset->addField('file_path', 'text', array(
            'name'      => 'file_info[file_path]',
            'title'     => Mage::helper('enterprise_importexport')->__('File Directory'),
            'label'     => Mage::helper('enterprise_importexport')->__('File Directory'),
            'required'  => true,
            'note'      => Mage::helper('enterprise_importexport')->__('For Type "Local Server" use relative path to Magento installation, e.g. var/export, var/import, var/export/some/dir')
        ));

        $fieldset->addField('host', 'text', array(
            'name'      => 'file_info[host]',
            'title'     => Mage::helper('enterprise_importexport')->__('FTP Host[:Port]'),
            'label'     => Mage::helper('enterprise_importexport')->__('FTP Host[:Port]'),
            'class'     => 'ftp-server server-dependent'
        ));

        $fieldset->addField('user', 'text', array(
            'name'      => 'file_info[user]',
            'title'     => Mage::helper('enterprise_importexport')->__('User Name'),
            'label'     => Mage::helper('enterprise_importexport')->__('User Name'),
            'class'     => 'ftp-server server-dependent'
        ));

        $fieldset->addField('password', 'password', array(
            'name'      => 'file_info[password]',
            'title'     => Mage::helper('enterprise_importexport')->__('Password'),
            'label'     => Mage::helper('enterprise_importexport')->__('Password'),
            'class'     => 'ftp-server server-dependent'
        ));

        $fieldset->addField('file_mode', 'select', array(
            'name'      => 'file_info[file_mode]',
            'title'     => Mage::helper('enterprise_importexport')->__('File Mode'),
            'label'     => Mage::helper('enterprise_importexport')->__('File Mode'),
            'values'    => Mage::getSingleton('enterprise_importexport/scheduled_operation_data')
                ->getFileModesOptionArray(),
            'class'     => 'ftp-server server-dependent'
        ));

        $fieldset->addField('passive', 'select', array(
            'name'      => 'file_info[passive]',
            'title'     => Mage::helper('enterprise_importexport')->__('Passive Mode'),
            'label'     => Mage::helper('enterprise_importexport')->__('Passive Mode'),
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'class'     => 'ftp-server server-dependent'
        ));

        return $this;
    }

    /**
     * Add file information fieldset to form
     *
     * @param Varien_Data_Form $form
     * @param Enterprise_ImportExport_Model_Scheduled_Operation $operation
     * @return Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Edit_Form
     */
    protected function _addEmailSettings($form, $operation)
    {
        $fieldset = $form->addFieldSet('email_settings', array(
            'legend' => $this->getEmailSettingsLabel()
        ));

        $emails = Mage::getModel('adminhtml/system_config_source_email_identity')->toOptionArray();
        $fieldset->addField('email_receiver', 'select', array(
            'name'      => 'email_receiver',
            'title'     => Mage::helper('enterprise_importexport')->__('Failed Email Receiver'),
            'label'     => Mage::helper('enterprise_importexport')->__('Failed Email Receiver'),
            'values'    => $emails
        ));

        $fieldset->addField('email_sender', 'select', array(
            'name'      => 'email_sender',
            'title'     => Mage::helper('enterprise_importexport')->__('Failed Email Sender'),
            'label'     => Mage::helper('enterprise_importexport')->__('Failed Email Sender'),
            'values'    => $emails
        ));

        $fieldset->addField('email_template', 'select', array(
            'name'      => 'email_template',
            'title'     => Mage::helper('enterprise_importexport')->__('Failed Email Template'),
            'label'     => Mage::helper('enterprise_importexport')->__('Failed Email Template')
        ));

        $fieldset->addField('email_copy', 'text', array(
            'name'      => 'email_copy',
            'title'     => Mage::helper('enterprise_importexport')->__('Send Failed Email Copy To'),
            'label'     => Mage::helper('enterprise_importexport')->__('Send Failed Email Copy To')
        ));

        $fieldset->addField('email_copy_method', 'select', array(
            'name'      => 'email_copy_method',
            'title'     => Mage::helper('enterprise_importexport')->__('Send Failed Email Copy Method'),
            'label'     => Mage::helper('enterprise_importexport')->__('Send Failed Email Copy Method'),
            'values'    => Mage::getModel('adminhtml/system_config_source_email_method')->toOptionArray()
        ));

        return $this;
    }

    /**
     * Set values to form from operation model
     *
     * @param array $data
     * @return Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Edit_Form|bool
     */
    protected function _setFormValues(array $data)
    {
        if (!is_object($this->getForm())) {
            return false;
        }
        if (isset($data['file_info'])) {
            $fileInfo = $data['file_info'];
            unset($data['file_info']);
            if (is_array($fileInfo)) {
                $data = array_merge($data, $fileInfo);
            }
        }
        if (isset($data['entity_type'])) {
            $data['entity'] = $data['entity_type'];
        }
        $this->getForm()->setValues($data);
        return $this;
    }
}
