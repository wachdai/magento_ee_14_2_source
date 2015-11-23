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
 * Reward resource model
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Model_Resource_Reward extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Internal constructor
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_reward/reward', 'reward_id');
    }

    /**
     * Fetch reward by customer and website and set data to reward object
     *
     * @param Enterprise_Reward_Model_Reward $reward
     * @param integer $customerId
     * @param integer $websiteId
     * @return Enterprise_Reward_Model_Resource_Reward
     */
    public function loadByCustomerId(Enterprise_Reward_Model_Reward $reward, $customerId, $websiteId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('customer_id = :customer_id')
            ->where('website_id = :website_id');
        $bind = array(
            ':customer_id' => $customerId,
            ':website_id'  => $websiteId
        );
        if ($data = $this->_getReadAdapter()->fetchRow($select, $bind)) {
            $reward->addData($data);
        }
        $this->_afterLoad($reward);
        return $this;
    }

    /**
     * Perform Row-level data update
     *
     * @param Enterprise_Reward_Model_Reward $object
     * @param array $data New data
     * @return Enterprise_Reward_Model_Resource_Reward
     */
    public function updateRewardRow(Enterprise_Reward_Model_Reward $object, $data)
    {
        if (!$object->getId() || !is_array($data)) {
            return $this;
        }
        $where = array($this->getIdFieldName() . '=?' => $object->getId());
        $this->_getWriteAdapter()
            ->update($this->getMainTable(), $data, $where);
        return $this;
    }

    /**
     * Prepare orphan points by given website id and website base currency code
     * after website was deleted
     *
     * @param integer $websiteId
     * @param string $baseCurrencyCode
     * @return Enterprise_Reward_Model_Resource_Reward
     */
    public function prepareOrphanPoints($websiteId, $baseCurrencyCode)
    {
        $adapter = $this->_getWriteAdapter();
        if ($websiteId) {
            $adapter->update($this->getMainTable(),
                array(
                    'website_id' => null,
                    'website_currency_code' => $baseCurrencyCode
                ), array('website_id = ?' => $websiteId));
        }
        return $this;
    }

    /**
     * Delete orphan (points of deleted website) points by given customer
     *
     * @param int $customerId
     * @return Enterprise_Reward_Model_Resource_Reward
     */
    public function deleteOrphanPointsByCustomer($customerId)
    {
        if ($customerId) {
            $this->_getWriteAdapter()->delete($this->getMainTable(),
                array(
                    'customer_id = ?' => $customerId,
                    new Zend_Db_Expr('website_id IS NULL')
                )
            );
        }
        return $this;
    }

    /**
     * Save salesrule reward points delta
     *
     * @param integer $ruleId
     * @param integer $pointsDelta
     * @return Enterprise_Reward_Model_Resource_Reward
     */
    public function saveRewardSalesrule($ruleId, $pointsDelta)
    {
        $this->_getWriteAdapter()->insertOnDuplicate(
            $this->getTable('enterprise_reward/reward_salesrule'),
            array(
                'rule_id' => $ruleId,
                'points_delta' => $pointsDelta
            ),
            array('points_delta')
        );

        return $this;
    }

    /**
     * Retrieve reward salesrule data by given rule Id or array of Ids
     *
     * @param integer | array $rule
     * @return array
     */
    public function getRewardSalesrule($rule)
    {
        $data = array();
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('enterprise_reward/reward_salesrule'))
            ->where('rule_id IN (?)', $rule);
        if (is_array($rule)) {
            $data = $this->_getReadAdapter()->fetchAll($select);
        } else {
            $data = $this->_getReadAdapter()->fetchRow($select);
        }
        return $data;
    }
}
