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
 * Gift wrapping checkout process options block
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftWrapping_Block_Checkout_Options extends Mage_Core_Block_Template
{
    /**
     * @var Enterprise_GiftWrapping_Model_Resource_Mysql4_Wrapping_Collection
     */
    protected $_designCollection;

    /**
     * @var bool
     */
    protected $_giftWrappingAvailable = false;

    /**
     * Gift wrapping collection
     *
     * @return Enterprise_GiftWrapping_Model_Resource_Mysql4_Wrapping_Collection
     */
    public function getDesignCollection()
    {
        if (is_null($this->_designCollection)) {
            $store = Mage::app()->getStore();
            $this->_designCollection = Mage::getModel('enterprise_giftwrapping/wrapping')->getCollection()
                ->addStoreAttributesToResult($store->getId())
                ->applyStatusFilter()
                ->applyWebsiteFilter($store->getWebsiteId());
        }
        return $this->_designCollection;
    }

    /**
     * Select element for choosing gift wrapping design
     *
     * @return array
     */
    public function getDesignSelectHtml()
    {
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setData(array(
                'id'    => 'giftwrapping_{{id}}',
                'class' => 'select'
            ))
            ->setName('giftwrapping[{{id}}][design]')
            ->setOptions($this->getDesignCollection()->toOptionArray());
        return $select->getHtml();
    }

    /**
     * Get quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Calculate including tax price
     *
     * @param Varien_Object $item
     * @param mixed $basePrice
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @param bool $includeTax
     * @return string
     */
    public function calculatePrice($item, $basePrice, $shippingAddress, $includeTax = false)
    {
        $billingAddress = $this->_getQuote()->getBillingAddress();
        $taxClass = Mage::helper('enterprise_giftwrapping')->getWrappingTaxClass();
        $item->setTaxClassId($taxClass);

        $price = Mage::helper('enterprise_giftwrapping')->getPrice($item, $basePrice, $includeTax, $shippingAddress,
            $billingAddress
        );
        return Mage::helper('core')->currency($price, true, false);
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
            $temp = array();
            foreach ($this->_getQuote()->getAllShippingAddresses() as $address) {
                $entityId = $this->_getQuote()->getIsMultiShipping() ? $address->getId() : $this->_getQuote()->getId();
                if ($this->getDisplayWrappingBothPrices()) {
                    $temp[$entityId]['price_incl_tax'] = $this->calculatePrice(
                        $item,
                        $item->getBasePrice(),
                        $address,
                        true
                     );
                    $temp[$entityId]['price_excl_tax'] = $this->calculatePrice(
                        $item,
                        $item->getBasePrice(),
                        $address
                    );
                } else {
                    $temp[$entityId]['price'] = $this->calculatePrice(
                        $item,
                        $item->getBasePrice(),
                        $address,
                        $this->getDisplayWrappingIncludeTaxPrice()
                    );
                }
            }
            $temp['path'] = $item->getImageUrl();
            $data[$item->getId()] = $temp;
        }
       return new Varien_Object($data);
    }

    /**
     * Prepare and return quote items info
     *
     * @return Varien_Object
     */
    public function getItemsInfo()
    {
        $data = array();
        if ($this->_getQuote()->getIsMultiShipping()) {
            foreach ($this->_getQuote()->getAllShippingAddresses() as $address) {
                $this->_processItems($address->getAllItems(), $address, $data);
            }
        } else {
            $this->_processItems($this->_getQuote()->getAllItems(), $this->_getQuote()->getShippingAddress(), $data);
        }
        return new Varien_Object($data);
    }

    /**
     * Process items
     *
     * @param array $items
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @param array $data
     * @return array
     */
    protected function _processItems($items, $shippingAddress, &$data)
    {
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $allowed = $item->getProduct()->getGiftWrappingAvailable();
            if (Mage::helper('enterprise_giftwrapping')->isGiftWrappingAvailableForProduct($allowed)
                && !$item->getIsVirtual()) {
                $temp = array();
                $price = $item->getProduct()->getGiftWrappingPrice();
                if ($price) {
                    if ($this->getDisplayWrappingBothPrices()) {
                        $temp['price_incl_tax'] = $this->calculatePrice(
                            new Varien_Object(),
                            $price,
                            $shippingAddress,
                            true
                        );
                        $temp['price_excl_tax'] = $this->calculatePrice(
                            new Varien_Object(),
                            $price,
                            $shippingAddress
                        );
                    } else {
                        $temp['price'] = $this->calculatePrice(
                            new Varien_Object(),
                            $price,
                            $shippingAddress,
                            $this->getDisplayWrappingIncludeTaxPrice()
                        );
                    }
                }
                $data[$item->getId()] = $temp;
            }
        }
        return $data;
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
            $price = Mage::helper('enterprise_giftwrapping')->getPrintedCardPrice();
            foreach ($this->_getQuote()->getAllShippingAddresses() as $address) {
                $entityId = $this->_getQuote()->getIsMultiShipping()
                    ? $address->getId()
                    : $this->_getQuote()->getId();

                if ($this->getDisplayCardBothPrices()) {
                    $data[$entityId]['price_incl_tax'] = $this->calculatePrice(
                        new Varien_Object(),
                        $price,
                        $address,
                        true
                    );
                    $data[$entityId]['price_excl_tax'] = $this->calculatePrice(
                        new Varien_Object(),
                        $price,
                        $address
                    );
                } else {
                    $data[$entityId]['price'] = $this->calculatePrice(
                        new Varien_Object(),
                        $price,
                        $address,
                        $this->getDisplayCardIncludeTaxPrice()
                    );
                }
            }
        }
        return new Varien_Object($data);
    }

    /**
     * Check display both prices for gift wrapping
     *
     * @return bool
     */
    public function getDisplayWrappingBothPrices()
    {
        return Mage::helper('enterprise_giftwrapping')->displayCartWrappingBothPrices();
    }

    /**
     * Check display both prices for printed card
     *
     * @return bool
     */
    public function getDisplayCardBothPrices()
    {
        return Mage::helper('enterprise_giftwrapping')->displayCartCardBothPrices();
    }

    /**
     * Check display prices including tax for gift wrapping
     *
     * @return bool
     */
    public function getDisplayWrappingIncludeTaxPrice()
    {
        return Mage::helper('enterprise_giftwrapping')->displayCartWrappingIncludeTaxPrice();
    }

    /**
     * Check display price including tax for printed card
     *
     * @return bool
     */
    public function getDisplayCardIncludeTaxPrice()
    {
        return Mage::helper('enterprise_giftwrapping')->displayCartCardIncludeTaxPrice();
    }

    /**
     * Check allow printed card
     *
     * @return bool
     */
    public function getAllowPrintedCard()
    {
        return Mage::helper('enterprise_giftwrapping')->allowPrintedCard();
    }

    /**
     * Check allow gift receipt
     *
     * @return bool
     */
    public function getAllowGiftReceipt()
    {
        return Mage::helper('enterprise_giftwrapping')->allowGiftReceipt();
    }

    /**
     * Check allow gift wrapping on order level
     *
     * @return bool
     */
    public function getAllowForOrder()
    {
        return Mage::helper('enterprise_giftwrapping')->isGiftWrappingAvailableForOrder();
    }

    /**
     * Check allow gift wrapping on order items
     *
     * @return bool
     */
    public function getAllowForItems()
    {
        return Mage::helper('enterprise_giftwrapping')->isGiftWrappingAvailableForItems();
    }

    /**
     * Check allow gift wrapping for order
     *
     * @return bool
     */
    public function canDisplayGiftWrapping()
    {
        $cartItems = $this->_getModelInstance('checkout/cart')->getItems();
        foreach ($cartItems as $item) {
            if ($item->getProduct()->getGiftWrappingAvailable()) {
                $this->_giftWrappingAvailable = true;
                continue;
            }
        }

        return $this->getAllowForOrder()
            || $this->getAllowForItems()
            || $this->getAllowPrintedCard()
            || $this->getAllowGiftReceipt()
            || $this->_giftWrappingAvailable;
    }

    /**
     * Get design collection count
     *
     * @return int
     */
    public function getDesignCollectionCount()
    {
        return count($this->getDesignCollection());
    }

    /**
     * Get Mage Model
     *
     * @param string $modelClass
     * @param array $arguments
     * @return false|Mage_Core_Model_Abstract
     */
    protected function _getModelInstance($modelClass = '', $arguments = array()) {
        return Mage::getModel($modelClass, $arguments);
    }
}
