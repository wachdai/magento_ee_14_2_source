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
 * Index Helper
 *
 * @category    Enterprise
 * @package     Enterprise_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Index_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Size of ids batch for reindex
     */
    const BATCH_SIZE = 500;

    /**
     * Path to indexers' config node
     */
    const XML_PATH_INDEXER_DATA = 'global/index/indexer';

    /**
     * Configuration object
     *
     * @var Mage_Core_Model_Config
     */
    protected $_config = null;

    /**
     * Configuration object
     *
     * @var Mage_Core_Model_Resource
     */
    protected $_resource;

    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_config = !empty($args['config']) ? $args['config'] : Mage::getConfig();
        $this->_resource = !empty($args['resource']) ? $args['resource'] : Mage::getSingleton('core/resource');
    }

    /**
     * Compare indexers sort order
     *
     * @param SimpleXMLElement $indexer1Parameters
     * @param SimpleXMLElement $indexer2Parameters
     * @return boolean
     */
    protected function _compareIndexerSortOrder($indexer1Parameters, $indexer2Parameters)
    {
        return (int)$indexer1Parameters->sort_order >= (int)$indexer2Parameters->sort_order ? 1 : -1;
    }

    /**
     * Get active indexer list
     *
     * @param boolean $useSortOrder
     * @return array
     */
    public function getIndexers($useSortOrder = true)
    {
        $indexers = (array)$this->_config->getNode(self::XML_PATH_INDEXER_DATA);

        foreach ($indexers as $indexerKey => $indexerData) {
            if (!isset($indexerData->action_model) || !isset($indexerData->index_table)) {
                unset($indexers[$indexerKey]);
            }
        }

        if ($useSortOrder) {
            uasort($indexers, array($this, '_compareIndexerSortOrder'));
        }

        return $indexers;
    }

    /**
     * Get action model name by index table and type
     *
     * @param string $indexTable
     * @param string $type
     * @return bool|string
     */
    public function getActionModelNameByIndexTable($indexTable, $type = 'all')
    {
        $indexers = $this->getIndexers();

        $actionModelName = false;
        foreach ($indexers as $indexer) {
            $prefixedTablrName = $this->_resource->getTableName($indexer->index_table);
            if ($prefixedTablrName == $indexTable && isset($indexer->action_model->$type)) {
                $actionModelName = (string)$indexer->action_model->$type;
                break;
            }
        }

        return $actionModelName;
    }

    /**
     * Get index table name by indexer name (node name)
     *
     * @param string $indexerNodeName
     * @return false|string
     */
    public function getIndexTableByIndexerName($indexerNodeName)
    {
        $indexers = $this->getIndexers(false);

        $indexTableName = false;
        foreach ($indexers as $indexerName => $indexer) {
            if ($indexerName == $indexerNodeName) {
                $indexTableName = (string)$indexer->index_table;
                break;
            }
        }

        return $indexTableName;
    }

    /**
     * Get config value by indexer name and node name
     *
     * @param string $indexerNodeName
     * @param string $nodeName
     * @return string
     */
    public function getIndexerConfigValue($indexerNodeName, $nodeName)
    {
        return (string)$this->_config->getNode(
            self::XML_PATH_INDEXER_DATA . '/' . $indexerNodeName . '/' . $nodeName
        );
    }

    /**
     * Get size of ids batch for reindex
     *
     * @param string $indexerNodeName
     * @return int
     */
    public function getBatchSize($indexerNodeName = null)
    {
        if (!empty($indexerNodeName)) {
            $batchSize = $this->getIndexerConfigValue($indexerNodeName, 'batch_size');
        }
        return (isset($batchSize) && !empty($batchSize)) ? $batchSize : self::BATCH_SIZE;
    }
}

