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
 * Index cron class
 *
 * @category    Enterprise
 * @package     Enterprise_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Index_Model_Cron
{
    /**
     * Index clean schedule enabled flag
     */
    const XML_PATH_INDEX_CLEAN_SCHEDULE_ENABLED = 'default/index_management/index_clean_schedule/enabled';

    /**
     * Factory instance
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Logger instance
     *
     * @var Mage_Core_Model_Logger
     */
    protected $_logger;

    /**
     * Config instance
     *
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * Initialize model
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('core/factory');
        $this->_logger = !empty($args['logger']) ? $args['logger'] : Mage::getSingleton('core/logger');
        $this->_config = !empty($args['config']) ? $args['config'] : Mage::getConfig();
    }

    /**
     * Clean changelog tables listed in enterprise_mview/metadata table.
     * Cleanup would be executed in case the "index_clean_schedule" is set to "1"
     *
     * @return void
     */
    public function scheduledCleanup()
    {
        if (!$this->_isCleanChangelogEnabled()) {
            return;
        }

        foreach ($this->_getMetadataCollection() as $metadata) {
            if (!$metadata->getChangelogName()) {
                continue;
            }
            $this->_runCleanupAction($metadata);
        }
    }

    /**
     * Initialize and execute changelog clear action with metadata provided.
     *
     * @param Enterprise_Mview_Model_Metadata $metadata
     */
    protected function _runCleanupAction(Enterprise_Mview_Model_Metadata $metadata)
    {
        $this->_getClient()->initByTableName($metadata->getTableName());
        try {
            $this->_getClient()->execute('enterprise_mview/action_changelog_clear');
        } catch (Exception $e) {
            $this->_logger->logException($e);
        }
    }

    /**
     * Retrieve metadata collection
     *
     * @return Enterprise_Mview_Model_Resource_Metadata_Collection
     */
    protected function _getMetadataCollection()
    {
        return $this->_factory->getModel('enterprise_mview/metadata')->getCollection();
    }

    /**
     * Retrieve clean schedule enabled flag
     *
     * @return bool
     */
    protected function _isCleanChangelogEnabled()
    {
        return (bool)(string)$this->_config->getNode(self::XML_PATH_INDEX_CLEAN_SCHEDULE_ENABLED);
    }

    /**
     * Retrieve client instance
     *
     * @return Enterprise_Mview_Model_Client
     */
    protected function _getClient()
    {
        return $this->_factory->getSingleton('enterprise_mview/client');
    }
}
