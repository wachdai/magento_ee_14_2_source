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
 * Enterprise index changelog model
 *
 * @category    Enterprise
 * @package     Enterprise_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Index_Model_Changelog
{
    /**
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
     * Construct
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->_setConnection($args['connection']);
        $this->_setMetadata($args['metadata']);
    }

    /**
     * Set connection
     *
     * @param Varien_Db_Adapter_Interface $connection
     */
    protected function _setConnection(Varien_Db_Adapter_Interface $connection)
    {
        $this->_connection = $connection;
    }

    /**
     * Set metadata
     *
     * @param Enterprise_Mview_Model_Metadata $metadata
     */
    protected function _setMetadata(Enterprise_Mview_Model_Metadata $metadata)
    {
        $this->_metadata = $metadata;
    }

    /**
     * Load changelog by metadata
     *
     * @param null|int $currentVersion
     * @return array
     */
    public function loadByMetadata($currentVersion = null)
    {
        $select = $this->_connection->select()
            ->from(array('changelog' => $this->_metadata->getChangelogName()), array())
            ->where('version_id >= ?', $this->_metadata->getVersionId())
            ->columns(array($this->_metadata->getKeyColumn()));

        if ($currentVersion) {
            $select->where('version_id < ?', $currentVersion);
        }

        return $this->_connection->fetchCol($select);
    }

    /**
     * Return last version id
     *
     * @return int
     */
    public function getLastVersionId()
    {
        $select = $this->_connection->select()
            ->from($this->_metadata->getChangelogName(), array('version_id'))
            ->order('version_id DESC')
            ->limit(1);

        return (int)$this->_connection->fetchOne($select);
    }
}
