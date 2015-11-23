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

class Enterprise_Support_Block_Adminhtml_Sysreport_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Update buttons, set header, controller, mode and block group
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_controller = 'adminhtml_sysreport';
        $this->_blockGroup = 'enterprise_support';
        $this->_mode       = 'create';
        parent::__construct($args);

        $this->removeButton('reset');
        $this->removeButton('back');
        $this->removeButton('save');
        $this->addButton('save', array(
            'label'     => Mage::helper('enterprise_support')->__('Create'),
            'onclick'   => 'SysreportPopupForm.submitForm(\'sysreport_create_form\',\'' . $this->getUrl('*/*/') . '\')',
            'class'     => 'save',
        ));
    }

    /**
     * Get header text for system report creation page
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('enterprise_support')->__('Create System Report');
    }
}
