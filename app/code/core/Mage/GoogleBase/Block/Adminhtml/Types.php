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
 * @package     Mage_GoogleBase
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Adminhtml GoogleBase Item Types Grid
 *
 * @category   Mage
 * @package    Mage_GoogleBase
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Mage_GoogleBase_Block_Adminhtml_Types extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'googlebase';
        $this->_controller = 'adminhtml_types';
        $this->_addButtonLabel = Mage::helper('googlebase')->__('Add Attribute Mapping');
        $this->_headerText = Mage::helper('googlebase')->__('Manage Attribute Mapping');
        parent::__construct();
    }

//    public function getGridHtml()
//    {
//        $_storeSwitcherHtml = $this->getLayout()->createBlock('googlebase/adminhtml_store_switcher')->toHtml();
//        return $_storeSwitcherHtml . parent::getGridHtml();
//    }
//
//    public function getCreateUrl()
//    {
//        return $this->getUrl('*/*/new', array('_current'=>true));
//    }
}
