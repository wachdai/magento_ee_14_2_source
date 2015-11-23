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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_GiftRegistry_Block_Adminhtml_Giftregistry_Edit_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Intialize form
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('enterprise_giftregistry_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('enterprise_giftregistry')->__('Gift Registry'));
    }

    /**
     * Add tab sections
     *
     * @return Enterprise_GiftRegistry_Block_Adminhtml_Giftregistry_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('general_section', array(
            'label'   => Mage::helper('enterprise_giftregistry')->__('General Information'),
            'content' => $this->getLayout()->createBlock('enterprise_giftregistry/adminhtml_giftregistry_edit_tab_general')->toHtml()
        ));

        $this->addTab('registry_attributes', array(
            'label'   => Mage::helper('enterprise_giftregistry')->__('Attributes'),
            'content' => $this->getLayout()->createBlock('enterprise_giftregistry/adminhtml_giftregistry_edit_tab_registry')->toHtml()
        ));

        return parent::_beforeToHtml();
    }

}
