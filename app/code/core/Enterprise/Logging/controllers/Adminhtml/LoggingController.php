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
 * @package     Enterprise_Logging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Log and archive grids controller
 */
class Enterprise_Logging_Adminhtml_LoggingController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Log page
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Admin Actions Logs'))
             ->_title($this->__('Report'));

        $this->loadLayout();
        $this->_setActiveMenu('system/enterprise_logging');
        $this->renderLayout();
    }

    /**
     * Log grid ajax action
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * View logging details
     */
    public function detailsAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Admin Actions Logs'))
             ->_title($this->__('Report'))
             ->_title($this->__('View Entry'));

        $eventId = $this->getRequest()->getParam('event_id');
        $model   = Mage::getModel('enterprise_logging/event')
            ->load($eventId);
        if (!$model->getId()) {
            $this->_redirect('*/*/');
            return;
        }
        Mage::register('current_event', $model);

        $this->loadLayout();
        $this->_setActiveMenu('system/enterprise_logging');
        $this->renderLayout();
    }

    /**
     * Export log to CSV
     */
    public function exportCsvAction()
    {
        $this->_prepareDownloadResponse('log.csv',
            $this->getLayout()->createBlock('enterprise_logging/adminhtml_index_grid')->getCsvFile()
        );
    }

    /**
     * Export log to MSXML
     */
    public function exportXmlAction()
    {
        $this->_prepareDownloadResponse('log.xml',
            $this->getLayout()->createBlock('enterprise_logging/adminhtml_index_grid')->getExcelFile()
        );
    }

    /**
     * Archive page
     */
    public function archiveAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Admin Actions Logs'))
             ->_title($this->__('Archive'));

        $this->loadLayout();
        $this->_setActiveMenu('system/enterprise_logging');
        $this->renderLayout();
    }

    /**
     * Archive grid ajax action
     */
    public function archiveGridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Download archive file
     */
    public function downloadAction()
    {
        $archive = Mage::getModel('enterprise_logging/archive')->loadByBaseName(
            $this->getRequest()->getParam('basename')
        );
        if ($archive->getFilename()) {
            $this->_prepareDownloadResponse($archive->getBaseName(), $archive->getContents(), $archive->getMimeType());
        }
    }

    /**
     * permissions checker
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'archive':
            case 'download':
            case 'archiveGrid':
                return Mage::getSingleton('admin/session')->isAllowed('admin/system/enterprise_logging/backups');
                break;
            case 'grid':
            case 'exportCsv':
            case 'exportXml':
            case 'details':
            case 'index':
                return Mage::getSingleton('admin/session')->isAllowed('admin/system/enterprise_logging/events');
                break;
        }

    }
}
