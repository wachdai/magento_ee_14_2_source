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
class Enterprise_GoogleAnalyticsUniversal_Block_Adminhtml_Creditmemo_Ga
    extends Enterprise_GoogleAnalyticsUniversal_Block_Ga
{
    /**
     * Get adminhtml session
     */
    public function getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * Get order ID for the recently created creditmemo
     */
    public function getOrderId()
    {
        $orderId = $this->getSession()->getData('googleanalytics_creditmemo_order');
        if ($orderId) {
            $this->getSession()->unsetData('googleanalytics_creditmemo_order');
            return $orderId;
        }

        return '';
    }

    /**
     * Get refunded amount for the recently created creditmemo
     */
    public function getRevenue()
    {
        if ($revenue = $this->getSession()->getData('googleanalytics_creditmemo_revenue', true)) {
            return $revenue;
        }

        return '';
    }

    /**
     * Get refunded products
     *
     * @return array
     */
    public function getProducts()
    {
        $products = $this->getSession()->getData('googleanalytics_creditmemo_products', true);
        if ($products) {
            return $products;
        }
        return array();
    }

    /**
     * Build json for dataLayer.push action
     *
     * @return string|null
     */
    public function getRefundJson()
    {
        $orderId = $this->getOrderId();
        if (!$orderId) {
            return null;
        }
        $refundJson = new StdClass();
        $refundJson->event = 'refund';
        $refundJson->ecommerce->refund->actionField->id = $orderId;
        $revenue = $this->getRevenue();
        if ($revenue) {
            $refundJson->ecommerce->refund->actionField->revenue = $revenue;
        }
        $refundJson->ecommerce->refund->products = $this->getProducts();
        return json_encode($refundJson);
    }
}
