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
 * @package     Enterprise_Support
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_Support_Model_Backup_Item_Abstract extends Mage_Core_Model_Abstract
{
    const STATUS_PROCESSING = 0;
    const STATUS_COMPLETE   = 1;

    /**
     * Cmd Model
     *
     * @var Enterprise_Support_Model_Backup_Item_Cmd_Bash
     */
    protected $_cmdObject;

    /**
     * Backup Model
     *
     * @var Enterprise_Support_Model_Backup
     */
    protected $_backup;

    /**
     * Set Backup
     *
     * @param Enterprise_Support_Model_Backup $backup
     */
    public function setBackup(Enterprise_Support_Model_Backup $backup)
    {
        $this->_backup = $backup;
    }

    /**
     * Get Backup
     *
     * @return Enterprise_Support_Model_Backup
     */
    public function getBackup()
    {
        if (!$this->_backup) {
            $this->_backup = Mage::getModel('enterprise_support/backup')->load($this->getBackupId());
        }

        return $this->_backup;
    }

    /**
     * Set Cmd object
     *
     * @param Enterprise_Support_Model_Backup_Item_Cmd_Bash $cmd
     */
    public function setCmdObject(Enterprise_Support_Model_Backup_Item_Cmd_Bash $cmd)
    {
        $this->_cmdObject = $cmd;
    }

    /**
     * Get Command object
     *
     * @return Enterprise_Support_Model_Backup_Item_Cmd_Bash
     */
    public function getCmdObject()
    {
        return $this->_cmdObject;
    }

    /**
     * Get Command
     *
     * @return string
     */
    public function getCmd()
    {
        return $this->getCmdObject()->generate();
    }

    /**
     * Update Status
     */
    public function updateStatus()
    {
        $file = Mage::helper('enterprise_support')->getFilePath($this->getName());
        $currentStatus = $this->getStatus();

        if ($currentStatus == self::STATUS_COMPLETE) {
            return;
        }

        if (file_exists($file)) {
            if (!Mage::helper('enterprise_support')->isFileLocked($file)) {
                $this->setStatus(self::STATUS_COMPLETE);
                $this->_updateFileInfo();
            } else {
                $this->setStatus(self::STATUS_PROCESSING);
            }
        } else {
            $this->setStatus(self::STATUS_PROCESSING);
        }

        $this->save();
    }

    /**
     * Update File Info
     */
    protected function _updateFileInfo()
    {
        $size = filesize(Mage::helper('enterprise_support')->getOutputPath() . $this->getName());
        $this->setSize($size);
    }

    /**
     * Get Name
     *
     * @return mixed|string
     */
    public function getName()
    {
        $name = $this->getBackup()->getName();
        $name = sprintf("%s.%s", $name, $this->getOutputFileExtension());

        return $name;
    }

    /**
     * Load Item by Backup ID & Type
     *
     * @param int $backupId
     * @param int $type
     *
     * @return Enterprise_Support_Model_Resource_Backup_Item
     */
    public function loadItemByBackupIdAndType($backupId, $type)
    {
        return $this->getResource()->loadItemByBackupIdAndType($this, $backupId, $type);
    }

    /**
     * Validate
     *
     * @return string
     */
    public function validate()
    {
        $outputPath = Mage::helper('enterprise_support')->getOutputPath();
        $error = '';

        if (!is_writable($outputPath) || !is_readable($outputPath)) {
            $error = sprintf(
                Mage::helper('enterprise_support')->__('Directory %s should have writable & readable permissions'), $outputPath);
        }

        return $error;
    }
}
