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
 * Url Rewrite Category Refresh Changelog
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Category_Refresh_Changelog
    extends Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Category_Refresh
{
    /**
     * The list of changed entity ids
     *
     * @var null|array
     */
    protected $_changedIds;

    /**
     * Refresh rows by ids based on changelog table
     * - clean category url rewrites
     * - refresh category url rewrites
     * - refresh category to url rewrite relations
     *
     * @return Enterprise_Mview_Model_Action_Interface
     * @throws Enterprise_Mview_Exception
     */
    public function execute()
    {
        $this->_validate();
        $categoriesList = $this->_getChangedIds();
        $lastVersionId = $this->_selectLastVersionId();
        if (empty($categoriesList)) {
            return $this;
        }
        try {
            $this->_connection->beginTransaction();
            foreach ($categoriesList as $categoryId) {
                /** @var Mage_Catalog_Model_Category $category */
                $category = $this->_factory->getModel('catalog/category', array('disable_flat' => true));
                $category->load($categoryId);
                if ($category->getLevel() > 1) {
                    $category->setRequestPath('');
                    $categoryStoreIds = $category->getStoreIds();
                    /** @var Mage_Core_Model_Store $store */
                    foreach ($this->_stores as $store) {
                        //skip store if category not assigned to it
                        //skip category indexation if this category is child of already indexed parent category
                        if (!in_array($store->getId(), $categoryStoreIds)
                            || (!empty($this->_indexedCategoryIds[$store->getId()])
                                && !empty($this->_indexedCategoryIds[$store->getId()][$category->getId()]))
                        ) {
                            continue;
                        }
                        $category->setStoreId($store->getId());
                        //skip root and default categories
                        $parentCategory = $category->getParentCategory();
                        $parentCategory->setStoreId($store->getId());
                        $rewrites = $this->_categoryRelation->loadByCategory($parentCategory);
                        $category->setParentUrl((string) $rewrites->getRequestPath());
                        $this->_indexCategoriesRecursively($category, $store);
                    }
                } elseif ($category->getId() === null) {
                    $this->_categoryId = $categoryId;
                    $this->_cleanDeletedUrlRewrites();
                }
            }
            $this->_metadata->setVersionId($lastVersionId);
            $this->_metadata->save();
            $this->_connection->commit();
            // we should clean cache after commit
            $this->_flushCache();
        } catch (Exception $e) {
            $this->_connection->rollBack();
            throw new Enterprise_Mview_Exception($e->getMessage(), $e->getCode());
        }
        return $this;
    }

    /**
     * Clean old url rewrites records from table (linked from deleted categories)
     *
     * @return $this
     */
    protected function _cleanDeletedUrlRewrites()
    {
        $select = $this->_connection->select()
            ->from(array('ur' => $this->_getTable('enterprise_urlrewrite/url_rewrite')))
            ->join(
                array('rc' => $this->_getTable('enterprise_catalog/category')),
                'rc.url_rewrite_id = ur.url_rewrite_id', array()
            )
            ->where('rc.category_id = ?', $this->_categoryId);
        $this->_connection->query($select->deleteFromSelect('ur'));
        return $this;
    }

    /**
     * Validate metadata before execute
     *
     * @return Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Category_Refresh_Changelog
     * @throws Enterprise_Index_Exception
     */
    protected function _validate()
    {
        if (!$this->_metadata->getId() || !$this->_metadata->getChangelogName()) {
            throw new Enterprise_Index_Exception('Can\'t perform operation, incomplete metadata!');
        }
        return $this;
    }

    /**
     * Returns list of changed Ids
     *
     * @return array
     */
    protected function _getChangedIds()
    {
        if (null === $this->_changedIds) {
            $select = $this->_connection->select()
                ->from(
                    $this->_metadata->getChangelogName(),
                    array($this->_metadata->getKeyColumn())
                )->where('version_id > ?', $this->_metadata->getVersionId());
            $this->_changedIds = $this->_connection->fetchCol($select);
        }
        return $this->_changedIds;
    }

    /**
     * Returns select query for deleting old url rewrites.
     *
     * @return Varien_Db_Select
     * @deprecated since 1.13.0.2
     */
    protected function _getCleanOldUrlRewriteSelect()
    {
        $select = parent::_getCleanOldUrlRewriteSelect();
        $select->where('rc.category_id IN (?)', $this->_getChangedIds());
        return $select;
    }

    /**
     * Prepares url rewrite select query
     *
     * @return Varien_Db_Select
     * @deprecated since 1.13.0.2
     */
    protected function _getUrlRewriteSelectSql()
    {
        $select = parent::_getUrlRewriteSelectSql();
        $select->where('uk.entity_id IN (?)', $this->_getChangedIds());
        return $select;
    }

    /**
     * Prepares refresh relation select query for given category_id
     *
     * @return Varien_Db_Select
     * @deprecated since 1.13.0.2
     */
    protected function _getRefreshRelationSelectSql()
    {
        $select = parent::_getRefreshRelationSelectSql();
        $select->where('uk.entity_id IN (?)', $this->_getChangedIds());
        return $select;
    }
}
