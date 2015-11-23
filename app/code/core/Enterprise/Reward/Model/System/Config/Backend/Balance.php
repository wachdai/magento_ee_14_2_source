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
 * Backend model for "Reward Points Balance"
 *
 */
class Enterprise_Reward_Model_System_Config_Backend_Balance extends Mage_Core_Model_Config_Data
{
    /**
     * Check if max_points_balance >= than min_points_balance
     * (max allowed to RP to gain is more than minimum to redeem)
     *
     * @return Enterprise_Reward_Model_System_Config_Backend_Balance
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if (!$this->isValueChanged()) {
            return $this;
        }

        if ($this->getFieldsetDataValue('min_points_balance') < 0) {
            Mage::throwException(
                Mage::helper('enterprise_reward')->__('"Minimum Reward Points Balance" should be positive number or empty.')
            );
        }
        if ($this->getFieldsetDataValue('max_points_balance') < 0) {
            Mage::throwException(
                Mage::helper('enterprise_reward')->__('"Cap Reward Points Balance" should be positive number or empty.')
            );
        }
        if ($this->getFieldsetDataValue('max_points_balance') &&
            ($this->getFieldsetDataValue('min_points_balance') > $this->getFieldsetDataValue('max_points_balance'))) {
            Mage::throwException(
                Mage::helper('enterprise_reward')->__('"Minimum Reward Points Balance" should be less or equal to "Cap Reward Points Balance".')
            );
        }
        return $this;
    }
}
