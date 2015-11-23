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
 * Full refresh price index
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_Catalog_Model_Index_Action_Product_Price_Abstract
    extends Enterprise_Index_Model_Action_Abstract
{
    /**
     * Default Product Type Price indexer resource model
     *
     * @var string
     */
    protected $_defaultPriceIndexer    = 'catalog/product_indexer_price_default';

    /**
     * Product Type Price indexer resource models
     *
     * @var array
     */
    protected $_indexers;

    /**
     * Flag that defines if need to use "_idx" index table suffix instead of "_tmp"
     *
     * @var bool
     */
    protected $_useIdxTable = false;

    /**
     * Synchronize data between index storage and original storage
     *
     * @param array $processIds
     * @return Enterprise_Catalog_Model_Index_Action_Product_Price_Abstract
     */
    protected function _syncData(array $processIds = array())
    {
        // ensure $processIds array is not a mix of string and int values
        $processIds = array_map('intval', $processIds);

        // delete invalid rows
        $select = $this->_connection->select()
            ->from(array('index_price' => $this->_getTable('catalog/product_index_price')), null)
            ->joinLeft(
                array('ip_tmp' => $this->_getIdxTable()),
                'index_price.entity_id = ip_tmp.entity_id AND index_price.website_id = ip_tmp.website_id',
                array()
            )
            ->where('ip_tmp.entity_id IS NULL');
        if (!empty($processIds)) {
            $select->where('index_price.entity_id IN(?)', $processIds);
        }
        $sql = $select->deleteFromSelect('index_price');
        $this->_connection->query($sql);

        $this->_insertFromTable($this->_getIdxTable(), $this->_getTable('catalog/product_index_price'));
        return $this;
    }

    /**
     * Returns table name for given entity
     * @param $entityName
     * @return string
     */
    protected function _getTable($entityName)
    {
        return $this->_metadata->getResource()->getTable($entityName);
    }

    /**
     * Prepare website current dates table
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Price_Abstract
     */
    protected function _prepareWebsiteDateTable()
    {
        $write = $this->_connection;
        $baseCurrency = Mage::app()->getBaseCurrencyCode();

        $select = $write->select()
            ->from(
                array('cw' => $this->_getTable('core/website')),
                array('website_id')
            )
            ->join(
                array('csg' => $this->_getTable('core/store_group')),
                'cw.default_group_id = csg.group_id',
                array('store_id' => 'default_store_id')
            )
            ->where('cw.website_id != 0');


        $data = array();
        foreach ($write->fetchAll($select) as $item) {
            /** @var $website Mage_Core_Model_Website */
            $website = Mage::app()->getWebsite($item['website_id']);

            if ($website->getBaseCurrencyCode() != $baseCurrency) {
                $rate = Mage::getModel('directory/currency')
                    ->load($baseCurrency)
                    ->getRate($website->getBaseCurrencyCode());
                if (!$rate) {
                    $rate = 1;
                }
            } else {
                $rate = 1;
            }

            /** @var $store Mage_Core_Model_Store */
            $store = Mage::app()->getStore($item['store_id']);
            if ($store) {
                $timestamp = Mage::app()->getLocale()->storeTimeStamp($store);
                $data[] = array(
                    'website_id'   => $website->getId(),
                    'website_date' => Varien_Date::formatDate($timestamp, false),
                    'rate'         => $rate
                );
            }
        }

        $table = $this->_getTable('catalog/product_index_website');
        $this->_emptyTable($table);
        if ($data) {
            $write->insertMultiple($table, $data);
        }

        return $this;
    }

    /**
     * Prepare tier price index table
     *
     * @param int|array $entityIds the entity ids limitation
     * @return Enterprise_Catalog_Model_Index_Action_Product_Price_Abstract
     */
    protected function _prepareTierPriceIndex($entityIds = null)
    {
        $write = $this->_connection;
        $table = $this->_getTable('catalog/product_index_tier_price');
        $this->_emptyTable($table);

        $websiteExpression = $write->getCheckSql('tp.website_id = 0', 'ROUND(tp.value * cwd.rate, 4)', 'tp.value');
        $select = $write->select()
            ->from(
                array('tp' => $this->_getTable(array('catalog/product', 'tier_price'))),
                array('entity_id')
            )
            ->join(
                array('cg' => $this->_getTable('customer/customer_group')),
                'tp.all_groups = 1 OR (tp.all_groups = 0 AND tp.customer_group_id = cg.customer_group_id)',
                array('customer_group_id')
            )
            ->join(
                array('cw' => $this->_getTable('core/website')),
                'tp.website_id = 0 OR tp.website_id = cw.website_id',
                array('website_id')
            )
            ->join(
                array('cwd' => $this->_getTable('catalog/product_index_website')),
                'cw.website_id = cwd.website_id',
                array()
            )
            ->where('cw.website_id != 0')
            ->columns(new Zend_Db_Expr("MIN({$websiteExpression})"))
            ->group(array('tp.entity_id', 'cg.customer_group_id', 'cw.website_id'));

        if (!empty($entityIds)) {
            $select->where('tp.entity_id IN(?)', $entityIds);
        }

        $query = $select->insertFromSelect($table);
        $write->query($query);

        return $this;
    }

    /**
     * Prepare group price index table
     *
     * @param int|array $entityIds the entity ids limitation
     * @return Enterprise_Catalog_Model_Index_Action_Product_Price_Abstract
     */
    protected function _prepareGroupPriceIndex($entityIds = null)
    {
        $write = $this->_connection;
        $table = $this->_getTable('catalog/product_index_group_price');
        $this->_emptyTable($table);

        $websiteExpression = $write->getCheckSql('gp.website_id = 0', 'ROUND(gp.value * cwd.rate, 4)', 'gp.value');
        $select = $write->select()
            ->from(
                array('gp' => $this->_getTable(array('catalog/product', 'group_price'))),
                array('entity_id')
            )
            ->join(
                array('cg' => $this->_getTable('customer/customer_group')),
                'gp.all_groups = 1 OR (gp.all_groups = 0 AND gp.customer_group_id = cg.customer_group_id)',
                array('customer_group_id')
            )
            ->join(
                array('cw' => $this->_getTable('core/website')),
                'gp.website_id = 0 OR gp.website_id = cw.website_id',
                array('website_id')
            )
            ->join(
                array('cwd' => $this->_getTable('catalog/product_index_website')),
                'cw.website_id = cwd.website_id',
                array()
            )
            ->where('cw.website_id != 0')
            ->columns(new Zend_Db_Expr("MIN({$websiteExpression})"))
            ->group(array('gp.entity_id', 'cg.customer_group_id', 'cw.website_id'));

        if (!empty($entityIds)) {
            $select->where('gp.entity_id IN(?)', $entityIds);
        }

        $query = $select->insertFromSelect($table);
        $write->query($query);

        return $this;
    }

    /**
     * Retrieve price indexers per product type
     *
     * @return array
     */
    protected function _getTypeIndexers()
    {
        if (is_null($this->_indexers)) {
            $this->_indexers = array();
            $types = Mage::getSingleton('catalog/product_type')->getTypesByPriority();
            foreach ($types as $typeId => $typeInfo) {
                if (isset($typeInfo['price_indexer'])) {
                    $modelName = $typeInfo['price_indexer'];
                } else {
                    $modelName = $this->_defaultPriceIndexer;
                }
                $isComposite = !empty($typeInfo['composite']);
                $indexer = Mage::getResourceModel($modelName)
                    ->setTypeId($typeId)
                    ->setIsComposite($isComposite);

                $this->_indexers[$typeId] = $indexer;
            }
        }

        return $this->_indexers;
    }

    /**
     * Retrieve Price indexer by Product Type
     *
     * @param string $productTypeId
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price_Interface
     * @throws Mage_Core_Exception
     */
    protected function _getIndexer($productTypeId)
    {
        $this->_getTypeIndexers();
        if (!isset($this->_indexers[$productTypeId])) {
            Mage::throwException(
                Mage::helper('enterprise_catalog')->__('Unsupported product type "%s".', $productTypeId)
            );
        }
        return $this->_indexers[$productTypeId];
    }

    /**
     * Copy data from source table of read adapter to destination table of index adapter
     *
     * @param string $sourceTable
     * @param string $destTable
     * @param null $where
     */
    protected function _insertFromTable($sourceTable, $destTable, $where = null)
    {
        $sourceColumns = array_keys($this->_connection->describeTable($sourceTable));
        $targetColumns = array_keys($this->_connection->describeTable($destTable));
        $select = $this->_connection->select()->from($sourceTable, $sourceColumns);
        if ($where) {
            $select->where($where);
        }
        $query = $this->_connection->insertFromSelect($select, $destTable, $targetColumns,
            Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE);
        $this->_connection->query($query);
    }

    /**
     * Set or get what either "_idx" or "_tmp" suffixed temporary index table need to use
     *
     * @param bool $value
     * @return bool
     */
    protected function _useIdxTable($value = null)
    {
        if (!is_null($value)) {
            $this->_useIdxTable = (bool)$value;
        }
        return $this->_useIdxTable;
    }

    /**
     * Retrieve temporary index table name
     *
     * @return string
     */
    protected function _getIdxTable()
    {
        if ($this->_useIdxTable()) {
            return $this->_getTable('catalog/product_price_indexer_idx');
        }
        return $this->_getTable('catalog/product_price_indexer_tmp');
    }

    /**
     * Removes all data from the table
     *
     * @param string $table
     */
    protected function _emptyTable($table)
    {
        if ($this->_connection->getTransactionLevel() == 0) {
            $this->_connection->truncateTable($table);
        } else {
            $this->_connection->delete($table);
        }
    }
}
