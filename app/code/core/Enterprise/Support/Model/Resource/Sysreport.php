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

class Enterprise_Support_Model_Resource_Sysreport extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Data helper object
     *
     * @var Enterprise_Support_Helper_Data
     */
    protected $_dataHelper = null;

    /**
     * Set main table name and id field
     * Initialize data helper
     */
    protected function _construct()
    {
        $this->_init('enterprise_support/sysreport', 'report_id');
        /** @var Enterprise_Support_Helper_Data $this->_dataHelper */
        $this->_dataHelper = Mage::helper('enterprise_support');
    }

    /**
     * Prepare system report data to be saved
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Enterprise_Support_Model_Resource_Sysreport
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $object->setClientHost($this->getTool()->getClientHost());
        $object->setReportVersion($this->getTool()->getVersion());
        $object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        $object->setMagentoVersion(Mage::getVersion());
        $types = $object->getReportTypes();
        $flags = $this->_dataHelper->getSysReportCommandsListByReportTypes($types);
        $object->setReportFlags(implode(',', $flags));
        $object->setReportTypes(implode(',', $types));
        $object->setReportData(serialize($object->getReportData()));

        parent::_afterSave($object);

        return $this;
    }

    /**
     * Unserialize system report data
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Enterprise_Support_Model_Resource_Sysreport
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        try {
            $data = unserialize($object->getReportData());
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('enterprise_support')->__('There was an error while loading system report data.')
            );
            $data = array();
        }
        $object->setReportData($data);

        parent::_afterLoad($object);

        return $this;
    }

    /**
     * Get system report tool
     *
     * @return Enterprise_Support_Model_Resource_Sysreport_Tool
     */
    public function getTool()
    {
        return Mage::getResourceSingleton('enterprise_support/sysreport_tool');
    }

    /**
     * Generate system reports by specified report types
     *
     * @param string|array $types
     *
     * @return array
     */
    public function generateReport($types)
    {
        $commands = $this->_dataHelper->getSysReportCommandsListByReportTypes($types);
        return $this->getTool()
            ->run($commands)
            ->getReport();
    }

    /**
     * Collects information about commands that were successfully completed during generating system report data
     *
     * Must be called after $this->generateReport() was called
     *
     * @param array $requestedTypes
     *
     * @return array
     */
    public function getReportCreationResults($requestedTypes)
    {
        $results = array();
        $commands = $this->getTool()->getSucceededCommands();
        $installedSysReportTypes = Mage::helper('enterprise_support')->getSysReportTypes();
        foreach ($installedSysReportTypes as $typeName => $typeConfig) {
            if (!in_array($typeName, $requestedTypes)) {
                continue;
            }
            $results[$typeName] = array(
                'title' => $typeConfig['title'],
                'total' => sizeof($typeConfig['commands']),
                'succeeded' => 0
            );

            foreach ($typeConfig['commands'] as $command) {
                if (!in_array($command, $commands)) {
                    continue;
                }
                $results[$typeName]['succeeded']++;
            }
        }

        return $results;
    }
}
