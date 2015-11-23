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
 * @package     Enterprise_GiftCard
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_GiftCard_Model_Source_Open extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $result = array();
        foreach ($this->_getValues() as $k => $v) {
            $result[] = array(
                'value' => $k,
                'label' => $v,
            );
        }

        return $result;
    }

    /**
     * Get option text
     *
     * @return string|null
     */
    public function getOptionText($value)
    {
        $options = $this->_getValues();
        if (isset($options[$value])) {
            return $options[$value];
        }
        return null;
    }

    /**
     * Get values
     *
     * @return array
     */
    protected function _getValues()
    {
        return array(
            Enterprise_GiftCard_Model_Giftcard::OPEN_AMOUNT_DISABLED => Mage::helper('enterprise_giftcard')->__('No'),
            Enterprise_GiftCard_Model_Giftcard::OPEN_AMOUNT_ENABLED  => Mage::helper('enterprise_giftcard')->__('Yes'),
        );
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColums()
    {
        $attributeDefaultValue = $this->getAttribute()->getDefaultValue();
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $attributeType = $this->getAttribute()->getBackendType();
        $isNullable = is_null($attributeDefaultValue) || empty($attributeDefaultValue);

        $column = array(
            'unsigned' => false,
            'extra'    => null,
            'default'  => $isNullable ? null : $attributeDefaultValue
        );

        if (Mage::helper('core')->useDbCompatibleMode()) {
            $column['type']     = $attributeType;
            $column['is_null']  = $isNullable;
        } else {
            $column['type']     = Mage::getResourceHelper('eav')->getDdlTypeByColumnType($attributeType);
            $column['nullable'] = $isNullable;
            $column['comment']  = 'Enterprise Giftcard Open ' . $attributeCode . ' column';
        }

        return array($attributeCode => $column);
    }

    /**
     * Retrieve select for flat attribute update
     *
     * @param int $store
     * @return Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('eav/entity_attribute')
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
