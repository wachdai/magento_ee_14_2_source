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
 * Orders conditions options group
 */
class Enterprise_CustomerSegment_Model_Segment_Condition_Sales
    extends Enterprise_CustomerSegment_Model_Condition_Abstract
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_sales');
        $this->setValue(null);
    }

    /**
     * Get condition "selectors"
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        return array(
            'value' => array(
                array( // order address combo
                    'value' => 'enterprise_customersegment/segment_condition_order_address',
                    'label' => Mage::helper('enterprise_customersegment')->__('Order Address')),
                array(
                    'value' => 'enterprise_customersegment/segment_condition_sales_salesamount',
                    'label' => Mage::helper('enterprise_customersegment')->__('Sales Amount')),
                array(
                    'value' => 'enterprise_customersegment/segment_condition_sales_ordersnumber',
                    'label' => Mage::helper('enterprise_customersegment')->__('Number of Orders')),
                array(
                    'value' => 'enterprise_customersegment/segment_condition_sales_purchasedquantity',
                    'label' => Mage::helper('enterprise_customersegment')->__('Purchased Quantity')),
             ),
            'label' => Mage::helper('enterprise_customersegment')->__('Sales')
        );
    }
}
