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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

require_once 'Enterprise/Staging/controllers/Adminhtml/Staging/ManageController.php';
/**
 * Staging Manage controller
 */
class Enterprise_Staging_Adminhtml_Staging_BackupController
    extends Enterprise_Staging_Adminhtml_Staging_ManageController
{
    /**
     * Initialize staging backup from request parameters
     *
     * @return Enterprise_Staging_Model_Staging_Backup
     */
    protected function _initBackup($backupId = null)
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Content Staging'))
             ->_title($this->__('Backups'));

        if (is_null($backupId)) {
            $backupId  = (int) $this->getRequest()->getParam('id');
        }

        if ($backupId) {
            $backup = Mage::getModel('enterprise_staging/staging_action')
                ->load($backupId);
            if ($backup->getId()) {
                $stagingId = $backup->getStagingId();
                if ($stagingId) {
                    $this->_initStaging($stagingId);
                }

                if ($backup->getId()) {
                    $backup->restoreMap();
                }

                Mage::register('staging_backup', $backup);

                return $backup;
            }
        }
        return false;
    }

    /**
     * Staging backup view action
     *
     */
    public function indexAction()
    {
        $this->_initStaging();

        $this->loadLayout();
        $this->_setActiveMenu('system/enterprise_staging');
        $this->renderLayout();
    }

    /**
     * backup edit process
     *
     */
    public function editAction()
    {
        $backup = $this->_initBackup();

        $staging = $backup->getStaging();

        if (!$backup->canRollback()) {
            $this->_getSession()->addNotice($this->__('All backup items are outdated. The backup is read-only.'));
        }

        if ($staging && $staging->isStatusProcessing()) {
            $this->_getSession()->addNotice(
                $this->__('This backup is read-only, because a merge or a rollback is in progress.')
            );
        }

        $this->_title($this->__('System'))
             ->_title($this->__('Content Staging'))
             ->_title($this->__('Backups'));

        $this->_title($backup->getName());

        $this->loadLayout();
        $this->_setActiveMenu('system/enterprise_staging');
        $this->renderLayout();
    }

    /**
     * Staging grid for AJAX request
     */
    public function gridAction()
    {
        $staging = $this->_initBackup();

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Remove mass backups
     *
     */
    public function massDeleteAction()
    {
        $backupDeleteIds = $this->getRequest()->getPost("backupDelete");
        if (is_array($backupDeleteIds)) {
            foreach ($backupDeleteIds as $backupId) {
                if (!empty($backupId)) {
                    $backup = Mage::getModel('enterprise_staging/staging_action')
                        ->load($backupId);
                    if ($backup->getId()) {
                        try{
                            $backup->setIsDeleteTables(true);
                            $backup->delete();
                        } catch (Exception $e) {
                            $this->_getSession()->addNotice(
                                $this->__('Could not remove the backup: #%s', $backup->getId()));
                        }
                    }
                }
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * Remove backup
     *
     */
    public function deleteAction()
    {
        $backup         = $this->_initBackup();
        $redirectBack   = false;

        if ($backup) {
            try{
                $backup->setIsDeleteTables(true);
                $backup->delete();
            } catch (Exception $e) {
                $redirectBack = true;
            }
        }

        if ($redirectBack) {
            $this->_redirect('*/*/', array(
                'id'        => $backup->getId(),
                '_current'  => true
            ));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Process rollback Action
     *
     */
    public function rollbackPostAction()
    {
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $backupId       = $this->getRequest()->getPost('backup_id');
        $backup         = $this->_initBackup();
        $staging        = $backup->getStaging();
        $mapDataRaw        = $this->getRequest()->getPost('map');

        $mapData = array('staging_items' => array_flip((array)$mapDataRaw));

        if (!$staging->checkCoreFlag()) {
            $this->_getSession()->addError(
                $this->__('Cannot perform rollback operation because reindexing process or another staging operation is running.')
            );
            $this->_redirect('*/*/edit', array(
                '_current'  => true
            ));
            return $this;
        }

        try {
            if (!empty($mapData['staging_items'])) {
                $staging->getMapperInstance()->setRollbackMapData($mapData);
                $staging->getMapperInstance()->setBackupTablePrefix($backup->getStagingTablePrefix());
                $staging->rollback();
                $this->_getSession()->addSuccess($this->__('The master website has been restored.'));
            } else {
                $this->_getSession()->addNotice($this->__('There are no items selected for rollback.'));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $staging->releaseCoreFlag();
            $redirectBack = true;
        } catch (Exception $e) {
            $this->_getSession()->addException(
                $e,
                Mage::helper('enterprise_staging')->__('An error occurred while performing rollback. Please review the log and try again.')
            );
            $staging->releaseCoreFlag();
            $redirectBack = true;
        }

        if ($redirectBack) {
            $this->_redirect('*/*/edit', array(
                'id'        => $backupId,
                '_current'  => true
            ));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Check current user permission on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/enterprise_staging/staging_backup');
    }
}
