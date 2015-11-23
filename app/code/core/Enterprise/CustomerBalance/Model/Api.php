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
 * Store credit API
 *
 * @category   Enterprise
 * @package    Enterprise_CustomerBalance
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CustomerBalance_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve customer store credit balance information
     *
     * @param  string $customerId
     * @param  string $websiteId
     * @return float
     */
    public function balance($customerId, $websiteId)
    {
        /**
         * @var Enterprise_CustomerBalance_Model_Balance $balanceModel
         */
        try {
            $balanceModel = Mage::getModel('enterprise_customerbalance/balance')
                    ->setCustomerId($customerId)
                    ->setWebsiteId($websiteId)
                    ->loadByCustomer();
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        // check if balance found
        if (!$balanceModel->getId()) {
            $this->_fault('balance_not_found');
        }
        return $balanceModel->getAmount();
    }

    /**
     * Retrieve customer store credit history information
     *
     * @param  string $customerId
     * @param  string|null $websiteId
     * @return array
     */
    public function history($customerId, $websiteId = null)
    {
        try {
            $result = Mage::getModel('enterprise_customerbalance/balance_history')
                    ->getHistoryData($customerId, $websiteId);
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        // check if history found
        if (empty($result)) {
            $this->_fault('history_not_found');
        }
        return $result;
    }

}
