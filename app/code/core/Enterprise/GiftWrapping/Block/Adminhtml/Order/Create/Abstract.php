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
 * @package     Enterprise_GiftWrapping
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Gift wrapping order create abstract block
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftWrapping_Block_Adminhtml_Order_Create_Abstract
    extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    protected $_designCollection;

    /**
     * Gift wrapping collection
     *
     * @return Enterprise_GiftWrapping_Model_Resource_Mysql4_Wrapping_Collection
     */
    public function getDesignCollection()
    {
        if (is_null($this->_designCollection)) {
            $this->_designCollection = Mage::getModel('enterprise_giftwrapping/wrapping')->getCollection()
                ->addStoreAttributesToResult($this->getStore()->getId())
                ->applyStatusFilter()
                ->applyWebsiteFilter($this->getStore()->getWebsiteId());
        }
        return $this->_designCollection;
    }

    /**
     * Return gift wrapping designs info
     *
     * @return Varien_Object
     */
    public function getDesignsInfo()
    {
        $data = array();
        foreach ($this->getDesignCollection()->getItems() as $item) {
            if ($this->getDisplayWrappingBothPrices()) {
                $temp['price_incl_tax'] = $this->calculatePrice($item, $item->getBasePrice(), true);
                $temp['price_excl_tax'] = $this->calculatePrice($item, $item->getBasePrice());
            } else {
                $temp['price'] = $this->calculatePrice($item, $item->getBasePrice(), $this->getDisplayWrappingPriceInclTax());
            }
            $temp['path'] = $item->getImageUrl();
            $temp['design'] = $item->getDesign();
            $data[$item->getId()] = $temp;
        }
       return new Varien_Object($data);
    }

    /**
     * Prepare and return printed card info
     *
     * @return Varien_Object
     */
    public function getCardInfo()
    {
        $data = array();
        if ($this->getAllowPrintedCard()) {
            $price = Mage::helper('enterprise_giftwrapping')->getPrintedCardPrice($this->getStoreId());
             if ($this->getDisplayCardBothPrices()) {
                 $data['price_incl_tax'] = $this->calculatePrice(new Varien_Object(), $price, true);
                 $data['price_excl_tax'] = $this->calculatePrice(new Varien_Object(), $price);
             } else {
                $data['price'] = $this->calculatePrice(new Varien_Object(), $price, $this->getDisplayCardPriceInclTax());
             }
        }
        return new Varien_Object($data);
    }

    /**
     * Calculate price
     *
     * @param Varien_Object $item
     * @param mixed $basePrice
     * @param bool $includeTax
     * @return string
     */
    public function calculatePrice($item, $basePrice, $includeTax = false)
    {
        $shippingAddress = $this->getQuote()->getShippingAddress();
        $billingAddress  = $this->getQuote()->getBillingAddress();

        $taxClass = Mage::helper('enterprise_giftwrapping')->getWrappingTaxClass($this->getStoreId());
        $item->setTaxClassId($taxClass);

        $price = Mage::helper('enterprise_giftwrapping')->getPrice($item, $basePrice, $includeTax, $shippingAddress, $billingAddress);
        return Mage::helper('core')->currency($price, true, false);
    }

    /**
     * Check ability to display both prices for gift wrapping in shopping cart
     *
     * @return bool
     */
    public function getDisplayWrappingBothPrices()
    {
        return Mage::helper('enterprise_giftwrapping')->displayCartWrappingBothPrices($this->getStoreId());
    }

    /**
     * Check ability to display prices including tax for gift wrapping in shopping cart
     *
     * @return bool
     */
    public function getDisplayWrappingPriceInclTax()
    {
        return Mage::helper('enterprise_giftwrapping')->displayCartWrappingIncludeTaxPrice($this->getStoreId());
    }

    /**
     * Return quote id
     *
     * @return array
     */
    public function getEntityId()
    {
        return $this->getQuote()->getId();
    }
}
