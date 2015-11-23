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
 * @package     Enterprise_Logging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Logging event changes model
 *
 * @method Enterprise_Logging_Model_Resource_Event_Changes _getResource()
 * @method Enterprise_Logging_Model_Resource_Event_Changes getResource()
 * @method string getSourceName()
 * @method Enterprise_Logging_Model_Event_Changes setSourceName(string $value)
 * @method int getEventId()
 * @method Enterprise_Logging_Model_Event_Changes setEventId(int $value)
 * @method int getSourceId()
 * @method Enterprise_Logging_Model_Event_Changes setSourceId(int $value)
 * @method string getOriginalData()
 * @method Enterprise_Logging_Model_Event_Changes setOriginalData(string $value)
 * @method string getResultData()
 * @method Enterprise_Logging_Model_Event_Changes setResultData(string $value)
 *
 * @category    Enterprise
 * @package     Enterprise_Logging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Logging_Model_Event_Changes extends Mage_Core_Model_Abstract
{
    /**
     * Config path to fields that must be not be logged for all models
     *
     */
    const XML_PATH_SKIP_GLOBAL_FIELDS = 'adminhtml/enterprise/logging/skip_fields';

    /**
     * Set of fields that should not be logged for all models
     *
     * @var array
     */
    protected $_globalSkipFields = array();

    /**
     * Set of fields that should not be logged per expected model
     *
     * @var array
     */
    protected $_skipFields = array();

    /**
     * Store difference between original data and result data of model
     *
     * @var array
     */
    protected $_difference = null;

    /**
     * Initialize resource
     * Get fields that should not be logged for all models
     *
     */
    protected function _construct()
    {
        $this->_globalSkipFields = array_map('trim', array_filter(explode(',',
            (string)Mage::getConfig()->getNode(self::XML_PATH_SKIP_GLOBAL_FIELDS))));

        $this->_init('enterprise_logging/event_changes');
    }

    /**
     * Set some data automatically before saving model
     *
     * @return Enterprise_Logging_Model_Event
     */
    protected function _beforeSave()
    {
        $this->_calculateDifference();
        $this->setOriginalData(serialize($this->getOriginalData()));
        $this->setResultData(serialize($this->getResultData()));
        return parent::_beforeSave();
    }

    /**
     * Define if current model has difference between original and result data
     *
     * @return bool
     */
    public function hasDifference()
    {
        $difference = $this->_calculateDifference();
        return !empty($difference);
    }

    /**
     * Calculate difference between original and result data and return that difference
     *
     * @return null|array|int
     */
    protected function _calculateDifference()
    {
        if (is_null($this->_difference)) {
            $updatedParams = $newParams = $sameParams = $difference = array();
            $newOriginalData = $origData = $this->getOriginalData();
            $newResultData = $resultData = $this->getResultData();

            if (!is_array($origData)) {
                $origData = array();
            }
            if (!is_array($resultData)) {
                $resultData = array();
            }

            if (!$origData && $resultData) {
                $newOriginalData = array('__was_created' => true);
                $difference = $resultData;
            }
            elseif ($origData && !$resultData) {
                $newResultData = array('__was_deleted' => true);
                $difference = $origData;
            }
            elseif ($origData && $resultData) {
                $newParams  = array_diff_key($resultData, $origData);
                $sameParams = array_intersect_key($origData, $resultData);
                foreach ($sameParams as $key => $value) {
                    if ($origData[$key] != $resultData[$key]) {
                        $updatedParams[$key] = $resultData[$key];
                    }
                }
                $newOriginalData = array_intersect_key($origData, $updatedParams);
                $difference = $newResultData = array_merge($updatedParams, $newParams);
                if ($difference && !$updatedParams) {
                    $newOriginalData = array('__no_changes' => true);
                }
            }

            $this->setOriginalData($newOriginalData);
            $this->setResultData($newResultData);

            $this->_difference = $difference;
        }
        return $this->_difference;
    }

    /**
     * Set skip fields and clear model data
     *
     * @param array $skipFields
     */
    public function cleanupData($skipFields)
    {
        if ($skipFields && is_array($skipFields)) {
            $this->_skipFields = $skipFields;
        }
        $this->setOriginalData($this->_cleanupData($this->getOriginalData()));
        $this->setResultData($this->_cleanupData($this->getResultData()));
    }

    /**
     * Clear model data from objects, arrays and fields that should be skipped
     *
     * @param array $data
     * @return array
     */
    protected function _cleanupData($data)
    {
        if (!$data || !is_array($data)) {
            return array();
        }
        $skipFields = $this->_skipFields;
        if (!$skipFields || !is_array($skipFields)) {
            $skipFields = array();
        }
        $clearedData = array();
        foreach ($data as $key => $value) {
            if (!in_array($key, $this->_globalSkipFields) && !in_array($key, $skipFields) && !is_array($value) && !is_object($value)) {
                $clearedData[$key] = $value;
            }
        }
        return $clearedData;
    }

    /**
     * Setter for source name of event changes
     * Used to save compatibility with older versions
     *
     * @deprecated after 1.6.0.0
     * @param string $modelName
     */
    public function setModelName($modelName)
    {
        $this->setSourceName($modelName);
    }

    /**
     * Getter for source name of event changes
     * Used to save compatibility with older versions
     *
     * @deprecated after 1.6.0.0
     * @return string
     */
    public function getModelName()
    {
        return $this->getSourceName();
    }

    /**
     * Setter for source id of event changes
     * Used to save compatibility with older versions
     *
     * @deprecated after 1.6.0.0
     * @param string $modelId
     */
    public function setModelId($modelId)
    {
        $this->setSourceId($modelId);
    }

    /**
     * Getter for source id of event changes
     * Used to save compatibility with older versions
     *
     * @deprecated after 1.6.0.0
     * @return string
     */
    public function getModelId()
    {
        return $this->getSourceId();
    }
}
