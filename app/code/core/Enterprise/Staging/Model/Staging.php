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
 * Staging model
 *
 * @method Enterprise_Staging_Model_Resource_Staging _getResource()
 * @method Enterprise_Staging_Model_Resource_Staging getResource()
 * @method string getType()
 * @method Enterprise_Staging_Model_Staging setType(string $value)
 * @method int getMasterWebsiteId()
 * @method Enterprise_Staging_Model_Staging setMasterWebsiteId(int $value)
 * @method int getStagingWebsiteId()
 * @method Enterprise_Staging_Model_Staging setStagingWebsiteId(int $value)
 * @method string getCreatedAt()
 * @method Enterprise_Staging_Model_Staging setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Enterprise_Staging_Model_Staging setUpdatedAt(string $value)
 * @method string getStatus()
 * @method Enterprise_Staging_Model_Staging setStatus(string $value)
 * @method int getSortOrder()
 * @method Enterprise_Staging_Model_Staging setSortOrder(int $value)
 * @method string getMergeSchedulingDate()
 * @method Enterprise_Staging_Model_Staging setMergeSchedulingDate(string $value)
 * @method string getMergeSchedulingMap()
 * @method Enterprise_Staging_Model_Staging setMergeSchedulingMap(string $value)
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Staging extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'enterprise_staging';
    protected $_eventObject = 'staging';
    protected $_tablePrefix = 'staging';

    /**
     * Staging mapper instance
     *
     * @var mixed Enterprise_Staging_Model_Staging_Mapper_Abstract
     */
    protected $_mapperInstance = null;

    /**
     * Keeps copied staging items collection
     *
     * @var object Varien_Data_Collection
     */
    protected $_items;

    /**
     * Initialize resources
     */
    protected function _construct()
    {
        $this->_init('enterprise_staging/staging');
    }

    public function getTablePrefix()
    {
        $prefix = Mage::getSingleton('enterprise_staging/staging_config')
            ->getTablePrefix();
        if ($this->getId()) {
            $prefix .= $this->getId();
        }
        return $prefix;
    }

    /**
     * Validate staging data
     *
     * @return boolean
     */
    public function validate()
    {
        $errors = array();
        $result = $this->_getResource()->validate($this);
        if (!empty($result)) {
            $errors[] = $result;
        }
        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Retrieve Master Website model
     *
     * @return Mage_Core_Model_Website
     */
    public function getMasterWebsite()
    {
        if ($this->hasData('master_website_id')) {
            return Mage::app()->getWebsite($this->getData('master_website_id'));
        } else {
            return false;
        }
    }

    /**
     * Retrieve Staging Website model
     *
     * @return Mage_Core_Model_Website
     */
    public function getStagingWebsite()
    {
        if ($this->hasData('staging_website_id')) {
            return Mage::app()->getWebsite($this->getData('staging_website_id'));
        } else {
            return false;
        }
    }

    /**
     * Get staging item codes
     *
     * @return array
     */
    public function getStagingItemCodes()
    {
        if ($this->hasData('staging_item_codes')) {
            $codes = $this->getData('staging_item_codes');
            if (!is_array($codes)) {
                $codes = !empty($codes) ? explode(',', $codes) : array();
                $this->setData('staging_item_codes', $codes);
            }
        } else {
            $codes = array();
            foreach ($this->getItemsCollection() as $item) {
                $codes[] = $item->getCode();
            }
            $this->setData('item_codes', $codes);
        }
        return $this->getData('item_codes');
    }

    /**
     * Add item in item collection
     *
     * @param Enterprise_Staging_Model_Staging_Item $item
     * @return Enterprise_Staging_Model_Staging
     */
    public function addItem(Enterprise_Staging_Model_Staging_Item $item)
    {
        $item->setStaging($this);
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }

    /**
     * Retrieve staging items
     *
     * @return Varien_Data_Collection
     */
    public function getItemsCollection()
    {
        if (is_null($this->_items)) {
            $this->_items = Mage::getResourceModel('enterprise_staging/staging_item_collection')
                ->setStagingFilter($this->getId());

            if ($this->getId()) {
                foreach ($this->_items as $item) {
                    $item->setStaging($this);
                }
            }
        }
        return $this->_items;
    }

    /**
     * Check posibility to run some staging action (create, merge etc)
     *
     * @return  boolean
     */
    public function checkCoreFlag()
    {
        $process = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_category_product');
        if ($process->isLocked()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Set core flag for reserve process
     *
     * @return  Enterprise_Staging_Model_Staging
     */
    public function setCoreFlag()
    {
        $process = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_category_product');
        $process->lock();
        return $this;
    }

    /**
     * Release core flag after process
     *
     * @return  Enterprise_Staging_Model_Staging
     */
    public function releaseCoreFlag()
    {
        $process = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_category_product');
        if ($process->isLocked()) {
            $process->unlock();
        }
        return $this;
    }

    /**
     * Processing object after save data
     *
     * @return Mage_Core_Model_Abstract
     */
    public function afterCommitCallback()
    {
        if ($this->getIsNew()) {
            $this->create();
        } else {
            $this->update();
        }
        return parent::afterCommitCallback();
    }

    /**
     * Processing staging process run
     *
     * @param  string $process
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function stagingProcessRun($process)
    {
        $logBefore = Mage::getModel('enterprise_staging/staging_log')
            ->saveOnProcessRun($this, $process, 'before');

        $method = $process.'Run';

        //$this->_getResource()->beginTransaction();
        try {
            $this->_beforeStagingProcessRun($process, $logBefore);

            $this->_getResource()->{$method}($this, $logBefore);

            $logAfter = Mage::getModel('enterprise_staging/staging_log');

            $this->_afterStagingProcessRun($process, $logAfter);

            //$this->_getResource()->commit();

            $logAfter->saveOnProcessRun($this, $process, 'after');
        }
        catch (Exception $e) {
            //$this->_getResource()->rollBack();
            $logBefore->saveOnProcessRun($this, $process, 'before', $e);
            throw $e;
        }

        return $this;
    }

    /**
     * Processing staging before process run data
     *
     * @param  string $process
     * @param  Enterprise_Staging_Model_Staging_Log $log
     *
     * @return Enterprise_Staging_Model_Staging
     */
    protected function _beforeStagingProcessRun($process, $log)
    {
        $this->setCoreFlag();

        Mage::dispatchEvent(
            $this->_eventPrefix . '_' . $process . '_process_run_before',
            array($this->_eventObject => $this, 'event' => $log)
        );

        return $this;
    }

    /**
     * Perform staging after process run data
     *
     * @param  string $process
     * @param  Enterprise_Staging_Model_Staging_Log $event
     *
     * @return Enterprise_Staging_Model_Staging
     */
    protected function _afterStagingProcessRun($process, $log)
    {
        Mage::dispatchEvent(
            $this->_eventPrefix . '_' . $process . '_process_run_after',
            array($this->_eventObject => $this, 'event' => $log)
        );

        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
        $isCategoryFlatAvailable = Mage::helper('catalog/category_flat')->isAvailable();

        // rebuild flat tables after rollback
        if ($process == 'rollback') {
            if ($isCategoryFlatAvailable) {
                Mage::getSingleton('index/indexer')
                    ->getProcessByCode(Mage_Catalog_Helper_Category_Flat::CATALOG_CATEGORY_FLAT_PROCESS_CODE)
                    ->reindexEverything();
            }

            $stores = $this->getMapperInstance()->getStores();
            if (!empty($stores)) {
                foreach ($stores as $storeIds) {
                    if (isset($storeIds[0]) && $storeIds[0]) {
                        if ($isCategoryFlatAvailable) {
                            Mage::getResourceModel('catalog/product_flat_indexer')->rebuild($storeIds[0]);
                        }
                    }
                }
            }
        }

        $this->releaseCoreFlag();
        return $this;
    }

    /**
     * Create Staging Website
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function create()
    {
        if ($this->checkCoreFlag() && !$this->getDontRunStagingProccess()) {
            $this->stagingProcessRun('create');
        }
        return $this;
    }

    /**
     * Update Staging Website staging attributes
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function update()
    {
        if ($this->checkCoreFlag() && !$this->getDontRunStagingProccess()) {
            $this->stagingProcessRun('update');
        }
        return $this;
    }

    /**
     * Merge Staging
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function merge()
    {
        if ($this->checkCoreFlag() && !$this->getDontRunStagingProccess()) {
            $this->stagingProcessRun('merge');
        }
        return $this;
    }

    /**
     * Unschedule Merge Staging
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function unscheduleMege()
    {
        if ($this->checkCoreFlag() && !$this->getDontRunStagingProccess()) {
            $this->stagingProcessRun('unscheduleMerge');
        }
        return $this;
    }

     /**
     * Unschedule Merge Staging
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function reset()
    {
        $this->stagingProcessRun('reset');
        return $this;
    }

    /**
     * Backup Master Website before Merge
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function backup()
    {
        if ($this->checkCoreFlag() && !$this->getDontRunStagingProccess()) {
            $this->stagingProcessRun('backup');
        }
        return $this;
    }

    /**
     * Restore Master Website from backup
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function rollback()
    {
        if ($this->checkCoreFlag() && !$this->getDontRunStagingProccess()) {
            $this->stagingProcessRun('rollback');
        }
        return $this;
    }

    /**
     * Check Frontend Staging Website
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function checkFrontend()
    {
        $this->_getResource()->checkfrontendRun($this);

        return $this;
    }

    /**
     * Processing staging after delete
     *
     * @return Enterprise_Staging_Model_Staging
     */
    protected function _afterDelete()
    {
        if ($this->getStagingWebsite()) {
            $this->getStagingWebsite()->delete();
        }

        foreach ($this->getLogsCollection() as $log) {
            $log->delete();
        }

        parent::_afterDelete();
        return $this;
    }

    /**
     * Retrieve history log collection
     *
     * @param boolean $reload
     * @return Enterprise_Staging_Model_Mysql4_Staging_Log_Collection
     */
    public function getLogCollection($reload=false)
    {
        if (is_null($this->_logCollection) || $reload) {
            $this->_logCollection = Mage::getResourceModel('enterprise_staging/staging_log_collection')
                ->setStagingFilter($this->getId())
                ->setOrder('created_at', 'desc')
                ->setOrder('log_id', 'desc');

            if ($this->getId()) {
                foreach ($this->_logCollection as $log) {
                    $log->setStaging($this);
                }
            }
        }
        return $this->_logCollection;
    }

    /**
     * Retrieve Mapper instance
     *
     * @return Enterprise_Staging_Model_Staging_Mapper_Website
     */
    public function getMapperInstance()
    {
        if ($this->_mapperInstance === null) {
            $this->_mapperInstance = Mage::getSingleton('enterprise_staging/staging_mapper_website');
        }
        return $this->_mapperInstance;
    }

    /**
     * Check if possible to save
     *
     * @return boolean
     */
    public function canSave()
    {
        if (!$this->getId()) {
            return false;
        }
        return true;
    }

    /**
     *  Check for processing status
     *  @return boolean
     */
    public function isStatusProcessing()
    {
         return $this->getStatus() && ($this->getStatus() != Enterprise_Staging_Model_Staging_Config::STATUS_COMPLETED);
    }

    /**
     * Check if possible to delete
     *
     * @return boolean
     */
    public function canDelete()
    {
        if (!$this->getId()) {
            return false;
        }
        if ($this->isScheduled() || $this->isStatusProcessing()) {
            return false;
        }
        return true;
    }

    /**
     * Check if possible to reset status
     * @return bool
     */
    public function canResetStatus()
    {
        return $this->isStatusProcessing();
    }

    /**
     * Check if possible to merge
     *
     * @return boolean
     */
    public function canMerge()
    {
        if (!$this->getId()) {
            return false;
        }
        if (!$this->checkCoreFlag()) {
            return false;
        }
        if ($this->isStatusProcessing()) {
            return false;
        }
        if ($this->canUnschedule()) {
            return false;
        }
        return true;
    }

    /**
     * Check if possible to uschedule
     *
     * @return boolean
     */
    public function canUnschedule()
    {
        return $this->isScheduled();
    }

    /**
     * Check if merge sheduled
     *
     * @return boolean
     */
    public function isScheduled()
    {
        return (bool)$this->getMergeSchedulingDate();
    }

    /**
     * Update staging attribute
     *
     * @param   string  $attribute
     * @param   mixed   $value
     * @return  Enterprice_Staging_Model_Staging
     */
    public function updateAttribute($attribute, $value)
    {
        return $this->getResource()->updateAttribute($this, $attribute, $value);
    }

    /**
     * Save staging items
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function saveItems()
    {
        $this->getResource()->saveItems($this);
        return $this;
    }

    /**
     * Load staging model by given staging website id
     *
     * @param int $stagingWebsiteId
     * @return Enterprise_Staging_Model_Staging
     */
    public function loadByStagingWebsiteId($stagingWebsiteId)
    {
        $this->load($stagingWebsiteId, 'staging_website_id');
        return $this;
    }

    /**
     * Collect all backup tables
     *
     * @param  Enterprise_Staging_Model_Staging_Event $log
     * @return Enterprise_Staging_Model_Staging
     */
    public function collectBackupTables($log)
    {
        $this->_getResource()->collectBackupTables($this, $log);
        return $this;
    }

    /**
     * Store found backup tables in staging
     *
     * @param  string $tableName
     * @return Enterprise_Staging_Model_Staging
     */
    public function addBackupTable($tableName)
    {
        $tables = $this->getData('backup_tables');
        if (!is_array($tables)) {
            $tables = array();
        }

        $tables[] = $tableName;

        $this->setData('backup_tables', $tables);

        return $this;
    }
}
