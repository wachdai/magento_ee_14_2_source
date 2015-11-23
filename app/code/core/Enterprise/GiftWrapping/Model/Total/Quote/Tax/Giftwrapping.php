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
 * GiftWrapping tax total calculator for quote
 *
 */
class Enterprise_GiftWrapping_Model_Total_Quote_Tax_Giftwrapping extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote;

    /**
     * @var Mage_Sales_Model_Quote|Mage_Sales_Model_Quote_Address
     */
    protected $_quoteEntity;

    /**
     * @var Mage_Tax_Model_Calculation
     */
    protected $_taxCalculationModel;

    /**
     * @var array
     */
    protected $_request;

    /**
     * @var float
     */
    protected $_rate;

    /**
     * @var Enterprise_GiftWrapping_Helper_Data
     */
    protected $_helper;

    /**
     * Init total model, set total code
     */
    public function __construct()
    {
        $this->setCode('tax_giftwrapping');
        $this->_taxCalculationModel = Mage::getSingleton('tax/calculation');
        $this->_helper = Mage::helper('enterprise_giftwrapping');
    }

    /**
     * Collect applied tax rates information on address level
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param array $applied
     * @param float $amount
     * @param float $baseAmount
     * @param float $rate
     * @return Enterprise_GiftWrapping_Model_Total_Quote_Tax_Giftwrapping
     */
    protected function _saveAppliedTaxes($address, $applied, $amount, $baseAmount, $rate)
    {
        $previouslyAppliedTaxes = $address->getAppliedTaxes();
        $process = count($previouslyAppliedTaxes);

        foreach ($applied as $row) {
            if ($row['percent'] == 0) {
                continue;
            }
            if (!isset($previouslyAppliedTaxes[$row['id']])) {
                $row['process']     = $process;
                $row['amount']      = 0;
                $row['base_amount'] = 0;
                $previouslyAppliedTaxes[$row['id']] = $row;
            }

            if (!is_null($row['percent'])) {
                $row['percent'] = $row['percent'] ? $row['percent'] : 1;
                $rate = $rate ? $rate : 1;

                $appliedAmount     = $amount/$rate * $row['percent'];
                $baseAppliedAmount = $baseAmount/$rate * $row['percent'];
            } else {
                $appliedAmount     = 0;
                $baseAppliedAmount = 0;
                foreach ($row['rates'] as $rate) {
                    $appliedAmount      += $rate['amount'];
                    $baseAppliedAmount  += $rate['base_amount'];
                }
            }

            if ($appliedAmount || $previouslyAppliedTaxes[$row['id']]['amount']) {
                $previouslyAppliedTaxes[$row['id']]['amount']      += $appliedAmount;
                $previouslyAppliedTaxes[$row['id']]['base_amount'] += $baseAppliedAmount;
            } else {
                unset($previouslyAppliedTaxes[$row['id']]);
            }
        }
        $address->setAppliedTaxes($previouslyAppliedTaxes);
        return $this;
    }

    /**
     * Collect gift wrapping tax totals
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Enterprise_GiftWrapping_Model_Total_Quote_Tax_Giftwrapping
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        if ($address->getAddressType() != Mage_Sales_Model_Quote_Address::TYPE_SHIPPING) {
            return $this;
        }

        $this->_quote = $address->getQuote();
        $quote = $this->_quote;
        if ($quote->getIsMultiShipping()) {
            $this->_quoteEntity = $address;
        } else {
            $this->_quoteEntity = $quote;
        }

        $this->_initRate($address)
            ->_collectWrappingForItems($address)
            ->_collectWrappingForQuote($address)
            ->_collectPrintedCard($address);

        $baseTaxAmount = $address->getGwItemsBaseTaxAmount()
            + $address->getGwBaseTaxAmount()
            + $address->getGwCardBaseTaxAmount();
        $taxAmount = $address->getGwItemsTaxAmount()
            + $address->getGwTaxAmount()
            + $address->getGwCardTaxAmount();
        $address->setBaseTaxAmount($address->getBaseTaxAmount() + $baseTaxAmount);
        $address->setTaxAmount($address->getTaxAmount() + $taxAmount);
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $baseTaxAmount);
        $address->setGrandTotal($address->getGrandTotal() + $taxAmount);

        if ($quote->getIsNewGiftWrappingTaxCollecting()) {
            $quote->setGwItemsBaseTaxAmount(0);
            $quote->setGwItemsTaxAmount(0);
            $quote->setGwBaseTaxAmount(0);
            $quote->setGwTaxAmount(0);
            $quote->setGwCardBaseTaxAmount(0);
            $quote->setGwCardTaxAmount(0);
            $quote->setIsNewGiftWrappingTaxCollecting(false);
        }
        $quote->setGwItemsBaseTaxAmount($address->getGwItemsBaseTaxAmount() + $quote->getGwItemsBaseTaxAmount());
        $quote->setGwItemsTaxAmount($address->getGwItemsTaxAmount() + $quote->getGwItemsTaxAmount());
        $quote->setGwBaseTaxAmount($address->getGwBaseTaxAmount() + $quote->getGwBaseTaxAmount());
        $quote->setGwTaxAmount($address->getGwTaxAmount() + $quote->getGwTaxAmount());
        $quote->setGwPrintedCardBaseTaxAmount(
            $address->getGwPrintedCardBaseTaxAmount() + $quote->getGwPrintedCardBaseTaxAmount()
        );
        $quote->setGwPrintedCardTaxAmount(
            $address->getGwPrintedCardTaxAmount() + $quote->getGwPrintedCardTaxAmount()
        );

        $applied = Mage::getSingleton('tax/calculation')->getAppliedRates($this->_request);
        $this->_saveAppliedTaxes($address, $applied, $taxAmount, $baseTaxAmount, $this->_rate);

        return $this;
    }

    /**
     * Collect wrapping tax total for items
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Enterprise_GiftWrapping_Model_Total_Quote_Tax_Giftwrapping
     */
    protected function _collectWrappingForItems($address)
    {
        $items = $this->_getAddressItems($address);
        $wrappingForItemsBaseTaxAmount = false;
        $wrappingForItemsTaxAmount = false;

        foreach ($items as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem() || !$item->getGwId()) {
                continue;
            }
            $wrappingBaseTaxAmount = $this->_calcTaxAmount($item->getGwBasePrice());
            $wrappingTaxAmount = $this->_calcTaxAmount($item->getGwPrice());
            $item->setGwBaseTaxAmount($wrappingBaseTaxAmount);
            $item->setGwTaxAmount($wrappingTaxAmount);

            $wrappingForItemsBaseTaxAmount += $wrappingBaseTaxAmount * $item->getQty();
            $wrappingForItemsTaxAmount += $wrappingTaxAmount * $item->getQty();
        }
        $address->setGwItemsBaseTaxAmount($wrappingForItemsBaseTaxAmount);
        $address->setGwItemsTaxAmount($wrappingForItemsTaxAmount);
        return $this;
    }

    /**
     * Collect wrapping tax total for quote
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Enterprise_GiftWrapping_Model_Total_Quote_Tax_Giftwrapping
     */
    protected function _collectWrappingForQuote($address)
    {
        $wrappingBaseTaxAmount = false;
        $wrappingTaxAmount = false;
        if ($this->_quoteEntity->getGwId()) {
            $wrappingBaseTaxAmount = $this->_calcTaxAmount($this->_quoteEntity->getGwBasePrice());
            $wrappingTaxAmount = $this->_calcTaxAmount($this->_quoteEntity->getGwPrice());
        }
        $address->setGwBaseTaxAmount($wrappingBaseTaxAmount);
        $address->setGwTaxAmount($wrappingTaxAmount);
        return $this;
    }

    /**
     * Collect printed card tax total for quote
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Enterprise_GiftWrapping_Model_Total_Quote_Tax_Giftwrapping
     */
    protected function _collectPrintedCard($address)
    {
        $printedCardBaseTaxAmount = false;
        $printedCardTaxAmount = false;
        if ($this->_quoteEntity->getGwAddCard()) {
            $printedCardBaseTaxAmount = $this->_calcTaxAmount($this->_quoteEntity->getGwCardBasePrice());
            $printedCardTaxAmount = $this->_calcTaxAmount($this->_quoteEntity->getGwCardPrice());
        }
        $address->setGwCardBaseTaxAmount($printedCardBaseTaxAmount);
        $address->setGwCardTaxAmount($printedCardTaxAmount);
        return $this;
    }

    /**
     * Init gift wrapping and printed card tax rate for address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Enterprise_GiftWrapping_Model_Total_Quote_Tax_Giftwrapping
     */
    protected function _initRate($address)
    {
        $store = $address->getQuote()->getStore();
        $billingAddress = $address->getQuote()->getBillingAddress();
        $custTaxClassId = $address->getQuote()->getCustomerTaxClassId();
        $this->_request = $this->_taxCalculationModel->getRateRequest(
            $address,
            $billingAddress,
            $custTaxClassId,
            $store
        );
        $this->_request->setProductClassId($this->_helper->getWrappingTaxClass($store));
        $this->_rate = $this->_taxCalculationModel->getRate($this->_request);
        return $this;
    }

    /**
     * Calculate tax for amount
     *
     * @param   float $price
     * @param   float $taxRate
     * @return  float
     */
    protected function _calcTaxAmount($price)
    {
        return $this->_taxCalculationModel->calcTaxAmount($price, $this->_rate);
    }

    /**
     * Assign wrapping tax totals and labels to address object
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Enterprise_GiftWrapping_Model_Total_Quote_Tax_Giftwrapping
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $address->addTotal(array(
            'code' => 'giftwrapping',
            'gw_price' => $address->getGwPrice(),
            'gw_base_price' => $address->getGwBasePrice(),
            'gw_items_price' => $address->getGwItemsPrice(),
            'gw_items_base_price' => $address->getGwItemsBasePrice(),
            'gw_card_price' => $address->getGwCardPrice(),
            'gw_card_base_price' => $address->getGwCardBasePrice(),
            'gw_tax_amount' => $address->getGwTaxAmount(),
            'gw_base_tax_amount' => $address->getGwBaseTaxAmount(),
            'gw_items_tax_amount' => $address->getGwItemsTaxAmount(),
            'gw_items_base_tax_amount' => $address->getGwItemsBaseTaxAmount(),
            'gw_card_tax_amount' => $address->getGwCardTaxAmount(),
            'gw_card_base_tax_amount' => $address->getGwCardBaseTaxAmount()
        ));
        return $this;
    }
}
