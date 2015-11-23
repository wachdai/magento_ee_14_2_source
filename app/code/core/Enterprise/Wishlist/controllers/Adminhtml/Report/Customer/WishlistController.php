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
 * @package     Enterprise_Wishlist
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Wishlist reports controller
 *
 * @category    Enterprise
 * @package     Enterprise_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Wishlist_Adminhtml_Report_Customer_WishlistController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init layout and add breadcrumbs
     *
     * @return Enterprise_Wishlist_Adminhtml_Report_Customer_WishlistController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/customers')
            ->_addBreadcrumb(
                Mage::helper('enterprise_wishlist')->__('Reports'),
                Mage::helper('enterprise_wishlist')->__('Reports')
            )
            ->_addBreadcrumb(
                Mage::helper('enterprise_wishlist')->__('Customers'),
                Mage::helper('enterprise_wishlist')->__('Customers')
            );
        return $this;
    }

    /**
     * Index Action.
     * Forward to Wishlist Action
     */
    public function indexAction()
    {
        $this->_forward('wishlist');
    }

    /**
     * Wishlist view action
     */
    public function wishlistAction()
    {
        $this->_title($this->__('Reports'))
            ->_title($this->__('Customers'))
            ->_title($this->__("Customer's wishlists"));

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Export Excel Action
     */
    public function exportExcelAction()
    {
        $fileName = 'customer_wishlists.xml';
        $content = $this->getLayout()
            ->createBlock('enterprise_wishlist/adminhtml_report_customer_wishlist_grid')
            ->getExcelFile($fileName);
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export Csv Action
     */
    public function exportCsvAction()
    {
        $fileName = 'customer_wishlists.csv';
        $content = $this->getLayout()
            ->createBlock('enterprise_wishlist/adminhtml_report_customer_wishlist_grid')
            ->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Retrieve admin session model
     *
     * @return Mage_Admin_Model_Session
     */
    protected function _getAdminSession()
    {
        return Mage::getSingleton('admin/session');
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return  Mage::getSingleton('admin/session')->isAllowed('admin/report/customers/wishlist');
    }
}
