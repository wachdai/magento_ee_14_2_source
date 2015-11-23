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
 * Catalog product resource model
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Resource_Product extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
    * Initialize resource
    */
    protected function _construct()
    {
        $this->_init('enterprise_catalog/product', 'id');
    }

    /**
     * Load Url rewrite by specified product
     *
     * @param Mage_Core_Model_Abstract $object
     * @param Mage_Catalog_Model_Product $product
     * @return Enterprise_Catalog_Model_Resource_Product
     */
    public function loadByProduct(Mage_Core_Model_Abstract $object, Mage_Catalog_Model_Product $product)
    {
        $idField = $this->_getReadAdapter()
            ->getIfNullSql('url_rewrite_cat.id', 'default_urc.id');
        $requestPath = $this->_getReadAdapter()
            ->getIfNullSql('url_rewrite.request_path', 'default_ur.request_path');

        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table' => $this->getTable('catalog/product')),
                array($this->getIdFieldName() => $idField))
            ->where('main_table.entity_id = ?', (int)$product->getId())
            ->joinLeft(array('url_rewrite_cat' => $this->getTable('enterprise_catalog/product')),
                'url_rewrite_cat.product_id = main_table.entity_id AND url_rewrite_cat.store_id = ' .
                    (int)$product->getStoreId(),
                array(''))
            ->joinLeft(array('url_rewrite' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                'url_rewrite.url_rewrite_id = url_rewrite_cat.url_rewrite_id',
                array(''))
            ->joinLeft(array('default_urc' => $this->getTable('enterprise_catalog/product')),
                'default_urc.product_id = main_table.entity_id AND default_urc.store_id = 0',
                array(''))
            ->joinLeft(array('default_ur' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                'default_ur.url_rewrite_id = default_urc.url_rewrite_id',
                array('request_path' => $requestPath));
        $result = $this->_getReadAdapter()->fetchRow($select);

        if (isset($result['id']) && !empty($result['id'])) {
            $object->setData($result);
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }

    /**
     * Get product id bu rewrite
     *
     * @param int $rewriteId
     * @param int $storeId
     * @return int
     */
    public function getProductIdByRewrite($rewriteId, $storeId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('prw' => $this->getTable('enterprise_catalog/product')),
                array('prw.product_id')
            )
            ->where('prw.url_rewrite_id = ?', $rewriteId)
            ->where('prw.store_id = 0 OR prw.store_id = ?', $storeId);

        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Get count of categories assigned to product by rewrite path
     *
     * @param int $productId
     * @param string $path
     * @param int $storeId
     * @return int
     */
    public function getCountProductCategoriesByRewrite($productId, $path, $storeId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('rw' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                array('COUNT(*)')
            )
            ->joinInner(
                array('crw' => $this->getTable('enterprise_catalog/category')),
                'crw.url_rewrite_id = rw.url_rewrite_id',
                array()
            )
            ->joinInner(
                array('cp' => $this->getTable('catalog/category_product_index')),
                'cp.category_id = crw.category_id',
                array()
            )
            ->where('rw.request_path = ?', $path)
            ->where('rw.store_id = ?', $storeId)
            ->where('cp.product_id = ?', $productId);
        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Get rewrite by product id
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getRewriteByProductId($productId, $storeId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('rw' => $this->getTable('enterprise_urlrewrite/url_rewrite')))
            ->joinInner(
                array('prw' => $this->getTable('enterprise_catalog/product')),
                'prw.url_rewrite_id = rw.url_rewrite_id',
                array()
            )
            ->where('prw.product_id = ?', $productId)
            ->where('prw.store_id = 0 OR prw.store_id = ?', $storeId)
            ->order('prw.store_id DESC')
            ->limit(1);
        return $this->_getReadAdapter()->fetchRow($select);
    }

    /**
     * Get rewrite by store and product
     *
     * @param int $storeId
     * @param int $productId
     * @return array
     */
    public function getRewriteByStoreId($storeId, $productId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('rw' => $this->getTable('enterprise_urlrewrite/url_rewrite')))
            ->joinInner(
                array('prw' => $this->getTable('enterprise_catalog/product')),
                'prw.url_rewrite_id = rw.url_rewrite_id',
                array()
            )
            ->where('prw.product_id = ?', $productId)
            ->where('prw.store_id = ?', $storeId);
        return $this->_getReadAdapter()->fetchRow($select);
    }
}
