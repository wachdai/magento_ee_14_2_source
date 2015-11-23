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
 * @package     Enterprise_ImportExport
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Import model
 *
 * @category    Enterprise
 * @package     Enterprise_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_ImportExport_Model_Import extends Mage_ImportExport_Model_Import
    implements Enterprise_ImportExport_Model_Scheduled_Operation_Interface
{
    /**
     * Reindex indexes by process codes.
     *
     * @return Enterprise_ImportExport_Model_Import
     */
    public function reindexAll()
    {
        if (!isset(self::$_entityInvalidatedIndexes[$this->getEntity()])) {
            return $this;
        }

        $indexers = self::$_entityInvalidatedIndexes[$this->getEntity()];
        foreach ($indexers as $indexer) {
            $indexProcess = Mage::getSingleton('index/indexer')->getProcessByCode($indexer);
            if ($indexProcess) {
                $indexProcess->reindexEverything();
            }
        }

        return $this;
    }

    /**
     * Run import through cron
     *
     * @param Enterprise_ImportExport_Model_Scheduled_Operation $operation
     * @return bool
     */
    public function runSchedule(Enterprise_ImportExport_Model_Scheduled_Operation $operation)
    {
        $sourceFile = $operation->getFileSource($this);
        $result = $sourceFile && $this->validateSource($sourceFile);
        $isAllowedForcedImport = $operation->getForceImport()
            && $this->getProcessedRowsCount() != $this->getInvalidRowsCount();
        if ($isAllowedForcedImport || $result) {
            $result = $this->importSource();
        }
        if ($result) {
            $this->reindexAll();
        }
        return (bool)$result;
    }

    /**
     * Initialize import instance from scheduled operation
     *
     * @param Enterprise_ImportExport_Model_Scheduled_Operation $operation
     * @return Enterprise_ImportExport_Model_Import
     */
    public function initialize(Enterprise_ImportExport_Model_Scheduled_Operation $operation)
    {
        $this->setData(array(
            'entity'         => $operation->getEntityType(),
            'behavior'       => $operation->getBehavior(),
            'operation_type' => $operation->getOperationType(),
            'run_at'         => $operation->getStartTime(),
            'scheduled_operation_id' => $operation->getId()
        ));
        return $this;
    }
}
