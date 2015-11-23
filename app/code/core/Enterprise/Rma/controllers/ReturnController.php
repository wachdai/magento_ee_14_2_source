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

class Enterprise_Rma_ReturnController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     * Customer returns history
     */
    public function historyAction()
    {
        if (!$this->_isEnabledOnFront()) {
            $this->_forward('noRoute');
            return false;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('enterprise_rma')->__('My Returns'));

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }

    /**
     * Customer create new return
     */
    public function createAction()
    {
        $orderId    = (int)$this->getRequest()->getParam('order_id');
        $order      = Mage::getModel('sales/order')->load($orderId);
        if (empty($orderId)) {
            $this->_redirect('sales/order/history');
            return;
        }
        Mage::register('current_order', $order);

        if (!$this->_loadOrderItems($orderId)) {
            return;
        }

        if ($this->_canViewOrder($order)) {
            $postData = $this->getRequest()->getPost();
            if (($postData) && !empty($postData['items'])) {
                try {
                    $rmaModel = Mage::getModel('enterprise_rma/rma');
                    $rmaData = array(
                        'status'                => Enterprise_Rma_Model_Rma_Source_Status::STATE_PENDING,
                        'date_requested'        => Mage::getSingleton('core/date')->gmtDate(),
                        'order_id'              => $order->getId(),
                        'order_increment_id'    => $order->getIncrementId(),
                        'store_id'              => $order->getStoreId(),
                        'customer_id'           => $order->getCustomerId(),
                        'order_date'            => $order->getCreatedAt(),
                        'customer_name'         => $order->getCustomerName(),
                        'customer_custom_email' => $postData['customer_custom_email']
                    );
                    $result = $rmaModel->setData($rmaData)->saveRmaData($postData);
                    if (!$result) {
                        $this->_redirectError(Mage::getUrl('*/*/create', array('order_id'  => $orderId)));
                        return;
                    }
                    $result->sendNewRmaEmail();
                    if (isset($postData['rma_comment']) && !empty($postData['rma_comment'])) {
                        Mage::getModel('enterprise_rma/rma_status_history')
                            ->setRmaEntityId($rmaModel->getId())
                            ->setComment($postData['rma_comment'])
                            ->setIsVisibleOnFront(true)
                            ->setStatus($rmaModel->getStatus())
                            ->setCreatedAt(Mage::getSingleton('core/date')->gmtDate())
                            ->save();
                    }
                    Mage::getSingleton('core/session')->addSuccess(
                        Mage::helper('enterprise_rma')->__('Return #%s has been submitted successfully', $rmaModel->getIncrementId())
                    );
                    $this->_redirectSuccess(Mage::getUrl('*/*/history'));
                    return;
                } catch (Exception $e) {
                    Mage::getSingleton('core/session')->addError(
                        Mage::helper('enterprise_rma')->__('Cannot create New Return, try again later')
                    );
                    Mage::logException($e);
                }
            }
            $this->loadLayout();
            $this->_initLayoutMessages('core/session');
            $this->getLayout()->getBlock('head')->setTitle(Mage::helper('enterprise_rma')->__('Create New Return'));
            if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
                $block->setRefererUrl($this->_getRefererUrl());
            }
            $this->renderLayout();
        } else {
            $this->_redirect('sales/order/history');
        }
    }

    /**
     * Check order view availability
     *
     * @param   Enterprise_Rma_Model_Rma | Mage_Sales_Model_Order $item
     * @return  bool
     */
    protected function _canViewOrder($item)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if ($item->getId() && $item->getCustomerId() && ($item->getCustomerId() == $customerId)) {
            return true;
        }
        return false;
    }

    /**
     * Try to load valid rma by entity_id and register it
     *
     * @param int $entityId
     * @return bool
     */
    protected function _loadValidRma($entityId = null)
    {
        if (null === $entityId) {
            $entityId = (int) $this->getRequest()->getParam('entity_id');
        }
        if (!$entityId || !$this->_isEnabledOnFront()) {
            $this->_forward('noRoute');
            return false;
        }

        $rma = Mage::getModel('enterprise_rma/rma')->load($entityId);

        if ($this->_canViewOrder($rma)) {
            Mage::register('current_rma', $rma);
            return true;
        } else {
            $this->_redirect('*/*/history');
        }
        return false;
    }

    /**
     * Try to load valid collection of ordered items
     *
     * @param int $orderId
     * @return bool
     */
    protected function _loadOrderItems($orderId)
    {
        if (Mage::helper('enterprise_rma')->canCreateRma($orderId)) {
            return true;
        }

        $incrementId    = Mage::registry('current_order')->getIncrementId();
        $message        = Mage::helper('enterprise_rma')->__('Cannot create rma for order #%s.', $incrementId);
        Mage::getSingleton('core/session')->addError($message);
        $this->_redirect('sales/order/history');
        return false;
    }

    /**
     * RMA view page
     */
    public function viewAction()
    {
        if (!$this->_loadValidRma()) {
            $this->_redirect('*/*/history');
            return;
        }

        $order = Mage::getModel('sales/order')->load(
            Mage::registry('current_rma')->getOrderId()
        );
        Mage::register('current_order', $order);

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()
            ->getBlock('head')
            ->setTitle(Mage::helper('enterprise_rma')->__('RMA #%s', Mage::registry('current_rma')->getIncrementId()));

        $this->renderLayout();
    }

    /**
     * View RMA for Order
     */
    public function returnsAction()
    {
        $orderId    = (int) $this->getRequest()->getParam('order_id');
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();

        if (!$orderId || !$this->_isEnabledOnFront()) {
            $this->_forward('noRoute');
            return false;
        }

        $order = Mage::getModel('sales/order')->load($orderId);

        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
            && in_array($order->getState(), $availableStates, $strict = true)
            ) {
            Mage::register('current_order', $order);
        } else {
            $this->_redirect('*/*/history');
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('sales/order/history');
        }
        $this->renderLayout();
    }

    /**
     * Add RMA comment action
     */
    public function addCommentAction()
    {
        if ($this->_loadValidRma()) {
            try {
                $response   = false;
                $comment    = $this->getRequest()->getPost('comment');
                $comment    = trim(strip_tags($comment));

                if (!empty($comment)) {
                    $result = Mage::getModel('enterprise_rma/rma_status_history')
                        ->setRmaEntityId(Mage::registry('current_rma')->getEntityId())
                        ->setComment($comment)
                        ->setIsVisibleOnFront(true)
                        ->setStatus(Mage::registry('current_rma')->getStatus())
                        ->setCreatedAt(Mage::getSingleton('core/date')->gmtDate())
                        ->save();
                    $result->setStoreId(Mage::registry('current_rma')->getStoreId());
                    $result->sendCustomerCommentEmail();
                } else {
                    Mage::throwException(Mage::helper('enterprise_rma')->__('Enter valid message.'));
                }
            } catch (Mage_Core_Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $e->getMessage(),
                );
            } catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => Mage::helper('enterprise_rma')->__('Cannot add message.')
                );
            }
            if (is_array($response)) {
               Mage::getSingleton('core/session')->addError($response['message']);
            }
            $this->_redirect('*/*/view', array('entity_id' => (int)$this->getRequest()->getParam('entity_id')));
            return;
        }
        return;
    }
    /**
     * Add Tracking Number action
     */
    public function addLabelAction()
    {
        if ($this->_loadValidRma()) {
            try {
                $rma = Mage::registry('current_rma');

                if (!$rma->isAvailableForPrintLabel()) {
                    Mage::throwException(Mage::helper('enterprise_rma')->__('Shipping Labels are not allowed.'));
                }

                $response   = false;
                $number    = $this->getRequest()->getPost('number');
                $number    = trim(strip_tags($number));
                $carrier   = $this->getRequest()->getPost('carrier');
                $carriers  = Mage::helper('enterprise_rma')->getShippingCarriers($rma->getStoreId());

                if (!isset($carriers[$carrier])) {
                    Mage::throwException(Mage::helper('enterprise_rma')->__('Select valid carrier.'));
                }

                if (empty($number)) {
                    Mage::throwException(Mage::helper('enterprise_rma')->__('Enter valid Tracking Number.'));
                }

                Mage::getModel('enterprise_rma/shipping')
                    ->setRmaEntityId($rma->getEntityId())
                    ->setTrackNumber($number)
                    ->setCarrierCode($carrier)
                    ->setCarrierTitle($carriers[$carrier])
                    ->save();

            } catch (Mage_Core_Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $e->getMessage(),
                );
            } catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => Mage::helper('enterprise_rma')->__('Cannot add label.')
                );
            }
        } else {
            $response = array(
                'error'     => true,
                'message'   => Mage::helper('enterprise_rma')->__('Wrong RMA Selected.')
            );
        }
        if (is_array($response)) {
            Mage::getSingleton('core/session')->setErrorMessage($response['message']);
        }

        $this->loadLayout();
        $response = $this->getLayout()->getBlock('enterprise_rma_return_tracking')->toHtml();
        $this->getResponse()->setBody($response);

        return;
    }
    /**
     * Delete Tracking Number action
     */
    public function delLabelAction()
    {
        if ($this->_loadValidRma()) {
            try {
                $rma = Mage::registry('current_rma');

                if (!$rma->isAvailableForPrintLabel()) {
                    Mage::throwException(Mage::helper('enterprise_rma')->__('Shipping Labels are not allowed.'));
                }

                $response   = false;
                $number    = intval($this->getRequest()->getPost('number'));

                if (empty($number)) {
                    Mage::throwException(Mage::helper('enterprise_rma')->__('Enter valid Tracking Number.'));
                }

                $trackingNumber = Mage::getModel('enterprise_rma/shipping')
                    ->load($number);
                if ($trackingNumber->getRmaEntityId() !== $rma->getId()) {
                    Mage::throwException(Mage::helper('enterprise_rma')->__('Wrong RMA Selected.'));
                }
                $trackingNumber->delete();

            } catch (Mage_Core_Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $e->getMessage(),
                );
            } catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => Mage::helper('enterprise_rma')->__('Cannot delete label.')
                );
            }
        } else {
            $response = array(
                'error'     => true,
                'message'   => Mage::helper('enterprise_rma')->__('Wrong RMA Selected.')
            );
        }
        if (is_array($response)) {
            Mage::getSingleton('core/session')->setErrorMessage($response['message']);
        }

        $this->loadLayout();
        $response = $this->getLayout()->getBlock('enterprise_rma_return_tracking')->toHtml();
        $this->getResponse()->setBody($response);

        return;
    }

    /**
     * Checks whether RMA module is enabled in system config
     *
     * @return boolean
     */
    protected function _isEnabledOnFront()
    {
        return Mage::helper('enterprise_rma')->isEnabled();
    }
}
