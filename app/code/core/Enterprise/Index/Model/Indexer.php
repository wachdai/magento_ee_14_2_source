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
 * Enterprise indexer model
 *
 * @category    Enterprise
 * @package     Enterprise_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Index_Model_Indexer extends Mage_Index_Model_Indexer
{
    /**
     * Indexer process collection
     *
     * @var $_processesCollection Enterprise_Index_Model_Resource_Process_Collection
     */
    protected $_processesCollection;

    /**
     * Factory instance
     *
     * @var $_factory false|Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Application instance
     *
     * @var $_app Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Constructor for Enterprise_Index_Model_Indexer with parameters
     * Array of arguments with keys
     *  - 'factory' Mage_Core_Model_Factory
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_app = !empty($args['app']) ? $args['app'] : Mage::app();
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getModel('core/factory');
    }

    /**
     * Get collection of all available processes
     *
     * @return Mage_Index_Model_Resource_Process_Collection
     */
    public function getProcessesCollection()
    {
        if (is_null($this->_processesCollection)) {
            $this->_processesCollection = $this->_factory->getResourceModel('enterprise_index/process_collection');
            $this->_app->dispatchEvent('enterprise_index_exclude_process_before',
                array('collection' => $this->_processesCollection)
            );
            $this->_processesCollection->initializeSelect();

            $processes = array();
            foreach($this->_processesCollection as $process) {
                $processes[$process->getIndexerCode()] = $process;
            }
            uasort($processes, array($this, '_sortProcessCollection'));

            $this->_processesCollection = $processes;
        }

        return $this->_processesCollection;
    }

    /**
     *
     *
     * @param $processFirst Enterprise_Index_Model_Process
     * @param $processSecond Enterprise_Index_Model_Process
     * @return int
     */
    protected function _sortProcessCollection($processFirst, $processSecond)
    {
        return (int)$processFirst->getIndexer()->getSortOrder() >= (int)$processSecond->getIndexer()->getSortOrder()
            ? 1
            : -1;
    }

    /**
     * Get index process by specific code
     *
     * @param string $code
     * @return Mage_Index_Model_Process | Enterprise_Index_Model_Process | false
     */
    public function getProcessByCode($code)
    {
        $this->getProcessesCollection();
        return empty($this->_processesCollection[$code]) ? false : $this->_processesCollection[$code];
    }

    /**
     * Function returns array of indexer's process with order by sort_order field
     *
     * @param array $codes
     * @return array
     */
    public function getProcessesCollectionByCodes(array $codes)
    {
        $processes = array();
        $this->_errors = array();
        $codes  = $this->_prepareCodes($codes);
        foreach($this->getProcessesCollection() as $code => $process) {
            if (!isset($codes[$code])) {
                continue;
            }
            $processes[$code] = $process;
            unset($codes[$code]);
        }

        return $processes;
    }

    /**
     * Return all codes of indexers with depends
     *
     * @param array $codes
     * @return array
     */
    protected function _prepareCodes(array $codes)
    {
        $prepareCodes = array();
        $codes  = array_combine(array_values($codes), array_values($codes));
        foreach($this->getProcessesCollection() as $code => $process) {
            if (!isset($codes[$code])) {
                continue;
            }
            if ($process->getDepends()) {
                foreach($process->getDepends() as $dependsCode) {
                    $prepareCodes = array_merge($prepareCodes, $this->_prepareCodes(array($dependsCode)));
                }
            }
            $prepareCodes[$code] = $code;
            unset($codes[$code]);
        }
        foreach($codes as $code) {
            $this->_errors[] = sprintf('Warning: Unknown indexer with code %s', trim($code));
        }

        return $prepareCodes;
    }
}
