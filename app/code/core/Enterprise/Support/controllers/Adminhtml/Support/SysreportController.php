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
 * @package     Enterprise_Support
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_Support_Adminhtml_Support_SysreportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Load layout, set active menu and breadcrumbs
     *
     * @return Enterprise_Support_Adminhtml_Support_SysreportController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/tools/enterprise_support/support_sysreport')
            ->_addBreadcrumb(
                Mage::helper('enterprise_support')->__('Support'),
                Mage::helper('enterprise_support')->__('Support'))
            ->_addBreadcrumb(
                Mage::helper('enterprise_support')->__('System Reports'),
                Mage::helper('enterprise_support')->__('System Reports')
            );

        $this->_title($this->__('Support'))
            ->_title($this->__('System Reports'));

        return $this;
    }

    /**
     * Load system report from request
     *
     * @param string $idFieldName
     *
     * @return Enterprise_Support_Model_Sysreport $model
     */
    protected function _initSysReport($idFieldName = 'id')
    {
        $id = (int)$this->getRequest()->getParam($idFieldName);
        $model = Mage::getModel('enterprise_support/sysreport');
        if ($id) {
            $model->load($id);
        }
        if (!Mage::registry('current_sysreport')) {
            Mage::register('current_sysreport', $model);
        }
        return $model;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('Manage Reports'));
        $this->renderLayout();
    }

    /**
     * Render system reports grid
     */
    public function gridAction()
    {
        $this->loadLayout()
            ->renderLayout();
    }

    /**
     * New system report form action
     */
    public function newAction()
    {
        $this->_getSession()->addWarning(
            Mage::helper('enterprise_support')->__('After you make your selections, click the "Create" button. Then stand by while the System Report is generated. This may take a few minutes. You will receive a notification once this step is completed.')
        );
        $this->loadLayout('empty')
            ->renderLayout();
    }

    /**
     * Create action
     */
    public function createAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_redirect('*/*/');
            return;
        }
        $types = $this->getRequest()->getPost('report_types', array());
        if (!$types) {
            $this->_getSession()->addError(
                Mage::helper('enterprise_support')->__('No types were specified to generate system report.')
            );

            return;
        }
        try {
            Mage::getSingleton('adminhtml/session')->setFormData($types);
            /** @var Enterprise_Support_Model_Sysreport $model */
            $model = Mage::getModel('enterprise_support/sysreport');
            /** @var Enterprise_Support_Model_Resource_Sysreport $resourceModel */
            $resourceModel = $model->getResource();

            $reportData = $resourceModel->generateReport($types);
            $model->setReportTypes($types);
            $model->setReportData($reportData);
            $model->save();

            Mage::getSingleton('adminhtml/session')->setFormData(false);
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('enterprise_support')->__('The system report has been generated.')
            );

            $empty = $partial = $completed = array();
            $results = $resourceModel->getReportCreationResults($types);
            foreach ($results as $resultDetails) {
                if ($resultDetails['succeeded'] == $resultDetails['total']) {
                    $completed[] = $resultDetails['title'];
                } else if ($resultDetails['succeeded'] == 0) {
                    $empty[] = $resultDetails['title'];
                } else {
                    $partial[] = $resultDetails['title'];
                }
            }
            if ($completed) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('enterprise_support')->__('Fully completed: %s', implode(', ', $completed))
                );
            }
            if ($partial) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('enterprise_support')->__('Partially completed: %s', implode(', ', $partial))
                );
            }
            if ($empty) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('enterprise_support')->__('Not completed at all: %s', implode(', ', $empty))
                );
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('enterprise_support')->__('An error occurred while the system report was being created. Please review the log and try again.')
            );
            Mage::logException($e);
        }
    }

    /**
     * View action
     */
    public function viewAction()
    {
        try {
            $model = $this->_initSysReport();
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('enterprise_support')->__('Requested system report no longer exists.')
                );
                $this->_redirect('*/*/');
                return;
            }

            $this->_initAction();

            $dateString = $model->getCreatedAt() . ' '
                . Mage::helper('enterprise_support')->getSinceTimeString($model->getCreatedAt());
            $this->_title($dateString);
            $this->_addBreadcrumb($dateString, $dateString)
                ->renderLayout();
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('enterprise_support')->__('Unable to read system report data to display.')
            );
            Mage::logException($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Download action
     */
    public function downloadAction()
    {
        try {
            $model = $this->_initSysReport();
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('enterprise_support')->__('Requested system report no longer exists.')
                );
                $this->_redirect('*/*/');
                return;
            }

            $content = $this->getLayout()
                ->createBlock(
                    'enterprise_support/adminhtml_sysreport_export_html',
                    'sysreport_export_html',
                    array('system_report' => $model)
                )
                ->toHtml();

            $this->_prepareDownloadResponse($model->getFileNameForSysreportDownload(), $content);

            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('enterprise_support')->__('Unable to generate HTML system report.')
            );
            Mage::logException($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete system report action
     */
    public function deleteAction()
    {
        try {
            $model = $this->_initSysReport();
            if (!$model->getId()) {
                Mage::throwException(
                    Mage::helper('enterprise_support')->__('Unable to find a system report to delete.')
                );
            }
            $model->delete();
            $this->_getSession()->addSuccess(
                Mage::helper('enterprise_support')->__('The system report has been deleted.')
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('enterprise_support')->__('An error occurred while deleting the system report. Please review log and try again.')
            );
            Mage::logException($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete specified system reports using grid massaction
     */
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('reports');
        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select report(s) to delete.'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getSingleton('enterprise_support/sysreport')->load($id);
                    $model->delete();
                }

                $this->_getSession()->addSuccess(
                    $this->__('Total of %d system report(s) have been deleted.', count($ids))
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('enterprise_support')->__('An error occurred while mass deleting the system reports. Please review log and try again.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('admin/system/tools/enterprise_support/support_sysreport');
    }
}
