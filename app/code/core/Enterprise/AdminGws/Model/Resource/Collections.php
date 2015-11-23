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
 * Collections limiter resource model
 *
 * @category    Enterprise
 * @package     Enterprise_AdminGws
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_AdminGws_Model_Resource_Collections extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Class construction & resource initialization
     */
    protected function _construct()
    {
        $this->_init('admin/role', 'role_id');
    }

    /**
     * Retreive role ids that has higher/other gws roles
     *
     * @param int $isAll
     * @param array $allowedWebsites
     * @param array $allowedStoreGroups
     * @return array
     */
    public function getRolesOutsideLimitedScope($isAll, $allowedWebsites, $allowedStoreGroups)
    {
        $result = array();
        if (!$isAll) {
            $select = $this->_getReadAdapter()->select();
            $select->from(
                $this->getTable('admin/role'),
                array(
                    'role_id',
                    'gws_is_all',
                    'gws_websites',
                    'gws_store_groups'
                )
            );
            $select->where('parent_id = ?', 0);
            $roles = $this->_getReadAdapter()->fetchAll($select);

            foreach ($roles as $role) {
                $roleStoreGroups = Mage::helper('enterprise_admingws')->explodeIds($role['gws_store_groups']);
                $roleWebsites = Mage::helper('enterprise_admingws')->explodeIds($role['gws_websites']);

                $hasAllPermissions = ($role['gws_is_all'] == 1);

                if ($hasAllPermissions) {
                    $result[] = $role['role_id'];
                    continue;
                }

                if ($allowedWebsites) {
                    foreach ($roleWebsites as $website) {
                        if (!in_array($website, $allowedWebsites)) {
                            $result[] = $role['role_id'];
                            continue 2;
                        }
                    }
                } else if ($roleWebsites) {
                    $result[] = $role['role_id'];
                    continue;
                }

                if ($allowedStoreGroups) {
                    foreach ($roleStoreGroups as $group) {
                        if (!in_array($group, $allowedStoreGroups)) {
                            $result[] = $role['role_id'];
                            continue 2;
                        }
                    }
                } else if ($roleStoreGroups) {
                    $result[] = $role['role_id'];
                    continue;
                }
            }
        }

        return $result;
    }

    /**
     * Retreive user ids that has higher/other gws roles
     *
     * @param int $isAll
     * @param array $allowedWebsites
     * @param array $allowedStoreGroups
     * @return array
     */
    public function getUsersOutsideLimitedScope($isAll, $allowedWebsites, $allowedStoreGroups)
    {
        $result = array();

        $limitedRoles = $this->getRolesOutsideLimitedScope($isAll, $allowedWebsites, $allowedStoreGroups);
        if ($limitedRoles) {
            $select = $this->_getReadAdapter()->select();
            $select->from($this->getTable('admin/role'), array('user_id'))
                ->where('parent_id IN (?)', $limitedRoles);

            $users = $this->_getReadAdapter()->fetchCol($select);

            if ($users) {
                $result = $users;
            }
        }

        return $result;
    }
}
