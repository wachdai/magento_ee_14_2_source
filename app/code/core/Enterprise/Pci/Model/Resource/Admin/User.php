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
 * @package     Enterprise_Pci
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Admin user resource model
 *
 * @category    Enterprise
 * @package     Enterprise_Pci
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pci_Model_Resource_Admin_User extends Mage_Admin_Model_Resource_User
{
    /**
     * Unlock specified user record(s)
     *
     * @param array $userIds
     * @return int number of affected rows
     */
    public function unlock($userIds)
    {
        if (!is_array($userIds)) {
            $userIds = array($userIds);
        }
        return $this->_getWriteAdapter()->update($this->getMainTable(), array(
            'failures_num'  => 0,
            'first_failure' => null,
            'lock_expires'  => null,
        ), $this->getIdFieldName() . ' IN (' . $this->_getWriteAdapter()->quote($userIds) . ')');
    }

    /**
     * Lock specified user record(s)
     *
     * @param array $userIds
     * @param int $exceptId
     * @param int $lifetime
     * @return int number of affected rows
     */
    public function lock($userIds, $exceptId, $lifetime)
    {
        if (!is_array($userIds)) {
            $userIds = array($userIds);
        }
        $exceptId = (int)$exceptId;
        return $this->_getWriteAdapter()->update($this->getMainTable(),
            array('lock_expires'  => $this->formatDate(time() + $lifetime),),
            "{$this->getIdFieldName()} IN (" . $this->_getWriteAdapter()->quote($userIds) . ")
            AND {$this->getIdFieldName()} <> {$exceptId}"
        );
    }

    /**
     * Increment failures count along with updating lock expire and first failure dates
     *
     * @param Mage_Admin_Model_User $user
     * @param int|false $setLockExpires
     * @param int|false $setFirstFailure
     */
    public function updateFaiure($user, $setLockExpires = false, $setFirstFailure = false)
    {
        $update = array('failures_num' => new Zend_Db_Expr('failures_num + 1'));
        if (false !== $setFirstFailure) {
            $update['first_failure'] = $this->formatDate($setFirstFailure);
            $update['failures_num']  = 1;
        }
        if (false !== $setLockExpires) {
            $update['lock_expires'] = $this->formatDate($setLockExpires);
        }
        $this->_getWriteAdapter()->update($this->getMainTable(), $update,
            $this->_getWriteAdapter()->quoteInto("{$this->getIdFieldName()} = ?", $user->getId())
        );
    }

    /**
     * Purge and get remaining old password hashes
     *
     * @param Mage_Admin_Model_User $user
     * @param int $retainLimit
     * @return array
     */
    public function getOldPasswords($user, $retainLimit = 4)
    {
        $userId = (int)$user->getId();
        $table  = $this->getTable('enterprise_pci/admin_passwords');

        // purge expired passwords, except that should retain
        $retainPasswordIds = $this->_getWriteAdapter()->fetchCol(
            $this->_getWriteAdapter()->select()
                ->from($table, 'password_id')
                ->where('user_id = :user_id')
                ->order('expires ' . Varien_Db_Select::SQL_DESC)
                ->order('password_id ' . Varien_Db_Select::SQL_DESC)
                ->limit($retainLimit),
            array(':user_id' => $userId)
        );
        $where = array('user_id = ?' => $userId, 'expires <= ?' => time());
        if ($retainPasswordIds) {
            $where['password_id NOT IN (?)'] = $retainPasswordIds;
        }
        $this->_getWriteAdapter()->delete($table, $where);

        // now get all remained passwords
        return $this->_getReadAdapter()->fetchCol(
            $this->_getReadAdapter()->select()
                ->from($table, 'password_hash')
                ->where('user_id = :user_id'),
            array(':user_id' => $userId)
        );
    }

    /**
     * Remember a password hash for further usage
     *
     * @param Mage_Admin_Model_User $user
     * @param string $passwordHash
     * @param int $lifetime
     */
    public function trackPassword($user, $passwordHash, $lifetime)
    {
        $now = time();
        $this->_getWriteAdapter()->insert($this->getTable('enterprise_pci/admin_passwords'), array(
            'user_id'       => $user->getId(),
            'password_hash' => $passwordHash,
            'expires'       => $now + $lifetime,
            'last_updated'  => $now,
        ));
    }

    /**
     * Get latest password for specified user id
     * Possible false positive when password was changed several times with different lifetime configuration
     *
     * @param int $userId
     * @return array
     */
    public function getLatestPassword($userId)
    {
        return $this->_getReadAdapter()->fetchRow(
            $this->_getReadAdapter()->select()
                ->from($this->getTable('enterprise_pci/admin_passwords'))
                ->where('user_id = :user_id')
                ->order('password_id ' . Varien_Db_Select::SQL_DESC)
                ->limit(1),
            array(':user_id' => $userId)
        );
    }
}
