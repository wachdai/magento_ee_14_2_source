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
 * Catalog category resource model
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Resource_Category extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
    * Initialize resource
    */
    protected function _construct()
    {
        $this->_init('enterprise_catalog/category', 'id');
    }

    /**
     * Load url rewrite based on specified category
     *
     * @param Mage_Core_Model_Abstract $object
     * @param Mage_Catalog_Model_Category $category
     * @return Enterprise_Catalog_Model_Resource_Category
     */
    public function loadByCategory(Mage_Core_Model_Abstract $object, Mage_Catalog_Model_Category $category)
    {
        $idField = $this->_getReadAdapter()
            ->getIfNullSql('url_rewrite_cat.id', 'default_urc.id');
        $requestPath = $this->_getReadAdapter()
            ->getIfNullSql('url_rewrite.request_path', 'default_ur.request_path');

        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table' => $this->getTable('catalog/category')),
                array($this->getIdFieldName() => $idField))
            ->where('main_table.entity_id = ?', (int)$category->getId())
            ->joinLeft(array('url_rewrite_cat' => $this->getTable('enterprise_catalog/category')),
                'url_rewrite_cat.category_id = main_table.entity_id AND url_rewrite_cat.store_id = ' .
                    (int)$category->getStoreId(),
                array(''))
            ->joinLeft(array('url_rewrite' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                'url_rewrite.url_rewrite_id = url_rewrite_cat.url_rewrite_id',
                array(''))
            ->joinLeft(array('default_urc' => $this->getTable('enterprise_catalog/category')),
                'default_urc.category_id = main_table.entity_id AND default_urc.store_id = 0',
                array(''))
            ->joinLeft(array('default_ur' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                'default_ur.url_rewrite_id = default_urc.url_rewrite_id',
                array('request_path' => $requestPath));

        $result = $this->_getReadAdapter()->fetchRow($select);

        if ($result) {
            $object->setData($result);
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }

    /**
     * Get rewrite by category id
     *
     * @param int $categoryId
     * @param int $storeId
     * @return array
     */
    public function getRewriteByCategoryId($categoryId, $storeId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('rw' => $this->getTable('enterprise_urlrewrite/url_rewrite')))
            ->joinInner(
                array('crw' => $this->getTable('enterprise_catalog/category')),
                'crw.url_rewrite_id = rw.url_rewrite_id',
                array()
            )
            ->where('crw.category_id = ?', $categoryId)
            ->where('rw.store_id = ?', $storeId);
        return $this->_getReadAdapter()->fetchRow($select);
    }


    /**
     * Get category id by rewrite id
     *
     * @param int $rewriteId
     * @return int
     */
    public function getCategoryIdByRewriteId($rewriteId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('rw' => $this->getTable('enterprise_urlrewrite/url_rewrite')), array())
            ->joinInner(
                array('crw' => $this->getTable('enterprise_catalog/category')),
                'crw.url_rewrite_id = rw.url_rewrite_id',
                array('crw.category_id')
            )
            ->where('rw.url_rewrite_id = ?', $rewriteId);
        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Get category id by request path
     *
     * @param $categoryPath
     * @param int $storeId
     * @return array
     */
    public function getCategoryIdByRequestPath($categoryPath, $storeId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('rw' => $this->getTable('enterprise_urlrewrite/url_rewrite')), array())
            ->joinInner(
                array('crw' => $this->getTable('enterprise_catalog/category')),
                'crw.url_rewrite_id = rw.url_rewrite_id',
                array('crw.category_id')
            )
            ->where('rw.request_path = ?', $categoryPath)
            ->where('rw.store_id = ?', $storeId);
        return $this->_getReadAdapter()->fetchOne($select);
    }
}
