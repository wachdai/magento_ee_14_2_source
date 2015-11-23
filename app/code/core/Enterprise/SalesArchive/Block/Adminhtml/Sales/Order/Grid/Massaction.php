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
 * @package     Enterprise_SalesArchive
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 *  Add sales archiving to order's grid view massaction
 *
 */
class Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction extends Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract
{
    /**
     * Before rendering html operations
     *
     * @return Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction
     */
    protected function _beforeToHtml()
    {
        $isActive = Mage::getSingleton('enterprise_salesarchive/config')->isArchiveActive();
        if ($isActive && Mage::getSingleton('admin/session')->isAllowed('sales/archive/order/add')) {
            $this->addItem('add_order_to_archive', array(
                 'label'=> Mage::helper('enterprise_salesarchive')->__('Move to Archive'),
                 'url'  => $this->getUrl('*/sales_archive/massAdd'),
            ));
        }
        return parent::_beforeToHtml();
    }
}
