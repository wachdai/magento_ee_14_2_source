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
 * Staging config model
 */
class Enterprise_Staging_Model_Staging_Config
{
    /**
     * Staging statuses
     */
    //Action statuses
    const STATUS_STARTED        = 'started';
    const STATUS_COMPLETED      = 'completed';

    /**
     * Staging actions
     */
    const ACTION_CREATE             = 'create';
    const ACTION_RESET              = 'reset';
    const ACTION_MERGE              = 'merge';
    const ACTION_CRON_MERGE         = 'cron_merge';
    const ACTION_SCHEDULE_MERGE     = 'schedule_merge';
    const ACTION_UNSCHEDULE_MERGE   = 'unschedule_merge';
    const ACTION_ROLLBACK           = 'rollback';
    const ACTION_BACKUP             = 'backup';


    /**
     * Staging visibility codes
     */
    const VISIBILITY_NOT_ACCESSIBLE     = 'not_accessible';
    const VISIBILITY_ACCESSIBLE         = 'accessible';
    const VISIBILITY_REQUIRE_HTTP_AUTH  = 'require_http_auth';

    /**
     * Retrieve staging module xml config as Varien_Simplexml_Element object
     *
     * @param   string $path
     * @return  object Varien_Simplexml_Element
     */
    static public function getConfig($path = null)
    {
        $_path = 'global/enterprise/staging/';
        if (!is_null($path)) {
            $_path .= ltrim($path, '/');
        }
        return Mage::getConfig()->getNode($_path);
    }

    /**
     * Get Config node as mixed option array
     *
     * @param string $nodeName
     * @return mixed
     */
    static public function getOptionArray($nodeName)
    {
        $options = array();
        $config = self::getConfig($nodeName);
        if ($config) {
            foreach ($config->children() as $node) {
                $label = Mage::helper('enterprise_staging')->__((string)$node->label);
                $options[$node->getName()] = $label;
            }
        }
        return $options;
    }

    public function getVisibilityOptionArray()
    {
        return array(
        self::VISIBILITY_NOT_ACCESSIBLE    => Mage::helper('enterprise_staging')->__('Not accessible'),
        self::VISIBILITY_ACCESSIBLE        => Mage::helper('enterprise_staging')->__('Accessible'),
        self::VISIBILITY_REQUIRE_HTTP_AUTH => Mage::helper('enterprise_staging')->__('Require HTTP Authentication')
        );
    }

    /**
     * Get Config node as mixed option array, with selected structure: value, label
     *
     * @param string $nodeName
     * @return mixed
     */
    static public function getAllOptions($nodeName)
    {
        $res = array();
        foreach (self::getOptionArray($nodeName) as $value => $label) {
            $res[] = array(
            'value' => $value,
            'label' => $label
            );
        }
        return $res;
    }

    /**
     * Get Config node as mixed option array, with selected structure: value, label
     * If $addEmpty true - add empty option
     *
     * @param string $nodeName
     * @param boolean $addEmpty
     * @return array
     */
    static public function toOptionArray($nodeName, $addEmpty = false)
    {
        $result = array();
        if ($addEmpty) {
            $result[] = array('value' => '','label' => '');
        }
        foreach (self::getOptionArray($nodeName) as $value => $label) {
            $result[] = array('value' => $value,'label' => $label);
        }
        return $result;
    }

    /**
     * get Config node as text by option id
     *
     * @param mixed  $optionId
     * @param string $nodeName
     * @return text
     */
    static public function getOptionText($optionId, $nodeName)
    {
        $options = self::getOptionArray($nodeName);
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * Retrieve Staging Items
     *
     * @return mixed
     */
    public function getStagingItems()
    {
        $stagingItems = self::getConfig('staging_items');
        if ($stagingItems) {
            return $stagingItems->children();
        }
        return array();
    }

    /**
     * Retrieve staging item by item code
     *
     * @param  string $itemCode
     * @return string
     */
    public function getStagingItem($itemCode)
    {
        $stagingItems = $this->getStagingItems();
        if (!empty($stagingItems->{$itemCode})) {
            return $stagingItems->{$itemCode};
        } else {
            foreach ($stagingItems as $stagingItem) {
                if ($stagingItem->extends) {
                    if ($stagingItem->extends->{$itemCode}) {
                        return $stagingItem->extends->{$itemCode};
                    }
                }
            }
            return null;
        }
    }

    /**
     * Check if module given item is active
     *
     * @param  Varien_Simplexml_Element $stagingItem
     * @return boolean
     */
    function isItemModuleActive($stagingItem)
    {
        $moduleName = (string) $stagingItem->module;
        if (!empty($moduleName)) {
            $module = Mage::getConfig()->getModuleConfig($moduleName);
            if ($module) {
                if ($module->is('active')) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Retrieve Staging Action Label
     *
     * @param   string $actionCode
     * @return  string
     */
    static public function getActionLabel($actionCode)
    {
        $action = '';
        $actionNode = self::getConfig('action/'.$actionCode);
        if ($actionNode) {
            $action = (string) $actionNode->label;
            return Mage::helper('enterprise_staging')->__($action);
        }
        return $action;
    }

    /**
     * Get actions array from config
     *
     * @return array
     */
    static public function getActionLabelsArray(){
        $actionNode = self::getConfig('action')->asArray();
        $actionArray = array();
        foreach ($actionNode as $code => $node){
            $actionArray[$code] = Mage::helper('enterprise_staging')->__((string)$node['label']);
        }
        asort($actionArray);
        return $actionArray;
    }

    /**
     * Retrieve status label
     *
     * @param   string $status
     * @return  string
     */
    static public function getStatusLabel($status)
    {
        $statusNode = self::getConfig('status/action/' . $status);
        if ($statusNode) {
            $status = (string) $statusNode->label;
            return Mage::helper('enterprise_staging')->__($status);
        }
        return $status;
    }

    /**
     * Get statuses array from config
     *
     * @return array
     */
    static public function getStatusLabelsArray(){
        $statusNode = self::getConfig('status/action')->asArray();
        $statusArray = array();
        foreach ($statusNode as $code => $node){
            $statusArray[$code] = (string) $node['label'];
        }
        return $statusArray;
    }


    /**
     * Retrieve visibility label
     *
     * @param   string $visibility
     * @return  string
     */
    static public function getVisibilityLabel($visibility)
    {
        $labels = $this->getVisibilityOptionArray();
        return isset($labels[$visibility]) ? $labels[$visibility] : null;
    }

    /**
     * Retrieve staging table prefix
     *
     * @param   Enterprise_Staging_Model_Staging $staging
     * @param   string $internalPrefix
     * @return  string
     */
    public function getTablePrefix($staging = null, $internalPrefix = '')
    {
        $globalTablePrefix  = (string) Mage::getConfig()->getTablePrefix();
        $stagingTablePrefix = $this->getStagingTablePrefix();

        if (!is_null($staging)) {
            $stagingTablePrefix = $staging->getTablePrefix();
        } else {
            $stagingTablePrefix = $globalTablePrefix . $stagingTablePrefix;
        }
        $stagingTablePrefix .= $internalPrefix;

        return $stagingTablePrefix;
    }

    /**
     * Get staging global table prefix
     *
     * @return string
     */
    public function getStagingTablePrefix()
    {
        return (string) self::getConfig('global_staging_table_prefix');
    }

    /**
     * Get staging global table prefix
     *
     * @return string
     */
    public function getStagingBackupTablePrefix()
    {
        return (string) self::getConfig('global_staging_backup_table_prefix');
    }

    /**
     * Get staging backend table name (for frontend usage)
     *
     * @param string $tableName
     * @param string $modelEntity
     * @param object Mage_Core_Model_Website $stagingWebsite
     *
     * @return false | string
     */
    public function getStagingFrontendTableName($tableName, $modelEntity, $stagingWebsite = null)
    {
        $stagingTablePrefix = $this->getTablePrefix();
        if (empty($stagingTablePrefix)) {
            return false;
        }

        $staging = Mage::getModel('enterprise_staging/staging');
        if (!Mage::getSingleton("core/session")->getData('staging_frontend_website_is_checked')) {
            $staging->checkFrontend();
        }

        list($model, $entity) = explode('/' , $modelEntity, 2);
        if (!$model){
            return false;
        }

        $globalTablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $_tableName = $globalTablePrefix . $tableName;
        if ($this->isStagingUpTableName($model, $tableName)) {
            return $stagingTablePrefix . $_tableName;
        }

        return false;
    }

    /**
     * Check in staging config if need to modify src table name
     *
     * @param string $model
     * @param string $tableName
     * @return bool
     */
    public function isStagingUpTableName($model, $tableName)
    {
        $itemSet = self::getConfig("staging_items");
        if ($itemSet) {
            foreach($itemSet->children() as $item) {
                $itemModel = (string) $item->model;
                if ($itemModel == $model) {
                    $isBackend = ((string)$item->is_backend === '1');
                    if ($isBackend) {
                        $ignoreTables = (array) $item->ignore_tables;
                        //ignore for specified tables
                        if (!empty($ignoreTables)){
                            if (array_key_exists($tableName, $ignoreTables)) {
                                return false;
                            }
                        }
                        $tables = (array)  $item->entities;
                        //apply for specified tables
                        if (!empty($tables)){
                            if (!array_key_exists($tableName, $tables)) {
                                return false;
                            }
                        } else {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Retrieve core resources version
     *
     * @return  string
     */
    public function getCoreResourcesVersion()
    {
        $coreResource = Mage::getSingleton('core/resource');
        $connection  = $coreResource->getConnection('core_read');
        $select = $connection->select()->from($coreResource->getTableName('core/resource'), array('code' , 'version'));
        $result = $connection->fetchPairs($select);
        if (is_array($result) && count($result)>0) {
            return $result;
        } else {
            return array();
        }
    }
}
