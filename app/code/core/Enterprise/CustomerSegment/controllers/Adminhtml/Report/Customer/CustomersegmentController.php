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
 * @package     Enterprise_CustomerSegment
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Customer Segment reports controller
 *
 * @category    Enterprise
 * @package     Enterprise_CustomerSegment
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CustomerSegment_Adminhtml_Report_Customer_CustomersegmentController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Admin session
     *
     * @var Mage_Admin_Model_Session
     */
    protected $_adminSession = null;

    /**
     * Init layout and adding breadcrumbs
     *
     * @return Enterprise_CustomerSegment_Adminhtml_Report_Customer_CustomersegmentController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/customers')
            ->_addBreadcrumb(
                Mage::helper('enterprise_customersegment')->__('Reports'),
                Mage::helper('enterprise_customersegment')->__('Reports')
            )
            ->_addBreadcrumb(
                Mage::helper('enterprise_customersegment')->__('Customers'),
                Mage::helper('enterprise_customersegment')->__('Customers')
            );
        return $this;
    }

    /**
     * Initialize Customer Segmen Model
     * or adding error to session storage if object was not loaded
     *
     * @param bool $outputMessage
     * @return Enterprise_CustomerSegment_Model_Segment|false
     */
    protected function _initSegment($outputMessage = true)
    {
        $segmentId = $this->getRequest()->getParam('segment_id', 0);
        $segmentIds = $this->getRequest()->getParam('massaction');
        if ($segmentIds) {
            $this->_getAdminSession()
                ->setMassactionIds($segmentIds)
                ->setViewMode($this->getRequest()->getParam('view_mode'));
        }

        /* @var $segment Enterprise_CustomerSegment_Model_Segment */
        $segment = Mage::getModel('enterprise_customersegment/segment');

        if ($segmentId) {
            $segment->load($segmentId);
        }
        if ($this->_getAdminSession()->getMassactionIds()) {
            $segment->setMassactionIds($this->_getAdminSession()->getMassactionIds());
            $segment->setViewMode($this->_getAdminSession()->getViewMode());
        }
        if (!$segment->getId() && !$segment->getMassactionIds()) {
            if ($outputMessage) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Wrong customer segment requested.'));
            }
            return false;
        }
        Mage::register('current_customer_segment', $segment);

        $websiteIds = $this->getRequest()->getParam('website_ids');
        if (!is_null($websiteIds) && empty($websiteIds)) {
            $websiteIds = null;
        } elseif (!is_null($websiteIds) && !empty($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }
        Mage::register('filter_website_ids', $websiteIds);

        return $segment;
    }

    /**
     * Index Action.
     * Forward to Segment Action
     *
     */
    public function indexAction()
    {
        $this->_forward('segment');
    }

    /**
     * Segment Action
     *
     */
    public function segmentAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Customers'))
             ->_title($this->__('Customer Segments'));

        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock('enterprise_customersegment/adminhtml_report_customer_segment')
            )
            ->renderlayout();
    }

    /**
     * Detail Action of customer segment
     *
     */
    public function detailAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Customers'))
             ->_title($this->__('Customer Segments'));

        if ($this->_initSegment()) {

            // Add help Notice to Combined Report
            if ($this->_getAdminSession()->getMassactionIds()) {
                $collection = Mage::getResourceModel('enterprise_customersegment/segment_collection')
                    ->addFieldToFilter(
                        'segment_id',
                        array('in' => $this->_getAdminSession()->getMassactionIds())
                    );

                $segments = array();
                foreach ($collection as $item) {
                    $segments[] = $item->getName();
                }
                /* @translation $this->__('Viewing combined "%s" report from segments: %s') */
                if ($segments) {
                    $viewModeLabel = Mage::helper('enterprise_customersegment')->getViewModeLabel(
                        $this->_getAdminSession()->getViewMode()
                    );
                    Mage::getSingleton('adminhtml/session')->addNotice(
                        $this->__('Viewing combined "%s" report from segments: %s.', $viewModeLabel, implode(', ', $segments))
                    );
                }
            }

            $this->_title($this->__('Details'));

            $this->_initAction()->renderLayout();
        } else {
            $this->_redirect('*/*/segment');
            return ;
        }
    }

    /**
     * Apply segment conditions to all customers
     */
    public function refreshAction()
    {
        $segment = $this->_initSegment();
        if ($segment) {
            try {
                if ($segment->getApplyTo() != Enterprise_CustomerSegment_Model_Segment::APPLY_TO_VISITORS) {
                    $segment->matchCustomers();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Customer Segment data has been refreshed.')
                );
                $this->_redirect('*/*/detail', array('_current' => true));
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/detail', array('_current' => true));
        return;
    }

    /**
     * Export Excel Action
     *
     */
    public function exportExcelAction()
    {
        if ($this->_initSegment()) {
            $fileName = 'customersegment_customers.xml';
            $content = $this->getLayout()
                ->createBlock('enterprise_customersegment/adminhtml_report_customer_segment_detail_grid')
                ->getExcelFile($fileName);
            $this->_prepareDownloadResponse($fileName, $content);
        } else {
            $this->_redirect('*/*/detail', array('_current' => true));
            return ;
        }
    }

    /**
     * Export Csv Action
     *
     */
    public function exportCsvAction()
    {
        if ($this->_initSegment()) {
            $fileName = 'customersegment_customers.csv';
            $content = $this->getLayout()
                ->createBlock('enterprise_customersegment/adminhtml_report_customer_segment_detail_grid')
                ->getCsvFile();
            $this->_prepareDownloadResponse($fileName, $content);
        } else {
            $this->_redirect('*/*/detail', array('_current' => true));
            return ;
        }
    }

    /**
     * Segment customer ajax grid action
     */
    public function customerGridAction()
    {
        if (!$this->_initSegment(false)) {
            return;
        }
        $grid = $this->getLayout()
            ->createBlock('enterprise_customersegment/adminhtml_report_customer_segment_detail_grid');
        $this->getResponse()->setBody($grid->toHtml());
    }

    /**
     * Retrieve admin session model
     *
     * @return Mage_Admin_Model_Session
     */
    protected function _getAdminSession()
    {
        if (is_null($this->_adminSession)) {
            $this->_adminSession = Mage::getModel('admin/session');
        }
        return $this->_adminSession;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return  Mage::getSingleton('admin/session')->isAllowed('customer/customersegment')
                && Mage::helper('enterprise_customersegment')->isEnabled();
    }
}
