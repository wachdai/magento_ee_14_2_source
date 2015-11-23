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
 * Product url rewrite helper
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Helper_Product_UrlRewrite implements Mage_Catalog_Helper_Product_Url_Rewrite_Interface
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
     * Prepare and return select
     *
     * @param array $productIds
     * @param int $categoryId
     * @param int $storeId
     * @return Varien_Db_Select
     */
    public function getTableSelect(array $productIds, $categoryId, $storeId)
    {
        $requestPath = $this->_connection->getIfNullSql('url_rewrite.request_path', 'default_ur.request_path');

        return $this->_connection->select()
            ->from(array('e' => $this->_resource->getTableName('catalog/product')), array('product_id' => 'entity_id'))
            ->where('e.entity_id IN(?)', $productIds)
            ->joinLeft(array('url_rewrite_product' => $this->_resource->getTableName('enterprise_catalog/product')),
                'url_rewrite_product.product_id = e.entity_id and url_rewrite_product.store_id = ' . (int)$storeId,
                array(''))
            ->joinLeft(array('url_rewrite' => $this->_resource->getTableName('enterprise_urlrewrite/url_rewrite')),
                'url_rewrite_product.url_rewrite_id = url_rewrite.url_rewrite_id AND url_rewrite.is_system = 1',
                array(''))
            ->joinLeft(array('default_urp' => $this->_resource->getTableName('enterprise_catalog/product')),
                'default_urp.product_id = e.entity_id AND default_urp.store_id = 0',
                array(''))
            ->joinLeft(array('default_ur' => $this->_resource->getTableName('enterprise_urlrewrite/url_rewrite')),
                'default_ur.url_rewrite_id = default_urp.url_rewrite_id',
                array('request_path' => $requestPath));
    }

    /**
     * Prepare url rewrite left join statement for given select instance and store_id parameter.
     *
     * @param Varien_Db_Select $select
     * @param int $storeId
     * @return Enterprise_Catalog_Helper_Product_UrlRewrite
     */
    public function joinTableToSelect(Varien_Db_Select $select, $storeId)
    {
        $requestPath = $this->_connection->getIfNullSql('url_rewrite.request_path', 'default_ur.request_path');

        $select->joinLeft(
            array('url_rewrite_product' => $this->_resource->getTableName('enterprise_catalog/product')),
            'url_rewrite_product.product_id = main_table.entity_id ' .
                'AND url_rewrite_product.store_id = ' . (int)$storeId,
                array('url_rewrite_product.product_id' => 'url_rewrite_product.product_id'))
            ->joinLeft(array('url_rewrite' => $this->_resource->getTableName('enterprise_urlrewrite/url_rewrite')),
                'url_rewrite_product.url_rewrite_id = url_rewrite.url_rewrite_id AND url_rewrite.is_system = 1',
                array(''))
            ->joinLeft(array('default_urp' => $this->_resource->getTableName('enterprise_catalog/product')),
                'default_urp.product_id = main_table.entity_id AND default_urp.store_id = 0',
                array(''))
            ->joinLeft(array('default_ur' => $this->_resource->getTableName('enterprise_urlrewrite/url_rewrite')),
                'default_ur.url_rewrite_id = default_urp.url_rewrite_id',
                array('request_path' => $requestPath)
            );
        return $this;
    }
}
