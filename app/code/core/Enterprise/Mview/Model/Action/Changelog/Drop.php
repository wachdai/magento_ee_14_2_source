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
 * @package     Enterprise_Mview
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Changelog drop action class
 *
 * @category    Enterprise
 * @package     Enterprise_Mview
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Mview_Model_Action_Changelog_Drop implements Enterprise_Mview_Model_Action_Interface
{
    /**
     * Connection instance
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Materialized view Metadata instance
     *
     * @var Enterprise_Mview_Model_Metadata
     */
    protected $_metadata;

    /**
     * Factory instance
     *
     * @var Enterprise_Mview_Model_Factory
     */
    protected $_factory;

    /**
     * Changelog instance
     *
     * @var Magento_Db_Object_Table
     */
    protected $_changelog;

    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'connection' Varien_Db_Adapter_Interface
     *  - 'metadata' Enterprise_Mview_Model_Metadata
     *  - 'factory' Enterprise_Mview_Model_Factory
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->_setConnection($args['connection']);
        $this->_setMetadata($args['metadata']);
        $this->_setFactory($args['factory']);

        $this->_changelog = $this->_factory->getMagentoDbObjectTable($this->_connection,
            $this->_metadata->getChangelogName());
    }

    /**
     * Sets connection object
     *
     * @param Varien_Db_Adapter_Interface $connection
     * @return Enterprise_Mview_Model_Action_Changelog_Drop
     */
    protected function _setConnection(Varien_Db_Adapter_Interface $connection)
    {
        $this->_connection = $connection;
        return $this;
    }

    /**
     * Sets metadata object
     *
     * @param Enterprise_Mview_Model_Metadata $metadata
     * @return Enterprise_Mview_Model_Action_Changelog_Drop
     */
    protected function _setMetadata(Enterprise_Mview_Model_Metadata $metadata)
    {
        $this->_metadata = $metadata;
        return $this;
    }

    /**
     * Set factory instance
     *
     * @param Enterprise_Mview_Model_Factory $factory
     * @return Enterprise_Mview_Model_Action_Changelog_Drop
     */
    protected function _setFactory(Enterprise_Mview_Model_Factory $factory)
    {
        $this->_factory  = $factory;
        return $this;
    }

    /**
     * Drops changelog table
     * Updates changelog_name in the metadata.
     *
     * @return Enterprise_Mview_Model_Action_Changelog_Create
     */
    public function execute()
    {
        $this->_changelog->drop();
        $this->_metadata->setData('changelog_name', null)
            ->save();
        return $this;
    }
}
