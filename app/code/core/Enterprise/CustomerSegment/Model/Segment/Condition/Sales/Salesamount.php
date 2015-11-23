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
 * Orders amount condition
 */
class Enterprise_CustomerSegment_Model_Segment_Condition_Sales_Salesamount
    extends Enterprise_CustomerSegment_Model_Segment_Condition_Sales_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_sales_salesamount');
        $this->setValue(null);
    }

    /**
     * Set data with filtering
     *
     * @param mixed $key
     * @param mixed $value
     * @return Enterprise_CustomerSegment_Model_Segment_Condition_Sales_Salesamount
     */
    public function setData($key, $value = null)
    {
        //filter key "value"
        if (is_array($key) && isset($key['value']) && $key['value'] !== null) {
            $key['value'] = (float) $key['value'];
        } elseif ($key == 'value' && $value !== null) {
            $value = (float) $value;
        }

        return parent::setData($key, $value);
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
     * Get HTML of condition string
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . Mage::helper('enterprise_customersegment')->__('%s Sales Amount %s %s while %s of these Conditions match:', $this->getAttributeElementHtml(), $this->getOperatorElementHtml(), $this->getValueElementHtml(), $this->getAggregatorElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }

    /**
     * Build query for matching orders amount
     *
     * @param $customer
     * @param int | Zend_Db_Expr $website
     * @return Varien_Db_Select
     */
    protected function _prepareConditionsSql($customer, $website)
    {
        $select = $this->getResource()->createSelect();

        $operator = $this->getResource()->getSqlOperator($this->getOperator());
        $aggrFunc = ($this->getAttribute() == 'total') ? 'SUM' : 'AVG';
        $adapter = $this->getResource()->getReadConnection();
        $firstIf = $adapter->getCheckSql($aggrFunc . '(sales_order.base_grand_total) IS NOT NULL',
            $aggrFunc . '(sales_order.base_grand_total)', 0);
        $value = (float) $this->getValue();
        $result = $adapter->getCheckSql($firstIf . ' ' . $operator . ' ' . $value, 1, 0);

        $select->from(
            array('sales_order' => $this->getResource()->getTable('sales/order')),
            array(new Zend_Db_Expr($result))
        );
        $this->_limitByStoreWebsite($select, $website, 'sales_order.store_id');
        $select->where($this->_createCustomerFilter($customer, 'sales_order.customer_id'));
        Mage::getResourceHelper('enterprise_customersegment')->setOneRowLimit($select);
        return $select;
    }

    /**
     * Reset setValueOption() to prevent displaying incorrect actual values
     *
     * @return Enterprise_CustomerSegment_Model_Segment_Condition_Sales_Salesamount
     */
    public function loadValueOptions()
    {
        $this->setValueOption(array());
        return $this;
    }
}
