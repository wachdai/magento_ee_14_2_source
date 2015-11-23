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
 * Flat product index refresh action class
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh_Row
    extends Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh
{
    /**
     * Value for updating mview table
     *
     * @var mixed
     */
    protected $_keyColumnIdValue;

    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'metadata' Enterprise_Mview_Model_Metadata
     *  - 'connection' Varien_Db_Adapter_Interface
     *  - 'factory' Enterprise_Mview_Model_Factory
     *  - 'value' int|decimal|string|double
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        parent::__construct($args);
        if (isset($args['value'])) {
            $this->_keyColumnIdValue = $args['value'];
        }
    }

    /**
     * Method deletes old row in the mview table and insert new one from view.
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Refresh_Row
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        if (!$this->_isFlatIndexerEnabled()) {
            return $this;
        }
        $this->_validate();
        $stores = Mage::app()->getStores();
        foreach ($stores as $store) {
            $this->_reindexSingleProduct($store->getId(), $this->_keyColumnIdValue);
        }

        Mage::dispatchEvent('catalog_product_flat_partial_reindex',
            array('product_ids' => array($this->_keyColumnIdValue)));
        return $this;
    }

    /**
     * Validates value
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Refresh_Row
     * @throws Enterprise_Index_Model_Action_Exception
     */
    protected function _validate()
    {
        if (empty($this->_keyColumnIdValue)) {
            throw new Enterprise_Index_Model_Action_Exception('Could not rebuild index for undefined product');
        }
        return $this;
    }


        /**
     * Reindex single product into flat product table
     *
     * @param int $storeId
     * @param int $productId
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Refresh_Row
     */
    protected function _reindexSingleProduct($storeId, $productId)
    {
        $this->_storeId = $storeId;
        $flatTable = $this->_productHelper->getFlatTableName($this->_storeId);

        if (!$this->_connection->isTableExists($flatTable)) {
            $this->_createTemporaryFlatTable();
            $this->_moveDataToFlatTable();
        }

        $attributes    = $this->_productHelper->getAttributes();
        $eavAttributes = $this->_productHelper->getTablesStructure($attributes);
        $updateData    = array();
        $describe      = $this->_connection->describeTable($flatTable);

        foreach ($eavAttributes as $tableName => $tableColumns) {
            $columnsChunks = array_chunk($tableColumns, self::ATTRIBUTES_CHUNK_SIZE, true);

            foreach ($columnsChunks as $columns) {
                $select      = $this->_connection->select();
                $selectValue = $this->_connection->select();
                $keyColumns  = array(
                    'entity_id'    => 'e.entity_id',
                    'attribute_id' => 't.attribute_id',
                    'value'        =>  $this->_connection->getIfNullSql('`t2`.`value`', '`t`.`value`'),
                );

                //EAV table/attributes
                if ($tableName != $this->_productHelper->getTable('catalog/product')) {
                    $valueColumns = array();
                    $ids          = array();
                    $select->from(
                        array('e' => $this->_productHelper->getTable('catalog/product')),
                        $keyColumns
                    );

                    $selectValue->from(
                        array('e' => $this->_productHelper->getTable('catalog/product')),
                        $keyColumns
                    );

                    /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                    foreach ($columns as $columnName => $attribute) {
                        if (isset($describe[$columnName])) {
                            $ids[$attribute->getId()] = $columnName;
                        }
                    }

                    $select->joinLeft(
                        array('t' => $tableName),
                        'e.entity_id = t.entity_id '
                        . $this->_connection->quoteInto(' AND t.attribute_id IN (?)', array_keys($ids))
                        . ' AND t.store_id = 0',
                        array()
                    )->joinLeft(
                        array('t2' => $tableName),
                        't.entity_id = t2.entity_id '
                            . ' AND t.attribute_id = t2.attribute_id  '
                            . $this->_connection->quoteInto(' AND t2.store_id = ?', $this->_storeId),
                        array()
                    )->where(
                        'e.entity_id = ' . $productId
                    )->where(
                        't.attribute_id IS NOT NULL'
                    );
                    $cursor = $this->_connection->query($select);
                    while ($row = $cursor->fetch(Zend_Db::FETCH_ASSOC)) {
                        $updateData[$ids[$row['attribute_id']]] = $row['value'];
                        $valueColumnName = $ids[$row['attribute_id']] . $this->_valueFieldSuffix;
                        if (isset($describe[$valueColumnName])) {
                            $valueColumns[$row['value']] = $valueColumnName;
                        }
                    }

                    //Update not simple attributes (eg. dropdown)
                    if (!empty($valueColumns)) {
                        $valueIds = array_keys($valueColumns);

                        $select = $this->_connection->select()
                            ->from(
                                array('t' => $this->_productHelper->getTable('eav/attribute_option_value')),
                                array('t.option_id', 't.value')
                            )->where(
                                $this->_connection->quoteInto('t.option_id IN (?)', $valueIds)
                            );
                        $cursor = $this->_connection->query($select);
                        while ($row = $cursor->fetch(Zend_Db::FETCH_ASSOC)) {
                            $valueColumnName = $valueColumns[$row['option_id']];
                            if (isset($describe[$valueColumnName])) {
                                $updateData[$valueColumnName] = $row['value'];
                            }
                        }
                    }

                } else {
                    $columnNames   = array_keys($columns);
                    $columnNames[] = 'attribute_set_id';
                    $columnNames[] = 'type_id';
                    $select->from(
                        array('e' => $this->_productHelper->getTable('catalog/product')),
                        $columnNames
                    )->where(
                        'e.entity_id = ' . $productId
                    );
                    $cursor = $this->_connection->query($select);
                    $row    = $cursor->fetch(Zend_Db::FETCH_ASSOC);
                    if (!empty($row)) {
                        foreach ($row as $columnName => $value) {
                            $updateData[$columnName] = $value;
                        }
                    }
                }
            }
        }

        if (!empty($updateData)) {
            $updateData   += array('entity_id' => $productId);
            $updateFields = array();
            foreach ($updateData as $key => $value) {
                $updateFields[$key] = $key;
            }
            $this->_connection->insertOnDuplicate($flatTable, $updateData, $updateFields);
        }

        return $this;
    }
}
