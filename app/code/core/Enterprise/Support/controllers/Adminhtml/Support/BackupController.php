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

class Enterprise_Support_Adminhtml_Support_BackupController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init Action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/tools/enterprise_support/support_backup')
            ->_addBreadcrumb(
                Mage::helper('enterprise_support')->__('Support'),
                Mage::helper('enterprise_support')->__('Support'))
            ->_addBreadcrumb(
                Mage::helper('enterprise_support')->__('System Backups'),
                Mage::helper('enterprise_support')->__('System Backups')
            );

        $this->_title($this->__('Support'))
            ->_title($this->__('System Backups'));
        return $this;
    }

    /**
     * Default Action
     *
     * @return void
     */
    public function indexAction()
    {
        Mage::dispatchEvent('enterprise_support_backups_controller_index_action');

        $errors = Mage::getModel('enterprise_support/backup')->validate();
        if ($errors) {
            foreach ($errors as $error) {
                $this->_getSession()->addError($error);
            }
        }

        $this->_initAction();
        $this->_title($this->__('Manage Backups'));
        $this->renderLayout();
    }

    /**
     * Grid Action
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Create Action
     */
    public function newAction()
    {
        /** @var $backup Enterprise_Support_Model_Backup */
        $backup = Mage::getModel('enterprise_support/backup');
        $collection = $backup->getCollection();

        try {
            $this->_issetProcessingBackups($collection);
            $backup->run();
            $backup->save();
            $this->_getSession()->addSuccess($this->__('The backup has been saved.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot save backup'));
        }

        $this->_redirect('*/*/index');
        return $this;
    }

    /**
     * Check if isset processing backups
     *
     * @param Enterprise_Support_Model_Resource_Backup_Collection $collection
     *
     * @return Enterprise_Support_Adminhtml_SupportController
     * @throws Mage_Core_Exception
     */
    protected function _issetProcessingBackups($collection)
    {
        $collection->addProcessingStatusFilter();

        if ($collection->count() > 0) {
            throw new Mage_Core_Exception($this->__('All processes should be complete'));
        }

        return $this;
    }

    /**
     * Delete Action
     *
     * @throws Mage_Core_Exception
     * @return Enterprise_Support_Adminhtml_SupportController
     */
    public function deleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id', 0);

        $backup = Mage::getModel('enterprise_support/backup')->load($id);

        if (!$backup->getId()) {
            $this->_getSession()->addError($this->__('Wrong param id'));
            $this->_redirect('*/*/index');
            return $this;
        }

        try {
            $backup->delete();
            $this->_getSession()->addSuccess($this->__('The backup has been deleted.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot delete backup'));
        }
        $this->_redirect('*/*/index');
        return $this;
    }

    /**
     * Log Detail Action Page
     */
    public function logAction()
    {
        $this->_initAction();
        $this->_title($this->__('Log Details'));
        $this->_addBreadcrumb(
            Mage::helper('enterprise_support')->__('Log Details'),
            Mage::helper('enterprise_support')->__('Log Details'));
        $this->renderLayout();
    }

    /**
     * Download Action
     */
    public function downloadAction()
    {
        $backupId = $this->getRequest()->getParam('backup_id', 0);
        $type     = $this->getRequest()->getParam('type', 0);
        $backup = Mage::getModel('enterprise_support/backup')->load($backupId);
        $item = '';

        foreach ($backup->getItems() as $itemVal) {
            if ($itemVal->getType() == $type) {
                $item = $itemVal;
                break;
            }
        }

        $file = '';
        if (is_object($item)) {
            $file = Mage::helper('enterprise_support')->getFilePath($item->getName());
        }

        if (!file_exists($file)) {

            $this->_getSession()->addError($this->__('File does not exist'));
            $this->_redirect('*/*/index');
            return;
        }

        $this->_prepareDownloadResponse($item->getName(), array(
            'value' => $file,
            'type'  => 'filename'
        ));
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return  Mage::getSingleton('admin/session')->isAllowed('admin/system/tools/enterprise_support/support_backup');
    }
}
