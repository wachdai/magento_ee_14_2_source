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
 * Reward rate resource model
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Model_Resource_Reward_Rate extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Internal constructor
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_reward/reward_rate', 'rate_id');
    }

    /**
     * Fetch rate customer group and website
     *
     * @param Enterprise_Reward_Model_Reward_Rate $rate
     * @param int $customerGroupId
     * @param int $websiteId
     * @param int $direction
     * @return Enterprise_Reward_Model_Resource_Reward_Rate
     */
    public function fetch(Enterprise_Reward_Model_Reward_Rate $rate, $customerGroupId, $websiteId, $direction)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('website_id IN (:website_id, 0)')
            ->where('customer_group_id IN (:customer_group_id, 0)')
            ->where('direction = :direction')
            ->order('customer_group_id DESC')
            ->order('website_id DESC')
            ->limit(1);

        $bind = array(
            ':website_id'        => (int)$websiteId,
            ':customer_group_id' => (int)$customerGroupId,
            ':direction'         => $direction
        );

        $row = $this->_getReadAdapter()->fetchRow($select, $bind);
        if ($row) {
            $rate->addData($row);
        }

        $this->_afterLoad($rate);
        return $this;
    }

    /**
     * Retrieve rate data bu given params or empty array if rate with such params does not exists
     *
     * @param integer $websiteId
     * @param integer $customerGroupId
     * @param integer $direction
     * @return array
     */
    public function getRateData($websiteId, $customerGroupId, $direction)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('website_id = :website_id')
            ->where('customer_group_id = :customer_group_id')
            ->where('direction = :direction');
        $bind = array(
            ':website_id'        => (int)$websiteId,
            ':customer_group_id' => (int)$customerGroupId,
            ':direction'         => $direction
        );
        $data = $this->_getReadAdapter()->fetchRow($select, $bind);
        if ($data) {
            return $data;
        }

        return array();
    }
}
