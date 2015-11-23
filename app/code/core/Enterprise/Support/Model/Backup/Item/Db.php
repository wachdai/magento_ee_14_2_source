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

class Enterprise_Support_Model_Backup_Item_Db extends Enterprise_Support_Model_Backup_Item_Abstract
{
    /**
     * Init Resource Model
     */
    protected function _construct()
    {
        $this->_init('enterprise_support/backup_item');
    }

    /**
     * Get Cmd Object
     *
     * @return Enterprise_Support_Model_Backup_Item_Cmd_Bash
     */
    public function getCmdObject()
    {
        if (!$this->_cmdObject) {
            /** @var $_cmdObject Enterprise_Support_Model_Backup_Item_Cmd_Bash */
            $this->_cmdObject = Mage::getModel('enterprise_support/backup_item_cmd_bash');
            $this->_cmdObject->setScriptInterpreter('/bin/bash ');
            $this->_cmdObject->setScriptName('backup.sh');
            $this->_cmdObject->setName($this->getBackup()->getName());
            $this->_cmdObject->setMode('db');
            $this->_cmdObject->setOutputpath(Mage::helper('enterprise_support')->getOutputPath());
        }

        return $this->_cmdObject;
    }
}
