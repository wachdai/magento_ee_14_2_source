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
 * @package     Enterprise_Catalog
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Category url rewrite helper
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Helper_Category_UrlRewrite implements Mage_Catalog_Helper_Category_Url_Rewrite_Interface
{
    /**
     * Adapter instance
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Resource instance
     *
     * @var Mage_Core_Model_Resource
     */
    protected $_resource;

    /**
     * Initialize resource and connection instances
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_connection = !empty($args['connection']) ? $args['connection'] : $this->_resource
            ->getConnection(Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE);
    }

    /**
     * Join url rewrite table to eav collection
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @param int $storeId
     * @return Enterprise_Catalog_Helper_Category_UrlRewrite
     */
    public function joinTableToEavCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection, $storeId)
    {
        $requestPath = $this->_connection->getIfNullSql('url_rewrite.request_path', 'default_ur.request_path');
        $collection->getSelect()->joinLeft(
                array('url_rewrite_category' => $collection->getTable('enterprise_catalog/category')),
                'url_rewrite_category.category_id = e.entity_id'.
                    ' AND ' . $collection->getConnection()->quoteInto('url_rewrite_category.store_id = ?', $storeId),
                array(''))
            ->joinLeft(
                array('url_rewrite' => $collection->getTable('enterprise_urlrewrite/url_rewrite')),
                'url_rewrite_category.url_rewrite_id = url_rewrite.url_rewrite_id AND url_rewrite.is_system = 1',
                array(''))
            ->joinLeft(array('default_urc' => $collection->getTable('enterprise_catalog/category')),
                'default_urc.category_id = e.entity_id AND default_urc.store_id = 0',
                array(''))
            ->joinLeft(array('default_ur' => $collection->getTable('enterprise_urlrewrite/url_rewrite')),
                'default_ur.url_rewrite_id = default_urc.url_rewrite_id AND default_ur.is_system = 1',
                array('request_path' => $requestPath));

        return $this;
    }

    /**
     * Join url rewrite table to flat collection
     *
     * @param Mage_Catalog_Model_Resource_Category_Flat_Collection $collection
     * @param int $storeId
     * @return Enterprise_Catalog_Helper_Category_UrlRewrite
     */
    public function joinTableToCollection(Mage_Catalog_Model_Resource_Category_Flat_Collection $collection, $storeId)
    {
        $requestPath = $this->_connection->getIfNullSql('url_rewrite.request_path', 'default_ur.request_path');
        $collection->getSelect()->joinLeft(
            array('url_rewrite_category' => $collection->getTable('enterprise_catalog/category')),
            'url_rewrite_category.category_id = main_table.entity_id'.
                ' AND ' . $collection->getConnection()->quoteInto('url_rewrite_category.store_id = ?', $storeId),
            array(''))
            ->joinLeft(
                array('url_rewrite' => $collection->getTable('enterprise_urlrewrite/url_rewrite')),
                'url_rewrite_category.url_rewrite_id = url_rewrite.url_rewrite_id AND url_rewrite.is_system = 1',
                array(''))
            ->joinLeft(array('default_urc' => $collection->getTable('enterprise_catalog/category')),
                'default_urc.category_id = main_table.entity_id AND default_urc.store_id = 0',
                array(''))
            ->joinLeft(array('default_ur' => $collection->getTable('enterprise_urlrewrite/url_rewrite')),
                'default_ur.url_rewrite_id = default_urc.url_rewrite_id AND default_ur.is_system = 1',
                array('request_path' => $requestPath));
        return $this;
    }

    /**
     * Join url rewrite to select
     *
     * @param Varien_Db_Select $select
     * @param int $storeId
     * @return Enterprise_Catalog_Helper_Category_UrlRewrite
     */
    public function joinTableToSelect(Varien_Db_Select $select, $storeId)
    {
        $requestPath = $this->_connection->getIfNullSql('url_rewrite.request_path', 'default_ur.request_path');
        $select->joinLeft(
            array('url_rewrite_category' => $this->_resource->getTableName('enterprise_catalog/category')),
            'url_rewrite_category.category_id = main_table.entity_id'.
                ' AND ' . $this->_connection->quoteInto('url_rewrite_category.store_id = ?', (int)$storeId),
                array('')
            )->joinLeft(
                array('url_rewrite' => $this->_resource->getTableName('enterprise_urlrewrite/url_rewrite')),
                'url_rewrite_category.url_rewrite_id = url_rewrite.url_rewrite_id AND url_rewrite.is_system = 1',
                array('')
            )->joinLeft(array('default_urc' => $this->_resource->getTableName('enterprise_catalog/category')),
                'default_urc.category_id = url_rewrite_category.category_id AND default_urc.store_id = 0',
                array('')
            )->joinLeft(array('default_ur' => $this->_resource->getTableName('enterprise_urlrewrite/url_rewrite')),
                'default_ur.url_rewrite_id = default_urc.url_rewrite_id  AND default_ur.is_system = 1',
                array('request_path' => $requestPath));
        return $this;
    }
}
