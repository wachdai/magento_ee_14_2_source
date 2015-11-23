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
 * @package     Enterprise_GiftWrapping
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Gift Wrapping Controller
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftWrapping_Adminhtml_GiftwrappingController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Init active menu
     *
     * @return Enterprise_GiftWrapping_Adminhtml_GiftwrappingController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/giftwrapping');

        $this->_title(Mage::helper('enterprise_giftwrapping')->__('Sales'))
            ->_title(Mage::helper('enterprise_giftwrapping')->__('Manage Gift Wrapping'));
        return $this;
    }

    /**
     * Init model
     *
     * @return Enterprise_Giftwrapping_Model_Wrapping
     */
    protected function _initModel($requestParam = 'id')
    {
        $model = Mage::registry('current_giftwrapping_model');
        if ($model) {
           return $model;
        }
        $model = Mage::getModel('enterprise_giftwrapping/wrapping');
        $model->setStoreId($this->getRequest()->getParam('store', 0));

        $wrappingId = $this->getRequest()->getParam($requestParam);
        if ($wrappingId) {
            $model->load($wrappingId);
            if (!$model->getId()) {
                Mage::throwException(Mage::helper('enterprise_giftwrapping')->__('Wrong gift wrapping requested.'));
            }
        }
        Mage::register('current_giftwrapping_model', $model);

        return $model;
    }

    /**
     * List of gift wrappings
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    /**
     * Create new gift wrapping
     *
     * @return void
     */
    public function newAction()
    {
        $model = $this->_initModel();
        $this->_initAction();
        $this->_title(Mage::helper('enterprise_giftwrapping')->__('New Gift Wrapping'));
        $this->renderLayout();
    }

    /**
     * Edit gift wrapping
     *
     * @return void
     */
    public function editAction()
    {
        $model = $this->_initModel();
        $this->_initAction();
        if ($formData = Mage::getSingleton('adminhtml/session')->getFormData()) {
            $model->addData($formData);
        }
        $this->_title(Mage::helper('enterprise_giftwrapping')->__('Edit Gift Wrapping "%s"', $model->getDesign()));
        $this->renderLayout();
    }

    /**
     * Save gift wrapping
     *
     * @return void
     */
    public function saveAction()
    {
        $wrappingRawData = $this->_prepareGiftWrappingRawData($this->getRequest()->getPost('wrapping'));
        if ($wrappingRawData) {
            try {
                $model = $this->_initModel();
                $model->addData($wrappingRawData);

                $data = new Varien_Object($wrappingRawData);
                if ($data->getData('image_name/delete')) {
                    $model->setImage('');
                    // Delete temporary image if exists
                    $model->unsTmpImage();
                } else {
                    try {
                        $model->attachUploadedImage('image_name');
                    } catch (Exception $e) {
                        Mage::throwException(
                            Mage::helper('enterprise_giftwrapping')->__('Image has not been uploaded.')
                        );
                    }
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('enterprise_giftwrapping')->__('The gift wrapping has been saved.')
                );

                $redirectBack = $this->getRequest()->getParam('back', false);
                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), 'store' => $model->getStoreId()));
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('enterprise_giftwrapping')->__('Failed to save gift wrapping.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Upload temporary gift wrapping image
     *
     * @return void
     */
    public function uploadAction()
    {
        $wrappingRawData = $this->_prepareGiftWrappingRawData($this->getRequest()->getPost('wrapping'));
        if ($wrappingRawData) {
            try {
                $model = $this->_initModel();
                $model->addData($wrappingRawData);
                try {
                    $model->attachUploadedImage('image_name', true);
                } catch (Exception $e) {
                    Mage::throwException(Mage::helper('enterprise_giftwrapping')->__('Image was not uploaded.'));
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_getSession()->setFormData($wrappingRawData);
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('enterprise_giftwrapping')->__('Failed to save gift wrapping.')
                );
                Mage::logException($e);
            }
        }

        if (isset($model) && $model->getId()) {
            $this->_forward('edit');
        } else {
            $this->_forward('new');
        }
    }

    /**
     * Change gift wrapping(s) status action
     *
     * @return void
     */
    public function changeStatusAction()
    {
        $wrappingIds = (array)$this->getRequest()->getParam('wrapping_ids');
        $status = (int)(bool)$this->getRequest()->getParam('status');
        try {
            $wrappingCollection = Mage::getModel('enterprise_giftwrapping/wrapping')->getCollection();
            $wrappingCollection->addFieldToFilter('wrapping_id', array('in' => $wrappingIds));
            foreach ($wrappingCollection as $wrapping) {
                $wrapping->setStatus($status);
            }
            $wrappingCollection->save();
            $this->_getSession()->addSuccess(
                Mage::helper('enterprise_giftwrapping')->__('Total of %d record(s) have been updated.', count($wrappingIds))
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException(
                $e,
                Mage::helper('enterprise_giftwrapping')->__('An error occurred while updating the wrapping(s) status.')
            );
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Delete specified gift wrapping(s)
     * This action can be performed on 'Manage Gift Wrappings' page
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $wrappingIds = (array)$this->getRequest()->getParam('wrapping_ids');
        if (!is_array($wrappingIds)) {
            $this->_getSession()->addError(Mage::helper('enterprise_giftwrapping')->__('Please select items.'));
        } else {
            try {
                $wrappingCollection = Mage::getModel('enterprise_giftwrapping/wrapping')->getCollection();
                $wrappingCollection->addFieldToFilter('wrapping_id', array('in' => $wrappingIds));
                foreach ($wrappingCollection as $wrapping) {
                    $wrapping->delete();
                }
                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_giftwrapping')->__('Total of %d record(s) have been deleted.', count($wrappingIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Delete current gift wrapping
     * This action can be performed on 'Edit Gift Wrapping' page
     *
     * @return void
     */
    public function deleteAction()
    {
        $wrapping = Mage::getModel('enterprise_giftwrapping/wrapping');
        $wrapping->load($this->getRequest()->getParam('id', false));
        if ($wrapping->getId()) {
            try {
                $wrapping->delete();
                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_giftwrapping')->__('The gift wrapping has been deleted.')
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('_current'=>true));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/enterprise_giftwrapping');
    }

    /**
     * Prepare Gift Wrapping Raw data
     *
     * @param array $wrappingRawData
     * @return array
     */
    protected function _prepareGiftWrappingRawData($wrappingRawData)
    {
        if (isset($wrappingRawData['tmp_image'])) {
            $wrappingRawData['tmp_image'] = basename($wrappingRawData['tmp_image']);
        }
        if (isset($wrappingRawData['image_name']['value'])) {
            $wrappingRawData['image_name']['value'] = basename($wrappingRawData['image_name']['value']);
        }
        return $wrappingRawData;
    }

    /**
     * Ajax action for GiftWrapping content in backend order creation
     *
     * @deprecated since 1.12.0.0
     *
     * @return void
     */
    public function orderOptionsAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
}
