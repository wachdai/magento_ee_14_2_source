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
 * @package     Enterprise_TargetRule
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * TargetRule data helper
 *
 * @category   Enterprise
 * @package    Enterprise_TargetRule
 */
class Enterprise_TargetRule_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_TARGETRULE_CONFIG    = 'catalog/enterprise_targetrule/';

    const MAX_PRODUCT_LIST_RESULT       = 20;

    /**
     * Retrieve Maximum Number of Products in Product List
     *
     * @param int $type product list type
     * @throws Mage_Core_Exception
     * @return int
     */
    public function getMaximumNumberOfProduct($type)
    {
        switch ($type) {
            case Enterprise_TargetRule_Model_Rule::RELATED_PRODUCTS:
                $number = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'related_position_limit');
                break;
            case Enterprise_TargetRule_Model_Rule::UP_SELLS:
                $number = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'upsell_position_limit');
                break;
            case Enterprise_TargetRule_Model_Rule::CROSS_SELLS:
                $number = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'crosssell_position_limit');
                break;
            default:
                Mage::throwException(Mage::helper('enterprise_targetrule')->__('Invalid product list type'));
        }

        return $this->getMaxProductsListResult($number);
    }

    /**
     * Show Related/Upsell/Cross-Sell Products behavior
     *
     * @param int $type
     * @throws Mage_Core_Exception
     * @return int
     */
    public function getShowProducts($type)
    {
        switch ($type) {
            case Enterprise_TargetRule_Model_Rule::RELATED_PRODUCTS:
                $show = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'related_position_behavior');
                break;
            case Enterprise_TargetRule_Model_Rule::UP_SELLS:
                $show = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'upsell_position_behavior');
                break;
            case Enterprise_TargetRule_Model_Rule::CROSS_SELLS:
                $show = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'crosssell_position_behavior');
                break;
            default:
                Mage::throwException(Mage::helper('enterprise_targetrule')->__('Invalid product list type'));
        }

        return $show;
    }

    /**
     * Retrieve maximum number of products can be displayed in product list
     *
     * if number is 0 (unlimited) or great global maximum return global maximum value
     *
     * @param int $number
     * @return int
     */
    public function getMaxProductsListResult($number = 0)
    {
        $number = abs((int)$number);
        if ($number == 0 || $number > self::MAX_PRODUCT_LIST_RESULT) {
            $number = self::MAX_PRODUCT_LIST_RESULT;
        }
        return $number;
    }

    /**
     * Retrieve Rotation Mode in Product List
     *
     * @param int $type product list type
     * @throws Mage_Core_Exception
     * @return int
     */
    public function getRotationMode($type)
    {
        switch ($type) {
            case Enterprise_TargetRule_Model_Rule::RELATED_PRODUCTS:
                $mode = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'related_rotation_mode');
                break;
            case Enterprise_TargetRule_Model_Rule::UP_SELLS:
                $mode = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'upsell_rotation_mode');
                break;
            case Enterprise_TargetRule_Model_Rule::CROSS_SELLS:
                $mode = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'crosssell_rotation_mode');
                break;
            default:
                Mage::throwException(Mage::helper('enterprise_targetrule')->__('Invalid rotation mode type'));
        }
        return $mode;
    }
}
