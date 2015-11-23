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
 * Order address condition
 */
class Enterprise_CustomerSegment_Model_Segment_Condition_Order_Address
    extends Enterprise_CustomerSegment_Model_Condition_Combine_Abstract
{
    protected $_inputType = 'select';

    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_order_address');
    }

    /**
     * Get array of event names where segment with such conditions combine can be matched
     *
     * @return array
     */
    public function getMatchedEvents()
    {
        return array('sales_order_save_commit_after');
    }

    /**
     * Get List of available selections inside this combine
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        return Mage::getModel('enterprise_customersegment/segment_condition_order_address_combine')
            ->getNewChildSelectOptions();
    }

    /**
     * Get html of order address combine
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . Mage::helper('enterprise_customersegment')->__('If Order Addresses match %s of these Conditions:', $this->getAggregatorElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }

    /**
     * Order address combine doesn't have declared value. We use "1" for it
     *
     * @return int
     */
    public function getValue()
    {
        return 1;
    }

    /**
     * Prepare base condition select which related with current condition combine
     *
     * @param $customer
     * @param int | Zend_Db_Expr $website
     * @return Varien_Db_Select
     */
    protected function _prepareConditionsSql($customer, $website)
    {
        $resource = $this->getResource();
        $select = $resource->createSelect();

        $mainAddressTable   = $this->getResource()->getTable('sales/order_address');
        $extraAddressTable  = $this->getResource()->getTable('enterprise_customer/sales_order_address');
        $orderTable         = $this->getResource()->getTable('sales/order');

        $select
            ->from(array('order_address' => $mainAddressTable), array(new Zend_Db_Expr(1)))
            ->join(
                array('order_address_order' => $orderTable),
                'order_address.parent_id = order_address_order.entity_id',
                array())
            ->joinLeft(
                array('extra_order_address' => $extraAddressTable),
                'order_address.entity_id = extra_order_address.entity_id',
                array())
            ->where($this->_createCustomerFilter($customer, 'order_address_order.customer_id'));

        Mage::getResourceHelper('enterprise_customersegment')->setOneRowLimit($select);

        $this->_limitByStoreWebsite($select, $website, 'order_address_order.store_id');

        return $select;
    }

    /**
     * Order address is joined to base query. We are applying address type condition as subfilter for main query
     *
     * @return array
     */
    protected function _getSubfilterMap()
    {
        return array(
            'order_address_type' => 'order_address_type.value',
        );
    }
}
