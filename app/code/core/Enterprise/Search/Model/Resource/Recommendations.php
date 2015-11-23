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
 * @package     Enterprise_Search
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Catalog search recommendations resource model
 *
 * @category    Enterprise
 * @package     Enterprise_Search
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Search_Model_Resource_Recommendations extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * @var Mage_Catalogsearch_Model_Query
     */
    protected $_searchQueryModel;

    /**
     * Init main table
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_search/recommendations', 'id');
    }

    /**
     * Save search relations
     *
     * @param int $queryId
     * @param array $relatedQueries
     * @return Enterprise_Search_Model_Resource_Query
     */
    public function saveRelatedQueries($queryId, $relatedQueries = array())
    {
        $adapter = $this->_getWriteAdapter();
        $whereOr = array();
        if (count($relatedQueries) > 0) {
            $whereOr[] = implode(' AND ', array(
                $adapter->quoteInto('query_id=?', $queryId),
                $adapter->quoteInto('relation_id NOT IN(?)', $relatedQueries)
            ));
            $whereOr[] = implode(' AND ', array(
                $adapter->quoteInto('relation_id = ?', $queryId),
                $adapter->quoteInto('query_id NOT IN(?)', $relatedQueries)
            ));
        } else {
            $whereOr[] = $adapter->quoteInto('query_id = ?', $queryId);
            $whereOr[] = $adapter->quoteInto('relation_id = ?', $queryId);

        }
        $whereCond = '(' . implode(') OR (', $whereOr) . ')';
        $adapter->delete($this->getMainTable(), $whereCond);

        $existsRelatedQueries = $this->getRelatedQueries($queryId);
        $neededRelatedQueries = array_diff($relatedQueries, $existsRelatedQueries);
        foreach ($neededRelatedQueries as $relationId) {
            $adapter->insert($this->getMainTable(), array(
                "query_id"    => $queryId,
                "relation_id" => $relationId
            ));
        }
        return $this;
    }

    /**
     * Retrieve related search queries
     *
     * @param int|array $queryId
     * @return array
     */
    public function getRelatedQueries($queryId, $limit = false, $order = false)
    {
        $queryIds = array();
        $collection = $this->_getSearchQueryModel()->getResourceCollection();
        $adapter = $this->_getReadAdapter();

        $queryIdCond = $adapter->quoteInto('main_table.query_id IN (?)', $queryId);

        $collection->getSelect()
            ->join(
                array('sr' => $collection->getTable("enterprise_search/recommendations")),
                '(sr.query_id=main_table.query_id OR sr.relation_id=main_table.query_id) AND ' . $queryIdCond
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                 'rel_id' => $adapter->getCheckSql('main_table.query_id=sr.query_id', 'sr.relation_id', 'sr.query_id')
            ));
        if (!empty($limit)) {
            $collection->getSelect()->limit($limit);
        }
        if (!empty($order)) {
            $collection->getSelect()->order($order);
        }

        $queryIds = $adapter->fetchCol($collection->getSelect());
        return $queryIds;
    }

    /**
     * Retrieve related search queries by single query
     *
     * @param string $query
     * @param array $params
     * @param int $searchRecommendationsCount
     * @return array
     */
    public function getRecommendationsByQuery($query, $params, $searchRecommendationsCount)
    {
        $model = $this->_getSearchQueryModel();
        $model->loadByQuery($query);

        if (isset($params['store_id'])) {
            $model->setStoreId($params['store_id']);
        }
        $relatedQueriesIds = $this->loadByQuery($query, $searchRecommendationsCount);
        $relatedQueries = array();
        if (count($relatedQueriesIds)) {
            $adapter = $this->_getReadAdapter();
            $mainTable = $model
                ->getResourceCollection()->getMainTable();
            $select = $adapter->select()
                ->from(
                    array('main_table' => $mainTable),
                    array('query_text', 'num_results')
                )
                ->where('query_id IN(?)', $relatedQueriesIds)
                ->where('num_results > 0');
            $relatedQueries = $adapter->fetchAll($select);
        }

        return $relatedQueries;
    }

    /**
     * Retrieve search terms which are started with $queryWords
     *
     * @param array $queryWords
     * @param int $searchRecommendationsCount
     * @return array
     */
    protected function loadByQuery($query, $searchRecommendationsCount)
    {
        $adapter        = $this->_getReadAdapter();
        $model          = $this->_getSearchQueryModel();
        $queryId        = $model->getId();
        $relatedQueries = $this->getRelatedQueries($queryId, $searchRecommendationsCount, 'num_results DESC');
        if ($searchRecommendationsCount - count($relatedQueries) < 1) {
            return $relatedQueries;
        }

        $queryWords = array($query);
        if (strpos($query, ' ') !== false) {
            $queryWords = array_unique(array_merge($queryWords, explode(' ', $query)));
            foreach ($queryWords as $key => $word) {
                $queryWords[$key] = trim($word);
                if (strlen($word) < 3) {
                    unset($queryWords[$key]);
                }
            }
        }

        $likeCondition = array();
        foreach ($queryWords as $word) {
            $likeCondition[] = $adapter->quoteInto('query_text LIKE ?', $word . '%');
        }
        $likeCondition = implode(' OR ', $likeCondition);

        $select = $adapter->select()
            ->from($model->getResource()->getMainTable(), array(
                'query_id'
            ))
            ->where(new Zend_Db_Expr($likeCondition))
            ->where('store_id=?', $model->getStoreId())
            ->order('num_results DESC')
            ->limit($searchRecommendationsCount + 1);
        $ids = $adapter->fetchCol($select);

        if (!is_array($ids)) {
            $ids = array();
        }

        $key = array_search($queryId, $ids);
        if ($key !== false) {
            unset($ids[$key]);
        }
        $ids = array_unique(array_merge($relatedQueries, $ids));
        $ids = array_slice($ids, 0, $searchRecommendationsCount);
        return $ids;
    }

    /**
     * Retrieve search query model
     *
     * @return object
     */
    protected function _getSearchQueryModel()
    {
        if (!$this->_searchQueryModel) {
            $this->_searchQueryModel = Mage::getModel('catalogsearch/query');
        }
        return $this->_searchQueryModel;
    }
}
