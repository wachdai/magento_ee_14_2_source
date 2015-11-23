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
 * @package     Enterprise_Index
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Enterprise index observer
 *
 * @category    Enterprise
 * @package     Enterprise_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Index_Model_Observer
{
    /**
     * Refresh index lock name
     */
    const REINDEX_FULL_LOCK = 'reindex_full';

    /**
     * Refresh fulltext index after product was saved
     *
     * @param Varien_Event_Observer $observer
     */
    public function catalogProductSaveAfterCommit(Varien_Event_Observer $observer)
    {
        /* var Mage_Catalog_Model_Product $product */
        $product    = $observer->getEvent()->getProduct();
        $productId  = $product->getId();
        $indexTable = 'catalogsearch_fulltext';

        /** @var $client Enterprise_Mview_Model_Client */
        $client = Mage::getModel('enterprise_mview/client');
        $client->init($indexTable);
        $client->execute('enterprise_index/action_catalog_fulltext_refresh_row', array('value' => $productId));
    }

    /**
     * Lock full reindex process
     *
     * @param Varien_Event_Observer $observer
     * @throws Enterprise_Index_Exception
     */
    public function lockFullReindexProcess(Varien_Event_Observer $observer)
    {
        if (!Mage_Index_Model_Lock::getInstance()->setLock(self::REINDEX_FULL_LOCK, true)) {
            throw new Enterprise_Index_Exception('Full reindex process is already running.');
        }
    }

    /**
     * Unlock full reindex process
     *
     * @param Varien_Event_Observer $observer
     */
    public function unlockFullReindexProcess(Varien_Event_Observer $observer)
    {
        Mage_Index_Model_Lock::getInstance()->releaseLock(self::REINDEX_FULL_LOCK, true);
    }

    /**
     * Run full re-index for all invalidated indexes
     * Refresh all active indexes by changeLog
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @throws Exception
     * @return Enterprise_Index_Model_Observer
     */
    public function refreshIndex(Mage_Cron_Model_Schedule $schedule)
    {
        /** @var $helper Enterprise_Index_Helper_Data */
        $helper = Mage::helper('enterprise_index');

        /** @var $lock Mage_Index_Model_Lock */
        $lock   = Mage_Index_Model_Lock::getInstance();

        if ($lock->setLock(self::REINDEX_FULL_LOCK, true)) {

            /**
             * Workaround for fatals and memory crashes: Invalidating indexers that are in progress
             * Successful lock setting is considered that no other full reindex processes are running
             */
            $this->_invalidateInProgressIndexers();

            $client = Mage::getModel('enterprise_mview/client');
            try {

                //full re-index
                $inactiveIndexes = $this->_getInactiveIndexersByPriority();
                $rebuiltIndexes = array();
                foreach ($inactiveIndexes as $inactiveIndexer) {
                    $tableName  = (string)$inactiveIndexer->index_table;
                    $actionName = (string)$inactiveIndexer->action_model->all;
                    $client->init($tableName);
                    if ($actionName) {
                        $client->execute($actionName);
                        $rebuiltIndexes[] = $tableName;
                    }
                }

                //re-index by changelog
                $indexers = $helper->getIndexers(true);
                foreach ($indexers as $indexerName => $indexerData) {
                    $indexTable = (string)$indexerData->index_table;
                    $actionName = (string)$indexerData->action_model->changelog;
                    $client->init($indexTable);
                    if (isset($actionName) && !in_array($indexTable, $rebuiltIndexes)) {
                        $client->execute($actionName);
                    }
                }

            } catch (Exception $e) {
                $lock->releaseLock(self::REINDEX_FULL_LOCK, true);
                throw $e;
            }

            $lock->releaseLock(self::REINDEX_FULL_LOCK, true);
        } else {
            throw new Enterprise_Index_Exception("Can't lock indexer process.");
        }

        return $this;
    }

    /**
     * Get inactive indexers sorted by priority
     *
     * @return array
     */
    protected function _getInactiveIndexersByPriority()
    {
        /** @var $metadataModel Enterprise_Mview_Model_Metadata */
        $metadataModel = Mage::getModel('enterprise_mview/metadata');

        /** @var $resource Mage_Core_Model_Resource  */
        $resource = Mage::getSingleton('core/resource');

        $inactiveIndexes = $metadataModel->getCollection()
            ->addFieldToFilter('status', Enterprise_Mview_Model_Metadata::STATUS_INVALID);

        /** @var $helper Enterprise_Index_Helper_Data */
        $helper   = Mage::helper('enterprise_index');
        $indexers = $helper->getIndexers(true);
        $resultIndexers = array();
        foreach ($indexers as $indexer) {
            foreach ($inactiveIndexes as $inactiveIndexer) {
                if ($resource->getTableName((string)$indexer->index_table) == $inactiveIndexer->getTableName()) {
                    $resultIndexers[] = $indexer;
                }
            }
        }
        return $resultIndexers;
    }

    /**
     * Invalidate indexers with IN_PROGRESS status
     *
     * @return $this
     */
    protected function _invalidateInProgressIndexers()
    {
        /** @var $metadataModel Enterprise_Mview_Model_Metadata */
        $metadataModel = Mage::getModel('enterprise_mview/metadata');
        $indexers = $metadataModel->getCollection()
            ->addFieldToFilter('status', Enterprise_Mview_Model_Metadata::STATUS_IN_PROGRESS);
        foreach ($indexers as $indexer) {
            /** @var $indexer Enterprise_Mview_Model_Metadata */
            $indexer->setStatus(Enterprise_Mview_Model_Metadata::STATUS_INVALID)->save();
        }
        return $this;
    }
}
