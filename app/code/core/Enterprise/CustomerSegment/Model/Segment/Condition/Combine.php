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
 * Segment conditions container
 *
 * @category    Enterprise
 * @package     Enterprise_CustomerSegment
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CustomerSegment_Model_Segment_Condition_Combine
    extends Enterprise_CustomerSegment_Model_Condition_Combine_Abstract
{
    /**
     * Initialize model
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_combine');
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = array(
            array(
                // Subconditions combo
                'value' => 'enterprise_customersegment/segment_condition_combine',
                'label' => Mage::helper('enterprise_customersegment')->__('Conditions Combination'),
                'available_in_guest_mode' => true
            ),
            array(
                // Customer address combo
                'value' => 'enterprise_customersegment/segment_condition_customer_address',
                'label' => Mage::helper('enterprise_customersegment')->__('Customer Address')
            ),
            // Customer attribute group
            Mage::getModel('enterprise_customersegment/segment_condition_customer')->getNewChildSelectOptions(),

            // Shopping cart group
            Mage::getModel('enterprise_customersegment/segment_condition_shoppingcart')->getNewChildSelectOptions(),

            array(
                'value' => array(
                    array(
                        // Product list combo
                        'value' => 'enterprise_customersegment/segment_condition_product_combine_list',
                        'label' => Mage::helper('enterprise_customersegment')->__('Product List'),
                        'available_in_guest_mode' => true
                    ),
                    array(
                        // Product history combo
                        'value' => 'enterprise_customersegment/segment_condition_product_combine_history',
                        'label' => Mage::helper('enterprise_customersegment')->__('Product History'),
                        'available_in_guest_mode' => true
                    )
                ),
                'label' => Mage::helper('enterprise_customersegment')->__('Products'),
                'available_in_guest_mode' => true
            ),

            // Sales group
            Mage::getModel('enterprise_customersegment/segment_condition_sales')->getNewChildSelectOptions(),
        );
        $conditions = array_merge_recursive(parent::getNewChildSelectOptions(), $conditions);
        return $this->_prepareConditionAccordingApplyToValue($conditions);
    }

    /**
     * Prepare base condition select which related with current condition combine
     *
     * @param $customer
     * @param $website
     * @return Varien_Db_Select
     */
    protected function _prepareConditionsSql($customer, $website)
    {
        $select = parent::_prepareConditionsSql($customer, $website);
        $select->limit(1);
        return $select;
    }

    /**
     * Prepare Condition According to ApplyTo Value
     *
     * @param array $conditions
     * @return array
     */
    protected function _prepareConditionAccordingApplyToValue(array $conditions)
    {
        $returnedConditions = null;
        switch ($this->getRule()->getApplyTo()) {
            case Enterprise_CustomerSegment_Model_Segment::APPLY_TO_VISITORS:
                $returnedConditions = $this->_removeUnnecessaryConditions($conditions);
                break;

            case Enterprise_CustomerSegment_Model_Segment::APPLY_TO_VISITORS_AND_REGISTERED:
                $returnedConditions = $this->_markConditions($conditions);
                break;

            case Enterprise_CustomerSegment_Model_Segment::APPLY_TO_REGISTERED:
                $returnedConditions = $conditions;
                break;

            default:
                Mage::throwException(Mage::helper('enterprise_customersegment')->__('Wrong "ApplyTo" type'));
                break;
        }
        return $returnedConditions;
    }


    /**
     * Remove unnecessary conditions
     *
     * @param array $conditionsList
     * @return array
     */
    protected function _removeUnnecessaryConditions(array $conditionsList)
    {
        $conditionResult = $conditionsList;
        foreach ($conditionResult as $key => $condition) {
            if ($key == 0 && isset($condition['value']) && $condition['value'] == '') {
                continue;
            }
            if (array_key_exists('available_in_guest_mode', $condition) && $condition['available_in_guest_mode']) {
                if (is_array($conditionResult[$key]['value'])) {
                    $conditionResult[$key]['value'] = $this->_removeUnnecessaryConditions($condition['value']);
                }
            } else {
                unset($conditionResult[$key]);
            }
        }
        return $conditionResult;
    }


    /**
     * Mark condition with asterisk
     *
     * @param array $conditionsList
     * @return array
     */
    protected function _markConditions(array $conditionsList)
    {
        $conditionResult = $conditionsList;
        foreach ($conditionResult as $key => $condition) {
            if (array_key_exists('available_in_guest_mode', $condition) && $condition['available_in_guest_mode']) {
                $conditionResult[$key]['label'] .= '*';
                if (is_array($conditionResult[$key]['value'])) {
                    $conditionResult[$key]['value'] = $this->_markConditions($condition['value']);
                }
            }
        }
        return $conditionResult;
    }
}
