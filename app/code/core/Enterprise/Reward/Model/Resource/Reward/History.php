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
 * Reward history resource model
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Model_Resource_Reward_History extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Internal constructor
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_reward/reward_history', 'history_id');
    }

    /**
     * Perform actions after object load
     *
     * @param Varien_Object $object
     * @return Enterprise_Reward_Model_Resource_Reward_History
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        parent::_afterLoad($object);
        if (is_string($object->getData('additional_data'))) {
            $object->setData('additional_data', unserialize($object->getData('additional_data')));
        }
        return $this;
    }

    /**
     * Perform actions before object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Enterprise_Reward_Model_Resource_Reward_History
     */
    public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeSave($object);
        if (is_array($object->getData('additional_data'))) {
            $object->setData('additional_data', serialize($object->getData('additional_data')));
        }
        return $this;
    }

    /**
     * Check if history update with given action, customer and entity exist
     *
     * @param integer $customerId
     * @param integer $action
     * @param integer $websiteId
     * @param mixed $entity
     * @return boolean
     */
    public function isExistHistoryUpdate($customerId, $action, $websiteId, $entity)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from(array('reward_table' => $this->getTable('enterprise_reward/reward')), array())
            ->joinInner(array('history_table' => $this->getMainTable()),
                'history_table.reward_id = reward_table.reward_id', array())
            ->where('history_table.action = :action')
            ->where('history_table.website_id = :website_id')
            ->where('history_table.entity = :entity')
            ->columns(array('history_table.history_id'));
        $bind = array(
            'action'     => $action,
            'website_id' => $websiteId,
            'entity'     => $entity
        );
        if ($this->_getWriteAdapter()->fetchRow($select, $bind)) {
            return true;
        }
        return false;
    }

    /**
     * Return total quantity rewards for specified action and customer
     *
     * @param int $action
     * @param int $customerId
     * @param integer $websiteId
     * @return int
     */
    public function getTotalQtyRewards($action, $customerId, $websiteId)
    {
        $select = $this->_getReadAdapter()
            ->select()
            ->from(array('history_table' => $this->getMainTable()), array('COUNT(*)'))
            ->joinInner(array('reward_table' => $this->getTable('enterprise_reward/reward')),
                'history_table.reward_id = reward_table.reward_id', array())
            ->where('history_table.action=:action')
            ->where('reward_table.customer_id=:customer_id')
            ->where('history_table.website_id=:website_id');
        $bind = array(
            'action'      => $action,
            'customer_id' => $customerId,
            'website_id'  => $websiteId
        );
        return intval($this->_getReadAdapter()->fetchOne($select, $bind));
    }

    /**
     * Retrieve actual history records that have unused points, i.e. points_delta-points_used > 0
     * Update points_used field for non-used points
     *
     * @param Enterprise_Reward_Model_Reward_History $history
     * @param int $required Points total that required
     * @return Enterprise_Reward_Model_Mysql4_Reward_History
     */
    public function useAvailablePoints($history, $required)
    {
        $required = (int)abs($required);
        if (!$required) {
            return $this;
        }
        $adapter = $this->_getWriteAdapter();
        try {
            $adapter->beginTransaction();
            $select = $adapter->select()
                ->from(array('history' => $this->getMainTable()), array('history_id', 'points_delta', 'points_used'))
                ->where('reward_id = :reward_id')
                ->where('website_id = :website_id')
                ->where('is_expired=0')
                ->where('points_delta - points_used > 0')
                ->order('history_id')
                ->forUpdate(true);
            $bind = array(
                ':reward_id'  => $history->getRewardId(),
                ':website_id' => $history->getWebsiteId()
            );

            $stmt = $adapter->query($select, $bind);

            $updateSqlValues = array();
            $data = array();
            while ($row = $stmt->fetch()) {
                if ($required <= 0) {
                    break;
                }
                $rowAvailable = $row['points_delta'] - $row['points_used'];
                $pointsUsed = min($required, $rowAvailable);
                $required -= $pointsUsed;
                $newPointsUsed = $pointsUsed + $row['points_used'];
                $data[] = array(
                    'history_id' => $row['history_id'],
                    'points_used' => $newPointsUsed
                );
            }

            if (count($data) > 0) {
                $adapter->insertOnDuplicate($this->getMainTable(), $data, array('history_id', 'points_used'));
            }

            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;
        }

        return $this;
    }

     /**
     * Update history expired_at_dynamic field for specified websites when config changed
     *
     * @param int $days Reward Points Expire in (days)
     * @param array $websiteIds Array of website ids that must be updated
     */
    public function updateExpirationDate($days, $websiteIds)
    {
        $adapter = $this->_getWriteAdapter();
        $websiteIds = is_array($websiteIds) ? $websiteIds : array($websiteIds);
        $days = (int)abs($days);
        $update = array();
        if ($days) {
            $update['expired_at_dynamic'] = $adapter->getDateAddSql(
                'created_at', $days, Varien_Db_Adapter_Interface::INTERVAL_DAY
            );
        } else {
            $update['expired_at_dynamic'] = new Zend_Db_Expr('NULL');
        }
        $where = array('website_id IN (?)' => $websiteIds);
        $adapter->update($this->getMainTable(), $update, $where);
        return $this;
    }


    /**
     * Make points expired for specified website
     *
     * @param int $websiteId
     * @param string $expiryType Expiry calculation (static or dynamic)
     * @param int $limit Limitation for records expired selection
     * @return Enterprise_Reward_Model_Resource_Reward_History
     */
    public function expirePoints($websiteId, $expiryType, $limit)
    {
        $adapter = $this->_getWriteAdapter();
        $now = $this->formatDate(time());
        $field = $expiryType == 'static' ? 'expired_at_static' : 'expired_at_dynamic';

        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('website_id = :website_id')
            ->where("{$field} < :time_now")
            ->where("{$field} IS NOT NULL")
            ->where('is_expired=?', 0)
            ->where('points_delta-points_used > ?', 0)
            ->limit((int)$limit);
        $bind = array(
            ':website_id' => $websiteId,
            ':time_now'   => $now
        );
        $duplicates = array();
        $expiredAmounts = array();
        $expiredHistoryIds = array();
        $stmt = $adapter->query($select, $bind);
        while ($row = $stmt->fetch()) {
            $row['created_at'] = $now;
            $row['expired_at_static'] = null;
            $row['expired_at_dynamic'] = null;
            $row['is_expired'] = '1';
            $row['is_duplicate_of'] = $row['history_id'];
            $expiredHistoryIds[] = $row['history_id'];
            unset($row['history_id']);
            if (!isset($expiredAmounts[$row['reward_id']])) {
                $expiredAmounts[$row['reward_id']] = 0;
            }
            $expiredAmount = $row['points_delta'] - $row['points_used'];
            $row['points_delta'] = -$expiredAmount;
            $row['points_used'] = 0;
            $expiredAmounts[$row['reward_id']] += $expiredAmount;
            $duplicates[] = $row;
        }

        if (count($expiredHistoryIds) > 0) {
            // decrease points balance of rewards
            foreach ($expiredAmounts as $rewardId => $expired) {
                if ($expired == 0) {
                    continue;
                }
                $bind = array(
                    'points_balance' => $adapter->getCheckSql(
                        "points_balance > {$expired}",
                        "points_balance-{$expired}",
                        0
                    )
                );
                $where = array('reward_id=?' => $rewardId);
                $adapter->update($this->getTable('enterprise_reward/reward'), $bind, $where);
            }

            // duplicate expired records
            $adapter->insertMultiple($this->getMainTable(), $duplicates);

            // update is_expired field (using history ids instead where clause for better performance)
            $adapter->update(
                $this->getMainTable(),
                array('is_expired' => '1'),
                array('history_id IN (?)' => $expiredHistoryIds)
            );
        }

        return $this;
    }

    /**
     * Mark history records as notification was sent to customer (about points expiration)
     *
     * @param array $ids
     * @return Enterprise_Reward_Model_Resource_Reward_History
     */
    public function markAsNotified($ids)
    {
        $this->_getWriteAdapter()->update($this->getMainTable(),
            array('notification_sent' => 1),
            array('history_id IN (?)' => $ids)
        );
        return $this;
    }

    /**
     * Perform Row-level data update
     *
     * @param Enterprise_Reward_Model_Reward_History $object
     * @param array $data New data
     * @return Enterprise_Reward_Model_Resource_Reward_History
     */
    public function updateHistoryRow(Enterprise_Reward_Model_Reward_History $object, $data)
    {
        if (!$object->getId() || !is_array($data)) {
            return $this;
        }
        $where = array($this->getIdFieldName() . '=?' => $object->getId());
        $this->_getWriteAdapter()
            ->update($this->getMainTable(), $data, $where);
        return $this;
    }
}
