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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Enter description here ...
 *
 * @method Enterprise_Staging_Model_Resource_Staging_Action _getResource()
 * @method Enterprise_Staging_Model_Resource_Staging_Action getResource()
 * @method int getStagingId()
 * @method Enterprise_Staging_Model_Staging_Action setStagingId(int $value)
 * @method string getType()
 * @method Enterprise_Staging_Model_Staging_Action setType(string $value)
 * @method string getName()
 * @method Enterprise_Staging_Model_Staging_Action setName(string $value)
 * @method string getStatus()
 * @method Enterprise_Staging_Model_Staging_Action setStatus(string $value)
 * @method string getCreatedAt()
 * @method Enterprise_Staging_Model_Staging_Action setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Enterprise_Staging_Model_Staging_Action setUpdatedAt(string $value)
 * @method string getStagingTablePrefix()
 * @method Enterprise_Staging_Model_Staging_Action setStagingTablePrefix(string $value)
 * @method string getMap()
 * @method Enterprise_Staging_Model_Staging_Action setMap(string $value)
 * @method string getMageVersion()
 * @method Enterprise_Staging_Model_Staging_Action setMageVersion(string $value)
 * @method string getMageModulesVersion()
 * @method Enterprise_Staging_Model_Staging_Action setMageModulesVersion(string $value)
 * @method int getStagingWebsiteId()
 * @method Enterprise_Staging_Model_Staging_Action setStagingWebsiteId(int $value)
 * @method int getMasterWebsiteId()
 * @method Enterprise_Staging_Model_Staging_Action setMasterWebsiteId(int $value)
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Staging_Action extends Mage_Core_Model_Abstract
{
    /**
     * Staging instance
     *
     * @var Enterprise_Staging_Model_Staging
     */
    protected $_staging;

    protected function _construct()
    {
        $this->_init('enterprise_staging/staging_action');
    }

    /**
     * Declare staging instance
     *
     * @param   Enterprise_Staging_Model_Staging $staging
     * @return  Enterprise_Staging_Model_Staging_Backup
     */
    public function setStaging(Enterprise_Staging_Model_Staging $staging)
    {
        $this->_staging = $staging;
        return $this;
    }

    /**
     * Retrieve staging instance
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function getStaging()
    {
        if (!$this->_staging instanceof Enterprise_Staging_Model_Staging) {
            $this->_staging = Mage::getModel('enterprise_staging/staging')->load($this->getStagingId());
        }
        return $this->_staging;
    }

    /**
     * Save backup from backup staging process
     *
     * @param  object Enterprise_Staging_Model_Staging $staging
     * @param  object Enterprise_Staging_Model_Staging_Log $log
     *
     * @return Enterprise_Staging_Model_Staging_Backup
     */
    public function saveOnBackupRun(Enterprise_Staging_Model_Staging $staging, Enterprise_Staging_Model_Staging_Log $log)
    {
        if ($staging->getId()) {
            $name = $staging->getMasterWebsite()->getName();

            $tablePrefix = Mage::getSingleton('enterprise_staging/staging_config')->getTablePrefix($staging)
                . Mage::getSingleton('enterprise_staging/staging_config')->getStagingBackupTablePrefix()
                . $log->getId() . "_";

            $this->setStagingId($staging->getId())
                ->setType('backup')
                ->setEventCode($log->getAction())
                ->setName($name)
                ->setStatus(Enterprise_Staging_Model_Staging_Config::STATUS_COMPLETED)
                ->setCreatedAt(Mage::registry($log->getAction() . "_event_start_time"))
                ->setUpdatedAt(now())
                ->setStagingTablePrefix($tablePrefix)
                ->setMap($staging->getMapperInstance()->serialize())
                ->setMageVersion(Mage::getVersion())
                ->setMageModulesVersion(serialize(Mage::getSingleton('enterprise_staging/staging_config')->getCoreResourcesVersion()));
            $this->save();

        }
        return $this;
    }

    /**
     * check rollback condition. If module version changed we can't rollback db
     *
     * @return bool
     */
    public function canRollback()
    {
        if (!$this->getId()) {
            return false;
        }
        if (Mage::helper('enterprise_staging')->getCatalogIndexRunningFlag()) {
            return false;
        }
        if ($this->getStaging() && $this->getStaging()->isStatusProcessing()) {
            return false;
        }
        $itemInfo = $this->getItemVersionCheck();
        if (empty($itemInfo)) {
            return true;
        }
        foreach ($itemInfo as $item) {
            if ($item["disabled"] == false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check items modules versions
     * return item version check array
     *
     * @return mixed
     */
    public function getItemVersionCheck()
    {
        if (!$this->getId()) {
            return false;
        }
        // get all current module version
        $currentModuleVersion = Mage::getSingleton('enterprise_staging/staging_config')->getCoreResourcesVersion();

        //get backup version list
        $backupModules = unserialize($this->getMageModulesVersion());

        $itemVersionCheck = array();

        $stagingItems = Mage::getSingleton('enterprise_staging/staging_config')->getStagingItems();
        foreach ($stagingItems as $stagingItem) {
            if ((int)$stagingItem->is_backend) {
                continue;
            }
            $this->_addStagingItemVersionInfo($itemVersionCheck, $stagingItem, $currentModuleVersion, $backupModules);
            if ($stagingItem->extends) {
                foreach ($stagingItem->extends->children() as $extendItem) {
                    if (!Mage::getSingleton('enterprise_staging/staging_config')->isItemModuleActive($extendItem)) {
                         continue;
                    }
                    $this->_addStagingItemVersionInfo($itemVersionCheck, $extendItem, $currentModuleVersion, $backupModules);
                }
            }
        }
        return $itemVersionCheck;
    }

    /**
     * Chcking version of stored modules and those which in the system now
     *
     * @param $itemVersionCheck
     * @param $stagingItem
     * @param $currentModuleVersion
     * @param $backupModules
     * @return Enterprise_Staging_Model_Staging_Action
     */
    protected function _addStagingItemVersionInfo(&$itemVersionCheck, $stagingItem, $currentModuleVersion, $backupModules)
    {
        $itemCode  = (string) $stagingItem->getName();
        if ($stagingItem->model) {
            $itemModel = (string) $stagingItem->model;
        } else {
            $itemModel = $itemCode;
        }
        $itemCheckModuleName = $itemModel . "_setup";

        if (isset($backupModules[$itemCheckModuleName])) {
            $itemVersionCheck[$itemCode]["model"] = $itemModel;
            $itemVersionCheck[$itemCode]["backupVersion"] = $backupModules[$itemCheckModuleName];
            $itemVersionCheck[$itemCode]["currentVersion"] =  $currentModuleVersion[$itemCheckModuleName];
            if ($backupModules[$itemCheckModuleName] == $currentModuleVersion[$itemCheckModuleName]) {
                $itemVersionCheck[$itemCode]["disabled"] = false;
                $itemVersionCheck[$itemCode]["note"] = Mage::helper('enterprise_staging')->__('ok');
            } else {
                $itemVersionCheck[$itemCode]["disabled"] = true;
                $itemVersionCheck[$itemCode]["reason"] = Mage::helper('enterprise_staging')->__('version mismatch');
                $itemVersionCheck[$itemCode]["note"] =
                    Mage::helper('enterprise_staging')->__('Backup version: ') . ' ' .
                    $backupModules[$itemCheckModuleName]. " ,".
                    Mage::helper('enterprise_staging')->__('Current: ') .
                    $currentModuleVersion[$itemCheckModuleName];
            }
        } else {
            $itemVersionCheck[$itemCode]["disabled"] = true;
            $itemVersionCheck[$itemCode]["reason"] = Mage::helper('enterprise_staging')->__('unknown item');
            $itemVersionCheck[$itemCode]["note"] =
                Mage::helper('enterprise_staging')->__('Item model "%s" is not under backup', $itemModel);
        }

        return $this;
    }

    /**
     * Restoring map data from serialized data
     *
     * @return Enterprise_Staging_Model_Staging_Action
     */
    public function restoreMap()
    {
        $map = $this->getMap();
        if (!empty($map)) {
            $this->getStaging()->getMapperInstance()->unserialize($map);
        }
        return $this;
    }
}
