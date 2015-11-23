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
 * @package     Enterprise_GoogleAnalyticsUniversal
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Class Enterprise_GoogleAnalyticsUniversal_Block_Ga
 * @method array getOrderIds()
 * @method Mage_Sales_Model_Quote setOrderIds(array $value)
 *
 */
class Enterprise_GoogleAnalyticsUniversal_Block_Ga extends Mage_GoogleAnalytics_Block_Ga
{
    /**
     * Is gtm available
     *
     * @return bool
     */
    protected function _isAvailable()
    {
        return Mage::helper('enterprise_googleanalyticsuniversal')->isGoogleAnalyticsAvailable();
    }
    /**
     * Render GA tracking scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_isAvailable()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Render IP anonymization code for page tracking javascript code
     *
     * @return string
     */
    public function getAnonymizationCode()
    {
        /** @var Mage_GoogleAnalytics_Helper_Data $helper */
        $helper = Mage::helper('googleanalytics');

        // Support for isIpAnonymizationEnabled was added in Magento 1.8.1.0 | 1.13.1.0
        if (method_exists($helper, 'isIpAnonymizationEnabled')
            && $helper->isIpAnonymizationEnabled()) {
            return "ga('set', 'anonymizeIp', true);";
        }
        return '';
    }

    /**
     * Get store currency code for page tracking javascript code
     *
     * @return string
     */
    public function getStoreCurrencyCode()
    {
        return Mage::app()->getStore()->getBaseCurrencyCode();
    }


    /**
     * Render information about specified orders and their items
     * @return string
     */
    public function getOrdersData()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return '';
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));

        $result = array();
        /** @var Mage_Sales_Model_Order $order*/
        foreach ($collection as $order) {
            $actionField['id'] = $order->getIncrementId();
            $actionField['revenue'] = $order->getBaseGrandTotal() -
                ($order->getBaseTaxAmount() + $order->getBaseShippingAmount());
            $actionField['tax'] = $order->getBaseTaxAmount();
            $actionField['shipping'] = $order->getBaseShippingAmount();
            $actionField['coupon'] = (string)$order->getCouponCode();

            $products = array();
            /** @var Mage_Sales_Model_Order_Item $item*/
            foreach ($order->getAllVisibleItems() as $item) {
                $product['id'] = $item->getSku();
                $product['name'] = $item->getName();
                $product['price'] = $item->getBasePrice();
                $product['quantity'] = $item->getQtyOrdered();
                //$product['category'] = ''; //Not available to populate
                $products[] = $product;
            }
            $json['ecommerce']['purchase']['actionField'] = $actionField;
            $json['ecommerce']['purchase']['products'] = $products;
            $json['ecommerce']['currencyCode'] = $this->getStoreCurrencyCode();
            $json['event'] = 'purchase';
            $result[] = 'dataLayer.push(' . Mage::helper('core')->jsonEncode($json) . ");\n";
        }
        return implode("\n", $result);
    }
}
