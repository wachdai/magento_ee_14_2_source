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
 * @package     Enterprise_AdminGws
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Websites fieldset for admin roles edit tab
 *
 */
class Enterprise_AdminGws_Block_Adminhtml_Permissions_Tab_Rolesedit_Gws extends Mage_Core_Block_Template
{
    /**
     * Check whether role assumes all websites permissions
     *
     * @return bool
     */
    public function getGwsIsAll()
    {
        if (!$this->canAssignGwsAll()) {
            return false;
        }

        if (!Mage::registry('current_role')->getId()) {
            return true;
        }

        return Mage::registry('current_role')->getGwsIsAll();
    }

    /**
     * Get the role object
     *
     * @return Mage_Admin_Model_Roles
     */
    public function getRole()
    {
        return Mage::registry('current_role');
    }

    /**
     * Check an ability to create 'no website restriction' roles
     *
     * @return bool
     */
    public function canAssignGwsAll()
    {
        return Mage::getSingleton('enterprise_admingws/role')->getIsAll();
    }

    /**
     * Gather disallowed store group ids and return them as Json
     *
     * @return string
     */
    public function getDisallowedStoreGroupsJson()
    {
        $result = array();
        foreach ($this->_getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $groupId = $group->getId();
                if (!$this->canAssignGwsAll() && !$this->_hasStoreGroupAccess($groupId))
                {
                    $result[$groupId] = $groupId;
                }
            }
        }
        return $this->_jsonEncode($result);
    }

    /**
     * Returns the websites
     *
     * @return array
     */
    protected function _getWebsites()
    {
        return Mage::app()->getWebsites();
    }


    /**
     * Checks whether the specified store group ID is allowed
     *
     * @param string|int $groupId
     * @return bool
     */
    protected function _hasStoreGroupAccess($groupId)
    {
        return Mage::getSingleton('enterprise_admingws/role')->hasStoreGroupAccess($groupId);
    }

    /**
     * Encodes the $result into the JSON format
     *
     * @param array $result
     * @return string
     */
    protected function _jsonEncode($result)
    {
        return Mage::helper('core')->jsonEncode($result);
    }
}
