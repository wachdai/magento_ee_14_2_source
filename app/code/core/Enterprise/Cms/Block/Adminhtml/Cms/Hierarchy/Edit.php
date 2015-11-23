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
 * @package     Enterprise_Cms
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Cms Page Tree Edit Form Container Block
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 */
class Enterprise_Cms_Block_Adminhtml_Cms_Hierarchy_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize Form Container
     *
     */
    public function __construct()
    {
        $this->_objectId   = 'node_id';
        $this->_blockGroup = 'enterprise_cms';
        $this->_controller = 'adminhtml_cms_hierarchy';

        parent::__construct();

        $this->_updateButton('save', 'onclick', 'hierarchyNodes.save()');
        $this->_removeButton('back');
        $this->_addButton('delete', array(
            'label'     => Mage::helper('enterprise_cms')->__('Delete Current Hierarchy'),
            'class'     => 'delete',
            'onclick'   => 'deleteCurrentHierarchy()',
        ), -1, 1);

        if (!Mage::app()->isSingleStoreMode()) {
            $this->_addButton('delete_multiple', array(
                'label'     => Mage::helper('enterprise_cms')->getDeleteMultipleHierarchiesText(),
                'class'     => 'delete',
                'onclick'   => "openHierarchyDialog('delete')",
            ), -1, 7);
            $this->_addButton('copy', array(
                'label'     => Mage::helper('enterprise_cms')->__('Copy'),
                'class'     => 'add',
                'onclick'   => "openHierarchyDialog('copy')",
            ), -1, 14);
        }
    }

    /**
     * Retrieve text for header element
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('enterprise_cms')->__('Manage Pages Hierarchy');
    }
}
