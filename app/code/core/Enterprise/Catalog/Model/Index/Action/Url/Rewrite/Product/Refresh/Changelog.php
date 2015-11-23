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
 * Product Refresh Changelog Action
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Product_Refresh_Changelog
    extends Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Product_Refresh
{
    /**
     * The list of changed entity ids
     *
     * @var null|array
     */
    protected $_changedIds;

    /**
     * Refresh rows by ids from changelog
     * - clean product url rewrites
     * - refresh product url rewrites
     * - refresh product to url rewrite relations
     *
     * @return Enterprise_Mview_Model_Action_Interface
     * @throws Enterprise_Index_Exception
     */
    public function execute()
    {
        $changedIds = $this->_getChangedIds();
        if (empty($changedIds)) {
            return $this;
        }
        $this->_validate();
        $this->_connection->beginTransaction();
        try {
            $this->_cleanOldUrlRewrite();
            $this->_refreshUrlRewrite();
            $this->_refreshRelation();
            $this->_metadata->setVersionId($this->_selectLastVersionId());
            $this->_metadata->save();
            $this->_connection->commit();
            $this->_flushCache();
        } catch (Exception $e) {
            $this->_connection->rollBack();
            throw new Enterprise_Index_Exception($e->getMessage(), $e->getCode());
        }
        return $this;
    }


    /**
     * Dispatches an event after reindex
     *
     * @return $this
     */
    protected function _flushCache()
    {
        $ids = $this->_getChangedIds();
        if ($ids) {
            Mage::dispatchEvent(
                'catalog_url_product_partial_reindex',
                array('product_ids' => $this->_getChangedIds())
            );
        }
        return $this;
    }
    /**
     * Validate metadata before execute
     *
     * @return Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Product_Refresh_Changelog
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
     * Prepares url rewrite select query
     *
     * @return Varien_Db_Select
     */
    protected function _getUrlRewriteSelectSql()
    {
        $select = parent::_getUrlRewriteSelectSql();
        $select->where('uk.entity_id IN (?)', $this->_getChangedIds());
        return $select;
    }

    /**
     * Prepares refresh relation select query for given product_id
     *
     * @return Varien_Db_Select
     */
    protected function _getRefreshRelationSelectSql()
    {
        $select = parent::_getRefreshRelationSelectSql();
        $select->where('uk.entity_id IN (?)', $this->_getChangedIds());
        return $select;
    }

    /**
     * Returns select query for deleting old url rewrites.
     *
     * @return Varien_Db_Select
     */
    protected function _getCleanOldUrlRewriteSelect()
    {
        $select = parent::_getCleanOldUrlRewriteSelect();
        $select->where('rp.product_id IN (?)', $this->_getChangedIds());
        return $select;
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
                ->from(array('changelog' => $this->_metadata->getChangelogName()),
                    array($this->_metadata->getKeyColumn()))
                ->where('version_id > ?', $this->_metadata->getVersionId());
            $this->_changedIds = $this->_connection->fetchCol($select);
        }
        return $this->_changedIds;
    }
}
