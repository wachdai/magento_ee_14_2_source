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
 * @package     Enterprise_CatalogSearch
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Fulltext index refresh action class
 *
 * @category    Enterprise
 * @package     Enterprise_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CatalogSearch_Model_Index_Action_Fulltext_Refresh_Row
    extends Enterprise_CatalogSearch_Model_Index_Action_Fulltext_Refresh
{
    /**
     * Value for updating mview table
     *
     * @var array
     */
    protected $_keyColumnIdValue;

    /**
     * Array of product IDs to reindex
     *
     * @var array
     */
    protected $_productIds = array();

    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'metadata' Enterprise_Mview_Model_Metadata
     *  - 'connection' Varien_Db_Adapter_Interface
     *  - 'factory' Enterprise_Mview_Model_Factory
     *  - 'value' array
     *
     * @param array $args
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function __construct(array $args)
    {
        if (isset($args['value']) && !empty($args['value'])) {
            $this->_keyColumnIdValue = is_array($args['value']) ?
                    array_unique($args['value']) : array($args['value']);
        }

        parent::__construct($args);
    }

    /**
     * Method deletes old row in the mview table and insert new one from view.
     *
     * @return Enterprise_CatalogSearch_Model_Index_Action_Fulltext_Refresh_Row
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        if (!$this->_metadata->isValid() || empty($this->_keyColumnIdValue)) {
            throw new Enterprise_Index_Model_Action_Exception("Can't perform operation, incomplete metadata!");
        }

        try {
            // Index basic products
            $this->_setProductIdsFromValue();
            $this->_indexer->rebuildIndex(null, $this->_productIds);
            // Index parent products
            $this->_setProductIdsFromParents();
            $this->_indexer->rebuildIndex(null, $this->_productIds);
            // Clear search results
            $this->_resetSearchResults();
        } catch (Exception $e) {
            throw new Enterprise_Index_Model_Action_Exception($e->getMessage(), $e->getCode());
        }

        return $this;
    }

    /**
     * Retrieve select for getting searchable products per store by key column ID
     *
     * @deprecated since version 1.13.2
     * @param int $storeId
     * @param array $staticFields
     * @param int $lastProductId
     * @param int $limit
     * @return Varien_Db_Select
     */
    protected function _getSearchableProductsSelect($storeId, array $staticFields, $lastProductId = 0, $limit = 100)
    {
        return parent::_getSearchableProductsSelect($storeId, $staticFields, $lastProductId, $limit)
            ->where('e.entity_id IN (?)', $this->_productIds);
    }

    /**
     * Get select for removing entity data from fulltext search table by key column ID
     *
     * @deprecated since version 1.13.2
     * @param int $storeId
     * @return array
     */
    protected function _getCleanIndexConditions($storeId)
    {
        $conditions = parent::_getCleanIndexConditions($storeId);
        $conditions[] = $this->_getWriteAdapter()->quoteInto('product_id IN (?)', $this->_productIds);
        return $conditions;
    }

    /**
     * Set value ID to product IDs to be re-indexed
     */
    protected function _setProductIdsFromValue()
    {
        $this->_productIds = $this->_keyColumnIdValue;
    }

    /**
     * Set parents IDS to product IDs to be re-indexed
     */
    protected function _setProductIdsFromParents()
    {
        $this->_productIds = $this->_getWriteAdapter()->select()
            ->from($this->_getTable('catalog/product_relation'), 'parent_id')
            ->distinct(true)
            ->where('child_id IN (?)', $this->_keyColumnIdValue)
            ->where('parent_id NOT IN (?)', $this->_keyColumnIdValue)
            ->query()->fetchAll(Zend_Db::FETCH_COLUMN);
    }
}
