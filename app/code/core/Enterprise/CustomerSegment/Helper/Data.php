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

class Enterprise_CustomerSegment_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Check whether customer segment functionality should be enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)Mage::getStoreConfig('customer/enterprise_customersegment/is_enabled');
    }

    /**
     * Retrieve options array for customer segment view mode
     *
     * @return array
     */
    public function getOptionsArray()
    {
        return array(
            array(
                'label' => '',
                'value' => ''
            ),
            array(
                'label' => Mage::helper('enterprise_customersegment')->__('Union'),
                'value' => Enterprise_CustomerSegment_Model_Segment::VIEW_MODE_UNION_CODE
            ),
            array(
                'label' => Mage::helper('enterprise_customersegment')->__('Intersection'),
                'value' => Enterprise_CustomerSegment_Model_Segment::VIEW_MODE_INTERSECT_CODE
            )
        );
    }

    /**
     * Return translated Label for option by specified option code
     *
     * @param string $code Option code
     * @return string
     */
    public function getViewModeLabel($code)
    {
        foreach ($this->getOptionsArray() as $option) {
            if (isset($option['label']) && isset($option['value']) && $option['value'] == $code) {
                return $option['label'];
            }
        }
        return '';
    }
}
