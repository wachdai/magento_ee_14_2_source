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
 * @method Enterprise_Staging_Model_Resource_Staging_Log _getResource()
 * @method Enterprise_Staging_Model_Resource_Staging_Log getResource()
 * @method int getStagingId()
 * @method Enterprise_Staging_Model_Staging_Log setStagingId(int $value)
 * @method int getIp()
 * @method Enterprise_Staging_Model_Staging_Log setIp(int $value)
 * @method string getAction()
 * @method Enterprise_Staging_Model_Staging_Log setAction(string $value)
 * @method string getStatus()
 * @method Enterprise_Staging_Model_Staging_Log setStatus(string $value)
 * @method int getIsBackuped()
 * @method Enterprise_Staging_Model_Staging_Log setIsBackuped(int $value)
 * @method string getCreatedAt()
 * @method Enterprise_Staging_Model_Staging_Log setCreatedAt(string $value)
 * @method int getUserId()
 * @method Enterprise_Staging_Model_Staging_Log setUserId(int $value)
 * @method string getUsername()
 * @method Enterprise_Staging_Model_Staging_Log setUsername(string $value)
 * @method int getIsAdminNotified()
 * @method Enterprise_Staging_Model_Staging_Log setIsAdminNotified(int $value)
 * @method string getAdditionalData()
 * @method Enterprise_Staging_Model_Staging_Log setAdditionalData(string $value)
 * @method string getMap()
 * @method Enterprise_Staging_Model_Staging_Log setMap(string $value)
 * @method int getStagingWebsiteId()
 * @method Enterprise_Staging_Model_Staging_Log setStagingWebsiteId(int $value)
 * @method string getStagingWebsiteName()
 * @method Enterprise_Staging_Model_Staging_Log setStagingWebsiteName(string $value)
 * @method int getMasterWebsiteId()
 * @method Enterprise_Staging_Model_Staging_Log setMasterWebsiteId(int $value)
 * @method string getMasterWebsiteName()
 * @method Enterprise_Staging_Model_Staging_Log setMasterWebsiteName(string $value)
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Staging_Log extends Mage_Core_Model_Abstract
{
    /**
     * Staging instance
     *
     * @var Enterprise_Staging_Model_Staging
     */
    protected $_staging;

    protected function _construct()
    {
        $this->_init('enterprise_staging/staging_log');
    }

    /**
     * Declare staging instance
     *
     * @param   Enterprise_Staging_Model_Staging $staging
     * @return  Enterprise_Staging_Model_Staging_Log
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

    public function restoreMap()
    {
        $map = $this->getMap();
        if (!empty($map)) {
            $this->getStaging()->getMapperInstance()->unserialize($map);
        }
        return $this;
    }

    /**
     * save event in db
     *
     * @param   Enterprise_Staging_Model_Staging $staging
     * @param   string $process
     * @param   string $onState
     * @param   Exception $exception
     *
     * @return Enterprise_Staging_Model_Staging_Log
     */
    public function saveOnProcessRun(Enterprise_Staging_Model_Staging $staging, $process, $onState, $exception = null)
    {
        $this->setStaging($staging);

        if ($process == 'update') {
            return $this;
        }
        if ($onState == 'before' && (($process == 'merge' && $staging->getIsMergeLater())
            || $process == 'reset' || $process == 'unscheduleMerge')
        ) {
            return $this;
        }

        if ($staging->getIsMegreByCron()) {
            $process = 'cron_merge';
        }

        $additionalData = array();
        $exceptionMessage = '';
        $config = Mage::getSingleton('enterprise_staging/staging_config');

        if ($onState == 'before') {
            $status = Enterprise_Staging_Model_Staging_Config::STATUS_STARTED;
        } else {
            $status = Enterprise_Staging_Model_Staging_Config::STATUS_COMPLETED;
        }

        switch ($process) {
            case 'create':
                $this->setStagingWebsiteId($staging->getStagingWebsiteId());
                $this->setMasterWebsiteId($staging->getMasterWebsiteId());
                $eventAction = Enterprise_Staging_Model_Staging_Config::ACTION_CREATE;
                break;
            case 'cron_merge':
                $this->setMergeMap($staging->getMapperInstance()->serialize());
                $this->setStagingWebsiteId($staging->getMasterWebsiteId());
                $this->setMasterWebsiteId($staging->getStagingWebsiteId());
                $eventAction = Enterprise_Staging_Model_Staging_Config::ACTION_CRON_MERGE;
                break;
            case 'merge':
                if ($staging->getIsMergeLater()) {
                    $eventAction = Enterprise_Staging_Model_Staging_Config::ACTION_SCHEDULE_MERGE;
                    $additionalData['schedule_date'] = $staging->getMergeSchedulingDate();
                }
                else {
                    $eventAction = Enterprise_Staging_Model_Staging_Config::ACTION_MERGE;
                }
                $this->setMergeMap($staging->getMapperInstance()->serialize());
                $this->setStagingWebsiteId($staging->getMasterWebsiteId());
                $this->setMasterWebsiteId($staging->getStagingWebsiteId());
                break;
            case 'rollback':
                $this->setMergeMap($staging->getMapperInstance()->serialize());
                $this->setStagingWebsiteId($staging->getMasterWebsiteId());
                $this->setMasterWebsiteId(null);
                $eventAction = Enterprise_Staging_Model_Staging_Config::ACTION_ROLLBACK;
                break;
            case 'backup':
                $this->setStagingWebsiteId(null);
                $this->setMasterWebsiteId($staging->getMasterWebsiteId());
                $eventAction = Enterprise_Staging_Model_Staging_Config::ACTION_BACKUP;
                break;
            case 'reset':
                $this->setStagingWebsiteId($staging->getStagingWebsiteId());
                $this->setMasterWebsiteId($staging->getMasterWebsiteId());
                $eventAction = Enterprise_Staging_Model_Staging_Config::ACTION_RESET;
                $additionalData['action_before_reset'] = $this->_getResource()->getLastLogAction($staging->getId());
                break;
            case 'unscheduleMerge':
                $this->setMergeMap($staging->getMapperInstance()->serialize());
                $this->setStagingWebsiteId($staging->getMasterWebsiteId());
                $this->setMasterWebsiteId($staging->getStagingWebsiteId());
                $eventAction = Enterprise_Staging_Model_Staging_Config::ACTION_UNSCHEDULE_MERGE;
                $staging->releaseCoreFlag();
                break;
        }
        $this->setSaveThrowException($exception);

        if (!is_null($exception)) {
            $exceptionMessage = $exception->getMessage();
        }

        $staging->updateAttribute('status', $status);

        $stagingWebsiteId   = $this->getStagingWebsiteId();
        $masterWebsiteId    = $this->getMasterWebsiteId();
        if (!empty($additionalData)) {
            $this->setAdditionalData(serialize($additionalData));
        }
        $this->setStagingId($staging->getId())
            ->setAction($eventAction)
            ->setStatus($status)
            ->setStagingWebsiteName($stagingWebsiteId === null ? null : Mage::app()->getWebsite($stagingWebsiteId)->getName())
            ->setMasterWebsiteName($masterWebsiteId === null ? null : Mage::app()->getWebsite($masterWebsiteId)->getName())
            ->setIsAdminNotified(false)
            ->setMap($staging->getMapperInstance()->serialize())
            ->setLog($exceptionMessage)
            ->save();

        if (($process == 'cron_merge' && $onState == 'after') || $process == 'reset' || $process == 'unscheduleMerge') {
             $staging->setMergeSchedulingDate(null)
                    ->setMergeSchedulingMap('')
                    ->setDontRunStagingProccess(true)
                    ->save();
        }


        return $this;
    }
}
