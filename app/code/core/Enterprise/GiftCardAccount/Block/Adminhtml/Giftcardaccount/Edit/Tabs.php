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

class Enterprise_GiftCardAccount_Block_Adminhtml_Giftcardaccount_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('giftcardaccount_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('enterprise_giftcardaccount')->__('Gift Card Account'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('info', array(
            'label'     => Mage::helper('enterprise_giftcardaccount')->__('Information'),
            'content'   => $this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_edit_tab_info')->initForm()->toHtml(),
            'active'    => true
        ));

        $this->addTab('send', array(
            'label'     => Mage::helper('enterprise_giftcardaccount')->__('Send Gift Card'),
            'content'   => $this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_edit_tab_send')->initForm()->toHtml(),
        ));

        $model = Mage::registry('current_giftcardaccount');
        if ($model->getId()) {
            $this->addTab('history', array(
                'label'     => Mage::helper('enterprise_giftcardaccount')->__('History'),
                'content'   => $this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_edit_tab_history')->toHtml(),
            ));
        }

        return parent::_beforeToHtml();
    }

}
