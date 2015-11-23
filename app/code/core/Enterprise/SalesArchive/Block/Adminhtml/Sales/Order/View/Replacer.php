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
 * Sales archive order view replacer for archive
 *
 */
class Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_View_Replacer extends Mage_Adminhtml_Block_Sales_Order_Abstract
{
    protected function _prepareLayout()
    {
        if ($this->getOrder()->getIsArchived()) {
            $this->getLayout()->getBlock('sales_order_tabs')->addTab('order_shipments', 'enterprise_salesarchive/adminhtml_sales_order_view_tab_shipments');
            $this->getLayout()->getBlock('sales_order_tabs')->addTab('order_invoices', 'enterprise_salesarchive/adminhtml_sales_order_view_tab_invoices');
            $this->getLayout()->getBlock('sales_order_tabs')->addTab('order_creditmemos', 'enterprise_salesarchive/adminhtml_sales_order_view_tab_creditmemos');

            $restoreUrl = $this->getUrl(
                '*/sales_archive/remove',
                array('order_id' => $this->getOrder()->getId())
            );
            if (Mage::getSingleton('admin/session')->isAllowed('sales/archive/orders/remove')) {
                $this->getLayout()->getBlock('sales_order_edit')->addButton('restore',  array(
                    'label' => Mage::helper('enterprise_salesarchive')->__('Move to Order Managment'),
                    'onclick' => 'setLocation(\'' . $restoreUrl . '\')',
                    'class' => 'cancel'
                ));
            }
        } elseif ($this->getOrder()->getIsMoveable() !== false) {
            $isActive = Mage::getSingleton('enterprise_salesarchive/config')->isArchiveActive();
            if ($isActive) {
                $archiveUrl = $this->getUrl(
                    '*/sales_archive/add',
                    array('order_id' => $this->getOrder()->getId())
                );
                if (Mage::getSingleton('admin/session')->isAllowed('sales/archive/orders/add')) {
                    $this->getLayout()->getBlock('sales_order_edit')->addButton('restore',  array(
                        'label' => Mage::helper('enterprise_salesarchive')->__('Move to Archive'),
                        'onclick' => 'setLocation(\'' . $archiveUrl . '\')',
                    ));
                }
            }
        }

        return $this;
    }

    protected function _toHtml()
    {
        return '';
    }
}
