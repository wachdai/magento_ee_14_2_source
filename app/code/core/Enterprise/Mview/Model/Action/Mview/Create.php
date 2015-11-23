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
 * Enterprise_Mview_Model_Action_Mview_Create
 *
 * @category    Enterprise
 * @package     Enterprise_Mview
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Mview_Model_Action_Mview_Create
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
     * Select object instance
     *
     * @var Zend_Db_Select
     */
    protected $_select;

    /**
     * Factory instance
     *
     * @var Enterprise_Mview_Model_Factory
     */
    protected $_factory;

    /**
     * View instance
     *
     * @var Magento_Db_Object_View
     */
    protected $_view;

    /**
     * Table instance
     *
     * @var Magento_Db_Object_Table
     */
    protected $_table;

    /**
     * Constructor
     * Arguments:
     *  connection - Varien_Db_Adapter_Interface;
     *  metadata - Enterprise_Mview_Model_Metadata object;
     *  select - Zend_Db_Select object;
     *  factory - Enterprise_Mview_Model_Factory.
     *
     * @param $args array
     */
    public function __construct(array $args)
    {
        $this->_setConnection($args['connection']);
        $this->_setMetadata($args['metadata']);
        $this->_setSelect($args['select']);
        $this->_setFactory($args['factory']);

        $this->_view = $this->_factory->getMagentoDbObjectView($this->_connection,
            $this->_getViewName());
        $this->_table = $this->_factory->getMagentoDbObjectTable($this->_connection,
            $this->_metadata->getTableName());
    }

    /**
     * Returns materialized view name
     *
     * @return string
     */
    protected function _getViewName()
    {
        return $this->_metadata->getTableName() . '_view';
    }

    /**
     * Set connection instance
     *
     * @param Varien_Db_Adapter_Interface $connection
     * @return Enterprise_Mview_Model_Action_Mview_Create
     */
    protected function _setConnection(Varien_Db_Adapter_Interface $connection)
    {
        $this->_connection  = $connection;
        return $this;
    }

    /**
     * Set metadata instance
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
     * Set select object instance
     *
     * @param Zend_Db_Select $select
     * @return Enterprise_Mview_Model_Action_Mview_Create
     */
    protected function _setSelect(Zend_Db_Select $select)
    {
        $this->_select  = $select;
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
     * Create view and mview table
     * 1) Create view;
     * 2) Create table;
     * 3) Update view name and status in metadata.
     *
     * @return Enterprise_Mview_Model_Action_Mview_Create
     * @throws Exception
     */
    public function execute()
    {
        try {
            if (!$this->_view->isExists()) {
                $this->_view->createFromSource($this->_select);
            }
            if (!$this->_table->isExists()) {
                $select = $this->_connection->select()
                    ->from($this->_view->getObjectName());
                $this->_table->createFromSource($select);
            }

            $this->_metadata->setViewName($this->_view->getObjectName());
            $this->_metadata->setValidStatus();
            $this->_metadata->save();
        } catch (Exception $e) {
            $this->_view->drop();
            $this->_table->drop();
            throw $e;
        }
        return $this;
    }
}
