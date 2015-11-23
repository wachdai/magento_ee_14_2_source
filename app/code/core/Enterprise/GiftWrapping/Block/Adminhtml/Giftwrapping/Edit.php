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
 * @package     Enterprise_GiftWrapping
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_GiftWrapping_Block_Adminhtml_Giftwrapping_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Intialize form
     *
     * @return void
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_giftwrapping';
        $this->_blockGroup = 'enterprise_giftwrapping';

        parent::__construct();

        $this->_removeButton('reset');

        $this->_addButton('save_and_continue_edit', array(
            'class'   => 'save',
            'label'   => Mage::helper('enterprise_giftwrapping')->__('Save and Continue Edit'),
            'onclick' => 'editForm.submit(\'' . $this->getSaveUrl() . '\' + \'back/edit/\')',
        ), 3);

        if (Mage::registry('current_giftwrapping_model')->getId()) {
            $confirmMessage = Mage::helper('enterprise_giftwrapping')->__('Are you sure you want to delete this gift wrapping?');
            $this->_updateButton('delete', 'onclick',
                'deleteConfirm(\'' . $this->jsQuoteEscape($confirmMessage) . '\', \'' . $this->getDeleteUrl() . '\')'
            );
        }

        $this->_formScripts[] = '
                function uploadImagesForPreview() {
                    var fform = $(editForm.formId)
                    fform.getElements().each(function(elm){
                        if (Element.readAttribute(elm, "type") == "file") {
                            Element.addClassName(elm, "required-entry")
                        } else {
                            Element.addClassName(elm, "ignore-validate")
                        }
                    });

                    editForm.submit("' . $this->getUploadUrl()  . '");
                }
            ';
    }

    /**
     * Return form header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $wrapping = Mage::registry('current_giftwrapping_model');
        if ($wrapping->getId()) {
            $title = $this->escapeHtml($wrapping->getDesign());
            return Mage::helper('enterprise_giftwrapping')->__('Edit Gift Wrapping "%s"', $title);
        }
        else {
            return Mage::helper('enterprise_giftwrapping')->__('New Gift Wrapping');
        }
    }

    /**
     * Return save url (used for Save and Continue button)
     *
     * @return string
     */
    public function getSaveUrl()
    {
        $wrapping = Mage::registry('current_giftwrapping_model');

        return $this->getUrl('*/*/save', array('id' => $wrapping->getId(), 'store' => $wrapping->getStoreId()));
    }

    /**
     * Return upload url (used for Upload button)
     *
     * @return string
     */
    public function getUploadUrl()
    {
        $wrapping = Mage::registry('current_giftwrapping_model');

        $params = array('store' => $wrapping->getStoreId());
        $id = $wrapping->getId();

        if (!is_null($id)) {
            $params['id'] = $id;
        }
        return $this->getUrl('*/*/upload', $params);
    }

}
