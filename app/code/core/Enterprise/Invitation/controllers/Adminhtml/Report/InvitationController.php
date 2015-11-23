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
 * @package     Enterprise_Invitation
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Invitation reports controller
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */

class Enterprise_Invitation_Adminhtml_Report_InvitationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init action breadcrumbs
     *
     * @return Enterprise_Invitation_Adminhtml_Report_InvitationController
     */
    public function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('reports')->__('Reports'), Mage::helper('reports')->__('Reports'))
            ->_addBreadcrumb(
                Mage::helper('enterprise_invitation')->__('Invitations'),
                Mage::helper('enterprise_invitation')->__('Invitations')
            );
        return $this;
    }

    /**
     * General report action
     */
    public function indexAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Invitations'))
             ->_title($this->__('General'));

        $this->_initAction()
            ->_setActiveMenu('report/enterprise_invitation/general')
            ->_addBreadcrumb(
                Mage::helper('enterprise_invitation')->__('General Report'),
                Mage::helper('enterprise_invitation')->__('General Report')
            )
            ->_addContent($this->getLayout()->createBlock('enterprise_invitation/adminhtml_report_invitation_general'))
            ->renderLayout();
    }

    /**
     * Export invitation general report grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'invitation_general.csv';
        $content    = $this->getLayout()->createBlock('enterprise_invitation/adminhtml_report_invitation_general_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export invitation general report grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'invitation_general.xml';
        $content    = $this->getLayout()->createBlock('enterprise_invitation/adminhtml_report_invitation_general_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Report by customers action
     */
    public function customerAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Invitations'))
             ->_title($this->__('Customers'));

        $this->_initAction()
            ->_setActiveMenu('report/enterprise_invitation/customer')
            ->_addBreadcrumb(
                Mage::helper('enterprise_invitation')->__('Invitation Report by Customers'),
                Mage::helper('enterprise_invitation')->__('Invitation Report by Customers')
            )
            ->_addContent($this->getLayout()->createBlock('enterprise_invitation/adminhtml_report_invitation_customer'))
            ->renderLayout();
    }

    /**
     * Export invitation customer report grid to CSV format
     */
    public function exportCustomerCsvAction()
    {
        $fileName   = 'invitation_customer.csv';
        $content    = $this->getLayout()->createBlock('enterprise_invitation/adminhtml_report_invitation_customer_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export invitation customer report grid to Excel XML format
     */
    public function exportCustomerExcelAction()
    {
        $fileName   = 'invitation_customer.xml';
        $content    = $this->getLayout()->createBlock('enterprise_invitation/adminhtml_report_invitation_customer_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Report by order action
     */
    public function orderAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Invitations'))
             ->_title($this->__('Order Conversion Rate'));

        $this->_initAction()
            ->_setActiveMenu('report/enterprise_invitation/order')
            ->_addBreadcrumb(
                Mage::helper('enterprise_invitation')->__('Invitation Report by Customers'),
                Mage::helper('enterprise_invitation')->__('Invitation Report by Order Conversion Rate')
            )
            ->_addContent($this->getLayout()->createBlock('enterprise_invitation/adminhtml_report_invitation_order'))
            ->renderLayout();
    }

    /**
     * Export invitation order report grid to CSV format
     */
    public function exportOrderCsvAction()
    {
        $fileName   = 'invitation_order.csv';
        $content    = $this->getLayout()->createBlock('enterprise_invitation/adminhtml_report_invitation_order_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export invitation order report grid to Excel XML format
     */
    public function exportOrderExcelAction()
    {
        $fileName   = 'invitation_order.xml';
        $content    = $this->getLayout()->createBlock('enterprise_invitation/adminhtml_report_invitation_order_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Acl admin user check
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('enterprise_invitation/config')->isEnabled() &&
               Mage::getSingleton('admin/session')->isAllowed('report/enterprise_invitation');
    }
}
