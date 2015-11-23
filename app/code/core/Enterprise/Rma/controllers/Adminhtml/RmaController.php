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
 * @package     Enterprise_Rma
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_Rma_Adminhtml_RmaController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init active menu and set breadcrumb
     *
     * @return Enterprise_Rma_Adminhtml_RmaController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/rma');

        $this->_title($this->__('Sales'))->_title($this->__('Manage RMA'));
        return $this;
    }

    /**
     * Initialize model
     *
     * @param string $requestParam
     * @return Enterprise_Rma_Model_Rma
     */
    protected function _initModel($requestParam = 'id')
    {
        $model = Mage::getModel('enterprise_rma/rma');
        $model->setStoreId($this->getRequest()->getParam('store', 0));

        $rmaId = $this->getRequest()->getParam($requestParam);
        if ($rmaId) {
            $model->load($rmaId);
            if (!$model->getId()) {
                Mage::throwException($this->__('Wrong RMA requested.'));
            }
            Mage::register('current_rma', $model);
            $orderId = $model->getOrderId();
        } else {
            $orderId = $this->getRequest()->getParam('order_id');
        }

        if ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if (!$order->getId()) {
                Mage::throwException($this->__('Wrong RMA order id.'));
            }
            Mage::register('current_order', $order);
        }

        return $model;
    }

    /**
     * Initialize model
     *
     * @return Enterprise_Rma_Model_Rma_Create
     */
    protected function _initCreateModel()
    {
        $model = Mage::getModel('enterprise_rma/rma_create');
        $orderId = $this->getRequest()->getParam('order_id');
        $model->setOrderId($orderId);
        if ($orderId) {
            $order =  Mage::getModel('sales/order')->load($orderId);
            $model->setCustomerId($order->getCustomerId());
            $model->setStoreId($order->getStoreId());
        }

        Mage::register('rma_create_model', $model);

        return $model;
    }

    /**
     * Default action
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    /**
     * Create new RMA
     *
     * @throws Mage_Core_Exception
     */
    public function newAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if (!$orderId) {
            $customerId = $this->getRequest()->getParam('customer_id');
            $this->_redirect('*/*/chooseorder', array('customer_id' => $customerId));
        } else {
            try {
                $this->_initCreateModel();
                $this->_initModel();

                if (!Mage::helper('enterprise_rma')->canCreateRmaByAdmin($orderId)) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('enterprise_rma')->__('There are no applicable items for return in this order')
                    );
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/');
                return;
            }

            $this->_initAction();
            $this->_title($this->__('Create New RMA'));
            $this->renderLayout();
        }
    }

    /**
     * Choose Order action during new RMA creation
     */
    public function chooseorderAction()
    {
        $this->_initCreateModel();

        $this->_initAction()
            ->_title($this->__('Create New RMA'))
            ->renderLayout();
    }

    /**
     * Edit RMA
     *
     * @throws Mage_Core_Exception
     */
    public function editAction()
    {
        try {
            $model = $this->_initModel();
            if (!$model->getId()) {
                Mage::throwException($this->__('Wrong RMA requested.'));
            }
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }
        $this->_initAction();
        $this->_title(sprintf("#%s", $model->getIncrementId()));
        $this->renderLayout();
    }

    /**
     * Save New RMA
     *
     * @throws Mage_Core_Exception
     */
    public function saveNewAction()
    {
        $data = $this->getRequest()->getPost();

        if ($data) {
            if ($this->getRequest()->getParam('back', false)) {
                $this->_redirect('*/*/');
                return;
            }
            try {
                /** @var $model Enterprise_Rma_Model_Rma */
                $model = $this->_initModel();
                $order = Mage::registry('current_order');
                $rmaData = array(
                    'status'                => Enterprise_Rma_Model_Rma_Source_Status::STATE_PENDING,
                    'date_requested'        => Mage::getSingleton('core/date')->gmtDate(),
                    'order_id'              => $order->getId(),
                    'order_increment_id'    => $order->getIncrementId(),
                    'store_id'              => $order->getStoreId(),
                    'customer_id'           => $order->getCustomerId(),
                    'order_date'            => $order->getCreatedAt(),
                    'customer_name'         => $order->getCustomerName(),
                    'customer_custom_email' => $data['contact_email']
                );
                $model->setData($rmaData);
                $result = $model->saveRmaData($data);

                if ($result && $result->getId()) {
                    if (isset($data['comment'])
                        && isset($data['comment']['comment'])
                        && !empty($data['comment']['comment'])
                    ) {
                        $visible = isset($data['comment']['is_visible_on_front']) ? true : false;

                        Mage::getModel('enterprise_rma/rma_status_history')
                            ->setRmaEntityId($result->getId())
                            ->setComment($data['comment']['comment'])
                            ->setIsVisibleOnFront($visible)
                            ->setStatus($result->getStatus())
                            ->setCreatedAt(Mage::getSingleton('core/date')->gmtDate())
                            ->setIsAdmin(1)
                            ->save();
                    }
                    if (isset($data['rma_confirmation']) && !empty($data['rma_confirmation'])) {
                        $model->sendNewRmaEmail();
                    }
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        $this->__('The RMA request has been submitted.')
                    );
                } else {
                    Mage::throwException($this->__('Failed to save RMA.'));
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $errorKeys = Mage::getSingleton('core/session')->getRmaErrorKeys();
                $controllerParams = array('order_id' => Mage::registry('current_order')->getId());
                if (!empty($errorKeys) && isset($errorKeys['tabs']) && ($errorKeys['tabs'] == 'items_section')) {
                    $controllerParams['active_tab'] = 'items_section';
                }
                $this->_redirect('*/*/new', $controllerParams);

                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Failed to save RMA.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Save RMA
     *
     * @throws Mage_Core_Exception
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $rmaId = $this->getRequest()->getParam('rma_id');
            if (!$rmaId) {
                $this->saveNewAction();
                return;
            }
            try {
                $model = $this->_initModel('rma_id');
                $statuses = array();
                foreach ($data['items'] as $key => &$value) {
                    if (strpos($key, '_') === false) {
                        $value['entity_id'] = $key;
                    } else {
                        $value['entity_id'] = false;
                    }
                    if (isset($value['status'])) {
                        $statuses[] = $value['status'];
                    }
                    if (!(isset($value['qty_authorized'])
                        || isset($value['qty_returned'])
                        || isset($value['qty_approved']))) {
                        unset($data['items'][$key]);
                    }
                }
                /* Merge RMA Items status with POST data*/
                $rmaItems = Mage::getModel('enterprise_rma/item')
                    ->getCollection()
                    ->addAttributeToFilter('rma_entity_id', $rmaId);
                foreach ($rmaItems as $rmaItem) {
                    if (!isset($data['items'][$rmaItem->getId()])) {
                        $statuses[] = $rmaItem->getStatus();
                    }
                }

                $this->getRequest()->setPost($data);
                $model->setStatus(
                    Mage::getModel('enterprise_rma/rma_source_status')
                        ->getStatusByItems($statuses)
                );
                $model->setIsUpdate(1);
                $result = $model->saveRmaData($data);
                if ($result && $result->getId()) {
                    $model->sendAuthorizeEmail();
                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The RMA request has been saved.'));
                } else {
                    Mage::throwException($this->__('Failed to save RMA.'));
                }

                if ($redirectBack = $this->getRequest()->getParam('back', false)) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), 'store' => $model->getStoreId()));
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $errorKeys = Mage::getSingleton('core/session')->getRmaErrorKeys();
                $controllerParams = array('id' => $model->getId());
                if (!empty($errorKeys) && isset($errorKeys['tabs']) && ($errorKeys['tabs'] == 'items_section')) {
                    $controllerParams['active_tab'] = 'items_section';
                }
                $this->_redirect('*/*/edit', $controllerParams);
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Failed to save RMA.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete rma
     */
    public function deleteAction()
    {
        $this->_redirect('*/*/');
    }

    /**
     * Close action for rma
     */
    public function closeAction(){
        $entityId = $this->getRequest()->getParam('entity_id');
        if ($entityId) {
            $entityId = intval($entityId);
            $entityIds = array($entityId);
            $returnRma = $entityId;
        } else {
            $entityIds = $this->getRequest()->getPost('entity_ids', array());
            $returnRma = null;
        }
        $countCloseRma = 0;
        $countNonCloseRma = 0;
        foreach ($entityIds as $entityId) {
            $rma = Mage::getModel('enterprise_rma/rma')->load($entityId);
            if ($rma->canClose()) {
                $rma->close()
                    ->save();
                $countCloseRma++;
            } else {
                $countNonCloseRma++;
            }
        }
        if ($countNonCloseRma) {
            if ($countCloseRma) {
                $this->_getSession()->addError($this->__('%s RMA(s) cannot be closed', $countNonCloseRma));
            } else {
                $this->_getSession()->addError($this->__('The RMA request(s) cannot be closed'));
            }
        }
        if ($countCloseRma) {
            $this->_getSession()->addSuccess($this->__('%s RMA (s) have been closed.', $countCloseRma));
        }

        if ($returnRma) {
            $this->_redirect('*/*/edit', array('id' => $returnRma));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Add RMA comment action
     *
     * @throws Mage_Core_Exception
     * @return void
     */
    public function addCommentAction()
    {
        try {
            $this->_initModel();

            $data = $this->getRequest()->getPost('comment');
            $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
            $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;

            $rma = Mage::registry('current_rma');
            if (!$rma) {
                Mage::throwException(Mage::helper('enterprise_rma')->__('Invalid RMA.'));
            }

            $comment = trim($data['comment']);
            if (!$comment) {
                Mage::throwException(Mage::helper('enterprise_rma')->__('Enter valid message.'));
            }

            /** @var $history Enterprise_Rma_Model_Rma_Status_History */
            $history = Mage::getModel('enterprise_rma/rma_status_history');
            $history->setRmaEntityId((int)$rma->getId())
                ->setComment($comment)
                ->setIsVisibleOnFront($visible)
                ->setIsCustomerNotified($notify)
                ->setStatus($rma->getStatus())
                ->setCreatedAt(Mage::getSingleton('core/date')->gmtDate())
                ->setIsAdmin(1)
                ->save();

            if ($notify && $history) {
                $history->setRma($rma);
                $history->setStoreId($rma->getStoreId());
                $history->sendCommentEmail();
            }

            $this->loadLayout();
            $response = $this->getLayout()->getBlock('comments_history')->toHtml();
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage(),
            );
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot add RMA history.'),
            );
        }
        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * Generate RMA grid for ajax request from customer page
     */
    public function rmaCustomerAction()
    {
        $customerId = intval($this->getRequest()->getParam('id'));
        if ($customerId) {
            $this->getResponse()->setBody(
                $this
                    ->getLayout()
                    ->createBlock('enterprise_rma/adminhtml_customer_edit_tab_rma')
                    ->setCustomerId($customerId)
                    ->toHtml()
            );
        }
    }

    /**
     * Generate RMA grid for ajax request from order page
     */
    public function rmaOrderAction()
    {
        $orderId = intval($this->getRequest()->getParam('order_id'));
        $this->getResponse()->setBody(
            $this
                ->getLayout()
                ->createBlock('enterprise_rma/adminhtml_order_view_tab_rma')
                ->setOrderId($orderId)
                ->toHtml()
        );
    }

    /**
     * Generate RMA items grid for ajax request from selecting product grid during RMA creation
     *
     * @throws Mage_Core_Exception
     */
    public function addProductGridAction()
    {
        try {
            $this->_initModel();
            $order = Mage::registry('current_order');
            if (!$order) {
                Mage::throwException(Mage::helper('enterprise_rma')->__('Invalid order.'));
            }
            $this->loadLayout();
            $response = $this->getLayout()->getBlock('add_product_grid')->toHtml();
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage(),
            );
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot get product list.')
            );
        }
        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
            $this->getResponse()->setBody($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Generate PDF form of RMA
     */
    public function printAction()
    {
        $rmaId = (int)$this->getRequest()->getParam('rma_id');
        if ($rmaId) {
            if ($rma = Mage::getModel('enterprise_rma/rma')->load($rmaId)) {
                $pdf = Mage::getModel('enterprise_rma/pdf_rma')->getPdf(array($rma));
                $this->_prepareDownloadResponse(
                    'rma'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf',
                    $pdf->render(),
                    'application/pdf'
                );
            }
        } else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Load user-defined attributes of RMA's item
     *
     * @throws Mage_Core_Exception
     */
    public function loadAttributesAction()
    {
        $response = false;
        $itemId = $this->getRequest()->getParam('item_id');

        try {
            $model = $this->_initModel();
            if (!$model->getId()) {
                Mage::throwException($this->__('Wrong RMA requested.'));
            }
            $rma_item = Mage::getModel('enterprise_rma/item');

            if ($itemId) {
                $rma_item->load($itemId);
                if (!$rma_item->getId()) {
                    Mage::throwException($this->__('Wrong RMA item requested.'));
                }
                Mage::register('current_rma_item', $rma_item);
            } else {
                Mage::throwException($this->__('Wrong RMA item requested.'));
            }
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage(),
            );
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot display item attributes.')
            );
        }

        $this->loadLayout();
        $block = $this
                ->getLayout()
                ->getBlock('enterprise_rma_edit_item')
                ->initForm();
        $block->getForm()->setHtmlIdPrefix('_rma' . $itemId);
        $response = $block->toHtml();

        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * Load user-defined attributes for new RMA's item
     */
    public function loadNewAttributesAction()
    {
        $response = false;
        $orderId = $this->getRequest()->getParam('order_id');
        $productId = $this->getRequest()->getParam('product_id');

        $rma_item = Mage::getModel('enterprise_rma/item');
        Mage::register('current_rma_item', $rma_item);

        $this->loadLayout();
        $response = $this
            ->getLayout()
            ->getBlock('enterprise_rma_edit_item')
            ->setProductId(intval($productId))
            ->initForm()
            ->toHtml();

        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
            $this->getResponse()->setBody($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }


    /**
     * Load new row of RMA's item for Split Line functionality
     *
     * @throws Mage_Core_Exception
     */
    public function loadSplitLineAction()
    {
        $response = false;
        $rmaId = $this->getRequest()->getParam('rma_id');
        $itemId = $this->getRequest()->getParam('item_id');

        try {
            $model = $this->_initModel();
            if (!$model->getId()) {
                Mage::throwException($this->__('Wrong RMA requested.'));
            }
            $rma_item = Mage::getModel('enterprise_rma/item');

            if ($itemId) {
                $rma_item->load($itemId);
                if (!$rma_item->getId()) {
                    Mage::throwException($this->__('Wrong RMA item requested.'));
                }
                Mage::register('current_rma_item', $rma_item);
            } else {
                Mage::throwException($this->__('Wrong RMA item requested.'));
            }
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage(),
            );
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot display item attributes.')
            );
        }

        $this->loadLayout();

        $response = $this
            ->getLayout()
            ->getBlock('enterprise_rma_edit_items_grid')
            ->setItemFilter($itemId)
            ->setAllFieldsEditable()
            ->toHtml();

        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }


    /**
     * Check the permission
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/enterprise_rma');
    }

    /**
     * Shows bundle items on rma create
     *
     * @throws Mage_Core_Exception
     */
    public function showBundleItemsAction()
    {
        $response   = false;
        $orderId    = $this->getRequest()->getParam('order_id');
        $itemId     = $this->getRequest()->getParam('item_id');

        try {
            if ($orderId && $itemId) {
                /** @var $items Enterprise_Rma_Model_Resource_Item */
                $items = Mage::getResourceModel('enterprise_rma/item')->getOrderItems($orderId, $itemId);
                if (empty($items)) {
                    Mage::throwException($this->__('No items for bundle product.'));
                }
            } else {
                Mage::throwException($this->__('Wrong order id or item id requested.'));
            }

            Mage::register('current_rma_bundle_item', $items);
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage(),
            );
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot display item attributes.')
            );
        }

        $this->loadLayout();
        $response = $this->getLayout()
            ->getBlock('enterprise_rma_bundle')
            ->toHtml()
        ;

        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
            $this->getResponse()->setBody($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Action for view full sized item atttribute image
     */
    public function viewfileAction()
    {
        $file   = null;
        $plain  = false;
        if ($this->getRequest()->getParam('file')) {
            // download file
            $file   = Mage::helper('core')->urlDecode($this->getRequest()->getParam('file'));
        } else if ($this->getRequest()->getParam('image')) {
            // show plain image
            $file   = Mage::helper('core')->urlDecode($this->getRequest()->getParam('image'));
            $plain  = true;
        } else {
            return $this->norouteAction();
        }

        $path = Mage::getBaseDir('media') . DS . 'rma_item';

        $ioFile = new Varien_Io_File();
        $ioFile->open(array('path' => $path));
        $fileName   = $ioFile->getCleanPath($path . $file);
        $path       = $ioFile->getCleanPath($path);

        if (!$ioFile->fileExists($fileName) || strpos($fileName, $path) !== 0) {
            return $this->norouteAction();
        }

        if ($plain) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            switch (strtolower($extension)) {
                case 'gif':
                    $contentType = 'image/gif';
                    break;
                case 'jpg':
                    $contentType = 'image/jpeg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    break;
            }

            $ioFile->streamOpen($fileName, 'r');
            $contentLength = $ioFile->streamStat('size');
            $contentModify = $ioFile->streamStat('mtime');

            $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', $contentLength)
                ->setHeader('Last-Modified', date('r', $contentModify))
                ->clearBody();
            $this->getResponse()->sendHeaders();

            while (false !== ($buffer = $ioFile->streamRead())) {
                echo $buffer;
            }
        } else {
            $name = pathinfo($fileName, PATHINFO_BASENAME);
            $this->_prepareDownloadResponse($name, array(
                'type'  => 'filename',
                'value' => $fileName
            ));
        }

        exit();
    }

    /**
     * Shows available shipping methods
     *
     * @throws Mage_Core_Exception
     */
    public function showShippingMethodsAction()
    {
        $response   = false;

        try {
            $model = $this->_initModel();
            if (!$model->getId()) {
                Mage::throwException($this->__('Wrong rma id.'));
            }

        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage(),
            );
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot display available shipping methods.')
            );
        }

        $this->loadLayout();
        $response = $this->getLayout()
            ->getBlock('enterprise_rma_shipping_available')
            ->toHtml()
        ;

        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
            $this->getResponse()->setBody($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Shows available shipping methods
     *
     * @return Zend_Controller_Response_Abstract
     * @throws Mage_Core_Exception
     */
    public function pslAction()
    {
        $data       = $this->getRequest()->getParam('data');
        $response   = false;

        try {
            $model = $this->_initModel();
            if (!$model->getId()) {
                Mage::throwException($this->__('Wrong rma id.'));
            }

        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage(),
            );
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot display available shipping methods.')
            );
        }

        if ($data) {
            return $this->getResponse()
                ->setBody($this->_getConfigDataJson($model)
            );
        }

        $this->loadLayout();
        $response = $this->getLayout()
            ->getBlock('enterprise_rma_shipment_packaging')
            ->toHtml()
        ;

        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * Configuration for popup window for packaging
     *
     * @param Enterprise_Rma_Model_Rma $model
     * @return string
     */
    protected function _getConfigDataJson($model)
    {
        $urlParams      = array();
        $itemsQty       = array();
        $itemsPrice     = array();
        $itemsName      = array();
        $itemsWeight    = array();
        $itemsProductId = array();

        $urlParams['id']    = $model->getId();
        $items              = $model->getShippingMethods(true);

        $createLabelUrl = $this->getUrl('*/*/saveShipping', $urlParams);
        $itemsGridUrl   = $this->getUrl('*/*/getShippingItemsGrid', $urlParams);
        $thisPage       = $this->getUrl('*/*/edit', $urlParams);

        $code    = $this->getRequest()->getParam('method');
        $carrier = Mage::helper('enterprise_rma')->getCarrier($code, $model->getStoreId());
        if ($carrier) {
            $getCustomizableContainers =  $carrier->getCustomizableContainerTypes();
        }

        foreach ($items as $item) {
            $itemsQty[$item->getItemId()]           = $item->getQty();
            $itemsPrice[$item->getItemId()]         = $item->getPrice();
            $itemsName[$item->getItemId()]          = $item->getName();
            $itemsWeight[$item->getItemId()]        = $item->getWeight();
            $itemsProductId[$item->getItemId()]     = $item->getProductId();
            $itemsOrderItemId[$item->getItemId()]   = $item->getItemId();
        }

        $shippingInformation = $this->getLayout()
            ->createBlock('enterprise_rma/adminhtml_rma_edit_tab_general_shipping_information')
            ->setIndex($this->getRequest()->getParam('index'))
            ->toHtml();

        $data = array(
            'createLabelUrl'            => $createLabelUrl,
            'itemsGridUrl'              => $itemsGridUrl,
            'errorQtyOverLimit'         => Mage::helper('enterprise_rma')->__('The quantity you want to add exceeds the total shipped quantity for some of selected Product(s)'),
            'titleDisabledSaveBtn'      => Mage::helper('enterprise_rma')->__('Products should be added to package(s)'),
            'validationErrorMsg'        => Mage::helper('enterprise_rma')->__('The value that you entered is not valid.'),
            'shipmentItemsQty'          => $itemsQty,
            'shipmentItemsPrice'        => $itemsPrice,
            'shipmentItemsName'         => $itemsName,
            'shipmentItemsWeight'       => $itemsWeight,
            'shipmentItemsProductId'    => $itemsProductId,
            'shipmentItemsOrderItemId'  => $itemsOrderItemId,

            'shippingInformation'       => $shippingInformation,
            'thisPage'                  => $thisPage,
            'customizable'              => $getCustomizableContainers
        );

        return Mage::helper('core')->jsonEncode($data);
    }

    /**
     * Return grid with shipping items for Ajax request
     */
    public function getShippingItemsGridAction()
    {
        $this->_initModel();
        $response = $this-> _initAction()
                ->getLayout()
                ->getBlock('enterprise_rma_getshippingitemsgrid')
                ->toHtml()
        ;

        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     *
     * @throws Mage_Core_Exception
     */
    public function saveShippingAction()
    {
        $responseAjax = new Varien_Object();

        try {
            $model = $this->_initModel();
            if ($model) {
                if ($this->_createShippingLabel($model)) {
                    $this->_getSession()
                        ->addSuccess($this->__('The shipping label has been created.'));
                    $responseAjax->setOk(true);
                }
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
            } else {
                $this->_forward('noRoute');
                return;
            }
        } catch (Mage_Core_Exception $e) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
        } catch (Exception $e) {
                Mage::logException($e);
                $responseAjax->setError(true);
                $responseAjax->setMessage(
                    Mage::helper('enterprise_rma')->__('An error occurred while creating shipping label.')
                );
        }
        $this->getResponse()->setBody($responseAjax->toJson());
    }

    /**
     * Create shipping label action for specific shipment
     *
     * @throws Mage_Core_Exception
     */
    public function createLabelAction()
    {
        $response = new Varien_Object();
        try {
            $shipment = $this->_initShipment();
            if ($this->_createShippingLabel($shipment)) {
                $shipment->save();
                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_rma')->__('The shipping label has been created.')
                );
                $response->setOk(true);
            }
        } catch (Mage_Core_Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $response->setError(true);
            $response->setMessage(
                Mage::helper('enterprise_rma')->__('An error occurred while creating shipping label.')
            );
        }

        $this->getResponse()->setBody($response->toJson());
        return;
    }

    /**
     * Create shipping label for specific shipment with validation.
     *
     * @param Enterprise_Rma_Model_Rma $model
     * @return bool
     */
    protected function _createShippingLabel(Enterprise_Rma_Model_Rma $model)
    {
        $data = $this->getRequest()->getPost();
        if ($model && isset($data['packages']) && !empty($data['packages'])) {
            /** @var $shipment Enterprise_Rma_Model_Shipping */
            $shipment =  Mage::getModel('enterprise_rma/shipping')
                ->getShippingLabelByRma($model);

            $carrier = Mage::helper('enterprise_rma')->getCarrier($data['code'], $model->getStoreId());
            if (!$carrier->isShippingLabelsAvailable()) {
                return false;
            }
            $shipment->setPackages($data['packages']);
            $shipment->setCode($data['code']);

            list($carrierCode, $methodCode) = explode('_', $data['code'], 2);
            $shipment->setCarrierCode($carrierCode);
            $shipment->setMethodCode($data['code']);

            $shipment->setCarrierTitle($data['carrier_title']);
            $shipment->setMethodTitle($data['method_title']);
            $shipment->setPrice($data['price']);
            $shipment->setRma($model);
            $shipment->setIncrementId($model->getIncrementId());
            $weight = 0;
            foreach ($data['packages'] as $package) {
                $weight += $package['params']['weight'];
            }
            $shipment->setWeight($weight);

            $response = $shipment->requestToShipment();

            if (!$response->hasErrors() && $response->hasInfo()) {
                $labelsContent      = array();
                $trackingNumbers    = array();
                $info = $response->getInfo();

                foreach ($info as $inf) {
                    if (!empty($inf['tracking_number']) && !empty($inf['label_content'])) {
                        $labelsContent[]    = $inf['label_content'];
                        $trackingNumbers[]  = $inf['tracking_number'];
                    }
                }
                $outputPdf = $this->_combineLabelsPdf($labelsContent);
                $shipment->setPackages(serialize($data['packages']));
                $shipment->setShippingLabel($outputPdf->render());
                $shipment->setIsAdmin(Enterprise_Rma_Model_Shipping::IS_ADMIN_STATUS_ADMIN_LABEL);
                $shipment->setRmaEntityId($model->getId());
                $shipment->save();

                $carrierCode = $carrier->getCarrierCode();
                $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title', $shipment->getStoreId());
                if ($trackingNumbers) {
                    Mage::getResourceModel('enterprise_rma/shipping')->deleteTrackingNumbers($model);
                    foreach ($trackingNumbers as $trackingNumber) {
                        Mage::getModel('enterprise_rma/shipping')
                            ->setTrackNumber($trackingNumber)
                            ->setCarrierCode($carrierCode)
                            ->setCarrierTitle($carrierTitle)
                            ->setRmaEntityId($model->getId())
                            ->setIsAdmin(Enterprise_Rma_Model_Shipping::IS_ADMIN_STATUS_ADMIN_LABEL_TRACKING_NUMBER)
                            ->save();
                    }
                }
                return true;
            } else {
                Mage::throwException($response->getErrors());
            }
        }
        return false;
    }

    /**
     * Print label for one specific shipment
     *
     * @return Mage_Adminhtml_Controller_Action
     * @throws Mage_Core_Exception
     */
    public function printLabelAction()
    {
        try {
            $model = $this->_initModel();
            $labelContent = Mage::getModel('enterprise_rma/shipping')
                ->getShippingLabelByRma($model)
                ->getShippingLabel();
            if ($labelContent) {
                $pdfContent = null;
                if (stripos($labelContent, '%PDF-') !== false) {
                    $pdfContent = $labelContent;
                } else {
                    $pdf = new Zend_Pdf();
                    $page = $this->_createPdfPageFromImageString($labelContent);
                    if (!$page) {
                        $this->_getSession()->addError(
                            Mage::helper('enterprise_rma')->__('File extension not known or unsupported type in the following shipment: %s', $model->getIncrementId())
                        );
                    }
                    $pdf->pages[] = $page;
                    $pdfContent = $pdf->render();
                }

                return $this->_prepareDownloadResponse(
                    'ShippingLabel(' . $model->getIncrementId() . ').pdf',
                    $pdfContent,
                    'application/pdf'
                );
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()
                ->addError(Mage::helper('enterprise_rma')->__('An error occurred while creating shipping label.'));
       }
        $this->_redirect('*/*/edit', array(
            'id' => $this->getRequest()->getParam('id')
        ));
    }

    /**
     * Create pdf document with information about packages
     */
    public function printPackageAction()
    {
        $model = $this->_initModel();
        $shipment = Mage::getModel('enterprise_rma/shipping')
            ->getShippingLabelByRma($model);

        if ($shipment) {
            $pdf = Mage::getModel('sales/order_pdf_shipment_packaging')
                    ->setPackageShippingBlock(
                        Mage::getBlockSingleton('enterprise_rma/adminhtml_rma_edit_tab_general_shippingmethod')
                    )
                    ->getPdf($shipment);
            $this->_prepareDownloadResponse(
                'packingslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                'application/pdf'
            );
        }
        else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Create Zend_Pdf_Page instance with image from $imageString. Supports JPEG, PNG, GIF, WBMP, and GD2 formats.
     *
     * @param string $imageString
     * @return Zend_Pdf_Page|bool
     */
    protected function _createPdfPageFromImageString($imageString)
    {
        $image = imagecreatefromstring($imageString);
        if (!$image) {
            return false;
        }

        $xSize = imagesx($image);
        $ySize = imagesy($image);
        $page = new Zend_Pdf_Page($xSize, $ySize);

        imageinterlace($image, 0);
        $tmpFileName = sys_get_temp_dir() . DS . 'shipping_labels_'
                     . uniqid(mt_rand()) . time() . '.png';
        imagepng($image, $tmpFileName);
        $pdfImage = Zend_Pdf_Image::imageWithPath($tmpFileName);
        $page->drawImage($pdfImage, 0, 0, $xSize, $ySize);
        unlink($tmpFileName);
        return $page;
    }

    /**
     * Combine Labels Pdf
     *
     * @param array $labelsContent
     * @return Zend_Pdf
     */
    protected function _combineLabelsPdf(array $labelsContent)
    {
        $outputPdf = new Zend_Pdf();
        foreach ($labelsContent as $content) {
            if (stripos($content, '%PDF-') !== false) {
                $pdfLabel = Zend_Pdf::parse($content);
                foreach ($pdfLabel->pages as $page) {
                    $outputPdf->pages[] = clone $page;
                }
            } else {
                $page = $this->_createPdfPageFromImageString($content);
                if ($page) {
                    $outputPdf->pages[] = $page;
                }
            }
        }
        return $outputPdf;
    }

    /**
     * Add new tracking number action
     *
     * @throws Mage_Core_Exception
     */
    public function addTrackAction()
    {
        try {
            $carrier = $this->getRequest()->getPost('carrier');
            $number  = $this->getRequest()->getPost('number');
            $title  = $this->getRequest()->getPost('title');
            if (empty($carrier)) {
                Mage::throwException($this->__('The carrier needs to be specified.'));
            }
            if (empty($number)) {
                Mage::throwException($this->__('Tracking number cannot be empty.'));
            }

            $model = $this->_initModel();
            if ($model->getId()) {
                Mage::getModel('enterprise_rma/shipping')
                    ->setTrackNumber($number)
                    ->setCarrierCode($carrier)
                    ->setCarrierTitle($title)
                    ->setRmaEntityId($model->getId())
                    ->setIsAdmin(Enterprise_Rma_Model_Shipping::IS_ADMIN_STATUS_ADMIN_TRACKING_NUMBER)
                    ->save()
                ;

                $this->loadLayout();
                $response = $this->getLayout()->getBlock('shipment_tracking')->toHtml();
            } else {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Cannot initialize rma for adding tracking number.'),
                );
            }
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage(),
            );
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot add tracking number.'),
            );
        }
        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * Remove tracking number from shipment
     */
    public function removeTrackAction()
    {
        $trackId    = $this->getRequest()->getParam('track_id');
        $track      = Mage::getModel('enterprise_rma/shipping')->load($trackId);
        if ($track->getId()) {
            try {
                $model = $this->_initModel();
                if ($model->getId()) {
                    $track->delete();

                    $this->loadLayout();
                    $response = $this->getLayout()->getBlock('shipment_tracking')->toHtml();
                } else {
                    $response = array(
                        'error'     => true,
                        'message'   => $this->__('Cannot initialize rma for delete tracking number.'),
                    );
                }
            } catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Cannot delete tracking number.'),
                );
            }
        } else {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot load track with retrieving identifier.'),
            );
        }
        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }
}
