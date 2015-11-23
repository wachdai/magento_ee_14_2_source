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
 * Customerbalance resource model
 *
 * @category    Enterprise
 * @package     Enterprise_CustomerBalance
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CustomerBalance_Model_Resource_Balance extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize table name and primary key name
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_customerbalance/balance', 'balance_id');
    }

    /**
     * Load customer balance data by specified customer id and website id
     *
     * @param Enterprise_CustomerBalance_Model_Balance $object
     * @param int $customerId
     * @param int $websiteId
     */
    public function loadByCustomerAndWebsiteIds($object, $customerId, $websiteId)
    {
        $read = $this->getReadConnection();
        if ($data = $read->fetchRow($read->select()
            ->from($this->getMainTable())
            ->where('customer_id = ?', $customerId)
            ->where('website_id = ?', $websiteId)
            ->limit(1))) {
            $object->addData($data);
        }
    }

    /**
     * Update customers balance currency code per website id
     *
     * @param int $websiteId
     * @param string $currencyCode
     * @return Enterprise_CustomerBalance_Model_Resource_Balance
     */
    public function setCustomersBalanceCurrencyTo($websiteId, $currencyCode)
    {
        $bind = array('base_currency_code' => $currencyCode);
        $this->_getWriteAdapter()->update(
            $this->getMainTable(), $bind,
            array('website_id=?' => $websiteId, 'base_currency_code IS NULL')
        );
        return $this;
    }

    /**
     * Delete customer orphan balances
     *
     * @param int $customerId
     * @return Enterprise_CustomerBalance_Model_Resource_Balance
     */
    public function deleteBalancesByCustomerId($customerId)
    {
        $adapter = $this->_getWriteAdapter();

        $adapter->delete(
            $this->getMainTable(), array('customer_id = ?' => $customerId, 'website_id IS NULL')
        );
        return $this;
    }

    /**
     * Get customer orphan balances count
     *
     * @param int $customerId
     * @return Enterprise_CustomerBalance_Model_Resource_Balance
     */
    public function getOrphanBalancesCount($customerId)
    {
        $adapter = $this->_getReadAdapter();
        return $adapter->fetchOne($adapter->select()
            ->from($this->getMainTable(), 'count(*)')
            ->where('customer_id = :customer_id')
            ->where('website_id IS NULL'), array('customer_id' => $customerId));
    }
}
