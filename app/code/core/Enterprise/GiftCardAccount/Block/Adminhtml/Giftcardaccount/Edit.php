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
 * @package     Enterprise_GiftCardAccount
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_GiftCardAccount_Block_Adminhtml_Giftcardaccount_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_giftcardaccount';
        $this->_blockGroup = 'enterprise_giftcardaccount';

        parent::__construct();

        $clickSave = "\$('_sendaction').value = 0;";
        $clickSave .= "\$('_sendrecipient_email').removeClassName('required-entry');";
        $clickSave .= "\$('_sendrecipient_name').removeClassName('required-entry');";
        $clickSave .= "editForm.submit();";

        $this->_updateButton('save', 'label', Mage::helper('enterprise_giftcardaccount')->__('Save'));
        $this->_updateButton('save', 'onclick', $clickSave);
        $this->_updateButton('delete', 'label', Mage::helper('enterprise_giftcardaccount')->__('Delete'));

        $clickSend = "\$('_sendrecipient_email').addClassName('required-entry');";
        $clickSend .= "\$('_sendrecipient_name').addClassName('required-entry');";
        $clickSend .= "\$('_sendaction').value = 1;";
        $clickSend .= "editForm.submit();";

        $this->_addButton('send', array(
            'label'     => Mage::helper('enterprise_giftcardaccount')->__('Save & Send Email'),
            'onclick'   => $clickSend,
            'class'     => 'save',
        ));
    }

    public function getGiftcardaccountId()
    {
        return Mage::registry('current_giftcardaccount')->getId();
    }

    public function getHeaderText()
    {
        if (Mage::registry('current_giftcardaccount')->getId()) {
            return Mage::helper('enterprise_giftcardaccount')->__('Edit Gift Card Account: %s', $this->escapeHtml(Mage::registry('current_giftcardaccount')->getCode()));
        }
        else {
            return Mage::helper('enterprise_giftcardaccount')->__('New Gift Card Account');
        }
    }

}
