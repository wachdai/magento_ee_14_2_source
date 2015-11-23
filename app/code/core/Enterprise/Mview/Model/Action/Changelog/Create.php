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
 * Changelog create action class
 *
 * @category    Enterprise
 * @package     Enterprise_Mview
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Mview_Model_Action_Changelog_Create implements Enterprise_Mview_Model_Action_Interface
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
     * Table instance
     *
     * @var Magento_Db_Object_Table
     */
    protected $_table;

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
     * @throws InvalidArgumentException
     */
    public function __construct(array $args)
    {
        $this->_setConnection($args['connection']);
        $this->_setMetadata($args['metadata']);
        $this->_setFactory($args['factory']);

        $this->_changelog = $this->_factory->getMagentoDbObjectTable($this->_connection,
            $this->_getChangelogName());
        $this->_table = $this->_factory->getMagentoDbObjectTable($this->_connection,
            $this->_metadata->getTableName());
    }

    /**
     * Sets metadata object
     *
     * @param Enterprise_Mview_Model_Metadata $metadata
     * @return Enterprise_Mview_Model_Action_Changelog_Create
     */
    protected function _setMetadata(Enterprise_Mview_Model_Metadata $metadata)
    {
        $this->_metadata = $metadata;
        return $this;
    }

    /**
     * Sets connection object
     *
     * @param Varien_Db_Adapter_Interface $connection
     * @return Enterprise_Mview_Model_Action_Changelog_Create
     */
    protected function _setConnection(Varien_Db_Adapter_Interface $connection)
    {
        $this->_connection = $connection;
        return $this;
    }

    /**
     * Set factory instance
     *
     * @param Enterprise_Mview_Model_Factory $factory
     * @return Enterprise_Mview_Model_Action_Mview_Create
     */
    protected function _setFactory(Enterprise_Mview_Model_Factory $factory)
    {
        $this->_factory  = $factory;
        return $this;
    }

    /**
     * Validates and creates changelog table with 2 columns.
     * Updates changelog_name in the metadata.
     *
     * @return Enterprise_Mview_Model_Action_Changelog_Create
     */
    public function execute()
    {
        $this->validate();
        if (!$this->_changelog->isExists()) {
            $this->_createChangelogTable();
        }
        $this->_metadata->setData('changelog_name', $this->_getChangelogName())
            ->save();
        return $this;
    }

    /**
     * Create Changelog Table
     *
     * @return Enterprise_Mview_Model_Action_Changelog_Create
     */
    protected function _createChangelogTable()
    {
        $this->_connection->createTable($this->_getChangelogTableConfig());
        return $this;
    }

    /**
     * Checks whether materialized view table exists.
     *
     * @return Enterprise_Mview_Model_Action_Changelog_Create
     * @throws Enterprise_Mview_Exception
     */
    public function validate()
    {
        if (!$this->_table->isExists()) {
            throw new Enterprise_Mview_Exception('Mview table does not exist');
        }
        if (!$this->_metadata->getKeyColumn()) {
            throw new Enterprise_Mview_Exception('Key Column is required field');
        }
        return $this;
    }

    /**
     * Prepare changelog table definition
     *
     * @return Varien_Db_Ddl_Table
     */
    protected function _getChangelogTableConfig()
    {
        $column = $this->_getKeyColumnConfig();
        return $this->_connection->newTable($this->_changelog->getObjectName(), $this->_changelog->getSchemaName())
            ->addColumn('version_id', Varien_Db_Ddl_Table::TYPE_BIGINT, null,
                array(
                    'unsigned'          => true,
                    'nullable'          => false,
                    'primary'           => true,
                    'auto_increment'    => true
                ), 'Version')
            ->addColumn($this->_metadata->getKeyColumn(), $column['type'], $column['length'],
                array(
                    'unsigned'  => !empty($options['unsigned']),
                    'nullable'  => false,
                ), $column['comment'])
            ->setComment(sprintf('Changelog for table `%s`', $this->_table->getObjectName()));
    }

    /**
     * Returns column definition for column provided in key_column parameter.
     *
     * @return array
     * @throws Enterprise_Mview_Exception
     */
    protected function _getKeyColumnConfig()
    {
        $describe = $this->_table->describe();
        if (!array_key_exists($this->_metadata->getKeyColumn(), $describe)) {
            throw new Enterprise_Mview_Exception(sprintf("Column '%s' does not exist in the '%s' table",
                $this->_metadata->getKeyColumn(), $this->_table->getObjectName()));
        }
        return $this->_connection->getColumnCreateByDescribe($describe[$this->_metadata->getKeyColumn()]);
    }

    /**
     * Prepare changelog table name
     *
     * @return string
     * @throws Enterprise_Mview_Exception
     */
    protected function _getChangelogName()
    {
        if (!$this->_metadata->getTableName()) {
            throw new Enterprise_Mview_Exception('Mview table name should be specified');
        }
        return $this->_metadata->getTableName() . '_cl';
    }
}
