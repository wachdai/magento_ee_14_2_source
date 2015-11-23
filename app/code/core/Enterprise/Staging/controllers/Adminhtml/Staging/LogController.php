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

/**
 * Staging Manage controller
 */
class Enterprise_Staging_Adminhtml_Staging_LogController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Enterprise_Staging');
    }

    /**
     * View History Log Grid
     *
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Content Staging'))
             ->_title($this->__('Log'));

        $this->loadLayout();
        $this->_setActiveMenu('system');
        $this->renderLayout();
    }

    /**
     * View details for Log  entry
     *
     */
    public function viewAction()
    {
        $this->_initLog();

        $this->_title($this->__('System'))
             ->_title($this->__('Content Staging'))
             ->_title($this->__('Log'))
             ->_title($this->__('Log Entry'));

        $this->loadLayout();
        $this->_setActiveMenu('system');
        $this->renderLayout();
    }

    /**
     * Preparing log model with loaded data by passed id
     *
     * @param int $logId
     * @return Enterprise_Staging_Model_Staging_Log
     */
    protected function _initLog($logId = null)
    {
        if (is_null($logId)) {
            $logId  = (int) $this->getRequest()->getParam('id');
        }

        if ($logId) {
            $log = Mage::getModel('enterprise_staging/staging_log')
                ->load($logId);

            if ($log->getId()) {
                Mage::register('log', $log);
                return $log;
            }
        }
        return false;
    }

    /**
     * Check current user permission on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/enterprise_staging/staging_log');
    }
}
