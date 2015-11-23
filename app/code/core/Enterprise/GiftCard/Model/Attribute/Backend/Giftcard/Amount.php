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

class Enterprise_GiftCard_Model_Attribute_Backend_Giftcard_Amount
    extends Mage_Catalog_Model_Product_Attribute_Backend_Price
{
    /**
     * Retrieve resource model
     *
     * @return Enterprise_GiftCard_Model_Mysql4_Attribute_Backend_Giftcard_Amounts
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('enterprise_giftcard/attribute_backend_giftcard_amount');
    }

    /**
     * Validate data
     *
     * @param   Mage_Catalog_Model_Product $object
     * @return  Enterprise_GiftCard_Model_Attribute_Backend_Giftcard_Amounts
     */
    public function validate($object)
    {
        $rows = $object->getData($this->getAttribute()->getName());
        if (empty($rows)) {
            return $this;
        }
        $dup = array();

        foreach ($rows as $row) {
            if (!isset($row['price']) || !empty($row['delete'])) {
                continue;
            }

            $key1 = implode('-', array($row['website_id'], $row['price']));

            if (!empty($dup[$key1])) {
                Mage::throwException(
                    Mage::helper('catalog')->__('Duplicate amount found.')
                );
            }
            $dup[$key1] = 1;
        }
        return $this;
    }

    /**
     * Assign amounts to product data
     *
     * @param   Mage_Catalog_Model_Product $object
     * @return  Enterprise_GiftCard_Model_Attribute_Backend_Giftcard_Amounts
     */
    public function afterLoad($object)
    {
        $data = $this->_getResource()->loadProductData($object, $this->getAttribute());

        foreach ($data as $i=>$row) {
            if ($data[$i]['website_id'] == 0) {
                $rate = Mage::app()->getStore()->getBaseCurrency()->getRate(Mage::app()->getBaseCurrencyCode());
                if ($rate) {
                    $data[$i]['website_value'] = $data[$i]['value']/$rate;
                } else {
                    unset($data[$i]);
                }
            } else {
                $data[$i]['website_value'] = $data[$i]['value'];
            }

        }
        $object->setData($this->getAttribute()->getName(), $data);
        return $this;
    }

    /**
     * Save amounts data
     *
     * @param Mage_Catalog_Model_Product $object
     * @return Enterprise_GiftCard_Model_Attribute_Backend_Giftcard_Amounts
     */
    public function afterSave($object)
    {
        $orig = $object->getOrigData($this->getAttribute()->getName());
        $current = $object->getData($this->getAttribute()->getName());
        if ($orig == $current) {
            return $this;
        }

        $this->_getResource()->deleteProductData($object, $this->getAttribute());
        $rows = $object->getData($this->getAttribute()->getName());

        if (!is_array($rows)) {
            return $this;
        }

        foreach ($rows as $row) {
            // Handle the case when model is saved whithout data received from user
            if (((!isset($row['price']) || empty($row['price'])) && !isset($row['value']))
                || !empty($row['delete'])
            ) {
                continue;
            }

            $data = array();
            $data['website_id']   = $row['website_id'];
            $data['value']        = (isset($row['price'])) ? $row['price'] : $row['value'];
            $data['attribute_id'] = $this->getAttribute()->getId();

            $this->_getResource()->insertProductData($object, $data);
        }

        return $this;
    }

    /**
     * Delete amounts data
     *
     * @param Mage_Catalog_Model_Product $object
     * @return Enterprise_GiftCard_Model_Attribute_Backend_Giftcard_Amounts
     */
    public function afterDelete($object)
    {
        $this->_getResource()->deleteProductData($object, $this->getAttribute());
        return $this;
    }

    /**
     * Retreive storage table
     *
     * @return string
     */
/*
    public function getTable()
    {
        return $this->_getResource()->getMainTable();
    }
*/
}
