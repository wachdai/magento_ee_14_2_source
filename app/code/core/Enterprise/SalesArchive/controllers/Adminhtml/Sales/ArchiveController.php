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
 * Archive controller
 *
 */
class Enterprise_SalesArchive_Adminhtml_Sales_ArchiveController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Render archive grid
     *
     * @return Enterprise_SalesArchive_Adminhtml_Sales_ArchiveController
     */
    protected function _renderGrid()
    {
        $this->loadLayout(false);
        $this->renderLayout();
        return $this;
    }

    /**
     * Orders view page
     */
    public function ordersAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sales/archive/orders');
        $this->renderLayout();
    }

    /**
     * Orders grid
     */
    public function ordersGridAction()
    {
        $this->_renderGrid();
    }

    /**
     * Invoices view page
     */
    public function invoicesAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sales/archive/invoices');
        $this->renderLayout();
    }

    /**
     * Invoices grid
     */
    public function invoicesGridAction()
    {
        $this->_renderGrid();
    }


    /**
     * Creditmemos view page
     */
    public function creditmemosAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sales/archive/creditmemos');
        $this->renderLayout();
    }

    /**
     * Creditmemos grid
     */
    public function creditmemosGridAction()
    {
        $this->_renderGrid();
    }

    /**
     * Shipments view page
     */
    public function shipmentsAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sales/archive/shipments');
        $this->renderLayout();
    }

    /**
     * Shipments grid
     */
    public function shipmentsGridAction()
    {
        $this->_renderGrid();
    }


    /**
     * Cancel orders mass action
     */
    public function massCancelAction()
    {
        $this->_forward('massCancel', 'sales_order', null, array('origin' => 'archive'));
    }

    /**
     * Hold orders mass action
     */
    public function massHoldAction()
    {
        $this->_forward('massHold', 'sales_order', null, array('origin' => 'archive'));
    }

    /**
     * Unhold orders mass action
     */
    public function massUnholdAction()
    {
        $this->_forward('massUnhold', 'sales_order', null, array('origin' => 'archive'));
    }

    /**
     * Massaction for removing orders from archive
     *
     */
    public function massRemoveAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $removedFromArchive = Mage::getSingleton('enterprise_salesarchive/archive')
            ->removeOrdersFromArchiveById($orderIds);

        $removedFromArchiveCount = count($removedFromArchive);
        if ($removedFromArchiveCount>0) {
            $this->_getSession()->addSuccess(
                $this->__('%s order(s) have been removed from archive.', $removedFromArchiveCount)
            );
        }
        else {
            // selected orders is not available for removing from archive
        }
        $this->_redirect('*/*/orders');
    }

    /**
     * Massaction for adding orders to archive
     *
     */
    public function massAddAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $archivedIds = Mage::getSingleton('enterprise_salesarchive/archive')
            ->archiveOrdersById($orderIds);

        $archivedCount = count($archivedIds);
        if ($archivedCount>0) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been archived.', $archivedCount));
        } else {
            $this->_getSession()->addWarning($this->__('Selected order(s) cannot be archived.'));
        }
        $this->_redirect('*/sales_order/');
    }

    /**
     * Archive order action
     */
    public function addAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            $archivedIds = Mage::getSingleton('enterprise_salesarchive/archive')
                ->archiveOrdersById($orderId);
            $this->_getSession()->addSuccess($this->__('The order has been archived.'));
            $this->_redirect('*/sales_order/view', array('order_id'=>$orderId));
        } else {
            $this->_getSession()->addError($this->__('Please specify order id to be archived.'));
            $this->_redirect('*/sales_order');
        }
    }

    /**
     * Unarchive order action
     */
    public function removeAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            $orderIds = Mage::getSingleton('enterprise_salesarchive/archive')
                ->removeOrdersFromArchiveById($orderId);
            $this->_getSession()->addSuccess($this->__('The order has been removed from the archive.'));
            $this->_redirect('*/sales_order/view', array('order_id'=>$orderId));
        } else {
            $this->_getSession()->addError($this->__('Please specify order id to be removed from archive.'));
            $this->_redirect('*/sales_order');
        }
    }

    /**
     * Print invoices mass action
     */
    public function massPrintInvoicesAction()
    {
        $this->_forward('pdfinvoices', 'sales_order', null, array('origin' => 'archive'));
    }

    /**
     * Print Credit Memos mass action
     */
    public function massPrintCreditMemosAction()
    {
        $this->_forward('pdfcreditmemos', 'sales_order', null, array('origin' => 'archive'));
    }

    /**
     * Print all documents mass action
     */
    public function massPrintAllDocumentsAction()
    {
        $this->_forward('pdfdocs', 'sales_order', null, array('origin' => 'archive'));
    }

    /**
     * Print packing slips mass action
     */
    public function massPrintPackingSlipsAction()
    {
        $this->_forward('pdfshipments', 'sales_order', null, array('origin' => 'archive'));
    }

    /**
     * Print shipping labels mass action
     */
    public function massPrintShippingLabelAction()
    {
        $this->_forward('massPrintShippingLabel', 'sales_order_shipment', null, array('origin' => 'archive'));
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $this->_export('csv');
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $this->_export('xml');
    }

    /**
     * Declare headers and content file in response for file download
     *
     * @param string $type
     */
    protected function _export($type)
    {
        $action = strtolower((string)$this->getRequest()->getParam('action'));
        $layout = $this->getLayout();

        switch ($action) {
            case 'invoice':
                $fileName = 'invoice_archive.' . $type;
                $grid = $layout->createBlock('enterprise_salesarchive/adminhtml_sales_archive_order_invoice_grid');
                break;
            case 'shipment':
                $fileName = 'shipment_archive.' . $type;
                $grid = $layout->createBlock('enterprise_salesarchive/adminhtml_sales_archive_order_shipment_grid');
                break;
            case 'creditmemo':
                $fileName = 'creditmemo_archive.' . $type;
                $grid = $layout->createBlock('enterprise_salesarchive/adminhtml_sales_archive_order_creditmemo_grid');
                break;
            default:
                $fileName = 'orders_archive.' . $type;
                $grid = $layout->createBlock('enterprise_salesarchive/adminhtml_sales_archive_order_grid');
        }

        if ($type == 'csv') {
            $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
        } else {
            $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
        }
    }

    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        switch (strtolower($this->getRequest()->getActionName())) {
            case 'orders':
            case 'ordersgrid':
                $acl = 'sales/archive/orders';
                break;

            case 'invoices':
            case 'invoicesgrid':
                $acl = 'sales/archive/invoices';
                break;

           case 'creditmemos':
           case 'creditmemosgrid':
                $acl = 'sales/archive/creditmemos';
                break;

           case 'shipments':
           case 'shipmentsgrid':
                $acl = 'sales/archive/shipments';
                break;

           case 'massadd':
           case 'add':
               $acl = 'sales/archive/orders/add';
                break;

           case 'massremove':
           case 'remove':
                $acl = 'sales/archive/orders/remove';
                break;

           default:
                $acl = 'sales/archive/orders';
                break;
        }

        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }
}
