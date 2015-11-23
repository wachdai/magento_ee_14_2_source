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
 * Url Rewrite Category Refresh Action
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Category_Refresh
    extends Enterprise_UrlRewrite_Model_Index_Action_Url_Rewrite_RefreshAbstract
    implements Enterprise_Mview_Model_Action_Interface
{

    /**
     * Base target path.
     * @deprecated since 1.13.0.2
     */
    const BASE_TARGET_PATH = 'catalog/category/view/id/';

    /**
     * List of already indexed category ids
     *
     * @var array
     */
    protected $_indexedCategoryIds = array();

    /**
     * Category id
     *
     * @var int
     */
    protected $_categoryId;

    /**
     * Current working store
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Eav helper for generate database specific sql
     *
     * @var Mage_Core_Model_Resource_Helper_Abstract
     */
    protected $_eavHelper;

    /**
     * Url resource model
     *
     * @var Mage_Catalog_Model_Resource_Url
     */
    protected $_urlResource;

    /**
     * Url model
     *
     * @var Mage_Catalog_Model_Url
     */
    protected $_urlModel;

    /**
     * Category entity
     *
     * @var Mage_Catalog_Model_Category
     */
    protected $_category;

    /**
     * Array of stores (Mage_Core_Model_Store) available in system
     *
     * @var array
     */
    protected $_stores;

    /**
     * Category relation
     *
     * @var Enterprise_Catalog_Model_Category
     */
    protected $_categoryRelation;

    /**
     * List of cateries with changed url keys
     *
     * @var array
     */
    protected $_changedCategoryIds = array();

    /**
     * Initialize unique value, relation columns and relation
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);

        $this->_urlResource = $this->_factory->getResourceModel('catalog/url');
        $this->_relationColumns = array('category_id', 'store_id', 'url_rewrite_id');
        $this->_relationTableName = $this->_getTable('enterprise_catalog/category');
        $this->_eavHelper = !empty($args['eavHelper']) ? $args['eavHelper'] : Mage::getResourceHelper('eav');
        $this->_urlModel = $this->_factory->getModel('catalog/url');
        $this->_category = $this->_factory->getModel('catalog/category', array('disable_flat' => true));
        $this->_stores = !empty($args['storeList']) ? $args['storeList'] : Mage::app()->getStores();
        $this->_categoryRelation = $this->_factory->getModel('enterprise_catalog/category');
    }

    /**
     * Execute refresh operation.
     *  - clean redirect url rewrites
     *  - refresh redirect url rewrites
     *  - refresh redirect to url rewrite relations
     *
     * @return Enterprise_Mview_Model_Action_Interface
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        try {
            $this->_metadata->setInProgressStatus()->save();
            $this->_connection->beginTransaction();
            $this->_cleanDeletedCategories();
            /** @var Mage_Core_Model_Store $store */
            foreach ($this->_stores as $store) {
                $rootCategoryId = $store->getGroup()->getRootCategoryId();
                $this->_category->load($rootCategoryId);
                $this->_category->setParentUrl('');
                $this->_indexCategoriesRecursively($this->_category, $store);
            }

            $this->_setChangelogValid();
            $this->_connection->commit();
            // we should clean cache after commit
            $this->_flushCache();
        } catch (Exception $e) {
            $this->_connection->rollBack();
            $this->_metadata->setInvalidStatus()->save();
            throw new Enterprise_Index_Model_Action_Exception($e->getMessage(), $e->getCode());
        }
        return $this;
    }

    /**
     * Set store specific data to category
     *
     * @param Mage_Catalog_Model_Category $category
     * @param Mage_Core_Model_Store $store
     * @return Mage_Catalog_Model_Category
     */
    protected function _setStoreSpecificData(Mage_Catalog_Model_Category $category, Mage_Core_Model_Store $store)
    {
        $category->setStoreId($store->getId());
        $storeCategoryData = $this->_urlResource->getCategory($category->getId(), $store->getId());
        if ($storeCategoryData) {
            foreach ($storeCategoryData->toArray() as $key => $data) {
                $category->setData($key, $data);
            }
        }
        $rewrites = $this->_categoryRelation->loadByCategory($category);
        $category->setRequestPath($rewrites->getRequestPath());
        return $category;
    }

    /**
     * Recursively index categories tree
     *
     * @param Mage_Catalog_Model_Category $category
     * @param Mage_Core_Model_Store $store
     * @return Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Category_Refresh
     */
    protected function _indexCategoriesRecursively(Mage_Catalog_Model_Category $category, Mage_Core_Model_Store $store)
    {
        $category = $this->_setStoreSpecificData($category, $store);
        //skip root and default categories
        if ($category->getLevel() > 1) {
            $category = $this->_formatUrlKey($category);
            if ($category->getUrlKey()) {
                $category = $this->_reindexCategoryUrlKey($category, $store);
            }
        }

        if ($category->getChildrenCount()) {
            /** @var Mage_Catalog_Model_Resource_Category_Collection $categoryCollection */
            $categoryCollection = $category->getChildrenCategoriesWithInactive();
            $categoryCollection->setDisableFlat(true);
            /** @var Mage_Catalog_Model_Category $childCategory */
            foreach ($categoryCollection as $childCategory) {
                $childCategory->setUrlKey($category->getUrlKey());
                $childCategory->setParentUrl($category->getRequestPath());
                $this->_indexCategoriesRecursively($childCategory, $store);
            }
        }

        return $this;
    }

    /**
     * Format url key of category into valid form
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Model_Category
     */
    protected function _formatUrlKey(Mage_Catalog_Model_Category $category)
    {
        if ($category->getUrlKey() == '') {
            $category->setUrlKey($category->formatUrlKey($category->getName()));
        } else {
            $category->setUrlKey($category->formatUrlKey($category->getUrlKey()));
        }

        return $category;
    }

    /**
     * Get value_id from catalog_category_entity_url_key for category
     *
     * @param Mage_Catalog_Model_Category $category
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    protected function _getUrlKeyAttributeValueId(Mage_Catalog_Model_Category $category, Mage_Core_Model_Store $store)
    {
        $select = $this->_connection->select()
            ->from(
                array('p' => $this->_getTable(array('catalog/category', 'url_key'))),
                array(
                    'value_id' => $this->_connection->getIfNullSql('c.value_id', 'p.value_id'),
                    'value' => $this->_connection->getIfNullSql('c.value', 'p.value'),
                    'entity_id' => 'p.entity_id',
                )
            )->joinLeft(
                array('c' => $this->_getTable(array('catalog/category', 'url_key'))),
                'p.entity_id = c.entity_id AND c.store_id = :store_id',
                array()
            )
            ->where('p.entity_id = :entity_id')
            ->where('p.store_id = 0');

        $bind = array(
            'store_id' => $store->getId(),
            'entity_id' => $category->getId(),
        );

        return $this->_connection->fetchRow($select, $bind);
    }

    /**
     * Get base select for _getRewrite and _getRewriteForValueId methods
     *
     * @return Zend_Db_Select
     */
    protected function _getRewriteBaseSelect()
    {
        return $this->_connection->select()
            ->from(
                array('e' => $this->_getTable('enterprise_urlrewrite/url_rewrite')),
                array('value_id', 'request_path', 'store_id', 'entity_type')
            )
            ->where('store_id = :store_id')
            ->where('entity_type = ?', Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE)
            ->limit(1);
    }

    /**
     * Get category request from enterprise_url_rewrite table
     *
     * @param string $requestPath
     * @param int $storeId
     * @return bool
     */
    protected function _getRewrite($requestPath, $storeId)
    {
        $select = $this->_getRewriteBaseSelect();
        $select->where('request_path = :request_path');

        $bind = array(
            'request_path' => $requestPath,
            'store_id' => $storeId,
        );

        $result = $this->_connection->fetchRow($select, $bind);
        return $result;
    }

    /**
     * Get category request from enterprise_url_rewrite table
     *
     * @param int $storeId
     * @param int $valueId
     * @return bool
     */
    protected function _getRewriteForValueId($storeId, $valueId)
    {
        $select = $this->_getRewriteBaseSelect();
        $select->where('value_id = :value_id');

        $bind = array(
            'store_id' => $storeId,
            'value_id' => $valueId,
        );

        $result = $this->_connection->fetchRow($select, $bind);
        return $result;
    }

    /**
     * Write category url_key into enterprise_url_rewrite table
     *
     * @param Mage_Catalog_Model_Category $category
     * @param Mage_Core_Model_Store $store
     * @return Mage_Catalog_Model_Category
     */
    protected function _reindexCategoryUrlKey(Mage_Catalog_Model_Category $category, Mage_Core_Model_Store $store)
    {
        $requestPath = trim($category->getParentUrl(), '/');
        $requestPath = (!empty($requestPath) ? $requestPath . '/' : '') . $category->getUrlKey();
        $requestPath = $this->_cutRequestPath($requestPath);
        $urlKeyValue = $this->_getUrlKeyAttributeValueId($category, $store);
        /*
         * if this category created on store view level, we should write default url key for this category,
         * or this category will be unaccessible from frontend
         */
        if (empty($urlKeyValue) && empty($urlKeyValue['value_id'])) {
            $category = $this->_setUrlKeyForDefaultStore($category, $store);
            //get url key value id from backend table after changes
            $urlKeyValue = $this->_getUrlKeyAttributeValueId($category, $store);
        }
        $valueId = $urlKeyValue['value_id'];
        /**
         * Check if we should insert rewrite into table
         */
        $rewriteRow = $this->_getRewrite($requestPath, $store->getId());
        if (!$rewriteRow || $rewriteRow['value_id'] != $valueId) {
            //get current url path from enterprise_url_rewrite
            $rewriteForValueId = $this->_getRewriteForValueId($store->getId(), $valueId);
            $suffix = trim(str_replace($requestPath, '', $category->getRequestPath()), '-');
            /*
             * theoretically we may face with situation when several categories have url_key like:
             * id url_key request path
             * 1  abc     abc
             * 2  abc     abc-1
             * 3  abc     abc-2
             * and we should reindex category with id 2, we can't be sure should we add prefix or not
             * so workaround with regexp cover most cases of this problem
             */
            $requestPathIncrement = (int) $this->_getRewriteRequestIncrement($requestPath, $store);
            if (!$rewriteForValueId || !preg_match('#^(\d)+$#', $suffix) || ($suffix > $requestPathIncrement)) {
                if ($rewriteRow && $rewriteRow['value_id'] != $valueId) {
                    $requestPath .= '-' . ++$requestPathIncrement;
                }
                $category = $this->_saveRewrite($category, $store, $requestPath, $valueId);
                // clean full page cache for category
            }
        }
        // save id of already indexed category into list
        $this->_indexedCategoryIds[$store->getId()][$category->getId()] = 1;

        return $category;
    }

    /**
     * Write category url_key for category into default store
     *
     * @param Mage_Catalog_Model_Category $category
     * @param Mage_Core_Model_Store $store
     * @return Mage_Catalog_Model_Category
     */
    protected function _setUrlKeyForDefaultStore(Mage_Catalog_Model_Category $category, Mage_Core_Model_Store $store)
    {
        //we should save url key for default store
        $category->setStoreId(0);
        $this->_urlModel->getResource()->saveCategoryAttribute($category, 'url_key');
        //return current store to category
        $category->setStoreId($store->getId());

        return $category;
    }
    /**
     * Cut request path to maximum allowed size in system
     *
     * @param string $requestPath
     * @return string
     */
    protected function _cutRequestPath($requestPath)
    {
        $maxLength = Mage_Catalog_Model_Url::MAX_REQUEST_PATH_LENGTH
            + Mage_Catalog_Model_Url::ALLOWED_REQUEST_PATH_OVERFLOW;
        if (strlen($requestPath) > $maxLength) {
            $requestPath = substr($requestPath, 0, Mage_Catalog_Model_Url::MAX_REQUEST_PATH_LENGTH);
        }

        return $requestPath;
    }

    /**
     * Write data into enterprise_url_rewrites
     *
     * @param Mage_Catalog_Model_Category $category
     * @param Mage_Core_Model_Store $store
     * @param string $requestPath
     * @param int $valueId
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _saveRewrite(Mage_Catalog_Model_Category $category, Mage_Core_Model_Store $store,
                                    $requestPath, $valueId
    ) {
        $this->_uniqueIdentifier = $this->_factory->getHelper('core')->uniqHash();
        $targetPath = $this->_urlModel->generatePath('type', null, $category);
        $rewriteData = array(
            'request_path' => $requestPath,
            'target_path'  => $targetPath,
            'guid'         => $this->_uniqueIdentifier,
            'is_system'    => new Zend_Db_Expr(1),
            'identifier'   => $requestPath,
            'value_id'     => $valueId,
            'store_id'     => $store->getId(),
            'entity_type'  => Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE,
        );

        $this->_categoryId = $category->getId();
        $this->_storeId = $store->getId();
        $this->_cleanOldUrlRewrite();
        $this->_connection->insert($this->_getTable('enterprise_urlrewrite/url_rewrite'), $rewriteData);
        $this->_refreshRelation();
        $category->setRequestPath($requestPath);
        $this->_changedCategoryIds[$category->getId()] = $category->getId();
        return $category;
    }

    /**
     * Get last used increment part of rewrite request path
     *
     * @param string $urlPath
     * @param Mage_Core_Model_Store $store
     * @return int
     */
    protected function _getRewriteRequestIncrement($urlPath, Mage_Core_Model_Store $store)
    {
        // match request_url abcdef1234(-12)(.html) pattern
        $match = array();
        $regularExpression = '#^([0-9a-z/-]+)(-([0-9]+))?$#i';
        preg_match($regularExpression, $urlPath, $match);
        $match[1] = $match[1] . '-';
        $match[4] = isset($match[4]) ? $match[4] : '';
        $prefix = $match[1];

        $requestPathField = new Zend_Db_Expr($this->_connection->quoteIdentifier('request_path'));
        //select increment part of request path and cast expression to integer
        $urlIncrementPartExpression = $this->_eavHelper->getCastToIntExpression(
            $this->_connection->getSubstringSql(
                $requestPathField,
                strlen($prefix) + 1,
                $this->_connection->getLengthSql($requestPathField) . ' - ' . strlen($prefix)
            )
        );
        $select = $this->_connection->select()
            ->from(
                $this->_getTable('enterprise_urlrewrite/url_rewrite'),
                new Zend_Db_Expr('MAX(' . $urlIncrementPartExpression . ')')
            )
            ->where('entity_type = :entity_type')
            ->where('store_id = :store_id')
            ->where('request_path LIKE :request_path')
            ->where(
                $this->_connection->prepareSqlCondition(
                    'request_path',
                    array(
                        'regexp' => '^' . preg_quote($prefix) . '[0-9]*$',
                    )
                )
            );
        $bind = array(
            'store_id'=> (int) $store->getId(),
            'request_path' => $prefix . '%',
            'entity_type' => Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE,
        );

        return (int)$this->_connection->fetchOne($select, $bind);
    }

    /**
     * Clean from enterprise_url_rewrite rewrites for deleted or moved into other website
     *
     * @return Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Category_Refresh
     */
    protected function _cleanDeletedCategories()
    {
        $subSelect = $this->_connection->select()
            ->from(
                array('sg' => $this->_getTable('core/store_group')),
                array('root_category_id')
            )
            ->join(
                array('cs' => $this->_getTable('core/store')),
                'sg.website_id = cs.website_id AND sg.website_id <> 0',
                array('store_id')
            )
            ->join(
                array('ce' => $this->_getTable('catalog/category')),
                'path LIKE CONCAT("%/", sg.root_category_id, "/%")',
                array('entity_id')
            );

        $select = $this->_connection->select()
            ->from(
                array('ur' => $this->_getTable('enterprise_urlrewrite/url_rewrite')),
                array()
            )
            ->join(
                array('cr' => $this->_getTable('enterprise_catalog/category')),
                'cr.url_rewrite_id = ur.url_rewrite_id',
                array()
            )
            ->joinLeft(
                array('sub' => new Zend_Db_Expr('(' . $subSelect . ')')),
                'sub.entity_id = cr.category_id AND sub.store_id = cr.store_id',
                array()
            )
            ->where('sub.entity_id IS NULL')
            ->where('ur.entity_type = ?', Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE);

        $this->_connection->query($select->deleteFromSelect('ur'));

        // remove url rewrites for categories with problems in mapping tables
        $select = $this->_connection->select()
            ->from(
                array('ur' => $this->_getTable('enterprise_urlrewrite/url_rewrite')),
                array()
            )
            ->joinLeft(
                array('uk' => $this->_getTable(array('catalog/category', 'url_key'))),
                'uk.value_id = ur.value_id',
                array()
            )
            ->where('uk.entity_id IS NULL')
            ->where('ur.entity_type = ?', Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE);

        $this->_connection->query($select->deleteFromSelect('ur'));

        return $this;
    }

    /**
     * Clean old url rewrites records from table
     *
     * @return Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Category_Refresh
     */
    protected function _cleanOldUrlRewrite()
    {
        $select = $this->_connection->select()
            ->from(array('ur' => $this->_getTable('enterprise_urlrewrite/url_rewrite')))
            ->join(
                array('rc' => $this->_getTable('enterprise_catalog/category')),
                'rc.url_rewrite_id = ur.url_rewrite_id', array()
            )
            ->where('rc.category_id = ?', $this->_categoryId)
            ->where('ur.store_id = ?', $this->_storeId);
        $this->_connection->query($select->deleteFromSelect('ur'));
        return $this;
    }


    /**
     * Refresh redirect to url rewrite relations
     *
     * @return Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Category_Refresh
     */
    protected function _refreshRelation()
    {
        $query = $this->_connection->select()
            ->from(
                array('ur' => $this->_getTable('enterprise_urlrewrite/url_rewrite')),
                array('category_id' => 'uk.entity_id', 'ur.store_id', 'url_rewrite_id')
            )->join(
                array('uk' => $this->_getTable(array('catalog/category', 'url_key'))),
                'uk.value_id = ur.value_id', array()
            )
            ->where('guid = ?', $this->_uniqueIdentifier);

        $insert = $this->_connection->insertFromSelect(
            $query,
            $this->_relationTableName,
            $this->_relationColumns
        );

        $insert .=  sprintf(' ON DUPLICATE KEY UPDATE %1$s = VALUES(%1$s)',
            $this->_connection->quoteIdentifier('url_rewrite_id')
        );

        $this->_connection->query($insert);
        return $this;
    }

    /**
     * Dispatches an event after reindex
     *
     * @return $this
     */
    protected function _flushCache()
    {
        if ($this->_changedCategoryIds) {
            Mage::dispatchEvent(
                'catalog_url_category_partial_reindex',
                array('category_ids' => $this->_changedCategoryIds)
            );
            Mage::dispatchEvent(
                'catalog_url_category_reindex',
                array('tags' => array(Mage_Catalog_Model_Category::CACHE_TAG))
            );

        }
        return $this;
    }

    /**
     * Returns select query for deleting old url rewrites.
     *
     * @return Varien_Db_Select
     * @deprecated since 1.13.0.2
     */
    protected function _getCleanOldUrlRewriteSelect()
    {
        return $this->_connection->select()
            ->from(array('ur' => $this->_getTable('enterprise_urlrewrite/url_rewrite')))
            ->join(array('rc' => $this->_getTable('enterprise_catalog/category')),
                'rc.url_rewrite_id = ur.url_rewrite_id', array());
    }

    /**
     * Prepares refresh relation select query
     *
     * @return Varien_Db_Select
     * @deprecated since 1.13.0.2
     */
    protected function _getRefreshRelationSelectSql()
    {
        return $this->_connection->select()
            ->from(array('ur' => $this->_getTable('enterprise_urlrewrite/url_rewrite')),
                array('category_id' => 'uk.entity_id', 'uk.store_id', 'url_rewrite_id'))
            ->join(array('uk' => $this->_getTable(array('catalog/category', 'url_key'))),
                'uk.value_id = ur.value_id', array()
            )
            ->where('guid = ?', $this->_uniqueIdentifier);
    }

    /**
     * Prepares url rewrite select query
     *
     * @return Varien_Db_Select
     * @deprecated since 1.13.0.2
     */
    protected function _getUrlRewriteSelectSql()
    {
        $caseSql = $this->_connection->getCaseSql('',
            array('ur.inc IS NULL OR ' .
                $this->_connection->quoteIdentifier('m.value') . ' = 1' => new Zend_Db_Expr("''")),
            $this->_connection->getConcatSql(array("'-'", 'ur.inc'))
        );

        $sRequestPath = $this->_connection->getConcatSql(array(
            $this->_connection->quoteIdentifier('uk.value'),
            $caseSql
        ));

        $sTargetPath = $this->_connection->getConcatSql(array("'" . self::BASE_TARGET_PATH . "'", 'uk.entity_id'));

        return $this->_connection->select()
            ->from(array('uk' => $this->_getTable(array('catalog/category', 'url_key'))),
                array(
                    'request_path'  => new Zend_Db_Expr($sRequestPath),
                    'target_path'   => new Zend_Db_Expr($sTargetPath),
                    'guid'          => new Zend_Db_Expr($this->_connection->quote($this->_uniqueIdentifier)),
                    'is_system'     => new Zend_Db_Expr(1),
                    'identifier'    => new Zend_Db_Expr($sRequestPath),
                    'value_id'      => 'uk.value_id',
                    'store_id'      => 'uk.store_id'
                ))
            ->joinLeft(array('ur' => $this->_getTable('enterprise_urlrewrite/url_rewrite')),
                'ur.identifier = ' . $this->_connection->quoteIdentifier('uk.value'), array())
            ->joinLeft(array('m' => $this->_getTable('enterprise_index/multiplier')),
                'ur.identifier IS NOT NULL', array());
    }
}
