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
 * @package     Enterprise_Cms
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Cms page revision collection
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_Cms_Model_Resource_Page_Collection_Abstract
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Array of admin users in loaded collection
     *
     * @var array
     */
    protected $_usersHash  = null;

    /**
     * Initialization
     *
     */
    protected function _construct()
    {
        $this->_map['fields']['user_id'] = 'main_table.user_id';
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

    /**
     * Add filtering by page id.
     * Parameter $page can be int or cms page object.
     *
     * @param mixed $page
     * @return Enterprise_Cms_Model_Resource_Page_Collection_Abstract
     */
    public function addPageFilter($page)
    {
        if ($page instanceof Mage_Cms_Model_Page) {
            $page = $page->getId();
        }

        if (is_array($page)) {
            $page = array('in' => $page);
        }

        $this->addFieldTofilter('page_id', $page);

        return $this;
    }

    /**
     * Adds filter by version access level specified by owner.
     *
     * @param mixed $userId
     * @param mixed $accessLevel
     * @return Enterprise_Cms_Model_Resource_Page_Collection_Abstract
     */
    public function addVisibilityFilter($userId, $accessLevel = Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PUBLIC)
    {
        $_condition = array();

        if (is_array($userId)) {
            $_condition[] = $this->_getConditionSql(
                $this->_getMappedField('user_id'), array('in' => $userId));
        } else if ($userId){
            $_condition[] = $this->_getConditionSql(
                $this->_getMappedField('user_id'), $userId);
        }

        if (is_array($accessLevel)) {
            $_condition[] = $this->_getConditionSql(
                $this->_getMappedField('access_level'), array('in' => $accessLevel));
        } else {
            $_condition[] = $this->_getConditionSql(
                $this->_getMappedField('access_level'), $accessLevel);
        }

        $this->getSelect()->where(implode(' OR ', $_condition));

        return $this;
    }

    /**
     * Mapping user_id to user column with additional value for non-existent users
     *
     * @return Enterprise_Cms_Model_Resource_Page_Collection_Abstract
     */
    public function addUserColumn()
    {
        $userField = $this->getConnection()->getIfNullSql('main_table.user_id', '-1');
        $this->getSelect()->columns(array('user' => $userField));

        $this->_map['fields']['user'] = $userField;

        return $this;
    }

    /**
     * Join username from system user table
     *
     * @return Enterprise_Cms_Model_Resource_Page_Collection_Abstract
     */
    public function addUserNameColumn()
    {
        if (!$this->getFlag('user_name_column_joined')) {
            $userField = $this->getConnection()->getIfNullSql('ut.username', '-1');
            $this->getSelect()->joinLeft(
                array('ut' => $this->getTable('admin/user')),
                'ut.user_id = main_table.user_id',
                array('username' => $userField));

            $this->setFlag('user_name_column_joined', true);
        }

        return $this;
    }

    /**
     * Retrieve array of admin users in collection
     *
     * @param bool $idAsKey default true if false then name will be used as key and value
     * @return array
     */
    public function getUsersArray($idAsKey = true)
    {
        if (!$this->_usersHash) {
            $this->_usersHash = array();
            foreach ($this->_toOptionHash('user_id', 'username') as $userId => $username) {
                if ($userId) {
                    if ($idAsKey) {
                        $this->_usersHash[$userId]   = $username;
                    } else {
                        $this->_usersHash[$username] = $username;
                    }
                } else {
                    $this->_usersHash['-1'] = Mage::helper('enterprise_cms')->__('[No Owner]');
                }
            }

            ksort($this->_usersHash);
        }
        return $this->_usersHash;
    }

    /**
     * Add filtering by user id.
     *
     * @param int|null $userId
     * @return Enterprise_Cms_Model_Resource_Page_Collection_Abstract
     */
    public function addUserIdFilter($userId = null)
    {
        if ($userId === null) {
            $condition = array('null' => true);
        } else {
            $condition = (int)$userId;
        }

        $this->addFieldTofilter('user_id', $condition);

        return $this;
    }
}
