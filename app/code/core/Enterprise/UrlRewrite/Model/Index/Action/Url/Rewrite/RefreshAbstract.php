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
 * @package     Enterprise_UrlRewrite
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Abstract refresh class for URL rewrite action classes
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_UrlRewrite_Model_Index_Action_Url_Rewrite_RefreshAbstract
    implements Enterprise_Mview_Model_Action_Interface
{
    /**
     * Connection instance
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Mview metadata instance
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
     * List of column names from relation table which connects entity with url_rewrite record.
     *
     * @var array
     */
    protected $_relationColumns;

    /**
     * Relation table name.
     *
     * @var string
     */
    protected $_relationTableName;

    /**
     * Unique identifier
     *
     * @var string
     */
    protected $_uniqueIdentifier;

    /**
     * Version ID action started with
     *
     * @var int
     */
    protected $_currentVersionId;

    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'metadata' Enterprise_Mview_Model_Metadata
     *  - 'connection' Varien_Db_Adapter_Interface
     *  - 'factory' Mage_Core_Model_Factory
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->_setConnection($args['connection']);
        $this->_setMetadata($args['metadata']);
        $this->_setFactory($args['factory']);

        $this->_uniqueIdentifier = $this->_factory->getHelper('core')->uniqHash();
    }

    /**
     * Sets mview metadata instance
     *
     * @param Enterprise_Mview_Model_Metadata $metadata
     */
    protected function _setMetadata(Enterprise_Mview_Model_Metadata $metadata)
    {
        $this->_metadata = $metadata;
    }

    /**
     * Set connection instance
     *
     * @param Varien_Db_Adapter_Interface $connection
     */
    protected function _setConnection(Varien_Db_Adapter_Interface $connection)
    {
        $this->_connection = $connection;
    }

    /**
     * Set factory instance
     *
     * @param Enterprise_Mview_Model_Factory $factory
     */
    protected function _setFactory(Enterprise_Mview_Model_Factory $factory)
    {
        $this->_factory = $factory;
    }

    /**
     * Execute refresh operation.
     *  - clean redirect url rewrites
     *  - refresh redirect url rewrites
     *  - refresh redirect to url rewrite relations
     *
     * @return Enterprise_Mview_Model_Action_Interface
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        try {
            $this->_metadata->setInProgressStatus()->save();

            $this->_cleanOldUrlRewrite();
            $this->_refreshUrlRewrite();
            $this->_refreshRelation();

            $this->_setChangelogValid();
        } catch (Exception $e) {
            $this->_metadata->setInvalidStatus()->save();
            throw new Enterprise_Index_Model_Action_Exception($e->getMessage(), $e->getCode());
        }
        return $this;
    }

    /**
     * Clean old url rewrites records from table
     *
     * @return Enterprise_UrlRewrite_Model_Index_Action_Url_Rewrite_RefreshAbstract
     */
    protected function _cleanOldUrlRewrite()
    {
        $select = $this->_getCleanOldUrlRewriteSelect();
        $this->_connection->query($select->deleteFromSelect('ur'));
        return $this;
    }

    /**
     * Refresh url rewrites
     *
     * @return Enterprise_UrlRewrite_Model_Index_Action_Url_Rewrite_RefreshAbstract
     */
    protected function _refreshUrlRewrite()
    {
        $insert = $this->_connection->insertFromSelect($this->_getUrlRewriteSelectSql(),
            $this->_getTable('enterprise_urlrewrite/url_rewrite'),
            array(
                'request_path',
                'target_path',
                'guid',
                'is_system',
                'identifier',
                'value_id',
                'store_id',
                'entity_type'
            )
        );

        $insert .= sprintf(' ON DUPLICATE KEY UPDATE %1$s = %1$s + 1',
            $this->_getTable('enterprise_urlrewrite/url_rewrite') . '.inc');

        $this->_connection->query($insert);
        return $this;
    }

    /**
     * Refresh redirect to url rewrite relations
     *
     * @return Enterprise_UrlRewrite_Model_Index_Action_Url_Rewrite_RefreshAbstract
     */
    protected function _refreshRelation()
    {
        $insert = $this->_connection->insertFromSelect(
            $this->_getRefreshRelationSelectSql(),
            $this->_relationTableName,
            $this->_relationColumns
        );

        $insert .=  sprintf(' ON DUPLICATE KEY UPDATE %1$s = VALUES(%1$s)',
            $this->_connection->quoteIdentifier('url_rewrite_id')
        );

        $this->_connection->query($insert);
        return $this;
    }

    /**
     * Returns select with last version_id
     *
     * @return int
     */
    protected function _selectLastVersionId()
    {
        $changelogName = $this->_metadata->getChangelogName();
        if (empty($changelogName)) {
            return 0;
        }
        $select = $this->_connection->select()
            ->from($changelogName, array('version_id'))
            ->order('version_id DESC')
            ->limit(1);
        return (int)$this->_connection->fetchOne($select);
    }

    /**
     * Returns table name
     *
     * @param string|array $name
     * @return string
     */
    protected function _getTable($name)
    {
        return $this->_metadata->getResource()->getTable($name);
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
     * Get current DB version
     *
     * @return int
     */
    protected function _getCurrentVersionId()
    {
        if (empty($this->_currentVersionId)) {
            // zend select query permanently requires FROM statement, so executing raw query
            $this->_currentVersionId = $this->_connection->query($this->_connection->select()
                ->from($this->_metadata->getChangelogName(), array())
                ->columns(array('max' => 'MAX(version_id)')))->fetchColumn();
        }
        return $this->_currentVersionId;
    }


    /**
     * Returns select query for deleting old url rewrites
     *
     * @return Varien_Db_Select
     */
    abstract protected function _getCleanOldUrlRewriteSelect();

    /**
     * Prepares url rewrite select query
     *
     * @return Varien_Db_Select
     */
    abstract protected function _getUrlRewriteSelectSql();

    /**
     * Prepares refresh relation select query
     *
     * @return Varien_Db_Select
     */
    abstract protected function _getRefreshRelationSelectSql();
}
