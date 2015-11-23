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
 * Changelog create action class
 *
 * @category    Enterprise
 * @package     Enterprise_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Index_Model_Action_Index_Changelog_Create extends Enterprise_Mview_Model_Action_Changelog_Create
{
    /**
     * Metadata table name
     *
     * @var string|null
     */
    protected $_tableName = null;

    /**
     * Metadata key column
     *
     * @var string|null
     */
    protected $_keyColumn = null;

    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'connection' Varien_Db_Adapter_Interface
     *  - 'metadata' Enterprise_Mview_Model_Metadata
     *  - 'factory' Enterprise_Mview_Model_Factory
     *  - 'key_column' array(type => type, length => length, comment => comment)
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
        if (!$this->_table->isExists()) {
            $this->_validateArguments($args);
            $this->_tableName = $this->_metadata->getTableName();
            $this->_keyColumn = isset($args['key_column']) ? $args['key_column'] : $this->prepareKeyColumnConfig();
        } else {
            $this->_tableName = $this->_table->getObjectName();
            $this->_keyColumn = $this->_getKeyColumnConfig();
        }
    }

    /**
     * Validate argument action
     *
     * @param array $arguments
     * @throws Enterprise_Index_Model_Action_Exception
     * @return Enterprise_Index_Model_Action_Index_Changelog_Create
     */
    protected function _validateArguments($arguments)
    {
        if (!isset($arguments['key_column']) && !$this->_metadata->getKeyColumn()) {
            throw new Enterprise_Index_Model_Action_Exception(
                "Key column param for table '{$this->_metadata->getTableName()}' is not set!"
            );
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
        $table = $this->_connection->newTable($this->_changelog->getObjectName(), $this->_changelog->getSchemaName());
        $table->addColumn('version_id', Varien_Db_Ddl_Table::TYPE_BIGINT, null,
            array(
                'unsigned'          => true,
                'nullable'          => false,
                'primary'           => true,
                'auto_increment'    => true
            ), 'Version'
        )->addColumn(
            $this->_metadata->getKeyColumn(),
            $this->_keyColumn['type'],
            $this->_keyColumn['length'],
            array(
                'unsigned' => !empty($options['unsigned']),
                'nullable' => false,
            ),
            $this->_keyColumn['comment']
        )->setComment(
            sprintf('Changelog for table `%s`', $this->_tableName)
        );
        return $table;
    }

    /**
     * Prepare key column for changelog
     *
     * @param string $name
     * @param string $type
     * @param int $length
     * @param string $comment
     * @param string $options
     * @return array
     */
    public function prepareKeyColumnConfig($name = '', $type = Varien_Db_Ddl_Table::TYPE_INTEGER, $length = 11,
                                           $comment = 'Key Column', $options = '')
    {
        if (empty($name)){
            $name = $this->_metadata->getKeyColumn();
        }
        return array(
            'name'    => $name,
            'type'    => $type,
            'length'  => $length,
            'options' => $options,
            'comment' => $comment
        );
    }

    /**
     * Checks whether materialized view table exists. (override for unexistent tables)
     *
     * @return Enterprise_Index_Model_Action_Index_Changelog_Create
     * @throws Enterprise_Mview_Exception
     */
    public function validate()
    {
        if (!$this->_metadata->getKeyColumn()) {
            throw new Enterprise_Mview_Exception('Key Column is required field');
        }
        return $this;
    }
}
