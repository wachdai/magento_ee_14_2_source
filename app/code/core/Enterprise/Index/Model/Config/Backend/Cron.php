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
 * Backend cron class for preparing cron_expr for index clean changelog
 *
 * @category    Enterprise
 * @package     Enterprise_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Index_Model_Config_Backend_Cron extends Mage_Core_Model_Config_Data
{
    /**
     * The cron_expr path
     */
    const CRON_STRING_PATH  = 'crontab/jobs/enterprise_index_clean_changelog/schedule/cron_expr';

    /**
     * Clean changelog model path
     */
    const CRON_MODEL_PATH   = 'crontab/jobs/enterprise_index_clean_changelog/run/model';


    /**#@+
     * Setting paths
     *
     * @var string
     */
    const XML_PATH_CLEAN_ENABLED       = 'groups/index_clean_schedule/fields/enabled/value';
    const XML_PATH_CLEAN_TIME          = 'groups/index_clean_schedule/fields/time/value';
    const XML_PATH_CLEAN_FREQUENCY     = 'groups/index_clean_schedule/fields/frequency/value';
    /**#@-*/

    /**
     * Factory instance
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Config instance
     *
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * Initialize helper instance
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('core/factory');
        $this->_config = !empty($args['config']) ? $args['config'] : Mage::getConfig();
        parent::__construct($args);
    }

    /**
     * Prepare and store cron settings after save
     *
     * @return Enterprise_Index_Model_Config_Backend_Cron
     */
    protected function _afterSave()
    {
        $this->_saveCronSettings();
        parent::_afterSave();
        return $this;
    }

    /**
     * Save cron settings for start change log model
     *
     * @return Enterprise_Index_Model_Config_Backend_Cron
     * @throws Mage_Core_Exception
     */
    protected function _saveCronSettings()
    {
        try {
            //save schedule cron expression
            $this->_saveConfig(self::CRON_STRING_PATH, $this->_getCronExpr());

            //save ChangeLog model
            $this->_saveConfig(
                self::CRON_MODEL_PATH,
                (string)$this->_config->getNode(self::CRON_MODEL_PATH)
            );
        } catch (Exception $e) {
            /* @var $logger Mage_Core_Model_Logger */
            $logger = $this->_factory->getModel('core/logger');
            $logger->logException($e);
            throw new Mage_Core_Exception(
                $this->helper('enterprise_index')->__('Unable to save the cron expression.')
            );
        }
        return $this;
    }

    /**
     * Save config data
     *
     * @param string $path
     * @param string $value
     * @return Enterprise_Index_Model_Config_Backend_Cron
     */
    protected function _saveConfig($path, $value)
    {
        $this->_getConfig()
            ->load($path, 'path')
            ->setValue($value)
            ->setPath($path)
            ->save();
        return $this;
    }

    /**
     * Get config model
     *
     * @return Mage_Core_Model_Config_Data
     */
    protected function _getConfig()
    {
        return $this->_factory->getModel('core/config_data');
    }

    /**
     * Returns cron_expr value
     *
     * @return string
     */
    protected function _getCronExpr()
    {
        if (!$this->getData(self::XML_PATH_CLEAN_ENABLED)) {
            return '';
        }

        $frequencyWeekly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
        $frequencyMonthly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;

        $time = $this->getData(self::XML_PATH_CLEAN_TIME);
        $frequency = $this->getData(self::XML_PATH_CLEAN_FREQUENCY);
        $cronExprArray = array(
            intval($time[1]), # Minute
            intval($time[0]), # Hour
            ($frequency == $frequencyMonthly) ? '1' : '*', # Day of the Month
            '*', # Month of the Year
            ($frequency == $frequencyWeekly) ? '1' : '*', # Day of the Week
        );

        return join(' ', $cronExprArray);
    }

    /**
     * Retrieves helper class based on its name
     *
     * @param string $name
     * @return Mage_Core_Helper_Abstract
     */
    public function helper($name)
    {
        return $this->_factory->getHelper($name);
    }
}
