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
 * Gift wrapping order view abstract block
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftWrapping_Block_Adminhtml_Order_View_Abstract extends Mage_Core_Block_Template
{
    protected $_designCollection;

    /*
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('sales_order');
    }

    /*
     * Get store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getOrder()->getStoreId();
    }

    /**
     * Gift wrapping collection
     *
     * @return Enterprise_GiftWrapping_Model_Resource_Mysql4_Wrapping_Collection
     */
    public function getDesignCollection()
    {
        if (is_null($this->_designCollection)) {
            $store = Mage::app()->getStore($this->getStoreId());
            $this->_designCollection = Mage::getModel('enterprise_giftwrapping/wrapping')->getCollection()
                ->addStoreAttributesToResult($store->getId())
                ->applyStatusFilter()
                ->applyWebsiteFilter($store->getWebsiteId());
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
            $temp['path'] = $item->getImageUrl();
            $temp['design'] = $this->escapeHtml($item->getDesign());
            $data[$item->getId()] = $temp;
        }
       return new Varien_Object($data);
    }

    /**
     * Prepare prices for display
     * @param float $basePrice
     * @param float $price
     * @return string
     */
    protected function _preparePrices($basePrice, $price)
    {
        return $this->helper('adminhtml/sales')->displayPrices($this->getOrder(), $basePrice, $price);
    }

    /**
     * Check ability to display both prices for gift wrapping in backend sales
     *
     * @return bool
     */
    public function getDisplayWrappingBothPrices()
    {
        return Mage::helper('enterprise_giftwrapping')->displaySalesWrappingBothPrices();
    }

    /**
     * Check ability to display prices including tax for gift wrapping in backend sales
     *
     * @return bool
     */
    public function getDisplayWrappingPriceInclTax()
    {
        return Mage::helper('enterprise_giftwrapping')->displaySalesWrappingIncludeTaxPrice();
    }

    /**
     * Check ability to display both prices for printed card in backend sales
     *
     * @return bool
     */
    public function getDisplayCardBothPrices()
    {
        return Mage::helper('enterprise_giftwrapping')->displaySalesCardBothPrices();
    }

    /**
     * Check ability to display prices including tax for printed card in backend sales
     *
     * @return bool
     */
    public function getDisplayCardPriceInclTax()
    {
        return Mage::helper('enterprise_giftwrapping')->displaySalesCardIncludeTaxPrice();
    }
}
