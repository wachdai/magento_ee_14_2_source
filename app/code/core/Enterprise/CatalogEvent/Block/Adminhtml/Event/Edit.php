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
 * @package     Enterprise_CatalogEvent
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Catalog Events edit page
 *
 * @category   Enterprise
 * @package    Enterprise_CatalogEvent
 */
class Enterprise_CatalogEvent_Block_Adminhtml_Event_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_objectId = 'id';
    protected $_blockGroup = 'enterprise_catalogevent';
    protected $_controller = 'adminhtml_event';

    /**
     * Prepare catalog event form or category selector
     *
     * @return Enterprise_CatalogEvent_Block_Adminhtml_Event_Edit
     */
    protected function _prepareLayout()
    {
        if (!$this->getEvent()->getId() && !$this->getEvent()->getCategoryId()) {
            $this->_removeButton('save');
            $this->_removeButton('reset');
        } else {
            $this->_addButton(
                'save_and_continue',
                array(
                    'label' => $this->helper('enterprise_catalogevent')->__('Save and Continue Edit'),
                    'class' => 'save',
                    'onclick'   => 'saveAndContinue()',
                ),
                1
            );

            $this->_formScripts[] = '
                function saveAndContinue() {
                    if (editForm.validator.validate()) {
                        $(editForm.formId).insert({bottom:
                            \'<\' + \'input type="hidden" name="_continue" value="1" /\' + \'>\'
                        });
                        editForm.submit();
                    }
                }
            ';
        }

        parent::_prepareLayout();

        if (!$this->getEvent()->getId() && !$this->getEvent()->getCategoryId()) {
            $this->setChild('form', $this->getLayout()->createBlock($this->_blockGroup . '/' . $this->_controller . '_' . $this->_mode . '_category'));
        }

        if ($this->getRequest()->getParam('category')) {
            $this->_updateButton('back', 'label', $this->helper('enterprise_catalogevent')->__('Back to Category'));
        }

        if ($this->getEvent()->isReadonly() && $this->getEvent()->getImageReadonly()) {
            $this->_removeButton('save');
            $this->_removeButton('reset');
            $this->_removeButton('save_and_continue');
        }

        if (!$this->getEvent()->isDeleteable()) {
            $this->_removeButton('delete');
        }

        return $this;
    }


    /**
     * Retrieve form back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRequest()->getParam('category')) {
            return $this->getUrl('*/catalog_category/edit',
                                array('clear' => 1, 'id' => $this->getEvent()->getCategoryId()));
        } elseif (!$this->getEvent()->getId() && $this->getEvent()->getCategoryId()) {
            return $this->getUrl('*/*/new',
                                 array('_current' => true, 'category_id' => null));
        }

        return parent::getBackUrl();
    }


    /**
     * Retrieve form container header
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getEvent()->getId()) {
            return Mage::helper('enterprise_catalogevent')->__('Edit Catalog Event');
        }
        else {
            return Mage::helper('enterprise_catalogevent')->__('Add Catalog Event');
        }
    }

    /**
     * Retrive catalog event model
     *
     * @return Enterprise_CatalogEvent_Model_Event
     */
    public function getEvent()
    {
        return Mage::registry('enterprise_catalogevent_event');
    }

}
