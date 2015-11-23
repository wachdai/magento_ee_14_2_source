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

class Enterprise_Rma_TrackingController extends Mage_Core_Controller_Front_Action
{
    /**
     * Popup action
     * Shows tracking info if it's present, otherwise redirects to 404
     *
     * @return null
     */
    public function popupAction()
    {
        $shippingInfoModel = Mage::getModel('enterprise_rma/shipping_info')
            ->loadByHash($this->getRequest()->getParam('hash'));

        Mage::register('rma_current_shipping', $shippingInfoModel);
        if (count($shippingInfoModel->getTrackingInfo()) == 0) {
            $this->norouteAction();
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Popup package action
     * Shows package info if it's present, otherwise redirects to 404
     *
     * @return null
     */
    public function packageAction()
    {
        $shippingInfoModel = Mage::getModel('enterprise_rma/shipping_info')
            ->loadPackage($this->getRequest()->getParam('hash'));

        Mage::register('rma_package_shipping', $shippingInfoModel);
        if (!$shippingInfoModel->getPackages()) {
            $this->norouteAction();
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Check order view availability
     *
     * @param   Enterprise_Rma_Model_Rma $rma
     * @return  bool
     */
    protected function _canViewRma($rma)
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $currentOrder = Mage::registry('current_order');
            if ($rma->getOrderId() && ($rma->getOrderId() === $currentOrder->getId())) {
                return true;
            }
            return false;
        } else {
            return true;
        }
    }

    /**
     * Try to load valid rma by entity_id and register it
     *
     * @param int $entityId
     * @return bool
     */
    protected function _loadValidRma($entityId = null)
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn() && !Mage::helper('sales/guest')->loadValidOrder()) {
            return;
        }

        if (null === $entityId) {
            $entityId = (int) $this->getRequest()->getParam('entity_id');
        }

        if (!$entityId) {
            $this->_forward('noRoute');
            return false;
        }

        $rma = Mage::getModel('enterprise_rma/rma')->load($entityId);

        if ($this->_canViewRma($rma)) {
            Mage::register('current_rma', $rma);
            return true;
        } else {
            $this->_redirect('*/*/returns');
        }
        return false;
    }

    /**
     * Print label for one specific shipment
     */
    public function printLabelAction()
    {
        try {
            $data = Mage::helper('enterprise_rma')->decodeTrackingHash($this->getRequest()->getParam('hash'));

            $rmaIncrementId = '';
            if ($data['key'] == 'rma_id') {
                $this->_loadValidRma($data['id']);
                if (Mage::registry('current_rma')) {
                    $rmaIncrementId = Mage::registry('current_rma')->getIncrementId();
                }
            }
            $model = Mage::getModel('enterprise_rma/shipping_info')
                ->loadPackage($this->getRequest()->getParam('hash'));

            $shipping = Mage::getModel('enterprise_rma/shipping');
            $labelContent = $model->getShippingLabel();
            if ($labelContent) {
                $pdfContent = null;
                if (stripos($labelContent, '%PDF-') !== false) {
                    $pdfContent = $labelContent;
                } else {
                    $pdf = new Zend_Pdf();
                    $page = $shipping->createPdfPageFromImageString($labelContent);
                    if (!$page) {
                        $this->_getSession()->addError(
                            Mage::helper('sales')->__('File extension not known or unsupported type in the following shipment: %s', $shipping->getIncrementId())
                        );
                    }
                    $pdf->pages[] = $page;
                    $pdfContent = $pdf->render();
                }

                return $this->_prepareDownloadResponse(
                    'ShippingLabel(' . $rmaIncrementId . ').pdf',
                    $pdfContent,
                    'application/pdf'
                );
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()
                ->addError(Mage::helper('sales')->__('An error occurred while creating shipping label.'));
        }
        $this->norouteAction();
        return;
    }

    /**
     * Create pdf document with information about packages
     *
     */
    public function packagePrintAction()
    {
        $data = Mage::helper('enterprise_rma')->decodeTrackingHash($this->getRequest()->getParam('hash'));

        if ($data['key'] == 'rma_id') {
            $this->_loadValidRma($data['id']);
        }
        $model = Mage::getModel('enterprise_rma/shipping_info')
            ->loadPackage($this->getRequest()->getParam('hash'));

        if ($model) {
            $pdf = Mage::getModel('sales/order_pdf_shipment_packaging')
                    ->setPackageShippingBlock(
                        Mage::getBlockSingleton('enterprise_rma/adminhtml_rma_edit_tab_general_shippingmethod')
                    )
                    ->getPdf($model)
            ;

            $this->_prepareDownloadResponse(
                'packingslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                'application/pdf'
            );
        }
    }
}
