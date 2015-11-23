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
 * @package     Enterprise_Reminder
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Abstract class for combine rule condition
 */
abstract class Enterprise_Reminder_Model_Condition_Combine_Abstract extends Mage_Rule_Model_Condition_Combine
{
    /**
     * Customize default operator input by type mapper for some types
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            $this->_defaultOperatorInputByType['numeric'] = array('==', '!=', '>=', '>', '<=', '<');
            $this->_defaultOperatorInputByType['string'] = array('==', '!=', '{}', '!{}');
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * Add operator when loading array
     *
     * @param array $arr
     * @param string $key
     * @return Enterprise_Reminder_Model_Rule_Condition_Combine
     */
    public function loadArray($arr, $key = 'conditions')
    {
        if (isset($arr['operator'])) {
            $this->setOperator($arr['operator']);
        }

        if (isset($arr['attribute'])) {
            $this->setAttribute($arr['attribute']);
        }

        return parent::loadArray($arr, $key);
    }

    /**
     * Get condition combine resource model
     *
     * @return Enterprise_Reminder_Model_Mysql4_Rule
     */
    public function getResource()
    {
        return Mage::getResourceSingleton('enterprise_reminder/rule');
    }

    /**
     * Get filter by customer condition for rule matching sql
     *
     * @param   int|Zend_Db_Expr $customer
     * @param   string $fieldName
     * @return  string
     */
    protected function _createCustomerFilter($customer, $fieldName)
    {
        return "{$fieldName} = root.entity_id";
    }

    /**
     * Build query for matching customer to rule condition
     *
     * @param $customer
     * @param $website
     * @return Varien_Db_Select
     */
    protected function _prepareConditionsSql($customer, $website)
    {
        $select = $this->getResource()->createSelect();
        $table = $this->getResource()->getTable('customer/entity');
        $select->from($table, array(new Zend_Db_Expr(1)));
        $select->where($this->_createCustomerFilter($customer, 'entity_id'));
        return $select;
    }

    /**
     * Check if condition is required. It affect condition select result comparison type (= || <>)
     *
     * @return bool
     */
    protected function _getRequiredValidation()
    {
        return ($this->getValue() == 1);
    }

    /**
     * Get SQL select for matching customer to rule condition
     *
     * @param $customer
     * @param $website
     * @return Varien_Db_Select
     */
    public function getConditionsSql($customer, $website)
    {
        /**
         * Build base SQL
         */
        $select         = $this->_prepareConditionsSql($customer, $website);
        $required       = $this->_getRequiredValidation();
        $whereFunction  = ($this->getAggregator() == 'all') ? 'where' : 'orWhere';
        $operator       = $required ? '=' : '<>';
        //$operator       = '=';

        $gotConditions = false;

        /**
         * Add children subselects conditions
         */
        foreach ($this->getConditions() as $condition) {
            if ($sql = $condition->getConditionsSql($customer, $website)) {
                $criteriaSql = "(". $select->getAdapter()->getIfNullSql("(" . $sql . ")", 0) . " {$operator} 1)";
                $select->$whereFunction($criteriaSql);
                $gotConditions = true;
            }
        }

        /**
         * Process combine subfilters. Subfilters are part of base select which can be affected by children.
         */
        $subfilterMap = $this->_getSubfilterMap();
        if ($subfilterMap) {
            foreach ($this->getConditions() as $condition) {
                $subfilterType = $condition->getSubfilterType();
                if (isset($subfilterMap[$subfilterType])) {
                    $subfilter = $condition->getSubfilterSql($subfilterMap[$subfilterType], $required, $website);
                    if ($subfilter) {
                        $select->$whereFunction($subfilter);
                        $gotConditions = true;
                    }
                }
            }
        }

        if (!$gotConditions) {
            $select->where('1=1');
        }

        return $select;
    }

    /**
     * Get infromation about subfilters map. Map contain children condition type and associated
     * column name from itself select.
     * Example: array('my_subtype'=>'my_table.my_column')
     * In practice - date range can be as subfilter for different types of condition combines.
     * Logic of this filter apply is same - but column names different
     *
     * @return array
     */
    protected function _getSubfilterMap()
    {
        return array();
    }

    /**
     * Limit select by website with joining to store table
     *
     * @param   Zend_Db_Select $select
     * @param   int|Zend_Db_Expr $website
     * @param   string $storeIdField
     * @return  Enterprise_Reminder_Model_Condition_Abstract
     */
    protected function _limitByStoreWebsite(Zend_Db_Select $select, $website, $storeIdField)
    {
        $storeTable = $this->getResource()->getTable('core/store');
        $select->join(array('store' => $storeTable), $storeIdField . '=store.store_id', array())
            ->where('store.website_id=?', $website);
        return $this;
    }
}
