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
 * Cms Hierarchy Pages Lock Model
 *
 * @method Enterprise_Cms_Model_Resource_Hierarchy_Lock _getResource()
 * @method Enterprise_Cms_Model_Resource_Hierarchy_Lock getResource()
 * @method int getUserId()
 * @method Enterprise_Cms_Model_Hierarchy_Lock setUserId(int $value)
 * @method string getUserName()
 * @method Enterprise_Cms_Model_Hierarchy_Lock setUserName(string $value)
 * @method string getSessionId()
 * @method Enterprise_Cms_Model_Hierarchy_Lock setSessionId(string $value)
 * @method int getStartedAt()
 * @method Enterprise_Cms_Model_Hierarchy_Lock setStartedAt(int $value)
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */

/**
 * @deprecated since 1.12.0.0
 */
class Enterprise_Cms_Model_Hierarchy_Lock extends Mage_Core_Model_Abstract
{
    /**
     * Session model instance
     *
     * @var Mage_Admin_Model_Session
     */
    protected $_session;

    /**
     * Flag indicating whether lock data loaded or not
     *
     * @var bool
     */
    protected $_dataLoaded = false;

    /**
     * Resource model initializing
     */
    protected function _construct()
    {
        $this->_init('enterprise_cms/hierarchy_lock');
    }

    /**
     * Setter for session instance
     *
     * @param Mage_Core_Model_Session_Abstract $session
     * @return Enterprise_Cms_Model_Hierarchy_Lock
     */
    public function setSession(Mage_Core_Model_Session_Abstract $session)
    {
        $this->_session = $session;
        return $this;
    }

    /**
     * Getter for session instance
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    protected function _getSession()
    {
        if ($this->_session === null) {
            return Mage::getSingleton('admin/session');
        }
        return $this->_session;
    }

    /**
     * Load lock data
     *
     * @return Enterprise_Cms_Model_Hierarchy_Lock
     */
    public function loadLockData()
    {
        if (!$this->_dataLoaded) {
            $data = $this->_getResource()->getLockData();
            $this->addData($data);
            $this->_dataLoaded = true;
        }
        return $this;
    }

    /**
     * Check whether page is locked for current user
     *
     * @return bool
     */
    public function isLocked()
    {
        return ($this->isEnabled() && $this->isActual());
    }

    /**
     * Check whether lock belongs to current user
     *
     * @return bool
     */
    public function isLockedByMe()
    {
        return ($this->isLocked() && $this->isLockOwner());
    }

    /**
     * Check whether lock belongs to other user
     *
     * @return bool
     */
    public function isLockedByOther()
    {
        return ($this->isLocked() && !$this->isLockOwner());
    }

    /**
     * Revalidate lock data
     *
     * @return Enterprise_Cms_Model_Hierarchy_Lock
     */
    public function revalidate()
    {
        if (!$this->isEnabled()) {
            return $this;
        }
        if (!$this->isLocked() || $this->isLockedByMe()) {
            $this->lock();
        }
        return $this;
    }

    /**
     * Check whether lock is actual
     *
     * @return bool
     */
    public function isActual()
    {
        $this->loadLockData();
        if ($this->hasData('started_at') && $this->_getData('started_at') + $this->getLockLifeTime() > time()) {
            return true;
        }
        return false;
    }

    /**
     * Check whether lock functionality is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return ($this->getLockLifeTime() > 0);
    }

    /**
     * Check whether current user is lock owner or not
     *
     * @return bool
     */
    public function isLockOwner()
    {
        $this->loadLockData();
        if ($this->_getData('user_id') == $this->_getSession()->getUser()->getId()
            && $this->_getData('session_id') == $this->_getSession()->getSessionId())
        {
            return true;
        }
        return false;
    }

    /**
     * Create lock for page, previously deleting existing lock
     *
     * @return Enterprise_Cms_Model_Hierarchy_Lock
     */
    public function lock()
    {
        $this->loadLockData();
        if ($this->getId()) {
            $this->delete();
        }

        $this->setData(array(
            'user_id' => $this->_getSession()->getUser()->getId(),
            'user_name' => $this->_getSession()->getUser()->getName(),
            'session_id' => $this->_getSession()->getSessionId(),
            'started_at' => time()
        ));
        $this->save();

        return $this;
    }

    /**
     * Return lock lifetime in seconds
     *
     * @return int
     */
    public function getLockLifeTime()
    {
        $timeout = (int)Mage::getStoreConfig('cms/hierarchy/lock_timeout');
        return ($timeout != 0 && $timeout < 120 ) ? 120 : $timeout;

    }
}
