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
 * Permissions observer
 *
 */
class Enterprise_AdminGws_Model_Observer extends Enterprise_AdminGws_Model_Observer_Abstract
{
    const XML_PATH_ACL_DENY_RULES = 'adminhtml/enterprise/admingws/acl_deny';
    const XML_PATH_VALIDATE_CALLBACK = 'adminhtml/enterprise/admingws/';

    const ACL_WEBSITE_LEVEL = 'website_level';
    const ACL_STORE_LEVEL = 'store_level';

    /**
     * @var Mage_Core_Model_Mysql4_Store_Group_Collection
     */
    protected $_storeGroupCollection;
    protected $_callbacks      = array();
    protected $_controllersMap = null;

    /**
     * Put websites/stores permissions data after loading admin role
     *
     * If all permissions are allowed, all possible websites / store groups / stores will be set
     * If only websites selected, all their store groups and stores will be set as well
     *
     * @param  Varien_Event_Observer $observer
     * @return Enterprise_Permissions_Model_Observer
     */
    public function addDataAfterRoleLoad($observer)
    {
        $object   = $observer->getEvent()->getObject();
        $gwsIsAll = (bool)(int)$object->getData('gws_is_all');
        $object->setGwsIsAll($gwsIsAll);

        $storeGroupIds = array();

        // set all websites and store groups
        if ($gwsIsAll) {
            $object->setGwsWebsites(array_keys(Mage::app()->getWebsites()));
            foreach ($this->_getAllStoreGroups() as $storeGroup) {
                $storeGroupIds[] = $storeGroup->getId();
            }
            $object->setGwsStoreGroups($storeGroupIds);
        }
        else {
            // set selected website ids
            $websiteIds = ($object->getData('gws_websites') != '' ?
                    explode(',', $object->getData('gws_websites')) :
                    array());
            $object->setGwsWebsites($websiteIds);

            // set either the set store group ids or all of allowed websites
            if ($object->getData('gws_store_groups') != '') {
                $storeGroupIds = explode(',', $object->getData('gws_store_groups'));
            }
            else {
                if ($websiteIds) {
                    foreach ($this->_getAllStoreGroups() as $storeGroup) {
                        if (in_array($storeGroup->getWebsiteId(), $websiteIds)) {
                            $storeGroupIds[] = $storeGroup->getId();
                        }
                    }
                }
            }
            $object->setGwsStoreGroups($storeGroupIds);
        }

        // determine and set store ids
        $storeIds = array();
        foreach (Mage::app()->getStores() as $store) {
            if (in_array($store->getGroupId(), $storeGroupIds)) {
                $storeIds[] = $store->getId();
            }
        }
        $object->setGwsStores($storeIds);

        // set relevant website ids from allowed store group ids
        $relevantWebsites = array();
        foreach ($this->_getAllStoreGroups() as $storeGroup) {
            if (in_array($storeGroup->getId(), $storeGroupIds)) {
                $relevantWebsites[] = $storeGroup->getWebsite()->getId();
            }
        }
        $object->setGwsRelevantWebsites(array_values(array_unique($relevantWebsites)));

        return $this;
    }

    /**
     * Get all store groups
     *
     * @return Mage_Core_Model_Mysql4_Store_Group_Collection
     */
    protected function _getAllStoreGroups()
    {
        if (null === $this->_storeGroupCollection) {
            $this->_storeGroupCollection = Mage::getResourceSingleton('core/store_group_collection');
        }
        return $this->_storeGroupCollection;
    }

    /**
     * Transform array of website ids and array of store group ids into comma-separated strings
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Permissions_Model_Observer
     */
    public function setDataBeforeRoleSave($observer)
    {
        $object = $observer->getEvent()->getObject();
        $websiteIds    = $object->getGwsWebsites();
        $storeGroupIds = $object->getGwsStoreGroups();

        // validate specified data
        if ($object->getGwsIsAll() == 0 && empty($websiteIds) && empty($storeGroupIds)) {
            Mage::throwException(
                Mage::helper('enterprise_admingws')->__('Please specify at least one website or one store group.')
            );
        }
        if (!$this->_role->getIsAll()) {
            if ($object->getGwsIsAll()) {
                Mage::throwException(
                    Mage::helper('enterprise_admingws')->__('Not enough permissions to set All Scopes to a Role.')
                );
            }
        }

        if (empty($websiteIds)) {
            $websiteIds = array();
        }
        else {
            if (!is_array($websiteIds)) {
                $websiteIds = explode(',', $websiteIds);
            }
            $allWebsiteIds = array_keys(Mage::app()->getWebsites());
            foreach ($websiteIds as $websiteId) {
                if (!in_array($websiteId, $allWebsiteIds)) {
                    Mage::throwException(Mage::helper('enterprise_admingws')->__('Wrong website ID: %d', $websiteId));
                }
                // prevent granting disallowed websites
                if (!$this->_role->getIsAll()) {
                    if (!$this->_role->hasWebsiteAccess($websiteId, true)) {
                        Mage::throwException(
                            Mage::helper('enterprise_admingws')->__('Website "%s" is not allowed in your current permission scope.', Mage::app()->getWebsite($websiteId)->getName())
                        );
                    }
                }
            }
        }
        if (empty($storeGroupIds)) {
            $storeGroupIds = array();
        }
        else {
            if (!is_array($storeGroupIds)) {
                $storeGroupIds = explode(',', $storeGroupIds);
            }
            $allStoreGroups = array();
            foreach (Mage::app()->getWebsites() as $website) {
                $allStoreGroups = array_merge($allStoreGroups, $website->getGroupIds());
            }
            foreach ($storeGroupIds as $storeGroupId) {
                if (!array($storeGroupId, $allStoreGroups)) {
                    Mage::throwException(Mage::helper('enterprise_admingws')->__('Wrong store ID: %d', $storeGroupId));
                }
                // prevent granting disallowed store group
                if (count(array_diff($storeGroupIds, $this->_role->getStoreGroupIds()))
                    && (!($this->_role->getIsAll()))
                ) {
                    Mage::throwException(
                        Mage::helper('enterprise_admingws')->__('Not enough permissions to save specified Combination of Store Scopes.')
                    );
                }
            }
        }

        $object->setGwsWebsites(implode(',', $websiteIds));
        $object->setGwsStoreGroups(implode(',', $storeGroupIds));

        return $this;
    }

    /**
     * Prepare role object permissions data before saving
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Permissions_Model_Observer
     */
    public function prepareRoleSave($observer)
    {
        $object = $observer->getEvent()->getObject();
        $request = $observer->getEvent()->getRequest();

        $isAll = (int)$request->getPost('gws_is_all');
        $websiteIds = (array)$request->getPost('gws_websites');
        $storeGroupIds = (array)$request->getPost('gws_store_groups');

        $object->setGwsIsAll($isAll);
        if (!$isAll) {
            $object->setGwsWebsites($websiteIds)->setGwsStoreGroups($storeGroupIds);
        }
        return $this;
    }

    /**
     * Copy permission scopes to new specified website
     *
     * @param Varien_Event_Observer $observer
     */
    public function copyWebsiteCopyPermissions($observer)
    {
        $oldWebsiteId = (string)$observer->getEvent()->getOldWebsiteId();
        $newWebsiteId = (string)$observer->getEvent()->getNewWebsiteId();
        $roles = Mage::getResourceSingleton('admin/roles_collection');
        foreach ($roles as $role) {
            $shouldRoleBeUpdated = false;
            $roleWebsites = explode(',', $role->getGwsWebsites());
            if ((!$role->getGwsIsAll()) && $role->getGwsWebsites()) {
                if (in_array($oldWebsiteId, $roleWebsites)) {
                    $roleWebsites[] = $newWebsiteId;
                    $shouldRoleBeUpdated = true;
                }
            }
            if ($shouldRoleBeUpdated) {
                $role->setGwsWebsites(implode(',', $roleWebsites));
                $role->save();
            }
        }
    }

    /**
     * Reinit stores only with allowed scopes
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminControllerPredispatch($observer)
    {
        /* @var $session Mage_Admin_Model_Session */
        $session = Mage::getSingleton('admin/session');

        if ($session->isLoggedIn()) {
            // load role with true websites and store groups
            $this->_role->setAdminRole($session->getUser()->getRole());

            if (!$this->_role->getIsAll()) {
                // disable single store mode
                Mage::app()->setIsSingleStoreModeAllowed(false);

                // cleanup Mage::app() from disallowed stores
                Mage::app()->reinitStores();

                // completely block some admin menu items
                $this->_denyAclLevelRules(self::ACL_WEBSITE_LEVEL);
                if (count($this->_role->getWebsiteIds()) === 0) {
                    $this->_denyAclLevelRules(self::ACL_STORE_LEVEL);
                }
                // cleanup dropdowns for forms/grids that are supposed to be built in future
                Mage::getSingleton('adminhtml/system_store')->setIsAdminScopeAllowed(false)->reload();
            }

            // inject into request predispatch to block disallowed actions
            $this->validateControllerPredispatch($observer);
        }
    }

    /**
     * Check access to massaction status block
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_AdminGws_Model_Observer
     */
    public function catalogProductPrepareMassAction($observer)
    {
        if ($this->_role->getIsAll()) {
            return $this;
        }

        $request = Mage::app()->getRequest();
        $storeId = $request->getParam('store', Mage_Core_Model_App::ADMIN_STORE_ID);
        if ($this->_role->hasStoreAccess($storeId)) {
            return $this;
        }

        $massActionBlock = $observer->getEvent()->getBlock()->getMassactionBlock();
        $massActionBlock->removeItem('status');
        $massActionBlock->removeItem('attributes');

        return $this;
    }

    /**
     * Deny acl level rules.
     *
     * @param string $level
     * @return Enterprise_AdminGws_Model_Observer
     */
    protected function _denyAclLevelRules($level)
    {
         /* @var $session Mage_Admin_Model_Session */
        $session = Mage::getSingleton('admin/session');

        foreach (Mage::getConfig()->getNode(self::XML_PATH_ACL_DENY_RULES . '/' . $level)->children() as $rule) {
            $session->getAcl()->deny($session->getUser()->getAclRole(), $rule);
        }
        return $this;
    }

    /**
     * Limit a collection
     *
     * @param Varien_Event_Observer $observer
     */
    public function limitCollection($observer)
    {
        if ($this->_role->getIsAll()) {
            return;
        }
        $collection = $observer->getEvent()->getCollection();
        if (!$callback = $this->_pickCallback('collection_load_before', $collection)) {
            return;
        }
        $this->_invokeCallback($callback, 'enterprise_admingws/collections', $collection);
    }

    /**
     * Validate / update a model before saving it
     *
     * @param unknown_type $observer
     */
    public function validateModelSaveBefore($observer)
    {
        if ($this->_role->getIsAll()) {
            return;
        }
        $model = $observer->getEvent()->getObject();
        if (!$callback = $this->_pickCallback('model_save_before', $model)) {
            return;
        }
        $this->_invokeCallback($callback, 'enterprise_admingws/models', $model);
    }

    /**
     * Initialize a model after loading it
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function validateModelLoadAfter($observer)
    {
        if ($this->_role->getIsAll()) {
            return;
        }
        $model = $observer->getEvent()->getObject();
        if (!$callback = $this->_pickCallback('model_load_after', $model)) {
            return;
        }
        $this->_invokeCallback($callback, 'enterprise_admingws/models', $model);
    }

    /**
     * Validate a model before delete
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function validateModelDeleteBefore($observer)
    {
        if ($this->_role->getIsAll()) {
            return;
        }

        $model = $observer->getEvent()->getObject();
        if (!$callback = $this->_pickCallback('model_delete_before', $model)) {
            return;
        }
        $this->_invokeCallback($callback, 'enterprise_admingws/models', $model);
    }

    /**
     * Validate page by current request (module, controller, action)
     *
     * @param Varien_Event_Observer $observer
     */
    public function validateControllerPredispatch($observer)
    {
        if ($this->_role->getIsAll()) {
            return;
        }

        // initialize controllers map
        if (null === $this->_controllersMap) {
            $this->_controllersMap = array('full' => array(), 'partial' => array());
            $children = Mage::getConfig()->getNode(self::XML_PATH_VALIDATE_CALLBACK . 'controller_predispatch')
                ->children();
            foreach ($children as $actionName => $method) {
                list($module, $controller, $action) = explode('__', $actionName);
                if ($action) {
                    $this->_controllersMap['full'][$module][$controller][$action] =
                        $this->_recognizeCallbackString((string)$method);
                }
                else {
                    $this->_controllersMap['partial'][$module][$controller] =
                        $this->_recognizeCallbackString((string)$method);
                }
            }
        }

        // map request to validator callback
        $request        = Mage::app()->getRequest();
        $routeName      = $request->getRouteName();
        $controllerName = $request->getControllerName();
        $actionName     = $request->getActionName();
        $callback       = false;
        if (isset($this->_controllersMap['full'][$routeName])
            && isset($this->_controllersMap['full'][$routeName][$controllerName])
            && isset($this->_controllersMap['full'][$routeName][$controllerName][$actionName])) {
            $callback = $this->_controllersMap['full'][$routeName][$controllerName][$actionName];
        }
        elseif (isset($this->_controllersMap['partial'][$routeName])
            && isset($this->_controllersMap['partial'][$routeName][$controllerName])) {
            $callback = $this->_controllersMap['partial'][$routeName][$controllerName];
        }

        if ($callback) {
            $this->_invokeCallback(
                $callback,
                'enterprise_admingws/controllers',
                $observer->getEvent()->getControllerAction()
            );
        }
    }

    /**
     * Apply restrictions to misc blocks before html
     *
     * @param Varien_Event_Observer $observer
     */
    public function restrictBlocks($observer)
    {
        if ($this->_role->getIsAll()) {
            return;
        }
        if (!$block = $observer->getEvent()->getBlock()) {
            return;
        }
        if (!$callback = $this->_pickCallback('block_html_before', $block)) {
            return;
        }
        /* the $observer is used intentionally */
        $this->_invokeCallback($callback, 'enterprise_admingws/blocks', $observer);
    }

    /**
     * Get a limiter callback for an instance from mappers configuration
     *
     * @param string $callbackGroup (collection, model)
     * @param object $instance
     * @return string
     */
    public function _pickCallback($callbackGroup, $instance)
    {
        if (!$instanceClass = get_class($instance)) {
            return;
        }

        // gather callbacks from mapper configuration
        if (!isset($this->_callbacks[$callbackGroup])) {
            $this->_callbacks[$callbackGroup] = array();
            $callbacks = (array)Mage::getConfig()->getNode(self::XML_PATH_VALIDATE_CALLBACK . $callbackGroup);
            foreach ($callbacks as $className => $callback) {
                $factoryClassName = str_replace('__', '/', $className);
                switch ($callbackGroup) {
                    case 'collection_load_before':
                        if (0 === strpos($factoryClassName, '_', 0)) {
                            $className = Mage::getConfig()->getModelClassName(substr($factoryClassName, 1));
                        }
                        else {
                            $className = Mage::getConfig()->getResourceModelClassName($factoryClassName);
                        }
                        break;
                    case 'block_html_before':
                        $className = Mage::getConfig()->getBlockClassName($factoryClassName);
                        break;
                    default:
                        $className = Mage::getConfig()->getModelClassName($factoryClassName);
                }

                /*
                 * Second parameter passed as FALSE to prevent usage of __autoload function
                 * which will result in not including new class file and search only by already included
                 *
                 * Note: Commented bc in case of Models this will result in not working
                 * observers for those models. In first call of this function observers for models will be not
                 * added into _callbacks bc their class are not loaded (included) yeat.
                 *
                 * So in result there will be garbage (non existing classes) in _callbacks
                 * but it will be initialized faster without __autoload calls.
                 */
                //if (class_exists($className, false)) {
                if ($className) {
                    $this->_callbacks[$callbackGroup][$className] = $this->_recognizeCallbackString($callback);
                }
                //}
            }
        }

        /**
         * Determine callback for current instance
         * Explicit class name has priority before inherited classes
         */
        $result = false;
        if (isset($this->_callbacks[$callbackGroup][$instanceClass])) {
            $result = $this->_callbacks[$callbackGroup][$instanceClass];
        }
        else {
            foreach ($this->_callbacks[$callbackGroup] as $className => $callback) {
                if ($instance instanceof $className) {
                    $result = $callback;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Seek for factory class name in specified callback string
     *
     * @param string $callbackString
     * @return string|array
     */
    protected function _recognizeCallbackString($callbackString)
    {
        if (preg_match('/^([^:]+?)::([^:]+?)$/', $callbackString, $matches)) {
            array_shift($matches);
            return $matches;
        }
        return $callbackString;
    }

    /**
     * Invoke specified callback depending on whether it is a string or array
     *
     * @param string|array $callback
     * @param string $defaultFactoryClassName
     * @param object $passthroughObject
     */
    protected function _invokeCallback($callback, $defaultFactoryClassName, $passthroughObject)
    {
        $class  = $defaultFactoryClassName;
        $method = $callback;
        if (is_array($callback)) {
            list($class, $method) = $callback;
        }
        Mage::getSingleton($class)->$method($passthroughObject);
    }
}
