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
 * ImportExport module observer
 *
 * @category    Enterprise
 * @package     Enterprise_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_ImportExport_Model_Observer
{
    /**
     * Cron tab expression path
     */
    const CRON_STRING_PATH = 'crontab/jobs/enterprise_import_export_log_clean/schedule/cron_expr';

    /**
     * Configuration path of log status
     */
    const LOG_CLEANING_ENABLE_PATH = 'system/enterprise_import_export_log/enabled';

    /**
     * Configuration path of log save days
     */
    const SAVE_LOG_TIME_PATH = 'system/enterprise_import_export_log/save_days';

    /**
     * Recipient email configuraiton path
     */
    const XML_RECEIVER_EMAIL_PATH = 'system/enterprise_import_export_log/error_email';

    /**
     * Sender email configuraiton path
     */
    const XML_SENDER_EMAIL_PATH   = 'system/enterprise_import_export_log/error_email_identity';

    /**
     * Email template configuraiton path
     */
    const XML_TEMPLATE_EMAIL_PATH = 'system/enterprise_import_export_log/error_email_template';

    /**
     * Clear old log files and folders
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @param bool $forceRun
     * @return bool
     */
    public function scheduledLogClean($schedule, $forceRun = false)
    {
        $result = false;
        if (!Mage::getStoreConfig(self::CRON_STRING_PATH)
            && (!$forceRun || !Mage::getStoreConfig(self::LOG_CLEANING_ENABLE_PATH))
        ) {
            return;
        }

        try {
            $logPath = Mage::getConfig()->getOptions()->getVarDir() . DS
                     . Mage_ImportExport_Model_Abstract::LOG_DIRECTORY;

            if (!file_exists($logPath) || !is_dir($logPath)) {
                if (!mkdir($logPath, 0777, true)) {
                    Mage::throwException(
                        Mage::helper('enterprise_importexport')->__('Unable to create directory "%s".', $logPath)
                    );
                }
            }

            if (!is_dir($logPath) || !is_writable($logPath)) {
                Mage::throwException(
                    Mage::helper('enterprise_importexport')->__('Directory "%s" is not writable.', $logPath)
                );
            }
            $saveTime = (int) Mage::getStoreConfig(self::SAVE_LOG_TIME_PATH) + 1;
            $dateCompass = new DateTime('-' . $saveTime . ' days');

            foreach ($this->_getDirectoryList($logPath) as $directory) {
                $separator = str_replace('\\', '\\\\', DS);
                if (!preg_match("~(\d{4})$separator(\d{2})$separator(\d{2})$~", $directory, $matches)) {
                    continue;
                }

                $direcotryDate = new DateTime($matches[1] . '-' . $matches[2] . '-' . $matches[3]);
                if ($forceRun || $direcotryDate < $dateCompass) {
                    $fs = new Varien_Io_File();
                    if (!$fs->rmdirRecursive($directory, true)) {
                        $directory = str_replace(Mage::getBaseDir() . DS, '', $directory);
                        Mage::throwException(
                            Mage::helper('enterprise_importexport')->__('Unable to delete "%s". Directory is not writable.', $directory)
                        );
                    }
                }
            }
            $result = true;
        } catch (Exception $e) {
            $this->_sendEmailNotification(array(
                'warnings' => $e->getMessage()
            ));
        }
        return $result;
    }

    /**
     * Parse log folder filesystem and find all directories on third nesting level
     *
     * @param string $logPath
     * @param int $level
     * @return array
     */
    protected function _getDirectoryList($logPath, $level = 1)
    {
        $result = array();

        $logPath = rtrim($logPath, DS);
        $fs = new Varien_Io_File();
        $fs->cd($logPath);

        foreach ($fs->ls() as $entity) {
            if ($entity['leaf']) {
                continue;
            }

            $childPath = $logPath . DS . $entity['text'];
            $mergePart = ($level < 3) ? $this->_getDirectoryList($childPath, $level + 1) : array($childPath);

            $result = array_merge($result, $mergePart);
        }
        return $result;
    }

    /**
     * Run operation in crontab
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @param bool $forceRun
     * @return bool
     */
    public function processScheduledOperation($schedule, $forceRun = false)
    {
        $operation = Mage::getModel('enterprise_importexport/scheduled_operation')
            ->loadByJobCode($schedule->getJobCode());

        $result = false;
        if ($operation && ($operation->getStatus() || $forceRun)) {
            $result = $operation->run();
        }

        return $result;
    }

    /**
     * Send email notification
     *
     * @param array $vars
     * @return Enterprise_ImportExport_Model_Observer
     */
    protected function _sendEmailNotification($vars)
    {
        $storeId = Mage::app()->getStore()->getId();
        $receiverEmail = Mage::getStoreConfig(self::XML_RECEIVER_EMAIL_PATH, $storeId);
        if (!$receiverEmail) {
            return $this;
        }

        $mailer = Mage::getSingleton('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($receiverEmail);

        $mailer->addEmailInfo($emailInfo);

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_SENDER_EMAIL_PATH, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId(Mage::getStoreConfig(self::XML_TEMPLATE_EMAIL_PATH, $storeId));
        $mailer->setTemplateParams($vars);
        $mailer->send();
        return $this;
    }
}
