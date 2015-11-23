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
 * GiftWrapping total calculator for quote
 *
 */
class Enterprise_GiftWrapping_Model_Total_Quote_Giftwrapping extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote;

    /**
     * @var Mage_Sales_Model_Quote|Mage_Sales_Model_Quote_Address
     */
    protected $_quoteEntity;

    /**
     * Init total model, set total code
     */
    public function __construct()
    {
        $this->setCode('giftwrapping');
    }

    /**
     * Collect gift wrapping totals
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Enterprise_GiftWrapping_Model_Total_Quote_Giftwrapping
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        if ($address->getAddressType() != Mage_Sales_Model_Quote_Address::TYPE_SHIPPING) {
            return $this;
        }

        $this->_quote = $address->getQuote();
        $this->_store = $this->_quote->getStore();
        $quote = $this->_quote;
        if ($quote->getIsMultiShipping()) {
            $this->_quoteEntity = $address;
        } else {
            $this->_quoteEntity = $quote;
        }

        $this->_collectWrappingForItems($address)
            ->_collectWrappingForQuote($address)
            ->_collectPrintedCard($address);

        $address->setBaseGrandTotal(
            $address->getBaseGrandTotal()
            + $address->getGwItemsBasePrice()
            + $address->getGwBasePrice()
            + $address->getGwCardBasePrice()
        );
        $address->setGrandTotal(
            $address->getGrandTotal()
            + $address->getGwItemsPrice()
            + $address->getGwPrice()
            + $address->getGwCardPrice()
        );

        if ($quote->getIsNewGiftWrappingCollecting()) {
            $quote->setGwItemsBasePrice(0);
            $quote->setGwItemsPrice(0);
            $quote->setGwBasePrice(0);
            $quote->setGwPrice(0);
            $quote->setGwCardBasePrice(0);
            $quote->setGwCardPrice(0);
            $quote->setIsNewGiftWrappingCollecting(false);
        }
        $quote->setGwItemsBasePrice($address->getGwItemsBasePrice() + $quote->getGwItemsBasePrice());
        $quote->setGwItemsPrice($address->getGwItemsPrice() + $quote->getGwItemsPrice());
        $quote->setGwBasePrice($address->getGwBasePrice() + $quote->getGwBasePrice());
        $quote->setGwPrice($address->getGwPrice() + $quote->getGwPrice());
        $quote->setGwCardBasePrice($address->getGwCardBasePrice() + $quote->getGwCardBasePrice());
        $quote->setGwCardPrice($address->getGwCardPrice() + $quote->getGwCardPrice());

        return $this;
    }

    /**
     * Collect wrapping total for items
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Enterprise_GiftWrapping_Model_Total_Quote_Giftwrapping
     */
    protected function _collectWrappingForItems($address)
    {
        $items = $this->_getAddressItems($address);
        $wrappingForItemsBaseTotal = false;
        $wrappingForItemsTotal = false;

        foreach ($items as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem() || !$item->getGwId()) {
                continue;
            }
            if ($item->getProduct()->getGiftWrappingPrice()) {
                $wrappingBasePrice = $item->getProduct()->getGiftWrappingPrice();
            } else {
                $wrapping = $this->_getWrapping($item->getGwId(), $this->_store);
                $wrappingBasePrice = $wrapping->getBasePrice();
            }
            $wrappingPrice = $this->_store->convertPrice($wrappingBasePrice);
            $item->setGwBasePrice($wrappingBasePrice);
            $item->setGwPrice($wrappingPrice);
            $wrappingForItemsBaseTotal += $wrappingBasePrice * $item->getQty();
            $wrappingForItemsTotal += $wrappingPrice * $item->getQty();
        }
        $address->setGwItemsBasePrice($wrappingForItemsBaseTotal);
        $address->setGwItemsPrice($wrappingForItemsTotal);

        return $this;
    }

    /**
     * Collect wrapping total for quote
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Enterprise_GiftWrapping_Model_Total_Quote_Giftwrapping
     */
    protected function _collectWrappingForQuote($address)
    {
        $wrappingBasePrice = false;
        $wrappingPrice = false;
        if ($this->_quoteEntity->getGwId()) {
            $wrapping = $this->_getWrapping($this->_quoteEntity->getGwId(), $this->_store);
            $wrappingBasePrice = $wrapping->getBasePrice();
            $wrappingPrice = $this->_store->convertPrice($wrappingBasePrice);
        }
        $address->setGwBasePrice($wrappingBasePrice);
        $address->setGwPrice($wrappingPrice);
        return $this;
    }

    /**
     * Collect printed card total for quote
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Enterprise_GiftWrapping_Model_Total_Quote_Giftwrapping
     */
    protected function _collectPrintedCard($address)
    {
        $printedCardBasePrice = false;
        $printedCardPrice = false;
        if ($this->_quoteEntity->getGwAddCard()) {
            $printedCardBasePrice = Mage::helper('enterprise_giftwrapping')->getPrintedCardPrice($this->_store);
            $printedCardPrice = $this->_store->convertPrice($printedCardBasePrice);
        }
        $address->setGwCardBasePrice($printedCardBasePrice);
        $address->setGwCardPrice($printedCardPrice);
        return $this;
    }

    /**
     * Return wrapping model for wrapping ID
     *
     * @param  int $wrappingId
     * @param  Mage_Core_Model_Store $store
     * @return Enterprise_GiftWrapping_Model_Wrapping
     */
    protected function _getWrapping($wrappingId, $store)
    {
        $wrapping = Mage::getModel('enterprise_giftwrapping/wrapping');
        $wrapping->setStoreId($store->getId());
        $wrapping->load($wrappingId);
        return $wrapping;
    }

    /**
     * Assign wrapping totals and labels to address object
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Subtotal
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $address->addTotal(array(
            'code'  => $this->getCode(),
            'gw_price' => $address->getGwPrice(),
            'gw_base_price' => $address->getGwBasePrice(),
            'gw_items_price' => $address->getGwItemsPrice(),
            'gw_items_base_price' => $address->getGwItemsBasePrice(),
            'gw_card_price' => $address->getGwCardPrice(),
            'gw_card_base_price' => $address->getGwCardBasePrice()
        ));
        return $this;
    }
}
