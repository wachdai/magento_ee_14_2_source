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
 * Gift registry frontend controller
 */
class Enterprise_GiftRegistry_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Only logged in users can use this functionality,
     * this function checks if user is logged in before all other actions
     *
     * @return Enterprise_GiftRegistry_IndexController
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::helper('enterprise_giftregistry')->isEnabled()) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return $this;
        }

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->getResponse()->setRedirect(Mage::helper('customer')->getLoginUrl());
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
        return $this;
    }

    /**
     * View gift registry list in 'My Account' section
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        if ($block = $this->getLayout()->getBlock('giftregistry_list')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(Mage::helper('enterprise_giftregistry')->__('Gift Registry'));
        }
        $this->renderLayout();
    }

    /**
     * Add quote items to customer active gift registry
     *
     * @return void
     */
    public function cartAction()
    {
        $count = 0;
        try {
            $entity = $this->_initEntity('entity');
            if ($entity && $entity->getId()) {
                $skippedItems = 0;
                $request = $this->getRequest();
                if ($request->getParam('product')) {//Adding from product page
                    $entity->addItem($request->getParam('product'), new Varien_Object($request->getParams()));
                    $count = ($request->getParam('qty')) ? $request->getParam('qty') : 1;
                } else {//Adding from cart
                    $cart = Mage::getSingleton('checkout/cart');
                    foreach ($cart->getQuote()->getAllVisibleItems() as $item) {
                        if (!Mage::helper('enterprise_giftregistry')->canAddToGiftRegistry($item)) {
                            $skippedItems++;
                            continue;
                        }
                        $entity->addItem($item);
                        $count += $item->getQty();
                        $cart->removeItem($item->getId());
                    }
                    $cart->save();
                }

                if ($count > 0) {
                    Mage::getSingleton('checkout/session')->addSuccess(
                        Mage::helper('enterprise_giftregistry')->__('%d item(s) have been added to gift registry.', $count)
                    );
                } else {
                    Mage::getSingleton('checkout/session')->addNotice(
                        Mage::helper('enterprise_giftregistry')->__('Nothing to add to gift registry.')
                    );
                }
                if (!empty($skippedItems)) {
                    Mage::getSingleton('checkout/session')->addNotice(
                        Mage::helper('enterprise_giftregistry')->__('Virtual, Downloadable, and virtual Gift Card products cannot be added to gift registries.')
                    );
                }
            }
        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() == Enterprise_GiftRegistry_Model_Entity::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
                $this->_getCheckoutSession()->addError($e->getMessage());
                $this->_redirectReferer('*/*');
            } else {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('giftregistry');
            }
            return;
        } catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError(
                $this->__('Failed to add shopping cart items to gift registry.')
            );
        }

        if ($entity->getId()) {
            $this->_redirect('giftregistry/index/items', array('id' => $entity->getId()));
        } else {
            $this->_redirect('giftregistry');
        }
    }

    /**
     * Add wishlist items to customer active gift registry action
     *
     * @return void
     */
    public function wishlistAction()
    {
        $itemId = $this->getRequest()->getParam('item');
        $redirectParams = array();
        if ($itemId) {
            try {
                $entity = $this->_initEntity('entity');
                $wishlistItem = Mage::getModel('wishlist/item')
                    ->loadWithOptions($itemId, 'info_buyRequest');
                $entity->addItem($wishlistItem->getProductId(), $wishlistItem->getBuyRequest());
                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_giftregistry')->__('Wishlist item have been added to gift registry.')
                );
                $redirectParams['wishlist_id'] = $wishlistItem->getWishlistId();
            } catch (Mage_Core_Exception $e) {
                if ($e->getCode() == Enterprise_GiftRegistry_Model_Entity::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
                    $product = Mage::getModel('catalog/product')->load((int)$wishlistItem->getProductId());
                    $query['options'] = Enterprise_GiftRegistry_Block_Product_View::FLAG;
                    $query['entity'] = $this->getRequest()->getParam('entity');
                    $this->_redirectUrl($product->getUrlModel()->getUrl($product, array('_query' => $query)));
                    return;
                }
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('giftregistry');
                return;
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Failed to add wishlist items to gift registry.'));
            }
        }

        $this->_redirect('wishlist', $redirectParams);
    }

    /**
     * Delete selected gift registry entity
     *
     * @return void
     */
    public function deleteAction()
    {
        try {
            $entity = $this->_initEntity();
            if ($entity->getId()) {
                $entity->delete();
                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_giftregistry')->__('Gift registry has been deleted.')
                );
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $message = Mage::helper('enterprise_giftregistry')->__('An error occurred while deleting gift registry.');
            $this->_getSession()->addException($e, $message);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Share selected gift registry entity
     *
     * @return void
     */
    public function shareAction()
    {
        try {
            $entity = $this->_initEntity();
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $headBlock = $this->getLayout()->getBlock('head');
            if ($headBlock) {
                $headBlock->setTitle(Mage::helper('enterprise_giftregistry')->__('Share Gift Registry'));
            }
            $this->getLayout()->getBlock('giftregistry.customer.share')->setEntity($entity);
            $this->renderLayout();
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $message = Mage::helper('enterprise_giftregistry')->__('An error occurred while sharing gift registry.');
            $this->_getSession()->addException($e, $message);
        }
        $this->_redirect('*/*/');
    }

    /**
     * View items of selected gift registry entity
     *
     * @return void
     */
    public function itemsAction()
    {
        try {
            Mage::register('current_entity', $this->_initEntity());
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('checkout/session');
            $headBlock = $this->getLayout()->getBlock('head');
            if ($headBlock) {
                $headBlock->setTitle(Mage::helper('enterprise_giftregistry')->__('Gift Registry Items'));
            }
            $this->renderLayout();
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * Update gift registry items
     *
     * @return void
     */
    public function updateItemsAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }

        try {
            $entity = $this->_initEntity();
            if ($entity->getId()) {
                $items = $this->getRequest()->getParam('items');
                $entity->updateItems($items);
                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_giftregistry')->__('The gift registry items have been updated.')
                );
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        } catch (Mage_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Failed to update gift registry items list.'));
        }
        $this->_redirect('*/*/items', array('_current' => true));
    }

    /**
     * Share selected gift registry entity
     *
     * @return void
     */
    public function sendAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/share', array('_current' => true));
            return;
        }

        $error  = false;
        // Escaped inside an email template
        $senderMessage = $this->getRequest()->getPost('sender_message');
        $senderName = htmlspecialchars($this->getRequest()->getPost('sender_name'));
        $senderEmail = htmlspecialchars($this->getRequest()->getPost('sender_email'));

        try {
            if (!empty($senderName) && !empty($senderMessage) && !empty($senderEmail)) {
                if (Zend_Validate::is($senderEmail, 'EmailAddress')) {
                    $emails = array();
                    $recipients = $this->getRequest()->getPost('recipients');
                    foreach ($recipients as $recipient) {
                        $recipientEmail = trim($recipient['email']);
                        if (!Zend_Validate::is($recipientEmail, 'EmailAddress')) {
                            $error = Mage::helper('enterprise_giftregistry')->__('Please input a valid recipient email address.');
                            break;
                        }

                        $recipient['name'] = htmlspecialchars($recipient['name']);
                        if (empty($recipient['name'])) {
                            $error = Mage::helper('enterprise_giftregistry')->__('Please input a recipient name.');
                            break;
                        }
                        $emails[] = $recipient;
                    }

                    $count = 0;
                    if (count($emails) && !$error){
                        $entity = $this->_initEntity();
                        foreach($emails as $recipient) {
                            $sender = array('name' => $senderName, 'email' => $senderEmail);
                            if ($entity->sendShareRegistryEmail($recipient, null, $senderMessage, $sender)) {
                                $count++;
                            }
                        }
                        if ($count > 0) {
                            $this->_getSession()->addSuccess(
                                Mage::helper('enterprise_giftregistry')->__('The gift registry has been shared for %d emails.', $count)
                            );
                        } else {
                            $this->_getSession()->addError(
                                Mage::helper('enterprise_giftregistry')->__('Failed to share gift registry.')
                            );
                        }
                    }
                } else {
                    $error = Mage::helper('enterprise_giftregistry')->__('Please input a valid sender email address.');
                }
            } else {
                $error = Mage::helper('enterprise_giftregistry')->__('Sender data can\'t be empty.');
            }

            if ($error) {
                $this->_getSession()->addError($error);
                $this->_getSession()->setSharingForm($this->getRequest()->getPost());
                $this->_redirect('*/*/share', array('_current' => true));
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $message = Mage::helper('enterprise_giftregistry')->__('An error occurred while sending email(s).');
            $this->_getSession()->addException($e, $message);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Get customer active gift registry entity
     *
     * @deprecated after 1.9.1.1
     * @return Enterprise_GiftRegistry_Model_Entity
     */
    protected function _getActiveEntity()
    {
        return Mage::getModel('enterprise_giftregistry/entity')
            ->getActiveEntity($this->_getSession()->getCustomerId());
    }

    /**
     * Get current customer session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get current checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * View gift registry action
     *
     * @return void
     */
    public function viewAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->loadLayoutUpdates();
        if ($block = $this->getLayout()->getBlock('giftregistry_view')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }

    /**
     * Add select gift registry action
     *
     * @return void
     */
    public function addSelectAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        if ($block = $this->getLayout()->getBlock('giftregistry_addselect')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(Mage::helper('enterprise_giftregistry')->__('Create Gift Registry'));
        }
        $this->renderLayout();
    }

    /**
     * Select gift registry type action
     *
     * @return void
     */
    public function editAction()
    {
        $typeId = $this->getRequest()->getParam('type_id');
        $entityId = $this->getRequest()->getParam('entity_id');
        try {
            if (!$typeId) {
                if (!$entityId) {
                    $this->_redirect('*/*/');
                    return;
                } else {
                    // editing existing entity
                    /* @var $model Enterprise_GiftRegistry_Model_Entity */
                    $model = $this->_initEntity('entity_id');
                }
            }

            if ($typeId && !$entityId) {
                // creating new entity
                /* @var $model Enterprise_GiftRegistry_Model_Entity */
                $model = Mage::getSingleton('enterprise_giftregistry/entity');
                if ($model->setTypeById($typeId) === false) {
                    Mage::throwException(Mage::helper('enterprise_giftregistry')->__('Incorrect gift registry type.'));
                }
            }

            Mage::register('enterprise_giftregistry_entity', $model);
            Mage::register('enterprise_giftregistry_address', $model->exportAddress());

            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');

            if ($model->getId()) {
                $pageTitle = Mage::helper('enterprise_giftregistry')->__('Edit Gift Registry');
            } else {
                $pageTitle = Mage::helper('enterprise_giftregistry')->__('Create Gift Registry');
            }
            $headBlock = $this->getLayout()->getBlock('head');
            if ($headBlock) {
                $headBlock->setTitle($pageTitle);
            }
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
        }
    }

    /**
     * Create gift registry action
     *
     * @return void
     */
    public function editPostAction()
    {
        if (!($typeId = $this->getRequest()->getParam('type_id'))) {
            $this->_redirect('*/*/addselect');
            return;
        }

        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/edit', array('type_id', $typeId));
            return ;
        }

        if ($this->getRequest()->isPost() && ($data = $this->getRequest()->getPost())) {
            $entityId = $this->getRequest()->getParam('entity_id');
            $isError = false;
            $isAddAction = true;
            try {
                if ($entityId){
                    $isAddAction = false;
                    $model = $this->_initEntity('entity_id');
                }
                if ($isAddAction) {
                    $entityId = null;
                    $model = Mage::getModel('enterprise_giftregistry/entity');
                    if ($model->setTypeById($typeId) === false) {
                        Mage::throwException(Mage::helper('enterprise_giftregistry')->__('Incorrect Type.'));
                    }
                }

                $data = Mage::helper('enterprise_giftregistry')->filterDatesByFormat(
                    $data,
                    $model->getDateFieldArray()
                );
                $data = $this->_filterPost($data);
                $this->getRequest()->setPost($data);
                $model->importData($data, $isAddAction);

                $registrantsPost = $this->getRequest()->getPost('registrant');
                $persons = array();
                if (is_array($registrantsPost)) {
                    foreach  ($registrantsPost as $index => $registrant) {
                        if (is_array($registrant)) {
                            /* @var $person Enterprise_GiftRegistry_Model_Person */
                            $person = Mage::getModel('enterprise_giftregistry/person');
                            $idField = $person->getIdFieldName();
                            if (!empty($registrant[$idField])) {
                                $person->load($registrant[$idField]);
                                if (!$person->getId()) {
                                    Mage::throwException(
                                        Mage::helper('enterprise_giftregistry')->__('Incorrect recipient data.')
                                    );
                                }
                            } else {
                                unset($registrant['person_id']);
                            }
                            $person->setData($registrant);
                            $errors = $person->validate();
                            if ($errors !== true) {
                                foreach ($errors as $err) {
                                    $this->_getSession()->addError($err);
                                }
                                $isError = true;
                            } else {
                                $persons[] = $person;
                            }
                        }
                    }
                }
                $addressTypeOrId = $this->getRequest()->getParam('address_type_or_id');
                if (!$addressTypeOrId || $addressTypeOrId == Enterprise_GiftRegistry_Helper_Data::ADDRESS_NEW) {
                    // creating new address
                    if (!empty($data['address'])) {
                        /* @var $address Mage_Customer_Model_Address */
                        $address = Mage::getModel('customer/address');
                        $address->setData($data['address']);
                        $errors = $address->validate();
                        $model->importAddress($address);
                    } else {
                        Mage::throwException(Mage::helper('enterprise_giftregistry')->__('Address is empty.'));
                    }
                    if ($errors !== true) {
                        foreach ($errors as $err) {
                            $this->_getSession()->addError($err);
                        }
                        $isError = true;
                    }
                } else if ($addressTypeOrId != Enterprise_GiftRegistry_Helper_Data::ADDRESS_NONE) {
                    // using one of existing Customer adressess
                    $addressId = $addressTypeOrId;
                    if (!$addressId) {
                        Mage::throwException(Mage::helper('enterprise_giftregistry')->__('No address selected.'));
                    }
                    /* @var $customer Mage_Customer_Model_Customer */
                    $customer  = Mage::getSingleton('customer/session')->getCustomer();

                    $address = $customer->getAddressItemById($addressId);
                    if (!$address) {
                        Mage::throwException(
                            Mage::helper('enterprise_giftregistry')->__('Incorrect address selected.')
                        );
                    }
                    $model->importAddress($address);
                }
                $errors = $model->validate();
                if ($errors !== true) {
                    foreach ($errors as $err) {
                        $this->_getSession()->addError($err);
                    }
                    $isError = true;
                }

                if (!$isError) {
                    $model->save();
                    $entityId = $model->getId();
                    $personLeft = array();
                    foreach ($persons as $person) {
                        $person->setEntityId($entityId);
                        $person->save();
                        $personLeft[] = $person->getId();
                    }
                    if (!$isAddAction) {
                        Mage::getModel('enterprise_giftregistry/person')
                            ->getResource()
                            ->deleteOrphan($entityId, $personLeft);
                    }
                    $this->_getSession()->addSuccess(
                        Mage::helper('enterprise_giftregistry')->__('Gift registry has been successfully saved.')
                    );
                    if ($isAddAction) {
                        $model->sendNewRegistryEmail();
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $isError = true;
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('enterprise_giftregistry')->__('Failed to save gift registry.')
                );
                Mage::logException($e);
                $isError = true;
            }

            if ($isError) {
                $this->_getSession()->setGiftRegistryEntityFormData($this->getRequest()->getPost());
                $params = $isAddAction ? array('type_id' => $typeId) : array('entity_id' => $entityId);
                $this->_redirect('*/*/edit', $params);
                return $this;
            } else {
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Load gift registry entity model by request argument
     *
     * @return Enterprise_GiftRegistry_Model_Entity
     */
    protected function _initEntity($requestParam = 'id')
    {
        $entity = Mage::getModel('enterprise_giftregistry/entity');
        $customerId = $this->_getSession()->getCustomerId();

        if ($entityId = $this->getRequest()->getParam($requestParam)) {
            $entity->load($entityId);
            if (!$entity->getId() || $entity->getCustomerId() != $customerId) {
                Mage::throwException(Mage::helper('enterprise_giftregistry')->__('Wrong gift registry ID specified.'));
            }
        }
        return $entity;
    }

     /**
     * Strip tags from received data
     *
     * @param  string|array $data
     * @return mixed
     */
    protected function _filterPost($data)
    {
        if (!is_array($data)) {
            return strip_tags($data);
        }
        foreach ($data as &$field) {
            if (!empty($field)) {
                if (!is_array($field)) {
                    $field = strip_tags($field);
                } else {
                    $field = $this->_filterPost($field);
                }
            }
        }
        return $data;
    }
}
