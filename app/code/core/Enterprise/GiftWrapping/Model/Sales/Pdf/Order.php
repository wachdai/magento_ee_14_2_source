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

class Enterprise_GiftWrapping_Model_Sales_Pdf_Order extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    /**
     * Get tax amount for gift wrapping for order
     *
     * @return float
     */
    protected function _getTaxAmount()
    {
        return $this->getSource()->getGwTaxAmount();
    }

    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'    => $amount,
     *      'label'     => $label,
     *      'font_size' => $font_size
     *  )
     * )
     *
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $order = $this->getOrder();
        $amount = $order->formatPriceTxt($this->getAmount());
        $amountInclTax = $order->formatPriceTxt($this->getAmount() + $this->_getTaxAmount());

        $helper   = Mage::helper('enterprise_giftwrapping');
        $store    = $order->getStore();
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $title    = $this->getTitle();

        if ($helper->displaySalesWrappingBothPrices($store)) {
            $totals = array(
                array(
                    'amount'    => $this->getAmountPrefix() . $amount,
                    'label'     => Mage::helper('enterprise_giftwrapping')->__($title . ' (Excl. Tax)') . ':',
                    'font_size' => $fontSize
                ),
                array(
                    'amount'    => $this->getAmountPrefix() . $amountInclTax,
                    'label'     => Mage::helper('enterprise_giftwrapping')->__($title . ' (Incl. Tax)') . ':',
                    'font_size' => $fontSize
                )
            );
        } elseif ($helper->displaySalesWrappingIncludeTaxPrice($store)) {
            $totals = array(array(
                'amount'    => $this->getAmountPrefix() . $amountInclTax,
                'label'     => Mage::helper('enterprise_giftwrapping')->__($title . ' (Incl. Tax)') . ':',
                'font_size' => $fontSize
            ));
        } else {
            $totals = array(array(
                'amount'    => $this->getAmountPrefix() . $amount,
                'label'     => Mage::helper('enterprise_giftwrapping')->__($title) . ':',
                'font_size' => $fontSize
            ));
        }

        return $totals;
    }
}
