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

class Enterprise_Support_Model_Backup extends Mage_Core_Model_Abstract
    implements Enterprise_Support_Model_Backup_Interface
{

    const STATUS_PROCESSING  = 0;
    const STATUS_COMPLETE    = 1;
    const STATUS_FAILED      = 2;
    const LOG_FILENAME       = 'backup.log';

    /**
     * Backup items
     *
     * @var array
     */
    protected $_items;

    /**
     * Init Model
     */
    protected function _construct()
    {
        $this->_init('enterprise_support/backup');
    }

    /**
     * Add item
     *
     * @param Enterprise_Support_Model_Backup_Item_Abstract $item
     * @param null|sting $key
     * @return Enterprise_Support_Model_Backup
     */
    public function addItem(Enterprise_Support_Model_Backup_Item_Abstract $item, $key = null)
    {
        if ($key) {
            $this->_items[$key] = $item;
        } else {
            $this->_items[] = $item;
        }

        return $this;
    }

    /**
     * Get Items
     *
     * @param string $key
     *
     * @return Enterprise_Support_Model_Backup_Item_Abstract|bool
     */
    public function getItem($key)
    {
        if (isset($this->_items[$key])) {
            return $this->_items[$key];
        }

        return false;
    }

    /**
     * Get Items
     *
     * @return array
     */
    public function getItems()
    {
        if (!$this->_items) {
            foreach (Mage::helper('enterprise_support')->getBackupItems() as $key => $item) {
                /** @var Enterprise_Support_Model_Backup_Item_Abstract $item*/
                $item->loadItemByBackupIdAndType($this->getId(), $item->getType());
                $item->setBackup($this);
                $this->addItem($item, $key);
            }
        }
        return $this->_items;
    }

    /**
     * Run cmd from backup items
     *
     * @throws Mage_Core_Exception
     * @return string
     */
    public function run()
    {
        $errors = $this->validate();
        if ($errors) {
            throw new Mage_Core_Exception(Mage::helper('enterprise_support')->__(current($errors)));
        }

        $cmd = array();
        foreach ($this->getItems() as $item) {
            $cmd[] = $item->getCmd();
        }

        $cmd = implode('; ', $cmd);
        $cmd = sprintf("(%s) > %s &", $cmd, Mage::helper('enterprise_support')->getOutputPath() . self::LOG_FILENAME);

        $this->setStatus(self::STATUS_PROCESSING);
        $shellOutput = exec($cmd);

        return $shellOutput;
    }

    /**
     * Validate backup script
     *
     * @return array
     */
    public function validate()
    {
        $errors = array();
        $result = array();

        $os = Mage::helper('enterprise_support')->getUnsupportedOs();
        if ($os) {
            $errors[] = sprintf(
                Mage::helper('enterprise_support')->__("Support Module doesn't support %s operation system"), $os);
            return $errors;
        }

        if (!Mage::helper('enterprise_support')->isExecEnabled()) {
            $errors[] = Mage::helper('enterprise_support')->__(
                'Unable to create backup due to php exec function is disabled');
            return $errors;
        }

        foreach ($this->getItems() as $item) {
            $errors[] = $item->validate();
        }

        /** @var $cmd Enterprise_Support_Model_Backup_Item_Cmd_Bash */
        $cmd = Mage::getModel('enterprise_support/backup_item_cmd_bash');
        $cmd->setScriptInterpreter('/bin/bash ');
        $cmd->setScriptName('backup.sh');
        $cmd->setMode('check');

        $errors[] = exec($cmd->generate());

        $errors = array_unique($errors);
        foreach ($errors as $error) {
            if ($error) {
                $result[] = $error;
            }
        }

        return $result;
    }

    /**
     * Generate random name if does not exist
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $this->_generateRandomName();
        return parent::_beforeSave();
    }

    /**
     * Set Backup Id to All Items
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        foreach ($this->getItems() as $item) {
            /** @var $item Enterprise_Support_Model_Backup_Item_Abstract */
            $item->setBackupId($this->getId());
            $item->save();
        }

        return parent::_afterSave();
    }

    /**
     * Remove Backups files and log
     *
     * @return Enterprise_Support_Model_Backup
     */
    protected function _afterDelete()
    {
        foreach ($this->getItems() as $item) {
            $file = Mage::helper('enterprise_support')->getFilePath($item->getName());
            if (file_exists($file) && is_readable($file)) {
                Mage::getSingleton('varien/io_file')->rm($file);
            }
        }

        $this->_removeLogFile();

        return $this;
    }

    /**
     * Get Backup Name
     *
     * @return string
     */
    public function getName()
    {
        if (!$this->getData('name')) {
            $this->_generateRandomName();
        }

        return $this->_getData('name');
    }

    /**
     * Generate Random Name for Backup
     *
     * @return string
     */
    protected function _generateRandomName()
    {
        if (!$this->getData('name')) {
            $this->setData('name', md5(time() . rand()));
        }

        return $this->getData('name');
    }

    /**
     * Update Status
     *
     * @return Enterprise_Support_Model_Backup
     */
    public function updateStatus()
    {
        if ($this->getStatus() == self::STATUS_COMPLETE) {
            return $this;
        }
        $this->_updateLog();

        $allItemsCompleted = $this->_isAllItemsCompleted();
        if ($allItemsCompleted) {
            $this->setStatus(self::STATUS_COMPLETE);
            $this->_removeLogFile();
        } else {
            $this->setStatus(self::STATUS_PROCESSING);
        }

        if ($this->_isItemsFilesNotExist() && $this->getLog()) {
            $this->setStatus(self::STATUS_FAILED);
        }

        return $this->save();
    }

    /**
     * Check is all items status completed
     *
     * @return bool
     */
    protected function _isAllItemsCompleted()
    {
        $complete = true;
        foreach ($this->getItems() as $item) {
            if ($item->getStatus() != Enterprise_Support_Model_Backup_Item_Abstract::STATUS_COMPLETE) {
                $complete = false;
                break;
            }
        }

        return $complete;
    }

    /**
     * Check if items files not exist
     *
     * @return bool
     */
    protected function _isItemsFilesNotExist()
    {
        $result = true;
        foreach ($this->getItems() as $item) {
            $file = Mage::helper('enterprise_support')->getFilePath($item->getName());
            if (file_exists($file)) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    /**
     * Update Log Data for Current Backup
     *
     * @return Enterprise_Support_Model_Backup
     */
    protected function _updateLog()
    {
        $logPath = Mage::helper('enterprise_support')->getOutputPath() . self::LOG_FILENAME;
        if (file_exists($logPath)) {
            $this->setLog(file_get_contents($logPath));
        }

        return $this;
    }

    /**
     * Remove Log File
     *
     * @return Enterprise_Support_Model_Backup
     */
    protected function _removeLogFile()
    {
        $logPath = Mage::helper('enterprise_support')->getOutputPath() . self::LOG_FILENAME;
        if (file_exists($logPath)) {
            Mage::getSingleton('varien/io_file')->rm($logPath);
        }

        return $this;
    }
}
