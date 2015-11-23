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
 * Backend model for "Reward Points Lifetime"
 *
 */
class Enterprise_Reward_Model_System_Config_Backend_Expiration extends Mage_Core_Model_Config_Data
{
    const XML_PATH_EXPIRATION_DAYS = 'enterprise_reward/general/expiration_days';

    /**
     * Update history expiration date to simplify frontend calculations
     *
     * @return Enterprise_Reward_Model_System_Config_Backend_Expiration
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if (!$this->isValueChanged()) {
            return $this;
        }

        $websiteIds = array();
        if ($this->getWebsiteCode()) {
            $websiteIds = array(Mage::app()->getWebsite($this->getWebsiteCode())->getId());
        } else {
            $collection = Mage::getResourceModel('core/config_data_collection')
                ->addFieldToFilter('path', self::XML_PATH_EXPIRATION_DAYS)
                ->addFieldToFilter('scope', 'websites');
            $websiteScopeIds = array();
            foreach ($collection as $item) {
                $websiteScopeIds[] = $item->getScopeId();
            }
            foreach (Mage::app()->getWebsites() as $website) {
                /* @var $website Mage_Core_Model_Website */
                if (!in_array($website->getId(), $websiteScopeIds)) {
                    $websiteIds[] = $website->getId();
                }
            }
        }
        if (count($websiteIds) > 0) {
            Mage::getResourceModel('enterprise_reward/reward_history')
                ->updateExpirationDate($this->getValue(), $websiteIds);
        }

        return $this;
    }

    /**
     * The same as _beforeSave, but executed when website config extends default values
     *
     * @return Enterprise_Reward_Model_System_Config_Backend_Expiration
     */
    protected function _beforeDelete()
    {
        parent::_beforeDelete();
        if ($this->getWebsiteCode()) {
            $default = (string)Mage::getConfig()->getNode('default/' . self::XML_PATH_EXPIRATION_DAYS);
            $websiteIds = array(Mage::app()->getWebsite($this->getWebsiteCode())->getId());
            Mage::getResourceModel('enterprise_reward/reward_history')
                ->updateExpirationDate($default, $websiteIds);
        }
        return $this;
    }
}
