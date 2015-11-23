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
 * Reward history model
 *
 * @method Enterprise_Reward_Model_Resource_Reward_History _getResource()
 * @method Enterprise_Reward_Model_Resource_Reward_History getResource()
 * @method int getRewardId()
 * @method Enterprise_Reward_Model_Reward_History setRewardId(int $value)
 * @method int getWebsiteId()
 * @method Enterprise_Reward_Model_Reward_History setWebsiteId(int $value)
 * @method int getStoreId()
 * @method Enterprise_Reward_Model_Reward_History setStoreId(int $value)
 * @method int getAction()
 * @method Enterprise_Reward_Model_Reward_History setAction(int $value)
 * @method int getEntity()
 * @method Enterprise_Reward_Model_Reward_History setEntity(int $value)
 * @method int getPointsBalance()
 * @method Enterprise_Reward_Model_Reward_History setPointsBalance(int $value)
 * @method int getPointsDelta()
 * @method Enterprise_Reward_Model_Reward_History setPointsDelta(int $value)
 * @method int getPointsUsed()
 * @method Enterprise_Reward_Model_Reward_History setPointsUsed(int $value)
 * @method int getPointsVoided()
 * @method Enterprise_Reward_Model_Reward_History setPointsVoided(int $value)
 * @method float getCurrencyAmount()
 * @method Enterprise_Reward_Model_Reward_History setCurrencyAmount(float $value)
 * @method float getCurrencyDelta()
 * @method Enterprise_Reward_Model_Reward_History setCurrencyDelta(float $value)
 * @method string getBaseCurrencyCode()
 * @method Enterprise_Reward_Model_Reward_History setBaseCurrencyCode(string $value)
 * @method Enterprise_Reward_Model_Reward_History setAdditionalData(string $value)
 * @method string getComment()
 * @method Enterprise_Reward_Model_Reward_History setComment(string $value)
 * @method string getCreatedAt()
 * @method Enterprise_Reward_Model_Reward_History setCreatedAt(string $value)
 * @method string getExpiredAtStatic()
 * @method Enterprise_Reward_Model_Reward_History setExpiredAtStatic(string $value)
 * @method string getExpiredAtDynamic()
 * @method Enterprise_Reward_Model_Reward_History setExpiredAtDynamic(string $value)
 * @method int getIsExpired()
 * @method Enterprise_Reward_Model_Reward_History setIsExpired(int $value)
 * @method int getIsDuplicateOf()
 * @method Enterprise_Reward_Model_Reward_History setIsDuplicateOf(int $value)
 * @method int getNotificationSent()
 * @method Enterprise_Reward_Model_Reward_History setNotificationSent(int $value)
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Model_Reward_History extends Mage_Core_Model_Abstract
{
    protected $_reward = null;
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->_init('enterprise_reward/reward_history');
    }

    /**
     * Processing object before save data.
     * Prepare history data
     *
     * @return Enterprise_Reward_Model_Reward_History
     */
    protected function _beforeSave()
    {
        if ($this->getWebsiteId()) {
            $this->setBaseCurrencyCode(
                Mage::app()->getWebsite($this->getWebsiteId())->getBaseCurrencyCode()
            );
        }
        if ($this->getPointsDelta() < 0) {
            $this->_spendAvailablePoints($this->getPointsDelta());
        }

        $now = time();
        $this->addData(array(
            'created_at' => $this->getResource()->formatDate($now),
            'expired_at_static' => null,
            'expired_at_dynamic' => null,
            'notification_sent' => 0
        ));

        $lifetime = (int)Mage::helper('enterprise_reward')->getGeneralConfig('expiration_days', $this->getWebsiteId());
        if ($lifetime > 0) {
            $expires = $now + $lifetime * 86400;
            $expires = $this->getResource()->formatDate($expires);
            $this->addData(array(
                'expired_at_static' => $expires,
                'expired_at_dynamic' => $expires,
            ));
        }

        return parent::_beforeSave();
    }

    /**
     * Setter
     *
     * @param Enterprise_Reward_Model_Reward $reward
     * @return Enterprise_Reward_Model_Reward_History
     */
    public function setReward($reward)
    {
        $this->_reward = $reward;
        return $this;
    }

    /**
     * Getter
     *
     * @return Enterprise_Reward_Model_Reward
     */
    public function getReward()
    {
        return $this->_reward;
    }

    /**
     * Create history data from reward object
     *
     * @return Enterprise_Reward_Model_Reward_History
     */
    public function prepareFromReward()
    {
        $store = $this->getReward()->getStore();
        if ($store === null) {
            $store = Mage::app()->getStore();
        }
        $this->setRewardId($this->getReward()->getId())
            ->setWebsiteId($this->getReward()->getWebsiteId())
            ->setStoreId($store->getId())
            ->setPointsBalance($this->getReward()->getPointsBalance())
            ->setPointsDelta($this->getReward()->getPointsDelta())
            ->setCurrencyAmount($this->getReward()->getCurrencyAmount())
            ->setCurrencyDelta($this->getReward()->getCurrencyDelta())
            ->setAction($this->getReward()->getAction())
            ->setComment($this->getReward()->getComment());

        $this->addAdditionalData(array(
            'rate' => array(
                'points' => $this->getReward()->getRate()->getPoints(),
                'currency_amount' => $this->getReward()->getRate()->getCurrencyAmount(),
                'direction' => $this->getReward()->getRate()->getDirection(),
                'currency_code' => Mage::app()->getWebsite($this->getReward()->getWebsiteId())->getBaseCurrencyCode()
            )
        ));

        if ($this->getReward()->getIsCappedReward()) {
            $this->addAdditionalData(array(
                'is_capped_reward' => true,
                'cropped_points'    => $this->getReward()->getCroppedPoints()
            ));
        }
        return $this;
    }

    /**
     * Getter.
     * Unserialize if need
     *
     * @return array
     */
    public function getAdditionalData()
    {
        if (is_string($this->_getData('additional_data'))) {
            $this->setData('additional_data', unserialize($this->_getData('additional_data')));
        }
        return $this->_getData('additional_data');
    }

    /**
     * Getter.
     * Return value of unserialized additional data item by given item key
     *
     * @param string $key
     * @return mixed | null
     */
    public function getAdditionalDataByKey($key)
    {
        $data = $this->getAdditionalData();
        if (is_array($data) && !empty($data) && isset($data[$key])) {
            return $data[$key];
        }
        return null;
    }

    /**
     * Add additional values to additional_data
     *
     * @param array $data
     * @return Enterprise_Reward_Model_Reward_History
     */
    public function addAdditionalData($data)
    {
        if (is_array($data)) {
            $additional = $this->getDataSetDefault('additional_data', array());
            foreach ($data as $k => $v) {
                $additional[$k] = $v;
            }
            $this->setData('additional_data', $additional);
        }

        return $this;
    }

    /**
     * Retrieve translated and prepared message
     *
     * @return string
     */
    public function getMessage()
    {
        if (!$this->hasData('message')) {
            $action = Mage::getSingleton('enterprise_reward/reward')->getActionInstance($this->getAction());
            $message = '';
            if ($action !== null) {
                $message = $action->getHistoryMessage($this->getAdditionalData());
            }
            $this->setData('message', $message);
        }
        return $this->_getData('message');
    }

    /**
     * Rate text getter
     *
     * @return string|null
     */
    public function getRateText()
    {
        $rate = $this->getAdditionalDataByKey('rate');
        if (isset($rate['points']) && isset($rate['currency_amount']) && isset($rate['direction'])) {
            return Enterprise_Reward_Model_Reward_Rate::getRateText(
                (int)$rate['direction'], (int)$rate['points'], (float)$rate['currency_amount'],
                $this->getBaseCurrencyCode()
            );
        }
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
        $result = $this->_getResource()->isExistHistoryUpdate($customerId, $action, $websiteId, $entity);
        return $result;
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
        return $this->_getResource()->getTotalQtyRewards($action, $customerId, $websiteId);
    }

    /**
     * Getter for date when the record is supposed to expire
     *
     * @return string|null
     */
    public function getExpiresAt()
    {
        if ($this->getPointsDelta() <= 0) {
            return null;
        }
        return Mage::helper('enterprise_reward')->getGeneralConfig('expiry_calculation') == 'static'
            ? $this->getExpiredAtStatic() : $this->getExpiredAtDynamic()
        ;
    }

    /**
     * Spend unused points for required amount
     *
     * @param int $required Points total that required
     * @return Enterprise_Reward_Model_Reward_History
     */
    protected function _spendAvailablePoints($required)
    {
        $this->getResource()->useAvailablePoints($this, $required);
        return $this;
    }
}
