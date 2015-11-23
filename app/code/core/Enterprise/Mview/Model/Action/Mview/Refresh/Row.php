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
 * Mview refresh row action class
 *
 * @category    Enterprise
 * @package     Enterprise_Mview
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Mview_Model_Action_Mview_Refresh_Row
    implements Enterprise_Mview_Model_Action_Interface
{
    /**
     * Connection instance
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Metadata instance
     *
     * @var Enterprise_Mview_Model_Metadata
     */
    protected $_metadata;

    /**
     * Value for updating mview table
     *
     * @var mixed
     */
    protected $_keyColumnIdValue;

    /**
     * Constructor
     * Arguments:
     *  connection - Varien_Db_Adapter_Interface;
     *  metadata - Enterprise_Mview_Model_Metadata object;
     *  value - int|decimal|string|double
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->_setConnection($args['connection']);
        $this->_setMetadata($args['metadata']);
        $this->_keyColumnIdValue = $args['value'];
    }

    /**
     * Sets connection instance
     *
     * @param Varien_Db_Adapter_Interface $connection
     * @return Enterprise_Mview_Model_Action_Mview_Create
     */
    protected function _setConnection(Varien_Db_Adapter_Interface $connection)
    {
        $this->_connection = $connection;
        return $this;
    }

    /**
     * Sets metadata instance
     *
     * @param Enterprise_Mview_Model_Metadata $metadata
     * @return Enterprise_Mview_Model_Action_Mview_Create
     */
    protected function _setMetadata(Enterprise_Mview_Model_Metadata $metadata)
    {
        $this->_metadata  = $metadata;
        return $this;
    }

    /**
     * Method deletes old row in the mview table and insert new one from view.
     *
     * @return Enterprise_Mview_Model_Action_Mview_Create
     * @throws Enterprise_Mview_Exception
     */
    public function execute()
    {
        $this->_validate();

        $this->_connection->beginTransaction();
        try {
            $this->_connection->delete($this->_metadata->getTableName(),
                array($this->_metadata->getKeyColumn() . '=?' => $this->_keyColumnIdValue));
            $this->_connection->query($this->_getInsertSql());
            $this->_connection->commit();
        } catch (Exception $e) {
            $this->_connection->rollBack();
            throw new Enterprise_Mview_Exception($e->getMessage(), $e->getCode());
        }

        return $this;
    }

    /**
     * Validates value
     *
     * @return Enterprise_Mview_Model_Action_Mview_Refresh_Row
     * @throws Enterprise_Mview_Exception
     */
    protected function _validate()
    {
        if (empty($this->_keyColumnIdValue)) {
            throw new Enterprise_Mview_Exception('Value can not be empty');
        }

        return $this;
    }

    /**
     * Returns insert sql
     *
     * @return string
     */
    public function _getInsertSql()
    {
        return $this->_connection->insertFromSelect($this->_getSelectSql(), $this->_metadata->getTableName());
    }

    /**
     * Returns select sql
     *
     * @return Varien_Db_Select
     */
    protected function _getSelectSql()
    {
        return $this->_connection->select()
            ->from($this->_metadata->getViewName())
            ->where($this->_metadata->getKeyColumn() . ' = ?', $this->_keyColumnIdValue);
    }
}
