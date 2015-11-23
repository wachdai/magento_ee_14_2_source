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
 * @category    Mage
 * @package     Mage_ConfigurableSwatches
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class Mage_ConfigurableSwatches_Model_System_Config_Source_Catalog_Product_Configattribute
{
    /**
     * Attributes array
     *
     * @var null|array
     */
    protected $_attributes = null;


    /**
     * Retrieve attributes as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (is_null($this->_attributes)) {
            $attrCollection = Mage::getResourceModel('catalog/product_attribute_collection')
                ->addVisibleFilter()
                ->setFrontendInputTypeFilter('select')
                ->addFieldToFilter('additional_table.is_configurable', 1)
                ->addFieldToFilter('additional_table.is_visible', 1)
                ->addFieldToFilter('main_table.is_user_defined', 1)
                ->setOrder('frontend_label', Varien_Data_Collection::SORT_ORDER_ASC);

            $this->_attributes = array();
            foreach ($attrCollection as $attribute) {
                $this->_attributes[] = array(
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $attribute->getId(),
                );
            }
        }
        return $this->_attributes;
    }
}
