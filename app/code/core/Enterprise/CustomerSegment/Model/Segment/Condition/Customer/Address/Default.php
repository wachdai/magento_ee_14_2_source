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
 * @package     Enterprise_CustomerSegment
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Customer address type selector
 */
class Enterprise_CustomerSegment_Model_Segment_Condition_Customer_Address_Default
    extends Enterprise_CustomerSegment_Model_Condition_Abstract
{
    protected $_inputType = 'select';

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_customer_address_default');
        $this->setValue('default_billing');
    }

    /**
     * Get array of event names where segment with such conditions combine can be matched
     *
     * @return array
     */
    public function getMatchedEvents()
    {
        return array(
            'customer_address_save_commit_after',
            'customer_save_commit_after',
            'customer_address_delete_commit_after'
        );
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
        public function getNewChildSelectOptions()
    {
        return array(
            'value' => $this->getType(),
            'label' => Mage::helper('enterprise_customersegment')->__('Default Address')
        );
    }

    /**
     * Init list of available values
     *
     * @return array
     */
    public function loadValueOptions()
    {
        $this->setValueOption(array(
            'default_billing'  => Mage::helper('enterprise_customersegment')->__('Billing'),
            'default_shipping' => Mage::helper('enterprise_customersegment')->__('Shipping'),
        ));
        return $this;
    }

    /**
     * Get element type for value select
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * Get HTML of condition string
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . Mage::helper('enterprise_customersegment')->__('Customer Address %s Default %s Address', $this->getOperatorElementHtml(), $this->getValueElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }

    /**
     * Prepare is default billing/shipping condition for customer address
     *
     * @param $customer
     * @param $website
     * @return Varien_Db_Select
     */
    public function getConditionsSql($customer, $website)
    {
        $select = $this->getResource()->createSelect();
        $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', $this->getValue());
        $select->from(array('default'=>$attribute->getBackendTable()), array(new Zend_Db_Expr(1)));

        $select->where('default.attribute_id = ?', $attribute->getId())
            ->where('default.value=customer_address.entity_id')
            ->where($this->_createCustomerFilter($customer, 'default.entity_id'));

        Mage::getResourceHelper('enterprise_customersegment')->setOneRowLimit($select);

        return $select;
    }
}
