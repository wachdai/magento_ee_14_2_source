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

class Enterprise_GiftCard_Model_Catalog_Product_Price_Giftcard extends Mage_Catalog_Model_Product_Type_Price
{
    /**
     * Cached amounts
     * @var array
     */
    protected $_amountCache = array();

    /**
     * Cached minimum and maximal amounts
     * @var array
     */
    protected $_minMaxCache = array();

    /**
     * Return price of the specified product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getPrice($product)
    {
        if ($product->getData('price')) {
            return $product->getData('price');
        } else {
            return 0;
        }
    }

    /**
     * Retrieve product final price
     *
     * @param integer $qty
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getFinalPrice($qty=null, $product)
    {
        $finalPrice = $product->getPrice();
        if ($product->hasCustomOptions()) {
            $customOption = $product->getCustomOption('giftcard_amount');
            if ($customOption) {
                $finalPrice += $customOption->getValue();
            }
        }
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);

        $product->setData('final_price', $finalPrice);
        return max(0, $product->getData('final_price'));
    }

    /**
     * Load and set gift card amounts into product object
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function getAmounts($product)
    {
        $allGroups = Mage_Customer_Model_Group::CUST_GROUP_ALL;
        $prices = $product->getData('giftcard_amounts');

        if (is_null($prices)) {
            if ($attribute = $product->getResource()->getAttribute('giftcard_amounts')) {
                $attribute->getBackend()->afterLoad($product);
                $prices = $product->getData('giftcard_amounts');
            }
        }

        return ($prices) ? $prices : array();
    }


    /**
     * Return minimal amount for Giftcard product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getMinAmount($product)
    {
        $minMax = $this->_calcMinMax($product);
        return $minMax['min'];
    }

    /**
     * Return maximal amount for Giftcard product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getMaxAmount($product)
    {
        $minMax = $this->_calcMinMax($product);
        return $minMax['max'];
    }

    /**
     * Fill in $_amountCache or return precalculated sorted values for amounts
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getSortedAmounts($product)
    {
        if (!isset($this->_amountCache[$product->getId()])) {
            $result = array();

            $giftcardAmounts = $this->getAmounts($product);
            if (is_array($giftcardAmounts)) {
                foreach ($giftcardAmounts as $amount) {
                    $result[] = Mage::app()->getStore()->roundPrice($amount['website_value']);
                }
            }
            sort($result);
            $this->_amountCache[$product->getId()] = $result;
        }
        return $this->_amountCache[$product->getId()];
    }

    /**
     * Fill in $_minMaxCache or return precalculated values for min, max
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _calcMinMax($product)
    {
        if (!isset($this->_minMaxCache[$product->getId()])) {
            $min = $max = null;
            if ($product->getAllowOpenAmount()) {
                $openMin = $product->getOpenAmountMin();
                $openMax = $product->getOpenAmountMax();

                if ($openMin) {
                    $min = $openMin;
                } else {
                    $min = 0;
                }
                if ($openMax) {
                    $max = $openMax;
                } else {
                    $max = 0;
                }
            }

            foreach ($this->getSortedAmounts($product) as $amount) {
                if ($amount) {
                    if (is_null($min)) {
                        $min = $amount;
                    }
                    if (is_null($max)) {
                        $max = $amount;
                    }

                    $min = min($min, $amount);
                    if ($max != 0) {
                        $max = max($max, $amount);
                    }
                }
            }

            $this->_minMaxCache[$product->getId()] = array('min'=>$min, 'max'=>$max);
        }
        return $this->_minMaxCache[$product->getId()];
    }
}
