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
 * @package     Enterprise_CustomerBalance
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Customerbalance helper
 *
 */
class Enterprise_CustomerBalance_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * XML configuration paths
     */
    const XML_PATH_ENABLED     = 'customer/enterprise_customerbalance/is_enabled';
    const XML_PATH_AUTO_REFUND = 'customer/enterprise_customerbalance/refund_automatically';

    /**
     * Check whether customer balance functionality should be enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_ENABLED) == 1;
    }

    /**
     * Check if automatically refund is enabled
     *
     * @return bool
     */
    public function isAutoRefundEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_AUTO_REFUND);
    }

    /**
     * Get customer balance model using sales entity
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $salesEntity
     *
     * @return Enterprise_CustomerBalance_Model_Balance|bool
     */
    public function getCustomerBalanceModelFromSalesEntity($salesEntity)
    {
        if ($salesEntity instanceof Mage_Sales_Model_Order) {
            $customerId = $salesEntity->getCustomerId();
            $quote = $salesEntity->getQuote();
        } elseif ($salesEntity instanceof Mage_Sales_Model_Quote) {
            $customerId = $salesEntity->getCustomer()->getId();
            $quote = $salesEntity;
        } else {
            return false;
        }

        if (!$customerId) {
            return false;
        }

        $customerBalanceModel = Mage::getModel('enterprise_customerbalance/balance')
            ->setCustomerId($customerId)
            ->setWebsiteId(Mage::app()->getStore($salesEntity->getStoreId())->getWebsiteId())
            ->loadByCustomer();

        if ($quote->getBaseCustomerBalanceVirtualAmount() > 0) {
            $customerBalanceModel->setAmount($customerBalanceModel->getAmount()
                + $quote->getBaseCustomerBalanceVirtualAmount());
        }

        return $customerBalanceModel;
    }
}
