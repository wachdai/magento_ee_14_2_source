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
 * Url Rewrite Redirect Refresh Action. Cleans orphan index records.
 *
 * @category    Enterprise
 * @package     Enterprise_UrlRewrite
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_UrlRewrite_Model_Index_Action_Url_Rewrite_Redirect_Refresh_Orphan
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
     * Removes redirect rows which have no corresponding records in redirect table from index.
     *
     * @return Enterprise_UrlRewrite_Model_Index_Action_Url_Rewrite_Redirect_Refresh_Orphan
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        try {
            $select = $this->_connection->select()
                ->from(array('ur' => $this->_getTable('enterprise_urlrewrite/url_rewrite')), '*')
                ->joinInner(array('rr' => $this->_getTable('enterprise_urlrewrite/redirect_rewrite')),
                    'ur.url_rewrite_id = rr.url_rewrite_id')
                ->joinLeft(array('redirect' => $this->_getTable('enterprise_urlrewrite/redirect')),
                    'redirect.redirect_id = rr.redirect_id')
                ->where('ur.entity_type = ?', Enterprise_UrlRewrite_Model_Redirect::URL_REWRITE_ENTITY_TYPE)
                ->where('redirect.redirect_id IS NULL');
            $this->_connection->query($this->_connection->deleteFromSelect($select, 'ur'));
        } catch (Exception $e) {
            $this->_metadata->setInvalidStatus()->save();
            throw new Enterprise_Index_Model_Action_Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $this;
    }
}
