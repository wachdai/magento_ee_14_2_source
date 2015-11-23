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
 * @package     Enterprise_Support
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_Support_Block_Adminhtml_Sysreport_Create_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldSet = $form->addFieldset('base_fieldset', array(
            'legend' => $this->helper('enterprise_support')->__('Chose Report Types')
        ));

        $fieldSet->addField('select_report_type', 'multiselect', array(
            'name'     => 'report_types[]',
            'label'    => Mage::helper('enterprise_support')->__('Types'),
            'title'    => Mage::helper('enterprise_support')->__('Types'),
            'required' => true,
            'values'   => Mage::helper('enterprise_support')->getSysReportTypeOptions(),
            'value'    => Mage::helper('enterprise_support')->getSysReportTypeNames(),
        ));

        $form->setUseContainer(true);
        $form->setId('sysreport_create_form');
        $form->setMethod('post');
        $form->setAction($this->getUrl('*/*/create'));
        $this->setForm($form);
    }
}
