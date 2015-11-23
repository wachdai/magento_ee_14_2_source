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
 * Abstract indexer action
 *
 * @category    Enterprise
 * @package     Enterprise_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_Index_Model_Action_Abstract implements Enterprise_Mview_Model_Action_Interface
{

    /**
     * Version ID action started with
     *
     * @var int
     */
    protected $_currentVersionId;

    /**
     * Instance of db adapter
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Instance of mview metadata
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
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'metadata' Enterprise_Mview_Model_Metadata
     *  - 'connection' Varien_Db_Adapter_Interface
     *  - 'factory' Enterprise_Mview_Model_Factory
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->_setConnection($args['connection']);
        $this->_setMetadata($args['metadata']);
        $this->_setFactory($args['factory']);
    }

    /**
     * Sets metadata instance
     *
     * @param Enterprise_Mview_Model_Metadata $metadata
     * @return Enterprise_Index_Model_Action_Abstract
     */
    protected function _setMetadata(Enterprise_Mview_Model_Metadata $metadata)
    {
        $this->_metadata  = $metadata;
        return $this;
    }
    /**
     * Set connection instance
     *
     * @param Varien_Db_Adapter_Interface $connection
     * @return Enterprise_Index_Model_Action_Abstract
     */
    protected function _setConnection(Varien_Db_Adapter_Interface $connection)
    {
        $this->_connection  = $connection;
        return $this;
    }

    /**
     * Sets mview factory instance
     *
     * @param Enterprise_Mview_Model_Factory $factory
     * @return Enterprise_Index_Model_Action_Abstract
     */
    protected function _setFactory(Enterprise_Mview_Model_Factory $factory)
    {
        $this->_factory = $factory;
        return $this;
    }

    /**
     * Validate metadata before execute
     *
     * @return Enterprise_Index_Model_Action_Abstract
     * @throws Enterprise_Index_Model_Action_Exception
     */
    protected function _validate()
    {
        if (!$this->_metadata->getId() || !$this->_metadata->getChangelogName()) {
            throw new Enterprise_Index_Model_Action_Exception("Can't perform operation, incomplete metadata!");
        }
        return $this;
    }

    /**
     * Returns select object with changed Ids
     *
     * @param int|null $maxVersion
     * @return Varien_Db_Select
     */
    protected function _getChangedIdsSelect($maxVersion = null)
    {
        if (empty($maxVersion)) {
            $maxVersion = $this->_getCurrentVersionId();
        }
        return $this->_connection->select()
            ->from($this->_metadata->getChangelogName(), array())
            ->where('version_id > ?', $this->_metadata->getVersionId())
            ->where('version_id <= ?', $maxVersion)
            ->columns(array($this->_metadata->getKeyColumn()))
            ->distinct();
    }

    /**
     * Get array of changed IDs
     *
     * @param int|null $maxVersion
     * @return array
     */
    protected function _selectChangedIds($maxVersion = null)
    {
        return $this->_getChangedIdsSelect($maxVersion)->query()->fetchAll(Zend_Db::FETCH_COLUMN);
    }

    /**
     * Set changelog valid
     *
     * @return Enterprise_Index_Model_Action_Abstract
     */
    protected function _setChangelogValid()
    {
        $this->_metadata->load($this->_metadata->getId());
        if ($this->_metadata->getStatus() == Enterprise_Mview_Model_Metadata::STATUS_IN_PROGRESS) {
            $this->_metadata->setValidStatus();
        }
        $this->_metadata->setVersionId($this->_getCurrentVersionId())->save();
        return $this;
    }

    /**
     * Get current DB date
     *
     * @return mixed
     */
    protected function _getCurrentVersionId()
    {
        if (empty($this->_currentVersionId)) {
            // zend select query permanently requires FROM statement, so executing raw query
            $this->_currentVersionId = $this->_connection->query(
                $this->_connection->select()
                ->from($this->_metadata->getChangelogName(), array())
                ->columns(array('max' => 'MAX(version_id)'))
            )->fetchColumn();
        }
        return $this->_currentVersionId;
    }
}
