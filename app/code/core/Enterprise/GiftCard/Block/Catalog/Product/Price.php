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

class Enterprise_GiftCard_Block_Catalog_Product_Price extends Mage_Catalog_Block_Product_Price
{
    /**
     * @deprecated See GiftCard price model
     * @var array
     */
    protected $_amountCache = array();

    /**
     * @deprecated See GiftCard price model
     * @var array
     */
    protected $_minMaxCache = array();

    /**
     * Return minimal amount for Giftcard product using price model
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getMinAmount($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        return $product->getPriceModel()->getMinAmount($product);
    }

    /**
     * Return maximal amount for Giftcard product using price model
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getMaxAmount($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        return $product->getPriceModel()->getMaxAmount($product);
    }

    /**
     * @deprecated See GiftCard price model
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _calcMinMax($product = null)
    {
        return array('min' => $this->getMinAmount($product), 'max' => $this->getMaxAmount($product));
    }

    /**
     * @deprecated See GiftCard price model
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _getAmounts($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        return $product->getPriceModel()->getSortedAmounts($product);
    }
}
