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
 * Product attributes condition combine
 */
class Enterprise_CustomerSegment_Model_Segment_Condition_Product_Combine
    extends Enterprise_CustomerSegment_Model_Condition_Combine_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_product_combine');
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $children = array_merge_recursive(
            parent::getNewChildSelectOptions(),
            array(
                array( // self
                    'value' => $this->getType(),
                    'label' => Mage::helper('rule')->__('Conditions Combination')
                )
            )
        );

        if ($this->getDateConditions()) {
            $children = array_merge_recursive(
                $children,
                array(
                    array(
                        'value' => array(
                            Mage::getModel('enterprise_customersegment/segment_condition_uptodate')->getNewChildSelectOptions(),
                            Mage::getModel('enterprise_customersegment/segment_condition_daterange')->getNewChildSelectOptions(),
                        ),
                        'label' => Mage::helper('enterprise_customersegment')->__('Date Ranges')
                    )
                )
            );
        }

        $children = array_merge_recursive(
            $children,
            array(
                Mage::getModel('enterprise_customersegment/segment_condition_product_attributes')->getNewChildSelectOptions()
            )
        );

        return $children;
    }

    /**
     * Combine not present his own SQL condition
     *
     * @param $customer
     * @param $website
     * @return false
     */
    public function getConditionsSql($customer, $website)
    {
        return false;
    }

    /**
     * Get combine subfilter type
     *
     * @return string
     */
    public function getSubfilterType()
    {
        return 'product';
    }

    /**
     * Apply product attribute subfilter to parent/base condition query
     *
     * @param string $fieldName base query field name
     * @param bool $requireValid strict validation flag
     * @param $website
     * @return string
     */
        public function getSubfilterSql($fieldName, $requireValid, $website)
    {
        $table = $this->getResource()->getTable('catalog/product');

        $select = $this->getResource()->createSelect();
        $select->from(array('main'=>$table), array('entity_id'));

        if ($this->getAggregator() == 'all') {
            $whereFunction = 'where';
        } else {
            $whereFunction = 'orWhere';
        }

        $gotConditions = false;
        foreach ($this->getConditions() as $condition) {
            if ($condition->getSubfilterType() == 'product') {
                $subfilter = $condition->getSubfilterSql('main.entity_id', ($this->getValue() == 1), $website);
                if ($subfilter) {
                    $select->$whereFunction($subfilter);
                    $gotConditions = true;
                }
            }
        }
        if (!$gotConditions) {
            $select->where('1=1');
        }

        $inOperator = ($requireValid ? 'IN' : 'NOT IN');
        return sprintf("%s %s (%s)", $fieldName, $inOperator, $select);
    }
}
