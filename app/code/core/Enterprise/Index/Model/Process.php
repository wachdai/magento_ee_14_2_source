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
 * Enterprise process model
 *
 * @method int getSortOrder()
 *
 * @category    Enterprise
 * @package     Enterprise_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Index_Model_Process extends Mage_Index_Model_Process
{
    /**
     * Default value for sorting
     */
    const DEFAULT_SORT_ORDER = 1000;

    /**
     * Scheduled status for mview indexers
     */
    const STATUS_SCHEDULED    = 'scheduled';

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('enterprise_index/process');
    }

    /**
     * Check if process is a native or enterprise.
     * Return TRUE if enterprise.
     *
     * @return bool
     */
    public function isEnterpriseProcess()
    {
        return !is_numeric($this->getId());
    }

    /**
     * Get Indexer strategy object
     *
     * @return Mage_Index_Model_Indexer_Abstract
     */
    public function getIndexer()
    {
        if ($this->_indexer === null) {
            $code = $this->_getData('indexer_code');
            if (!$code) {
                Mage::throwException(Mage::helper('index')->__('Indexer code is not defined.'));
            }
            $xmlPath = self::XML_PATH_INDEXER_DATA . '/' . $code;
            $config = Mage::getConfig()->getNode($xmlPath);
            if (!$config || empty($config->model)) {
                Mage::throwException(Mage::helper('index')->__('Indexer model is not defined.'));
            }
            $model = Mage::getModel((string)$config->model);
            if ($model instanceof Mage_Index_Model_Indexer_Abstract) {
                $this->_indexer = $model;
            } else {
                Mage::throwException(
                    Mage::helper('index')->__('Indexer model should extend Mage_Index_Model_Indexer_Abstract.')
                );
            }
            if ((string)$config->visibility != '') {
                $this->_indexer->setVisibility((string)$config->visibility==='1');
            }
            if (empty($config->sort_order)) {
                $config->sort_order = self::DEFAULT_SORT_ORDER;
            }
            $this->_indexer->setSortOrder((int)$config->sort_order);
        }
        return $this->_indexer;
    }

    /**
     * Get list of process status options
     *
     * @return array
     */
    public function getStatusesOptions()
    {
        return array(
            self::STATUS_PENDING            => Mage::helper('enterprise_index')->__('Ready'),
            self::STATUS_RUNNING            => Mage::helper('enterprise_index')->__('Processing'),
            self::STATUS_REQUIRE_REINDEX    => Mage::helper('enterprise_index')->__('Reindex Required'),
            self::STATUS_SCHEDULED          => Mage::helper('enterprise_index')->__('Scheduled'),
        );
    }
}
