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
 * RMA Item Status Manager
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Model_Item_Status extends Varien_Object
{
    /**
     * Artificial "maximal" item status when whole order is closed
     */
    const STATUS_ORDER_IS_CLOSED = 'order_is_closed';

    /**
     * Artificial "minimal" item status when all allowed fields are editable
     */
    const STATUS_ALL_ARE_EDITABLE = 'all_are_editable';

    /**
     * Flag for artificial statuses
     *
     * @var bool
     */
    protected $_isSpecialStatus = false;

    /**
     * Get options array for display in grid, consisting only from allowed statuses
     *
     * @return array
     */
    public function getAllowedStatuses()
    {
        $statusesAllowed = array(
            Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_PENDING => array(
                Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_PENDING,
                Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_AUTHORIZED,
                Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_DENIED
            ),
            Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_AUTHORIZED => array(
                Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_AUTHORIZED,
                Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_RECEIVED
            ),
            Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_RECEIVED => array(
                Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_RECEIVED,
                Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_APPROVED,
                Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_REJECTED
            ),
            Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_APPROVED => array(
                Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_APPROVED
            ),
            Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_REJECTED => array(
                Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_REJECTED
            ),
            Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_DENIED => array(
                Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_DENIED
            ),
        );
        $boundingArray = isset($statusesAllowed[$this->getStatus()])
            ? $statusesAllowed[$this->getStatus()]
            : array();
        return
            array_intersect_key(
                Mage::getSingleton('enterprise_rma/item_attribute_source_status')->getAllOptionsForGrid(),
                array_flip($boundingArray)
            );
    }

    /**
     * Get item status sequence - linear order on item statuses set
     *
     * @return array
     */
    protected function _getStatusSequence()
    {
        return array(
            self::STATUS_ALL_ARE_EDITABLE,
            Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_PENDING,
            Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_AUTHORIZED,
            Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_RECEIVED,
            Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_APPROVED,
            Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_REJECTED,
            Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_DENIED,
            self::STATUS_ORDER_IS_CLOSED,
        );
    }

    /**
     * Get Border status for each attribute.
     *
     * For statuses, "less" than border status, attribute becomes uneditable
     * For statuses, "equal or greater" than border status, attribute becomes editable
     *
     * @param  $attribute
     * @return string
     */
    public function getBorderStatus($attribute)
    {
        switch ($attribute) {
            case 'qty_requested':
                return Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_PENDING;
                break;
            case 'qty_authorized':
                return Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_AUTHORIZED;
                break;
            case 'qty_returned':
                return Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_RECEIVED;
                break;
            case 'qty_approved':
                return Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_APPROVED;
                break;
            case 'reason':
                return Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_PENDING;
                break;
            case 'condition':
                return Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_PENDING;
                break;
            case 'resolution':
                return Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_APPROVED;
                break;
            case 'status':
                return Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_APPROVED;
                break;
            case 'action':
                return self::STATUS_ORDER_IS_CLOSED;
                break;
            default:
                return Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_PENDING;
                break;
        }
    }

    /**
     * Get whether attribute is editable
     *
     * @param string $attribute
     * @return bool
     */
    public function getAttributeIsEditable($attribute)
    {
        $typeSequence = $this->_getStatusSequence();
        $itemStateKey = array_search($this->getSequenceStatus(), $typeSequence);
        if ($itemStateKey === false) {
            return false;
        }

        if (array_search($this->getBorderStatus($attribute), $typeSequence) > $itemStateKey){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get whether editable attribute is disabled
     *
     * @param string $attribute
     * @return bool
     */
    public function getAttributeIsDisabled($attribute)
    {
        if($this->getSequenceStatus() == self::STATUS_ALL_ARE_EDITABLE) {
            return false;
        }

        switch ($attribute) {
            case 'qty_authorized':
                $enabledStatus = Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_PENDING;
                break;
            case 'qty_returned':
                $enabledStatus = Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_AUTHORIZED;
                break;
            case 'qty_approved':
                $enabledStatus = Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_RECEIVED;
                break;
            default:
                return false;
                break;
        }

        if ($enabledStatus == $this->getSequenceStatus()){
            return false;
        } else {
            return true;
        }
    }

    /**
     * Sets "maximal" status for closed orders
     *
     * For closed orders no attributes should be editable.
     * So this method sets item status to artificial "maximum" value
     *
     * @return void
     */
    public function setOrderIsClosed()
    {
        $this->setSequenceStatus(self::STATUS_ORDER_IS_CLOSED);
        $this->_isSpecialStatus = true;
    }

    /**
     * Sets "minimal" status
     *
     * For split line functionality all fields must be editable
     *
     * @return void
     */
    public function setAllEditable()
    {
        $this->setSequenceStatus(self::STATUS_ALL_ARE_EDITABLE);
        $this->_isSpecialStatus = true;
    }

    /**
     * Sets status to object but not for self::STATUS_ORDER_IS_CLOSED status
     *
     * @param  $status
     * @return Enterprise_Rma_Model_Item_Status
     */
    public function setStatus($status)
    {
        if (!$this->getSequenceStatus() || !$this->_isSpecialStatus) {
            $this->setSequenceStatus($status);
        }
        return parent::setStatus($status);
    }

}
