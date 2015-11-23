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
 * Mview drop action class
 *
 * @category    Enterprise
 * @package     Enterprise_Mview
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Mview_Model_Action_Mview_Refresh_Changelog implements Enterprise_Mview_Model_Action_Interface
{
    /**
     * Instance of db adapter
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection  = null;

    /**
     * Instance of mview metadata
     *
     * @var Enterprise_Mview_Model_Metadata
     */
    protected $_metadata    = null;

    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'metadata' Enterprise_Mview_Model_Metadata
     *  - 'connection' Varien_Db_Adapter_Interface
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->_setConnection($args['connection']);
        $this->_setMetadata($args['metadata']);
    }

    /**
     * Sets metadata instance
     *
     * @param Enterprise_Mview_Model_Metadata $metadata
     * @return Enterprise_Mview_Model_Action_Mview_Refresh_Changelog
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
     * @return Enterprise_Mview_Model_Action_Mview_Refresh_Changelog
     */
    protected function _setConnection(Varien_Db_Adapter_Interface $connection)
    {
        $this->_connection  = $connection;
        return $this;
    }

    /**
     * Returns select object with changed Ids
     *
     * @return Varien_Db_Select
     */
    protected function _selectChangedIds()
    {
        return $this->_connection->select()
            ->from(array('changelog' => $this->_metadata->getChangelogName(), array()))
            ->where('version_id >= ?', $this->_metadata->getVersionId())
            ->columns(array($this->_metadata->getKeyColumn()));
    }

    /**
     * Returns last version_id
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
            ->from(array('changelog' => $changelogName), array('version_id'))
            ->order('version_id DESC')
            ->limit(1);
        return (int)$this->_connection->fetchOne($select);
    }

    /**
     * Returns select with changed rows
     *
     * @return Varien_Db_Select
     */
    protected function _selectChangedRows()
    {
        return $this->_connection->select()
            ->from(array('source' => $this->_metadata->getViewName()))
            ->where($this->_metadata->getKeyColumn() . ' IN ( '  . $this->_selectChangedIds() . ' )');
    }

    /**
     * Validate metadata before execute
     *
     * @return Enterprise_Mview_Model_Action_Mview_Refresh_Changelog
     * @throws Enterprise_Mview_Exception
     */
    protected function _validate()
    {
        if (!$this->_metadata->getId() || !$this->_metadata->getChangelogName()) {
            throw new Enterprise_Mview_Exception('Can\'t perform operation, incomplete metadata!');
        }
        return $this;
    }

    /**
     * Refresh rows by ids from changelog
     *
     * @return Enterprise_Mview_Model_Action_Mview_Refresh_Changelog
     * @throws Enterprise_Mview_Exception
     */
    public function execute()
    {
        $this->_validate();
        $this->_connection->beginTransaction();
        try {
            $this->_connection->delete($this->_metadata->getTableName(), array(
                $this->_metadata->getKeyColumn() . ' IN ( '  . $this->_selectChangedIds() . ' )'
            ));
            $this->_connection->query(
                $this->_connection->insertFromSelect($this->_selectChangedRows(), $this->_metadata->getTableName())
            );
            $this->_metadata->setVersionId($this->_selectLastVersionId());
            $this->_metadata->save();
            $this->_connection->commit();
        } catch (Exception $e) {
            $this->_connection->rollBack();
            $this->_metadata->setOutOfDateStatus()
                ->save();
            throw new Enterprise_Mview_Exception($e->getMessage(), $e->getCode());
        }
        return $this;
    }
}
