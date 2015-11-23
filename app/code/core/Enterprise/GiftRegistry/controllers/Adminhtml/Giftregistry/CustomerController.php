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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Gift Registry controller
 *
 * @category    Enterprise
 * @package     Enterprise_GiftRegistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftRegistry_Adminhtml_Giftregistry_CustomerController extends Mage_Adminhtml_Controller_Action
{
    protected function _initEntity($requestParam = 'id')
    {
        $entity = Mage::getModel('enterprise_giftregistry/entity');
        if ($entityId = $this->getRequest()->getParam($requestParam)) {
            $entity->load($entityId);
            if (!$entity->getId()) {
                Mage::throwException($this->__('Wrong gift registry entity requested.'));
            }
        }
        Mage::register('current_giftregistry_entity', $entity);
        return $entity;
    }

    /**
     * Get customer gift registry grid
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Get customer gift registry info block
     */
    public function editAction()
    {
        try {
            $model = $this->_initEntity();
            $customer = Mage::getModel('customer/customer')->load($model->getCustomerId());

            $this->_title($this->__('Customers'))
                ->_title($this->__('Manage Customers'))
                ->_title($customer->getName())
                ->_title($this->__("Edit '%s' Gift Registry", $model->getTitle()));

            $this->loadLayout()->renderLayout();
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/customer/edit', array(
                'id'         => $this->getRequest()->getParam('customer'),
                'active_tab' => 'giftregistry'
            ));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')
                ->addError($this->__('An error occurred while editing gift registry.'));
            Mage::logException($e);
            $this->_redirect('*/customer/edit', array(
                'id'         => $this->getRequest()->getParam('customer'),
                'active_tab' => 'giftregistry'
            ));
        }
    }

    /**
     * Add quote items to gift registry
     */
    public function addAction()
    {
        if ($quoteIds = $this->getRequest()->getParam('products')){
            $model = $this->_initEntity();
            try {
                $skippedItems = $model->addQuoteItems($quoteIds);
                if (count($quoteIds) - $skippedItems > 0) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        $this->__('Shopping cart items have been added to gift registry.')
                    );
                }
                if ($skippedItems) {
                    Mage::getSingleton('adminhtml/session')->addNotice(
                        $this->__('Virtual, Downloadable, and virtual Gift Card products cannot be added to gift registries.')
                    );
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError($this->__('Failed to add shopping cart items to gift registry.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/edit', array('id' => $model->getId()));
    }

    /**
     * Update gift registry items qty
     */
    public function updateAction()
    {
        $items = $this->getRequest()->getParam('items');
        $entity = $this->_initEntity();
        $updatedCount = 0;

        if (is_array($items)) {
            try {
                $model = Mage::getModel('enterprise_giftregistry/item');
                foreach ($items as $itemId => $data) {
                    if (!empty($data['action'])) {
                        $model->load($itemId);
                        if ($model->getId() && $model->getEntityId() == $entity->getId()) {
                            if ($data['action'] == 'remove') {
                                $model->delete();
                            } else {
                                $model->setQty($data['qty']);
                                $model->save();
                            }
                        }
                        $updatedCount++;
                    }
                }
                if ($updatedCount) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        $this->__('Gift registry items have been updated.')
                    );
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $entity->getId()));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Failed to update gift registry items.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/edit', array('id' => $entity->getId()));
    }

    /**
     * Share gift registry action
     */
    public function shareAction()
    {
        $model = $this->_initEntity();

        if ($data = $this->getRequest()->getParam('emails')) {
            $emails = explode(',', $data);
            $emailsForSend = array();

            if (Mage::app()->isSingleStoreMode()) {
                $storeId = Mage::app()->getStore(true)->getId();
            } else {
                $storeId = $this->getRequest()->getParam('store_id');
            }
            $model->setStoreId($storeId);

            try {
                $sentCount   = 0;
                $failedCount = 0;
                foreach ($emails as $email) {
                    if (!empty($email)) {
                        if ($model->sendShareRegistryEmail(
                                $email,
                                $storeId,
                                $this->getRequest()->getParam('message')
                            )
                        ) {
                            $sentCount++;
                        } else {
                            $failedCount++;
                        }
                        $emailsForSend[] = $email;
                    }
                }
                if (empty($emailsForSend)) {
                    Mage::throwException($this->__('Please specify at least one email.'));
                }
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }

            if ($sentCount) {
                $this->_getSession()->addSuccess($this->__('%d email(s) were sent.', $sentCount));
            }
            if ($failedCount) {
                $this->_getSession()->addError(
                    $this->__('Failed to send %1$d of %2$d email(s).', $failedCount, count($emailsForSend))
                );
            }
        }
        $this->_redirect('*/*/edit', array('id' => $model->getId()));
    }

    /**
     * Delete gift registry action
     */
    public function deleteAction()
    {
        try {
            $model = $this->_initEntity();
            $customerId = $model->getCustomerId();
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('The gift registry entity has been deleted.')
            );
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/edit', array('id' => $model->getId()));
            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Failed to delete gift registry entity.'));
            Mage::logException($e);
        }
        $this->_redirect('*/customer/edit', array('id' => $customerId, 'active_tab' => 'giftregistry'));
    }

    /**
     * Check the permission
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/enterprise_giftregistry');
    }
}
