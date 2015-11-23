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
 * @package     Enterprise_ImportExport
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Operation controller
 *
 * @category    Enterprise
 * @package     Enterprise_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_ImportExport_Adminhtml_Scheduled_OperationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Custom constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Enterprise_ImportExport');
    }

    /**
     * Initialize layout.
     *
     * @return Enterprise_ImportExport_Adminhtml_ImportController
     */
    protected function _initAction()
    {
        try {
            $this->_title($this->__('Scheduled Imports/Exports'))
                ->loadLayout()
                ->_setActiveMenu('system/enterprise_importexport');
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/scheduled_operation/index');
        }

        return $this;
    }

    /**
     * Check access (in the ACL) for current user.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/convert/enterprise_scheduled_operation');
    }

    /**
     * Index action.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Create new operation action.
     *
     * @return void
     */
    public function newAction()
    {
        $operationType = $this->getRequest()->getParam('type');
        $this->_initAction()
            ->_title(Mage::helper('enterprise_importexport')->getOperationHeaderText($operationType, 'new'));

        $this->renderLayout();
    }

    /**
     * Edit operation action.
     *
     * @return void
     */
    public function editAction()
    {
        $this->_initAction();
        $operationType = Mage::registry('current_operation')->getOperationType();
        $this->_title(Mage::helper('enterprise_importexport')->getOperationHeaderText($operationType, 'edit'));

        $this->renderLayout();
    }

    /**
     * Save operation action
     *
     * @return void
     */
    public function saveAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if (isset($data['id']) && !is_numeric($data['id'])
                || !isset($data['id']) && (!isset($data['operation_type']) || empty($data['operation_type']))
                || !is_array($data['start_time'])
            ) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to save scheduled operation'));
                return $this->_redirect('*/*/*', array('_current' => true));
            }
            $data['start_time'] = join(':', $data['start_time']);
            if (isset($data['export_filter']) && is_array($data['export_filter'])) {
                $data['entity_attributes']['export_filter'] = $data['export_filter'];
                if (isset($data['skip_attr']) && is_array($data['skip_attr'])) {
                    $data['entity_attributes']['skip_attr'] = array_filter($data['skip_attr'], 'intval');
                }
            }

            try {
                $operation = Mage::getModel('enterprise_importexport/scheduled_operation')->setData($data);
                $operation->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('enterprise_importexport')->getSuccessSaveMessage($operation->getOperationType())
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    $this->__('Unable to save scheduled operation')
                );
            }
        }
        $this->_redirect('*/scheduled_operation/index');
    }

    /**
     * Delete operation action
     *
     * @return void
     */
    public function deleteAction()
    {
        $request = $this->getRequest();
        $id = (int)$request->getParam('id');
        if ($id) {
            try {
                Mage::getModel('enterprise_importexport/scheduled_operation')->setId($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('enterprise_importexport')->getSuccessDeleteMessage($request->getParam('type'))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    $this->__('Unable to delete scheduled operation')
                );
            }
        }
        $this->_redirect('*/scheduled_operation/index');
    }

    /**
     * Ajax grid action
     *
     * @return void
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('enterprise_importexport/adminhtml_scheduled_operation_grid')->toHtml()
        );
    }

    /**
     * Batch delete action
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $request = $this->getRequest();
        $ids = $request->getParam('operation');
        if (is_array($ids)) {
            $ids = array_filter($ids, 'intval');
            try {
                $operations = Mage::getResourceModel('enterprise_importexport/scheduled_operation_collection');
                $operations->addFieldToFilter($operations->getResource()->getIdFieldName(), array('in' => $ids));
                foreach ($operations as $operation) {
                    $operation->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Total of %s record(s) have been deleted', count($operations))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError($this->__('Can not delete all items'));
            }
        }
        $this->_redirect('*/scheduled_operation/index');
    }

    /**
     * Batch change status action
     *
     * @return void
     */
    public function massChangeStatusAction()
    {
        $request = $this->getRequest();
        $ids = $request->getParam('operation');
        $status = (bool)$request->getParam('status');

        if (is_array($ids)) {
            $ids = array_filter($ids, 'intval');

            try {
                $operations = Mage::getResourceModel('enterprise_importexport/scheduled_operation_collection');
                $operations->addFieldToFilter($operations->getResource()->getIdFieldName(), array('in' => $ids));

                foreach ($operations as $operation) {
                    $operation->setStatus($status)
                        ->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Total of %s record(s) have been updated', count($operations))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError($this->__('Can not change status for all items'));
            }
        }
        $this->_redirect('*/scheduled_operation/index');
    }

    /**
     * Get grid-filter of entity attributes action.
     *
     * @return void
     */
    public function getFilterAction()
    {
        $data = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest() && $data) {
            try {
                $this->loadLayout();

                /** @var $export Enterprise_ImportExport_Model_Export */
                $export = Mage::getModel('enterprise_importexport/export')->setData($data);

                /** @var $attrFilterBlock Enterprise_ImportExport_Block_Adminhtml_Export_Filter */
                $attrFilterBlock = $this->getLayout()->getBlock('export.filter')
                    ->setOperation($export);

                $export->filterAttributeCollection(
                    $attrFilterBlock->prepareCollection(
                        $export->getEntityAttributeCollection()
                    )
                );
                $this->renderLayout();
                return;
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->addError($this->__('No valid data sent'));
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Run task through http request.
     *
     * @return void
     */
    public function cronAction()
    {
        try {
            $operationId = (int)$this->getRequest()->getParam('operation');
            $schedule = new Varien_Object();
            $schedule->setJobCode(
                Enterprise_ImportExport_Model_Scheduled_Operation::CRON_JOB_NAME_PREFIX . $operationId
            );
            $result = false;
            $result = Mage::getModel('enterprise_importexport/observer')->processScheduledOperation($schedule, true);
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        if ($result) {
            $this->_getSession()
                ->addSuccess(Mage::helper('enterprise_importexport')->__('Operation has been successfully run'));
        } else {
            $this->_getSession()
                ->addError(Mage::helper('enterprise_importexport')->__('Unable to run operation'));
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Run log cleaning through http request.
     *
     * @return void
     */
    public function logCleanAction()
    {
        $schedule = new Varien_Object();
        $result = Mage::getModel('enterprise_importexport/observer')->scheduledLogClean($schedule, true);
        if ($result) {
            $this->_getSession()
                ->addSuccess(Mage::helper('enterprise_importexport')->__('History files have been deleted'));
        } else {
            $this->_getSession()
                ->addError(Mage::helper('enterprise_importexport')->__('Unable to delete history files'));
        }
        $this->_redirect('*/system_config/edit', array('section' => $this->getRequest()->getParam('section')));
    }
}
