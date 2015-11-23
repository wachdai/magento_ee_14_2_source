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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Currency edit tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Currency_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('currency_edit_tabs');
        $this->setDestElementId('currency_edit_form');
        $this->setTitle(Mage::helper('adminhtml')->__('Currency'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label'     => Mage::helper('adminhtml')->__('General Information'),
            'content'   => $this->getLayout()->createBlock('adminhtml/system_currency_edit_tab_main')->toHtml(),
            'active'    => true
        ));

        $this->addTab('currency_rates', array(
            'label'     => Mage::helper('adminhtml')->__('Rates'),
            'content'   => $this->getLayout()->createBlock('adminhtml/system_currency_edit_tab_rates')->toHtml(),
        ));
        
        return parent::_beforeToHtml();
    }
}
