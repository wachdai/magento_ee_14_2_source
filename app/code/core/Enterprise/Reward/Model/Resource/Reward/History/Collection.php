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
 * @package     Enterprise_Reward
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Reward history collection
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Model_Resource_Reward_History_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Expiry config
     *
     * @var array
     */
    protected $_expiryConfig     = array();

    /**
     * Internal constructor
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_reward/reward_history');
    }

    /**
     * Join reward table and retrieve total balance total with customer_id
     *
     * @return Enterprise_Reward_Model_Resource_Reward_History_Collection
     */
    protected function _joinReward()
    {
        if ($this->getFlag('reward_joined')) {
            return $this;
        }
        $this->getSelect()->joinInner(
            array('reward_table' => $this->getTable('enterprise_reward/reward')),
            'reward_table.reward_id = main_table.reward_id',
            array('customer_id', 'points_balance_total' => 'points_balance')
        );
        $this->setFlag('reward_joined', true);
        return $this;
    }

    /**
     * Getter for $_expiryConfig
     *
     * @param int $websiteId Specified Website Id
     * @return array|Varien_Object
     */
    protected function _getExpiryConfig($websiteId = null)
    {
        if ($websiteId !== null && isset($this->_expiryConfig[$websiteId])) {
            return $this->_expiryConfig[$websiteId];
        }
        return $this->_expiryConfig;
    }

    /**
     * Setter for $_expiryConfig
     *
     * @param array $config
     * @return Enterprise_Reward_Model_Resource_Reward_History_Collection
     */
    public function setExpiryConfig($config)
    {
        if (!is_array($config)) {
            return $this;
        }
        $this->_expiryConfig = $config;
        return $this;
    }

    /**
     * Join reward table to filter history by customer id
     *
     * @param string $customerId
     * @return Enterprise_Reward_Model_Resource_Reward_History_Collection
     */
    public function addCustomerFilter($customerId)
    {
        if ($customerId) {
            $this->_joinReward();
            $this->getSelect()->where('reward_table.customer_id = ?', $customerId);
        }
        return $this;
    }

    /**
     * Skip Expired duplicates records (with action = -1)
     *
     * @return Enterprise_Reward_Model_Resource_Reward_History_Collection
     */
    public function skipExpiredDuplicates()
    {
        $this->getSelect()->where('main_table.is_duplicate_of IS NULL');
        return $this;
    }

    /**
     * Add filter by website id
     *
     * @param integer|array $websiteId
     * @return Enterprise_Reward_Model_Resource_Reward_History_Collection
     */
    public function addWebsiteFilter($websiteId)
    {
        $this->getSelect()->where(
            is_array($websiteId) ? 'main_table.website_id IN (?)' : 'main_table.website_id = ?', $websiteId
        );
        return $this;
    }

    /**
     * Join additional customer information, such as email, name etc.
     *
     * @return Enterprise_Reward_Model_Resource_Reward_History_Collection
     */
    public function addCustomerInfo()
    {
        if ($this->getFlag('customer_added')) {
            return $this;
        }

        $this->_joinReward();

        $customer = Mage::getModel('customer/customer');
        /* @var $customer Mage_Customer_Model_Customer */
        $firstname  = $customer->getAttribute('firstname');
        $middlename = $customer->getAttribute('middlename');
        $lastname   = $customer->getAttribute('lastname');
        $warningNotification = $customer->getAttribute('reward_warning_notification');

        $connection = $this->getConnection();
        /* @var $connection Zend_Db_Adapter_Abstract */

        $this->getSelect()
            ->joinInner(
                array('ce' => $customer->getAttribute('email')->getBackend()->getTable()),
                'ce.entity_id=reward_table.customer_id',
                array('customer_email' => 'email')
            )
            ->joinInner(
                array('cg' => $customer->getAttribute('group_id')->getBackend()->getTable()),
                'cg.entity_id=reward_table.customer_id',
                array('customer_group_id' => 'group_id')
            )
            ->joinLeft(
                array('clt' => $lastname->getBackend()->getTable()),
                $connection->quoteInto(
                    'clt.entity_id=reward_table.customer_id AND clt.attribute_id = ?',
                    $lastname->getAttributeId()
                ),
                array('customer_lastname' => 'value')
            )
            ->joinLeft(
                array('crt' => $middlename->getBackend()->getTable()),
                $connection->quoteInto(
                    'crt.entity_id=reward_table.customer_id AND crt.attribute_id = ?',
                    $middlename->getAttributeId()
                ),
                array('customer_middlename' => 'value')
            )
            ->joinLeft(
                array('cft' => $firstname->getBackend()->getTable()),
                $connection->quoteInto(
                    'cft.entity_id=reward_table.customer_id AND cft.attribute_id = ?',
                    $firstname->getAttributeId()
                ),
                array('customer_firstname' => 'value')
            )
            ->joinLeft(
                array('warning_notification' => $warningNotification->getBackend()->getTable()),
                $connection->quoteInto(
                    'warning_notification.entity_id=reward_table.customer_id AND warning_notification.attribute_id = ?',
                    $warningNotification->getAttributeId()
                ),
                array('reward_warning_notification' => 'value')
            );

        $this->setFlag('customer_added', true);
        return $this;
    }

    /**
     * Add correction to expiration date based on expiry calculation
     * CASE ... WHEN ... THEN is used only in admin area to show expiration date for all stores
     *
     * @param int $websiteId
     * @return Enterprise_Reward_Model_Resource_Reward_History_Collection
     */
    public function addExpirationDate($websiteId = null)
    {
        $expiryConfig = $this->_getExpiryConfig($websiteId);
        $adapter = $this->getConnection();
        if (!$expiryConfig) {
            return $this;
        }

        if ($websiteId !== null) {
            $field = $expiryConfig->getExpiryCalculation()== 'static' ? 'expired_at_static' : 'expired_at_dynamic';
            $this->getSelect()->columns(array('expiration_date' => $field));
        } else {
            $cases = array();
            foreach ($expiryConfig as $wId => $config) {
                $field = $config->getExpiryCalculation()== 'static' ? 'expired_at_static' : 'expired_at_dynamic';
                $cases[$wId] = $field;
            }

            if (count($cases) > 0) {
                $sql = $adapter->getCaseSql('main_table.website_id', $cases);
                $this->getSelect()->columns(array('expiration_date' => new Zend_Db_Expr($sql)));
            }
        }

        return $this;
    }

    /**
     * Return total amounts of points that will be expired soon (pre-configured days value) for specified website
     * Result is grouped by customer
     *
     * @param int $websiteId Specified Website
     * @param bool $subscribedOnly Whether to load expired soon points only for subscribed customers
     * @return Enterprise_Reward_Model_Resource_Reward_History_Collection
     */
    public function loadExpiredSoonPoints($websiteId, $subscribedOnly = true)
    {
        $expiryConfig = $this->_getExpiryConfig($websiteId);
        if (!$expiryConfig) {
            return $this;
        }
        $inDays = (int)$expiryConfig->getExpiryDayBefore();
        // Empty Value disables notification
        if (!$inDays) {
            return $this;
        }

        // join info about current balance and filter records by website
        $this->_joinReward();
        $this->addWebsiteFilter($websiteId);

        $field = $expiryConfig->getExpiryCalculation()== 'static' ? 'expired_at_static' : 'expired_at_dynamic';
        $locale = Mage::app()->getLocale()->getLocale();
        $expireAtLimit = new Zend_Date($locale);
        $expireAtLimit->addDay($inDays);
        $expireAtLimit = $this->formatDate($expireAtLimit);

        $this->getSelect()
            ->columns(
                array('total_expired' => new Zend_Db_Expr('SUM(points_delta-points_used)'))
            )
            ->where('points_delta-points_used > 0')
            ->where('is_expired=0')
            ->where("{$field} IS NOT NULL") // expire_at - BEFORE_DAYS < NOW()
            ->where("{$field} < ?", $expireAtLimit) // eq. expire_at - BEFORE_DAYS < NOW()
            ->group(array('reward_table.customer_id', 'main_table.store_id'));

        if ($subscribedOnly) {
            $this->addCustomerInfo();
            $this->getSelect()->where('warning_notification.value=1');
        }

        $this->setFlag('expired_soon_points_loaded', true);

        return $this;
    }

    /**
     * Add filter for notification_sent field
     *
     * @param bool $flag
     * @return Enterprise_Reward_Model_Resource_Reward_History_Collection
     */
    public function addNotificationSentFlag($flag)
    {
        $this->addFieldToFilter('notification_sent', (bool)$flag ? 1 : 0);
        return $this;
    }

    /**
     * Return array of history ids records that will be expired.
     * Required loadExpiredSoonPoints() call first, based on its select object
     *
     * @return array|bool
     */
    public function getExpiredSoonIds()
    {
        if (!$this->getFlag('expired_soon_points_loaded')) {
            return array();
        }

        $additionalWhere = array();
        foreach ($this as $item) {
            $where = array(
                $this->getConnection()->quoteInto('reward_table.customer_id=?', $item->getCustomerId()),
                $this->getConnection()->quoteInto('main_table.store_id=?', $item->getStoreId())
            );
            $additionalWhere[] = '(' . implode(' AND ', $where). ')';
        }
        if (count($additionalWhere) == 0) {
            return array();
        }
        // filter rows by customer and store, as result of grouped query
        $where = new Zend_Db_Expr(implode(' OR ', $additionalWhere));

        $select = clone $this->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns('history_id')
            ->reset(Zend_Db_Select::GROUP)
            ->reset(Zend_Db_Select::LIMIT_COUNT)
            ->reset(Zend_Db_Select::LIMIT_OFFSET)
            ->where($where);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Order by primary key desc
     *
     * @return Enterprise_Reward_Model_Resource_Reward_History_Collection
     */
    public function setDefaultOrder()
    {
        $this->getSelect()->reset(Zend_Db_Select::ORDER);

        return $this
            ->addOrder('created_at', 'DESC')
            ->addOrder('history_id', 'DESC');
    }
}
