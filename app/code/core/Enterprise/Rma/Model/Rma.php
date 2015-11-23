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

/**
 * RMA model
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Model_Rma extends Mage_Core_Model_Abstract
{
    /**
     * XML configuration paths
     */
    const XML_PATH_SECTION_RMA       = 'sales/enterprise_rma/';
    const XML_PATH_ENABLED           = 'sales/enterprise_rma/enabled';
    const XML_PATH_USE_STORE_ADDRESS = 'sales/enterprise_rma/use_store_address';
     /**
     * Rma Instance
     *
     * @var Enterprise_Rma_Model_Rma
     */
    protected $_rma = null;

    /**
     * Rma items collection
     *
     * @var null
     */
    protected $_items           = null;

    /**
     * Rma order object
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order           = null;

    protected $_trackingNumbers = null;
    protected $_shippingLabel   = null;

    /**
     * Init resource model
     */
    protected function _construct() {
        $this->_init('enterprise_rma/rma');
        parent::_construct();
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getIncrementId()) {
            $incrementId = Mage::getSingleton('eav/config')
                ->getEntityType('rma_item')
                ->fetchNewIncrementId($this->getStoreId());
            $this->setIncrementId($incrementId);
        }
        if (!$this->getIsUpdate()) {
            $this->setData('protect_code', substr(md5(uniqid(mt_rand(), true) . ':' . microtime(true)), 5, 6));
        }
        return $this;
    }

    /**
     * Save related items
     *
     * @return Enterprise_Rma_Model_Rma
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        /** @var $gridModel Enterprise_Rma_Model_Grid */
        $gridModel = Mage::getModel('enterprise_rma/grid');
        $gridModel->addData($this->getData());
        $gridModel->save();

        Mage::getModel('enterprise_rma/rma_status_history')->setRma($this)->saveSystemComment();

        $itemsCollection = $this->getItemsCollection();
        if (is_array($itemsCollection)) {
            foreach ($itemsCollection as $item) {
                $item->save();
            }
        }
        return $this;
    }

    /**
     * Return Entity Type ID
     *
     * @return int
     */
    public function getEntityTypeId()
    {
        $entityTypeId = $this->getData('entity_type_id');
        if (!$entityTypeId) {
            $entityTypeId = $this->getEntityType()->getId();
            $this->setData('entity_type_id', $entityTypeId);
        }
        return $entityTypeId;
    }

    /**
     * Get available statuses for RMAs
     *
     * @return array
     */
    public function getAllStatuses()
    {
        return Mage::getModel('enterprise_rma/rma_source_status')->getAllOptionsForGrid();
    }

    /**
     * Get RMA's status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        if (is_null(parent::getStatusLabel())){
            $this->setStatusLabel(Mage::getModel('enterprise_rma/rma_source_status')->getItemLabel($this->getStatus()));
        }
        return parent::getStatusLabel();
    }

    /**
     * Gets Rma items collection
     *
     * @return Enterprise_Rma_Model_Resource_Item_Collection
     */
    public function getItemsCollection()
    {
        if ($this->getId() && !empty($this->_items)) {
            foreach ($this->_items as $item) {
                if (!$item->getRmaEntityId()) {
                    $item->setRmaEntityId($this->getId());
                }
            }
        }
        return $this->_items;
    }

    /**
     * Get rma order object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
        }
        return $this->_order;
    }

    /**
     * Retrieves rma close availability
     *
     * @return bool
     */
    public function canClose()
    {
        $status = $this->getStatus();
        if ($status === Enterprise_Rma_Model_Rma_Source_Status::STATE_CLOSED
            || $status === Enterprise_Rma_Model_Rma_Source_Status::STATE_PROCESSED_CLOSED) {
            return false;
        }

        return true;
    }

    /**
     * Close rma
     *
     * @return Enterprise_Rma_Model_Rma
     */
    public function close()
    {
        if ($this->canClose()) {
            $this->setStatus(Enterprise_Rma_Model_Rma_Source_Status::STATE_CLOSED);
        }
        return $this;
    }

    /**
     * Save RMA
     *
     * @param array $data
     * @return bool|Enterprise_Rma_Model_Rma
     */
    public function saveRmaData($data)
    {
        // TODO: move errors adding to controller
        $errors = 0;

        if ($this->getCustomerCustomEmail()) {
            $validateEmail = $this->_validateEmail($this->getCustomerCustomEmail());
            if (is_array($validateEmail)) {
                $session = Mage::getSingleton('core/session');
                foreach ($validateEmail as $error) {
                    $session->addError($error);
                }
                $session->setRmaFormData($data);
                $errors = 1;
            }
        }

        $itemModels = $this->_createItemsCollection($data);
        if (!$itemModels || $errors) {
            return false;
        }

        $this->save();
        $this->_rma = $this;
        return $this;
    }

    /**
     * Save Rma
     *
     * @deprecated
     * DO NOT USE THIS METHOD
     *
     * @return bool|Enterprise_Rma_Model_Rma
     */
    public function saveRma()
    {
       return $this->saveRmaData($_POST);
    }

    /**
     * Sending email with RMA data
     *
     * @return Enterprise_Rma_Model_Rma
     */
    public function sendNewRmaEmail()
    {
        /** @var $configRmaEmail Enterprise_Rma_Model_Config */
        $configRmaEmail = Mage::getSingleton('enterprise_rma/config');
        return $this->_sendRmaEmailWithItems($configRmaEmail->getRootRmaEmail());
    }

    /**
     * Sending authorizing email with RMA data
     *
     * @return Enterprise_Rma_Model_Rma
     */
    public function sendAuthorizeEmail()
    {
        if (!$this->getIsSendAuthEmail()) {
            return $this;
        }
        /** @var $configRmaEmail Enterprise_Rma_Model_Config */
        $configRmaEmail = Mage::getSingleton('enterprise_rma/config');
        return $this->_sendRmaEmailWithItems($configRmaEmail->getRootAuthEmail());
    }

    /**
     * Sending authorizing email with RMA data
     *
     * @param string $rootConfig
     * @return Enterprise_Rma_Model_Rma
     */
    public function _sendRmaEmailWithItems($rootConfig)
    {
        /** @var $configRmaEmail Enterprise_Rma_Model_Config */
        $configRmaEmail = Mage::getSingleton('enterprise_rma/config');
        $configRmaEmail->init($rootConfig, $this->getStoreId());

        if (!$configRmaEmail->isEnabled()) {
            return $this;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $copyTo = $configRmaEmail->getCopyTo();
        $copyMethod = $configRmaEmail->getCopyMethod();
        if ($copyTo && $copyMethod == 'bcc') {
            foreach ($copyTo as $email) {
                $mailTemplate->addBcc($email);
            }
        }

        if ($this->getOrder()->getCustomerIsGuest()) {
            $template = $configRmaEmail->getGuestTemplate();
            $customerName = $this->getOrder()->getBillingAddress()->getName();
        } else {
            $template = $configRmaEmail->getTemplate();
            $customerName = $this->getCustomerName();
        }

        $sendTo = array(
            array(
                'email' => $this->getOrder()->getCustomerEmail(),
                'name'  => $customerName
            )
        );
        if ($this->getCustomerCustomEmail()) {
            $sendTo[] = array(
                            'email' => $this->getCustomerCustomEmail(),
                            'name'  => $customerName
                        );
        }
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $sendTo[] = array(
                    'email' => $email,
                    'name'  => null
                );
            }
        }

        $returnAddress = Mage::helper('enterprise_rma')->getReturnAddress('html', array(), $this->getStoreId());

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))
                ->sendTransactional(
                    $template,
                    $configRmaEmail->getIdentity(),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'rma'               => $this,
                        'order'             => $this->getOrder(),
                        'return_address'    => $returnAddress,
                        //We cannot use $this->_items as items collection, because some items might not be loaded now
                        'item_collection'   => $this->getItemsForDisplay(),
                    )
                );
        }
        $this->setEmailSent(true);
        $translate->setTranslateInline(true);

        return $this;
    }

    /**
     * Prepares Item's data
     *
     * @param  $item
     * @return array
     */
    protected function _preparePost($item)
    {
        $errors         = false;
        $preparePost    = array();
        $qtyKeys        = array('qty_authorized', 'qty_returned', 'qty_approved');

        ksort($item);
        foreach ($item as $key=>$value) {
            if ($key == 'order_item_id') {
                $preparePost['order_item_id'] = (int)$value;
            } elseif ($key == 'qty_requested') {
                $preparePost['qty_requested'] = is_numeric($value) ? $value : 0;
            } elseif (in_array($key, $qtyKeys)) {
                if (is_numeric($value)) {
                    $preparePost[$key] = (float)$value;
                } else {
                    $preparePost[$key] = '';
                }
            } elseif ($key == 'resolution') {
                $preparePost['resolution'] = (int)$value;
            } elseif ($key == 'condition') {
                $preparePost['condition'] = (int)$value;
            } elseif ($key == 'reason') {
                $preparePost['reason'] = (int)$value;
            } elseif ($key == 'reason_other' && !empty($value)) {
                $preparePost['reason_other'] = $value;
            } else {
                $preparePost[$key] = $value;
            }
        }

        $order      = $this->getOrder();
        $realItem   = $order->getItemById($preparePost['order_item_id']);

        $stat = Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_PENDING;
        if (!empty($preparePost['status'])) {
            $status = Mage::getModel('enterprise_rma/item_attribute_source_status');
            if ($status->checkStatus($preparePost['status'])) {
                $stat = $preparePost['status'];
            }
        }

        $preparePost['status']              = $stat;

        $preparePost['product_name']        = $realItem->getName();
        $preparePost['product_sku']         = $realItem->getSku();
        $preparePost['product_admin_name']  = Mage::helper('enterprise_rma')->getAdminProductName($realItem);
        $preparePost['product_admin_sku']   = Mage::helper('enterprise_rma')->getAdminProductSku($realItem);
        $preparePost['product_options']     = serialize($realItem->getProductOptions());
        $preparePost['is_qty_decimal']      = $realItem->getIsQtyDecimal();

        if ($preparePost['is_qty_decimal']) {
            $preparePost['qty_requested']   = (float)$preparePost['qty_requested'];
        } else {
            $preparePost['qty_requested']   = (int)$preparePost['qty_requested'];

            foreach ($qtyKeys as $key) {
                if (!empty($preparePost[$key])) {
                    $preparePost[$key] = (int)$preparePost[$key];
                }
            }
        }

        if (isset($preparePost['qty_requested'])
            && $preparePost['qty_requested'] <= 0
        ) {
            $errors = true;
        }

        foreach ($qtyKeys as $key) {
            if (isset($preparePost[$key])
                && !is_string($preparePost[$key])
                && $preparePost[$key] <= 0
            ) {
                $errors = true;
            }
        }

        if ($errors) {
            $session = Mage::getSingleton('core/session');
            $session->addError(
                Mage::helper('enterprise_rma')->__('There is an error in quantities for item %s.', $preparePost['product_name'])
            );
        }

        return $preparePost;
    }

    /**
     * Checks Items Quantity in Return
     *
     * @param  Enterprise_Rma_Model_Item $itemModels
     * @param  $orderId
     * @return array|bool
     */
    protected function _checkPost($itemModels, $orderId)
    {
        $errors     = array();
        $errorKeys  = array();
        if (!$this->getIsUpdate()) {
            $availableItems = Mage::helper('enterprise_rma')->getOrderItems($orderId);
        } else {
            $availableItems = Mage::getResourceModel('enterprise_rma/item')->getOrderItemsCollection($orderId);
        }

        $itemsArray = array();
        foreach ($itemModels as $item) {
            if (!isset($itemsArray[$item->getOrderItemId()])) {
                $itemsArray[$item->getOrderItemId()] = $item->getQtyRequested();
            } else {
                $itemsArray[$item->getOrderItemId()] += $item->getQtyRequested();
            }

            if ($this->getIsUpdate()) {
                $validation = array();
                foreach (array('qty_requested', 'qty_authorized', 'qty_returned', 'qty_approved') as $tempQty) {
                    if (is_null($item->getData($tempQty))) {
                        if (!is_null($item->getOrigData($tempQty))) {
                            $validation[$tempQty] = (float)$item->getOrigData($tempQty);
                        }
                    } else {
                        $validation[$tempQty] = (float)$item->getData($tempQty);
                    }
                }
                $validation['dummy'] = -1;
                $previousValue = null;
                $escapedProductName = Mage::helper('enterprise_rma')->escapeHtml($item->getProductName());
                foreach ($validation as $key => $value) {
                    if (isset($previousValue) && $value > $previousValue) {
                        $errors[] = Mage::helper('enterprise_rma')->__('There is an error in quantities for item %s.', $escapedProductName);
                        $errorKeys[$item->getId()] = $key;
                        $errorKeys['tabs'] = 'items_section';
                        break;
                    }
                    $previousValue = $value;
                }

                //if we change item status i.e. to authorized, then qty_authorized must be non-empty and so on.
                $qtyToStatus = array(
                    'qty_authorized' => array(
                            'name' => Mage::helper('enterprise_rma')->__('Authorized Qty'),
                            'status' => Enterprise_Rma_Model_Rma_Source_Status::STATE_AUTHORIZED
                        ),
                    'qty_returned' => array(
                            'name' => Mage::helper('enterprise_rma')->__('Returned Qty'),
                            'status' => Enterprise_Rma_Model_Rma_Source_Status::STATE_RECEIVED
                        ),
                    'qty_approved' => array(
                            'name' => Mage::helper('enterprise_rma')->__('Approved Qty'),
                            'status' => Enterprise_Rma_Model_Rma_Source_Status::STATE_APPROVED
                        ),

                );
                foreach ($qtyToStatus as $qtyKey => $qtyValue) {
                    if ($item->getStatus() === $qtyValue['status']
                        && $item->getOrigData('status') !== $qtyValue['status']
                        && !$item->getData($qtyKey)
                    ) {
                        $errors[] = Mage::helper('enterprise_rma')->__('%s for item %s cannot be empty.', $qtyValue['name'], $escapedProductName);
                        $errorKeys[$item->getId()] = $qtyKey;
                        $errorKeys['tabs'] = 'items_section';
                    }
                }
            }
        }
        ksort($itemsArray);

        $availableItemsArray = array();
        foreach ($availableItems as $item) {
            $availableItemsArray[$item->getId()] = array(
                'name'  => $item->getName(),
                'qty'   => $item->getAvailableQty()
            );
        }

        foreach ($itemsArray as $key=>$qty) {
            $escapedProductName = Mage::helper('enterprise_rma')->escapeHtml($availableItemsArray[$key]['name']);
            if (!array_key_exists($key, $availableItemsArray)) {
                $errors[] = Mage::helper('enterprise_rma')->__('You cannot return %s.', $escapedProductName);
            }
            if (isset($availableItemsArray[$key]) && $availableItemsArray[$key]['qty'] < $qty) {
                $errors[] = Mage::helper('enterprise_rma')->__('Quantity of %s is greater than you can return.', $escapedProductName);
                $errorKeys[$key] = 'qty_requested';
                $errorKeys['tabs'] = 'items_section';
            }
        }

        if (!empty($errors)) {
            return array($errors, $errorKeys);
        }
        return true;
    }

    /**
     * Create Items Collection
     *
     * @param array $data
     * @return array|bool
     */
    protected function _createItemsCollection($data)
    {
        if (!is_array($data)) {
            $data = (array) $data;
        }
        $order      = $this->getOrder();
        $itemModels = array();
        $errors     = array();
        $errorKeys  = array();

        foreach ($data['items'] as $key=>$item) {
            if (isset($item['items'])) {
                $itemModel  = $firstModel   = false;
                $files      = $f            =array();
                foreach ($item['items'] as $id=>$qty) {
                    if ($itemModel) {
                        $firstModel = $itemModel;
                    }
                    $itemModel                  = Mage::getModel('enterprise_rma/item');
                    $subItem                    = $item;
                    unset($subItem['items']);
                    $subItem['order_item_id']   = $id;
                    $subItem['qty_requested']   = $qty;

                    $itemPost                   = $this->_preparePost($subItem);

                    $f = $itemModel->setData($itemPost)
                        ->prepareAttributes($itemPost, $key);

                    /* Copy image(s) to another bundle items */
                    if (!empty($f)) {
                        $files = $f;
                    }
                    if (!empty($files) && $firstModel) {
                        foreach ($files as $code) {
                            $itemModel->setData($code, $firstModel->getData($code));
                        }
                    }
                    $errors = array_merge($itemModel->getErrors(), $errors);

                    $itemModels[] = $itemModel;
                }
            } else {
                $itemModel = Mage::getModel('enterprise_rma/item');
                if (isset($item['entity_id']) && $item['entity_id']) {
                    $itemModel->load($item['entity_id']);
                    if ($itemModel->getId()) {
                        if (empty($item['reason'])) {
                            $item['reason'] = $itemModel->getReason();
                        }

                        if (empty($item['reason_other'])) {
                            $item['reason_other'] = $itemModel->getReasonOther() === null ? ''
                                : $itemModel->getReasonOther();
                        }

                        if (empty($item['condition'])) {
                            $item['condition'] = $itemModel->getCondition();
                        }

                        if (empty($item['qty_requested'])) {
                            $item['qty_requested'] = $itemModel->getQtyRequested();
                        }
                    }

                }

                $itemPost = $this->_preparePost($item);

                $itemModel->setData($itemPost)
                    ->prepareAttributes($itemPost, $key);
                $errors = array_merge($itemModel->getErrors(), $errors);
                if ($errors) {
                    $errorKeys['tabs'] = 'items_section';
                }

                $itemModels[] = $itemModel;

                if (($itemModel->getStatus() === Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_AUTHORIZED)
                    && ($itemModel->getOrigData('status') !== $itemModel->getStatus())) {
                    $this->setIsSendAuthEmail(1);
                }
            }
        }

        $result = $this->_checkPost($itemModels, $order->getId());

        if ($result !== true) {
            list($result, $errorKey) = $result;
            $errors     = array_merge($result, $errors);
            $errorKeys  = array_merge($errorKey, $errorKeys);
        }

        $session    = Mage::getSingleton('core/session');
        $eMessages  = $session->getMessages()->getErrors();

        if (!empty($errors) || !empty($eMessages)) {
            $session->setRmaFormData($data);
            if (!empty($errorKeys)) {
                $session->setRmaErrorKeys($errorKeys);
            }
            if (!empty($errors)) {
                foreach ($errors as $message) {
                    $session->addError($message);
                }
            }
            return false;
        }
        $this->_items = $itemModels;
        return $itemModels;
    }


    /**
     * Validate email
     *
     * @param string $value
     * @return string
     */
    protected function _validateEmail($value)
    {
        $label = Mage::helper('enterprise_rma')->getContactEmailLabel();

        $validator = new Zend_Validate_EmailAddress();
        $validator->setMessage(
            Mage::helper('enterprise_rma')->__('"%s" invalid type entered.', $label),
            Zend_Validate_EmailAddress::INVALID
        );
        $validator->setMessage(
            Mage::helper('enterprise_rma')->__('"%s" is not a valid email address.', $label),
            Zend_Validate_EmailAddress::INVALID_FORMAT
        );
        $validator->setMessage(
            Mage::helper('enterprise_rma')->__('"%s" is not a valid hostname.', $label),
            Zend_Validate_EmailAddress::INVALID_HOSTNAME
        );
        $validator->setMessage(
            Mage::helper('enterprise_rma')->__('"%s" is not a valid hostname.', $label),
            Zend_Validate_EmailAddress::INVALID_MX_RECORD
        );
        $validator->setMessage(
            Mage::helper('enterprise_rma')->__('"%s" is not a valid hostname.', $label),
            Zend_Validate_EmailAddress::INVALID_MX_RECORD
        );
        $validator->setMessage(
            Mage::helper('enterprise_rma')->__('"%s" is not a valid email address.', $label),
            Zend_Validate_EmailAddress::DOT_ATOM
        );
        $validator->setMessage(
            Mage::helper('enterprise_rma')->__('"%s" is not a valid email address.', $label),
            Zend_Validate_EmailAddress::QUOTED_STRING
        );
        $validator->setMessage(
            Mage::helper('enterprise_rma')->__('"%s" is not a valid email address.', $label),
            Zend_Validate_EmailAddress::INVALID_LOCAL_PART
        );
        $validator->setMessage(
            Mage::helper('enterprise_rma')->__('"%s" exceeds the allowed length.', $label),
            Zend_Validate_EmailAddress::LENGTH_EXCEEDED
        );
        if (!$validator->isValid($value)) {
            return array_unique($validator->getMessages());
        }

        return true;
    }

    /**
     * Get formated RMA created date in store timezone
     *
     * @param   string $format date format type (short|medium|long|full)
     * @return  string
     */
    public function getCreatedAtFormated($format)
    {
        return Mage::helper('core')->formatDate($this->getCreatedAtStoreDate(), $format, true);
    }

    /**
     * Gets Shipping Methods
     *
     * @param bool $returnItems Flag if needs to return Items
     * @return array|bool
     */
    public function getShippingMethods($returnItems = false)
    {
        $found      = false;

        $rmaItems   = Mage::getResourceModel('enterprise_rma/item')
            ->getAuthorizedItems($this->getId())
        ;

        if (!empty($rmaItems)) {
            $quoteItemsCollection = Mage::getResourceModel('sales/order_item_collection')
                ->addFieldToFilter('item_id', array('in' => array_keys($rmaItems)))
                ->getData()
            ;

            $quoteItems = array();
            $subtotal   = $weight = $qty = $storeId = 0;
            foreach ($quoteItemsCollection as $item) {
                $itemModel = Mage::getModel('sales/quote_item');

                $item['qty']                    = $rmaItems[$item['item_id']]['qty'];
                $item['name']                   = $rmaItems[$item['item_id']]['product_name'];
                $item['row_total']              = $item['price'] * $item['qty'];
                $item['base_row_total']         = $item['base_price'] * $item['qty'];
                $item['row_total_with_discount']= 0;
                $item['row_weight']             = $item['weight'] * $item['qty'];
                $item['price_incl_tax']         = $item['price'];
                $item['base_price_incl_tax']    = $item['base_price'];
                $item['row_total_incl_tax']     = $item['row_total'];
                $item['base_row_total_incl_tax']= $item['base_row_total'];

                $quoteItems[] = $itemModel->setData($item);

                $subtotal   += $item['base_row_total'];
                $weight     += $item['row_weight'];
                $qty        += $item['qty'];

                if (!$storeId) {
                    $storeId = $item['store_id'];
                    /** @var $address Mage_Sales_Model_Order */
                    $address = Mage::getModel('sales/order')->load($item['order_id'])->getShippingAddress();
                }
                $quote = Mage::getModel('sales/quote')
                        ->setStoreId($storeId);
                $itemModel->setQuote($quote);
            }

            if ($returnItems) {
                return $quoteItems;
            }

            $store      = Mage::app()->getStore($storeId);
            $this->setStore($store);

            $found = $this->_requestShippingRates($quoteItems, $address, $store, $subtotal, $weight, $qty);
        }

        return $found;
    }

    /**
     * Returns Shipping Rates
     *
     * @param  $items
     * @param  $address Shop address
     * @param  $store
     * @param  $subtotal
     * @param  $weight
     * @param  $qty
     *
     * @return array|bool
     */
    protected function _requestShippingRates($items, $address, $store, $subtotal, $weight, $qty)
    {
        $shippingDestinationInfo = Mage::helper('enterprise_rma')->getReturnAddressModel($this->getStoreId());

        /** @var $request Mage_Shipping_Model_Rate_Request */
        $request = Mage::getModel('shipping/rate_request');
        $request->setAllItems($items);
        $request->setDestCountryId($shippingDestinationInfo->getCountryId());
        $request->setDestRegionId($shippingDestinationInfo->getRegionId());
        $request->setDestRegionCode($shippingDestinationInfo->getRegionId());
        $request->setDestStreet($shippingDestinationInfo->getStreet(-1));
        $request->setDestCity($shippingDestinationInfo->getCity());
        $request->setDestPostcode($shippingDestinationInfo->getPostcode());
        $request->setDestCompanyName($shippingDestinationInfo->getCompany());

        $request->setPackageValue($subtotal);
        $request->setPackageValueWithDiscount($subtotal);
        $request->setPackageWeight($weight);
        $request->setPackageQty($qty);

        //shop destination address data
        //different carriers use different variables. So we duplicate them
        $request
            ->setOrigCountryId($address->getCountryId())
            ->setOrigCountry($address->getCountryId())
            ->setOrigState($address->getRegionId())
            ->setOrigRegionCode($address->getRegionId())
            ->setOrigCity($address->getCity())
            ->setOrigPostcode($address->getPostcode())
            ->setOrigPostal($address->getPostcode())
            ->setOrigCompanyName($address->getCompany() ? $address->getCompany() : 'NA')
            ->setOrig(true);

        /**
         * Need for shipping methods that use insurance based on price of physical products
         */
        $request->setPackagePhysicalValue($subtotal);

        $request->setFreeMethodWeight(0);

        /**
         * Store and website identifiers need specify from quote
         */
        $request->setStoreId($store->getId());
        $request->setWebsiteId($store->getWebsiteId());
        /**
         * Currencies need to convert in free shipping
         */
        $request->setBaseCurrency($store->getBaseCurrency());
        $request->setPackageCurrency($store->getCurrentCurrency());

        /*
         * For international shipments we must set customs value larger than zero
         * This number is being taken from items' prices
         * But for the case when we try to return bundle items from fixed-price bundle,
         * we have no items' prices. We should add this customs value manually
         */
        if (($request->getOrigCountryId() !== $request->getDestCountryId()) && ($request->getPackageValue() < 1)) {
            $request->setPackageCustomsValue(1);
        }

        $request->setIsReturn(true);

        /** @var $result Mage_Shipping_Model_Shipping */
        $result = Mage::getModel('shipping/shipping')
            ->setCarrierAvailabilityConfigField('active_rma')
            ->collectRates($request)
            ->getResult();

        $found = false;
        if ($result) {
            $shippingRates = $result->getAllRates();

            foreach ($shippingRates as $shippingRate) {
                if (
                    in_array(
                        $shippingRate->getCarrier(),
                        array_keys(Mage::helper('enterprise_rma')->getShippingCarriers())
                    )
                ) {
                    $found[] = Mage::getModel('sales/quote_address_rate')->importShippingRate($shippingRate);
                }
            }
        }
        return $found;
    }

    /**
     * Get collection of tracking on this RMA
     *
     * @return Enterprise_Rma_Model_Resource_Shipping_Collection
     */
    public function getTrackingNumbers()
    {
        if (is_null($this->_trackingNumbers)) {
            $this->_trackingNumbers = Mage::getModel('enterprise_rma/shipping')
            ->getCollection()
            ->addFieldToFilter('rma_entity_id', $this->getEntityId())
            ->addFieldToFilter('is_admin', array('neq' => Enterprise_Rma_Model_Shipping::IS_ADMIN_STATUS_ADMIN_LABEL));
        }
        return $this->_trackingNumbers;
    }

    /**
     * Get shipping label RMA
     *
     * @return Enterprise_Rma_Model_Shipping
     */
    public function getShippingLabel()
    {
        if (is_null($this->_shippingLabel)) {
            $this->_shippingLabel = Mage::getModel('enterprise_rma/shipping')
            ->getCollection()
            ->addFieldToFilter('rma_entity_id', $this->getEntityId())
            ->addFieldToFilter('is_admin', Enterprise_Rma_Model_Shipping::IS_ADMIN_STATUS_ADMIN_LABEL)
            ->getFirstItem();
        }
        return $this->_shippingLabel;
    }

    /**
     * Defines whether RMA status and RMA Items statuses allow to create shipping label
     *
     * @return bool
     */
    public function isAvailableForPrintLabel()
    {
        return (bool)($this->_isRmaAvailableForPrintLabel() && $this->_isItemsAvailableForPrintLabel());
    }

    /**
     * Defines whether RMA status allow to create shipping label
     *
     * @return bool
     */
    protected function _isRmaAvailableForPrintLabel()
    {
        return ($this->getStatus() !== Enterprise_Rma_Model_Rma_Source_Status::STATE_CLOSED)
            && ($this->getStatus() !== Enterprise_Rma_Model_Rma_Source_Status::STATE_PROCESSED_CLOSED)
            && ($this->getStatus() !== Enterprise_Rma_Model_Rma_Source_Status::STATE_PENDING);
    }

    /**
     * Defines whether RMA items' statuses allow to create shipping label
     *
     * @return bool
     */
    protected function _isItemsAvailableForPrintLabel()
    {
        $collection = Mage::getResourceModel('enterprise_rma/item_collection')
            ->addFieldToFilter('rma_entity_id', $this->getEntityId());

        $return = false;
        foreach ($collection as $item) {
            if (!in_array($item->getStatus(),
                array(
                    Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_AUTHORIZED,
                    Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_DENIED,
                ), true)
            ) {
                return false;
            }
            if (($item->getStatus() === Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_AUTHORIZED)
                && is_numeric($item->getQtyAuthorized())
                && $item->getQtyAuthorized() > 0
            ) {
                $return = true;
            }
        }
        return $return;
    }

    /**
     * Get collection of RMA Items with common order rules to be displayed in different lists
     *
     * @param bool $withoutAttributes - sets whether add EAV attributes into select
     * @return Enterprise_Rma_Model_Resource_Item_Collection
     */
    public function getItemsForDisplay($withoutAttributes = false)
    {
        $collection = Mage::getResourceModel('enterprise_rma/item_collection')
            ->addFieldToFilter('rma_entity_id', $this->getEntityId())
            ->setOrder('order_item_id')
            ->setOrder('entity_id');

        if (!$withoutAttributes) {
            $collection->addAttributeToSelect('*');
        }
        return $collection;
    }

    /**
     * Get button disabled status
     *
     * @return bool
     */
    public function getButtonDisabledStatus()
    {
        return (bool)(
            Mage::getModel('enterprise_rma/rma_source_status')->getButtonDisabledStatus($this->getStatus())
            && $this->_isItemsNotInPendingStatus()
        );
    }

    /**
     * Defines whether RMA items' not in pending status
     *
     * @return bool
     */
    public function _isItemsNotInPendingStatus()
    {
        $collection = Mage::getResourceModel('enterprise_rma/item_collection')
            ->addFieldToFilter('rma_entity_id', $this->getEntityId());

        foreach ($collection as $item) {
            if ($item->getStatus() == Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_PENDING) {
                return false;
            }
        }
        return true;
    }
}
