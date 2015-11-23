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
 * Enterprise_Mview_Model_Client
 *
 * @category    Enterprise
 * @package     Enterprise_Mview
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Mview_Model_Client
{
    /**
     * Metadata object
     *
     * @var Enterprise_Mview_Model_Metadata
     */
    protected $_metadata;

    /**
     * Models factory
     *
     * @var Enterprise_Mview_Model_Factory
     */
    protected $_factory;

    /**
     * Default adapter instance
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_defaultConnection;

    /**
     * Constructor
     *
     * @param array $argument
     */
    public function __construct(array $argument = array())
    {
        if (!empty($argument['factory']) && is_object($argument['factory'])) {
            $this->_factory = $argument['factory'];
        } else {
            $this->_factory = Mage::getModel('enterprise_mview/factory');
        }
    }

    /**
     * Init materialized view metadata by name
     *
     * @param string $name
     *
     * @return Enterprise_Mview_Model_Client
     */
    public function init($name)
    {
        $tableName = $this->_factory->getSingleton('core/resource')->getTableName($name);

        return $this->initByTableName($tableName);
    }

    /**
     * Init materialized view metadata by table name
     *
     * @param string $tableName
     *
     * @return Enterprise_Mview_Model_Client
     */
    public function initByTableName($tableName)
    {
        $this->_metadata = $this->_factory->getModel('enterprise_mview/metadata')
            ->load($tableName, 'table_name');
        if (!$this->_metadata->getId()) {
            $this->_metadata->setTableName($tableName);
        }
        return $this;
    }

    /**
     * Execute action
     *
     * @param string $classPath
     * @param array $args
     * @return Enterprise_Mview_Model_Client
     * @throws Enterprise_Mview_Exception
     */
    public function execute($classPath, array $args = array())
    {
        if (!is_object($this->_metadata)) {
            throw new Enterprise_Mview_Exception('Metadata should be initialized before action is executed');
        }

        $args = $this->_prepareActionParameters($args);

        $action = $this->_factory->getModel($classPath, $args);
        if (!$action instanceof Enterprise_Mview_Model_Action_Interface) {
            throw new Enterprise_Mview_Exception('Action "' . get_class($action) . '" must be an instance of ' .
                'Enterprise_Mview_Model_Action_Interface');
        }
        $action->execute();

        return $this;
    }

    /**
     * Returns metadata object
     *
     * @return Enterprise_Mview_Model_Metadata|null
     */
    public function getMetadata()
    {
        return $this->_metadata;
    }

    /**
     * Prepare and set parameters for action initialization
     *
     * @param array $args
     * @return array
     */
    protected function _prepareActionParameters(array $args)
    {
        $args['metadata'] = $this->_metadata;
        if (!array_key_exists('connection', $args)) {
            $args['connection'] = $this->_getDefaultConnection();
        }
        if (!array_key_exists('factory', $args)) {
            $args['factory'] = $this->_factory;
        }
        return $args;
    }

    /**
     * Retrieve default connection
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getDefaultConnection()
    {
        if (null === $this->_defaultConnection) {
            $this->_defaultConnection = $this->_factory->getSingleton('core/resource')
                ->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        }
        return $this->_defaultConnection;
    }
}
