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
 * @package     Enterprise_PageCache
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Crawler resource model
 *
 * @category    Enterprise
 * @package     Enterprise_PageCache
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_PageCache_Model_Resource_Crawler extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->_init('enterprise_urlrewrite/url_rewrite', 'url_rewrite_id');
    }

    /**
     * Initialize application, adapter factory
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_app = !empty($args['app']) ? $args['app'] : Mage::app();
        parent::__construct();
    }

    /**
     * Get statement for iterating store urls
     *
     * @deprecated after 1.11.0.0 - use getUrlsPaths() instead
     *
     * @param int $storeId
     * @return Zend_Db_Statement
     */
    public function getUrlStmt($storeId)
    {
        $table = $this->getTable('core/url_rewrite');
        $select = $this->_getReadAdapter()->select()
            ->from($table, array('store_id', 'request_path'))
            ->where('store_id = :store_id')
            ->where('is_system=1');
        return $this->_getReadAdapter()->query($select, array(':store_id' => $storeId));
    }

    /**
     * Retrieve URLs paths that must be visited by crawler
     *
     * @param  $storeId
     * @return array
     * @deprecated after 1.12.0.2 - use getUrlsPaths() instead
     */
    public function getUrlsPaths($storeId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getTable('core/url_rewrite'), array('request_path'))
            ->where('store_id=?', $storeId)
            ->where('is_system=1');
        return $adapter->fetchCol($select);
    }

    /**
     * Get store urls
     *
     * @param int $storeId
     * @param int $batchSize
     * @param int $offset
     * @return array
     */
    public function getRequestPaths($storeId, $batchSize, $offset)
    {
        $store = $this->_app->getStore($storeId);

        $rootCategoryId = $store->getRootCategoryId();

        $selectProduct = $this->_getReadAdapter()->select()
            ->from(array('url_product_default' => $this->getTable('enterprise_catalog/product')),
                array(''))
            ->joinInner(array('url_rewrite' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                'url_rewrite.url_rewrite_id = url_product_default.url_rewrite_id',
                array('request_path', 'entity_type')
            )
            ->joinInner(array('cp' => $this->getTable('catalog/category_product_index')),
                'url_product_default.product_id = cp.product_id',
                array('category_id')
            )
            ->where('url_rewrite.entity_type = ?', Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE)
            ->where('cp.store_id = ?', (int) $storeId)
            ->where('cp.category_id != ?', (int) $rootCategoryId)
            ->limit($batchSize, $offset);

        $selectCategory = $this->_getReadAdapter()->select()
            ->from(array('url_rewrite' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                array(
                    'request_path',
                    'entity_type',
                    'category_id' => new Zend_Db_Expr('NULL'),
                )
            )
            ->where('url_rewrite.store_id = ?', $storeId)
            ->where('url_rewrite.entity_type = ?', Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE)
            ->limit($batchSize, $offset);

        $selectPaths = $this->_getReadAdapter()->select()
            ->union(array('(' . $selectProduct . ')', '(' . $selectCategory . ')'));

        return $this->_getReadAdapter()->fetchAll($selectPaths);
    }
}
