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
 * @package     Enterprise_Pci
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Encryption key change form block
 *
 */
class Enterprise_Pci_Block_Adminhtml_Crypt_Key_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Add form fields
     *
     * @return Enterprise_Pci_Block_Adminhtml_Crypt_Key_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));
        $fieldset = $form->addFieldset('main_fieldset', array('legend' => Mage::helper('enterprise_pci')->__('New Encryption Key')));
        $fieldset->addField('enc_key_note', 'note', array(
            'text' => Mage::helper('enterprise_pci')->__('The encryption key is used to encrypt passwords and other sensitive data.')
        ));
        $fieldset->addField('generate_random', 'select', array(
            'name'    => 'generate_random',
            'label'   => Mage::helper('enterprise_pci')->__('Auto-generate a Key'),
            'options' => array(
                0 => Mage::helper('adminhtml')->__('No'),
                1 => Mage::helper('adminhtml')->__('Yes'),
            ),
            'onclick' => "var cryptKey = $('crypt_key'); cryptKey.disabled = this.value == 1; if (cryptKey.disabled) {cryptKey.parentNode.parentNode.hide();} else {cryptKey.parentNode.parentNode.show();}",
            'note'    => Mage::helper('enterprise_pci')->__('The generated key will be displayed after changing.'),
        ));
        $fieldset->addField('crypt_key', 'text', array(
            'name'      => 'crypt_key',
            'label'     => Mage::helper('enterprise_pci')->__('New Key'),
            'style'     => 'width:32em;',
            'maxlength' => 32,
        ));
        $form->setUseContainer(true);
        if ($data = $this->getFormData()) {
            $form->addValues($data);
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
