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
 * RMA Item model
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Model_Item extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY            = 'rma_item';

    /**
     * Rma instance
     *
     * @var Enterprise_Rma_Model_Rma
     */
    protected $_rma         = null;

    /**
     * Store firstly set all item attributes
     *
     * @var array
     */
    protected $_attributes;

    /**
     * Rma item $_FILES collection
     *
     * @var array
     */
    protected $_filesArray  = array();

    /**
     * Rma item errors
     *
     * @var array
     */
    protected $_errors      = array();

    /**
     * Image url
     */
    const ITEM_IMAGE_URL    = 'rma_item';

    /**
     * Init resource model
     */
    protected function _construct() {
        $this->_init('enterprise_rma/item');
    }

    /**
     * Get all available item states
     *
     * @return array
     */
    public function getAllStates()
    {
        return Mage::getModel('enterprise_rma/item_attribute_source_state_status')->getAllOptions();
    }

    /**
     * Declare rma instance
     *
     * @param   Enterprise_Rma_Model_Rma $rma
     * @return  Enterprise_Rma_Model_Item
     */
    public function setRma(Enterprise_Rma_Model_Rma $rma)
    {
        $this->_rma = $rma;
        $this->setRmaEntityId($rma->getId());
        return $this;
    }

    /**
     * Retrieve rma instance
     *
     * @return Enterprise_Rma_Model_Rma
     */
    public function getRma()
    {
        $rmaId = $this->getRmaEntityId();
        if (is_null($this->_rma) && $rmaId) {
            $rma = Mage::getModel('enterprise_rma/rma');
            $rma->load($rmaId);
            $this->setRma($rma);
        }
        return $this->_rma;
    }

    /**
     * Get RMA item's status label
     *
     * @return mixed
     */
    public function getStatusLabel()
    {
        if (is_null(parent::getStatusLabel())){
            $this->setStatusLabel(
                Mage::getModel('enterprise_rma/item_attribute_source_status')->getItemLabel($this->getStatus())
            );
        }
        return parent::getStatusLabel();
    }

    /**
     * Prepare data before save
     *
     * @return Enterprise_Rma_Model_Item
     */
    protected function _beforeSave()
    {
        if (!$this->getRmaEntityId() && $this->getRma()) {
            $this->setRmaEntityId($this->getRma()->getId());
        }
        if ($this->getQtyAuthorized() === '') {
            $this->unsQtyAuthorized();
        }
        if ($this->getQtyReturned() === '') {
            $this->unsQtyReturned();
        }
        if ($this->getQtyApproved() === '') {
            $this->unsQtyApproved();
        }
        parent::_beforeSave();
    }

    /**
     * Prepare data before save
     *
     * @return Enterprise_Rma_Model_Item
     */
    protected function _afterSave()
    {
        $qtyReturnedChange = 0;
        if ($this->getOrigData('status') == Enterprise_Rma_Model_Rma_Source_Status::STATE_APPROVED) {
            if ($this->getStatus() == Enterprise_Rma_Model_Rma_Source_Status::STATE_APPROVED) {
                $qtyReturnedChange = $this->getQtyApproved() - $this->getOrigData('qty_approved');
            } else {
                $qtyReturnedChange = - $this->getOrigData('qty_approved');
            }
        } else {
            if ($this->getStatus() == Enterprise_Rma_Model_Rma_Source_Status::STATE_APPROVED) {
                $qtyReturnedChange = $this->getQtyApproved();
            }
        }

        if ($qtyReturnedChange) {
            $item = Mage::getModel('sales/order_item')->load($this->getOrderItemId());
            if ($item->getId()) {
                $item->setQtyReturned($item->getQtyReturned() + $qtyReturnedChange)
                    ->save();
            }
        }
        parent::_afterSave();
    }

    /**
     * Retrieve all item attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = $this->_getResource()
            ->loadAllAttributes($this)
            ->getSortedAttributes();
        }
        return $this->_attributes;
    }

    /**
     * Retrieve all item errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Get rma item attribute model object
     *
     * @param   string $attributeCode
     * @return  Enterprise_Rma_Model_Item_Attribute | null
     */
    public function getAttribute($attributeCode)
    {
        $this->getAttributes();
        if (isset($this->_attributes[$attributeCode])) {
            return $this->_attributes[$attributeCode];
        }
        return null;
    }

    /**
     * Prepares and adds $_POST data to item's attribute
     *
     * @param  array $itemPost
     * @param  int $key
     * @return array
     */
    public function prepareAttributes($itemPost, $key)
    {
        $httpRequest = new Zend_Controller_Request_Http();
        $httpRequest->setPost($itemPost);

        /** @var $itemForm Enterprise_Rma_Model_Item_Form */
        $itemForm = Mage::getModel('enterprise_rma/item_form');
        $itemForm->setFormCode('default')
            ->setEntity($this);

        $itemData = $itemForm->extractData($httpRequest);

        $files = array();
        foreach ($itemData as $code=>&$value) {
            if (is_array($value) && empty($value)) {
                if (array_key_exists($code.'_'.$key, $_FILES)) {
                    $value = $_FILES[$code.'_'.$key];
                    $files[] = $code;
                }
            }
        }

        $itemErrors = $itemForm->validateData($itemData);
        if ($itemErrors !== true) {
            $this->_errors = array_merge($itemErrors, $this->_errors);
        } else {
            $itemForm->compactData($itemData);
        }

        if (!empty($files)) {
            foreach ($files as $code) {
                unset($_FILES[$code.'_'.$key]);
            }
            return $files;
        }
    }

    /**
     * Gets item options
     *
     * @return array|bool
     */
    public function getOptions()
    {
        $result = array();
        $options = unserialize($this->getProductOptions());
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }

            return $result;
        }
        return false;
    }

}
