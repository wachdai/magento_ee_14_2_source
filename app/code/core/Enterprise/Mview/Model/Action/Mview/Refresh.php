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
 * Mview refresh action class
 *
 * @category    Enterprise
 * @package     Enterprise_Mview
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Mview_Model_Action_Mview_Refresh implements Enterprise_Mview_Model_Action_Interface
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
     * @return Enterprise_Mview_Model_Action_Mview_Refresh
     */
    protected function _setConnection(Varien_Db_Adapter_Interface $connection)
    {
        $this->_connection = $connection;
    }

    /**
     * Refresh materialized view
     *
     * @return Enterprise_Mview_Model_Action_Mview_Refresh
     * @throws Exception
     */
    public function execute()
    {
        try {
            $insert = $this->_connection->insertFromSelect(
                $this->_connection->select()->from($this->_metadata->getViewName()),
                $this->_metadata->getTableName()
            );
            $this->_connection->delete($this->_metadata->getTableName());
            $this->_connection->query($insert);

            $this->_metadata->setValidStatus()
                ->setVersionId($this->_selectLastVersionId())
                ->save();
        } catch (Exception $e) {
            $this->_metadata->setInvalidStatus()->save();
            throw $e;
        }
        return $this;
    }

    /**
     * Returns last version_id from changelog table
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
}
