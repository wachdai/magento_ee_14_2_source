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
 * Gift wrapping order view block
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftWrapping_Block_Adminhtml_Order_View_Info
    extends Enterprise_GiftWrapping_Block_Adminhtml_Order_View_Abstract
{
    /**
     * Prepare and return order items info
     *
     * @return Varien_Object
     */
    public function getOrderInfo()
    {
        $data = array();
        $order = $this->getOrder();
        if ($order && $order->getGwId()) {
            if ($this->getDisplayWrappingBothPrices()) {
                 $data['price_excl_tax'] = $this->_preparePrices($order->getGwBasePrice(), $order->getGwPrice());
                 $data['price_incl_tax'] = $this->_preparePrices(
                    $order->getGwBasePrice() + $order->getGwBaseTaxAmount(),
                    $order->getGwPrice() + $order->getGwTaxAmount()
                 );
            } else if ($this->getDisplayWrappingPriceInclTax()) {
                $data['price'] = $this->_preparePrices(
                    $order->getGwBasePrice() + $order->getGwBaseTaxAmount(),
                    $order->getGwPrice() + $order->getGwTaxAmount()
                );
            } else {
                $data['price'] = $this->_preparePrices($order->getGwBasePrice(), $order->getGwPrice());
            }
            $wrapping = Mage::getModel('enterprise_giftwrapping/wrapping')->load($order->getGwId());
            $data['path'] = $wrapping->getImageUrl();
            $data['design'] = $wrapping->getDesign();
        }
        return new Varien_Object($data);
    }

    /**
     * Prepare and return order items info
     *
     * @return Varien_Object
     */
    public function getCardInfo()
    {
        $data = array();
        $order = $this->getOrder();
        if ($order && $order->getGwAddCard()) {
            if ($this->getDisplayCardBothPrices()) {
                $data['price_excl_tax'] = $this->_preparePrices(
                    $order->getGwCardBasePrice(),
                    $order->getGwCardPrice()
                );
                $data['price_incl_tax'] = $this->_preparePrices(
                    $order->getGwCardBasePrice() + $order->getGwCardBaseTaxAmount(),
                    $order->getGwCardPrice() + $order->getGwCardTaxAmount()
                );
            } else if ($this->getDisplayCardPriceInclTax()) {
                $data['price'] = $this->_preparePrices(
                    $order->getGwCardBasePrice() + $order->getGwCardBaseTaxAmount(),
                    $order->getGwCardPrice() + $order->getGwCardTaxAmount()
                );
            } else {
                $data['price'] = $this->_preparePrices(
                    $order->getGwCardBasePrice(),
                    $order->getGwCardPrice()
                );
            }
        }
        return new Varien_Object($data);
    }

    /**
     * Check using printed card for order
     *
     * @return bool
     */
    public function getPrintedCard()
    {
        return (bool)$this->getOrder()->getGwAddCard();
    }

    /**
     * Check allow gift receipt for order
     *
     * @return bool
     */
    public function getGiftReceipt()
    {
        return (bool)$this->getOrder()->getGwAllowGiftReceipt();
    }

    /**
     * Check allow printed card
     *
     * @return bool
     */
    public function getAllowPrintedCard()
    {
        return Mage::helper('enterprise_giftwrapping')->allowPrintedCard($this->getStoreId());
    }

    /**
     * Check allow gift receipt
     *
     * @return bool
     */
    public function getAllowGiftReceipt()
    {
        return Mage::helper('enterprise_giftwrapping')->allowGiftReceipt($this->getStoreId());
    }

    /**
     * Check allow gift wrapping for order
     *
     * @return bool
     */
    public function canDisplayGiftWrapping()
    {
        return $this->getOrder()->getGwId() || $this->getGiftReceipt() || $this->getPrintedCard();
    }
}
