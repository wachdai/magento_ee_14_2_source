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
 * Reward rate model
 *
 * @method Enterprise_Reward_Model_Resource_Reward_Rate _getResource()
 * @method Enterprise_Reward_Model_Resource_Reward_Rate getResource()
 * @method int getWebsiteId()
 * @method Enterprise_Reward_Model_Reward_Rate setWebsiteId(int $value)
 * @method int getCustomerGroupId()
 * @method Enterprise_Reward_Model_Reward_Rate setCustomerGroupId(int $value)
 * @method int getDirection()
 * @method Enterprise_Reward_Model_Reward_Rate setDirection(int $value)
 * @method Enterprise_Reward_Model_Reward_Rate setPoints(int $value)
 * @method Enterprise_Reward_Model_Reward_Rate setCurrencyAmount(float $value)
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Model_Reward_Rate extends Mage_Core_Model_Abstract
{
    const RATE_EXCHANGE_DIRECTION_TO_CURRENCY = 1;
    const RATE_EXCHANGE_DIRECTION_TO_POINTS   = 2;

    /**
     * Rate text getter
     *
     * @param int $direction
     * @param int $points
     * @param float $amount
     * @param string $currencyCode
     * @return string|null
     */
    public static function getRateText($direction, $points, $amount, $currencyCode = null)
    {
        switch ($direction) {
            case self::RATE_EXCHANGE_DIRECTION_TO_CURRENCY:
                return Mage::helper('enterprise_reward')->formatRateToCurrency($points, $amount, $currencyCode);
            case self::RATE_EXCHANGE_DIRECTION_TO_POINTS:
                return Mage::helper('enterprise_reward')->formatRateToPoints($points, $amount, $currencyCode);
        }
    }

    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->_init('enterprise_reward/reward_rate');
    }

    /**
     * Processing object before save data.
     * Prepare rate data
     *
     * @return Enterprise_Reward_Model_Reward_Rate
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $this->_prepareRateValues();
        return $this;
    }

    /**
     * Validate rate data
     *
     * @return boolean | string
     */
    public function validate()
    {
        return true;
    }

    /**
     * Reset rate data
     *
     * @return Enterprise_Reward_Model_Reward_Rate
     */
    public function reset()
    {
        $this->setData(array());
        return $this;
    }

    /**
     * Check if given rate data (website, customer group, direction)
     * is unique to current (already loaded) rate
     *
     * @param integer $websiteId
     * @param integer $customerGroupId
     * @param integer $direction
     * @return boolean
     */
    public function getIsRateUniqueToCurrent($websiteId, $customerGroupId, $direction)
    {
        $data = $this->_getResource()->getRateData($websiteId, $customerGroupId, $direction);
        if ($data && $data['rate_id'] != $this->getId()) {
            return false;
        }
        return true;
    }

    /**
     * Prepare values in order to defined direction
     *
     * @return Enterprise_Reward_Model_Reward_Rate
     */
    protected function _prepareRateValues()
    {
        if ($this->_getData('direction') == self::RATE_EXCHANGE_DIRECTION_TO_CURRENCY) {
            $this->setData('points', (int)$this->_getData('value'));
            $this->setData('currency_amount', (float)$this->_getData('equal_value'));
        } elseif ($this->_getData('direction') == self::RATE_EXCHANGE_DIRECTION_TO_POINTS) {
            $this->setData('currency_amount', (float)$this->_getData('value'));
            $this->setData('points', (int)$this->_getData('equal_value'));
        }
        return $this;
    }

    /**
     * Fetch rate by customer group and website
     *
     * @param integer $customerGroupId
     * @param integer $websiteId
     * @return Enterprise_Reward_Model_Reward_Rate
     */
    public function fetch($customerGroupId, $websiteId, $direction) {
        $this->setData('original_website_id', $websiteId)
            ->setData('original_customer_group_id', $customerGroupId);
        $this->_getResource()->fetch($this, $customerGroupId, $websiteId, $direction);
        return $this;
    }

    /**
     * Calculate currency amount of given points by rate
     *
     * @param integer $points
     * @param bool Whether to round points to integer or not
     * @return float
     */
    public function calculateToCurrency($points, $rounded = true)
    {
        $amount = 0;
        if ($this->getPoints()) {
            if ($rounded) {
                $roundedPoints = (int)($points/$this->getPoints());
            } else {
                $roundedPoints = round($points/$this->getPoints(), 2);
            }
            if ($roundedPoints) {
                $amount = $this->getCurrencyAmount()*$roundedPoints;
            }
        }
        return (float)$amount;
    }

    /**
     * Calculate points of given amount by rate
     *
     * @param float $amount
     * @return integer
     */
    public function calculateToPoints($amount)
    {
        $points = 0;
        if ($this->getCurrencyAmount() && $amount >= $this->getCurrencyAmount()) {
            /**
             * Type casting made in such way to avoid wrong automatic type casting and calculation.
             * $amount always int and $this->getCurrencyAmount() is string or float
             */
            $amountValue = (int)((string)$amount/(string)$this->getCurrencyAmount());
            if ($amountValue) {
                $points = $this->getPoints()*$amountValue;
            }
        }
        return $points;
    }

    /**
     * Retrieve option array of rate directions with labels
     *
     * @return array
     */
    public function getDirectionsOptionArray()
    {
        $optArray = array(
            self::RATE_EXCHANGE_DIRECTION_TO_CURRENCY => Mage::helper('enterprise_reward')->__('Points to Currency'),
            self::RATE_EXCHANGE_DIRECTION_TO_POINTS => Mage::helper('enterprise_reward')->__('Currency to Points')
        );
        return $optArray;
    }

    /**
     * Getter for currency part of the rate
     * Formatted value returns string
     *
     * @param bool $formatted
     * @return mixed|string
     */
    public function getCurrencyAmount($formatted = false)
    {
        $amount = $this->_getData('currency_amount');
        if ($formatted) {
            $websiteId = $this->getOriginalWebsiteId();
            if ($websiteId === null) {
                $websiteId = $this->getWebsiteId();
            }
            $currencyCode = Mage::app()->getWebsite($websiteId)->getBaseCurrencyCode();
            return Mage::app()->getLocale()->currency($currencyCode)->toCurrency($amount);
        }
        return $amount;
    }

    /**
     * Getter for points part of the rate
     * Formatted value returns as int
     *
     * @param bool $formatted
     * @return mixed|int
     */
    public function getPoints($formatted = false)
    {
        $pts = $this->_getData('points');
        return $formatted ? (int)$pts : $pts;
    }
}
