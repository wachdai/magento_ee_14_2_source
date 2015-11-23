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
 * @package     Enterprise_SalesArchive
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Module setup
 *
 * @category    Enterprise
 * @package     Enterprise_SalesArchive
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_SalesArchive_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    /**
     * Call afterApplyAllUpdates flag
     *
     * @var boolean
     */
    protected $_callAfterApplyAllUpdates = true;

    /**
     * Map of tables aliases to archive tables
     *
     * @var array
     */
    protected $_tablesMap = array(
        'sales/order_grid'      => 'enterprise_salesarchive/order_grid',
        'sales/invoice_grid'    => 'enterprise_salesarchive/invoice_grid',
        'sales/creditmemo_grid' => 'enterprise_salesarchive/creditmemo_grid',
        'sales/shipment_grid'   => 'enterprise_salesarchive/shipment_grid'
    );

    /**
     * Map of flat tables to archive tables
     *
     * @var array
     */
    protected $_tableContraintMap = array(
        'sales/order_grid'      => array('SALES_FLAT_ORDER_GRID',      'SALES_FLAT_ORDER_GRID_ARCHIVE'),
        'sales/invoice_grid'    => array('SALES_FLAT_INVOICE_GRID',    'SALES_FLAT_INVOICE_GRID_ARCHIVE'),
        'sales/creditmemo_grid' => array('SALES_FLAT_CREDITMEMO_GRID', 'SALES_FLAT_CREDITMEMO_GRID_ARCHIVE'),
        'sales/shipment_grid'   => array('SALES_FLAT_SHIPMENT_GRID',   'SALES_FLAT_SHIPMENT_GRID_ARCHIVE')
    );

    /**
     * Run each time after applying of all updates,
     * if setup model setted  $_callAfterApplyAllUpdates flag to true
     *
     * @return Enterprise_SalesArchive_Model_Resource_Setup
     */
    public function afterApplyAllUpdates()
    {
        $this->_syncArchiveStructure();
        return $this;
    }

    /**
     * Synchronize archive structure
     *
     * @return Enterprise_SalesArchive_Model_Resource_Setup
     */
    protected function _syncArchiveStructure()
    {
        foreach ($this->_tablesMap as $sourceTable => $targetTable) {
            $this->_syncTable(
                $this->getTable($sourceTable),
                $this->getTable($targetTable)
            );
        }
        return $this;
    }

    /**
     * Fast table describe retrieve
     *
     * @param string $table
     * @return array
     */
    protected function _fastDescribe($table)
    {
        $description = $this->getConnection()->describeTable($table);
        $result = array();
        foreach ($description as $column) {
            $result[$column['COLUMN_NAME']] = $column['DATA_TYPE'];
        }
        return $result;
    }

    /**
     * Synchronize tables structure
     *
     * @param string $sourceTable
     * @param string $targetTable
     * @return Enterprise_SalesArchive_Model_Resource_Setup
     */
    protected function _syncTable($sourceTable, $targetTable)
    {
        $adapter = $this->getConnection();
        if (!$this->tableExists($targetTable)) {
            $newTable = $adapter->createTableByDdl($sourceTable, $targetTable);
            $adapter->createTable($newTable);
        } else {
            $sourceFields = $adapter->describeTable($sourceTable);
            $targetFields = $adapter->describeTable($targetTable);
            foreach ($sourceFields as $field => $definition) {
                if (isset($targetFields[$field])) {
                    if ($this->_checkColumnDifference($targetFields[$field], $definition)) {
                        $adapter->modifyColumnByDdl($targetTable, $field, $definition);
                    }
                } else {
                    $columnInfo = $adapter->getColumnCreateByDescribe($definition);
                    $adapter->addColumn($targetTable, $field, $columnInfo);
                    $targetFields[$field] = $definition;
                }
            }

            $previous = false;
            // Synchronize column positions
            $sourceFields = $this->_fastDescribe($sourceTable);
            $targetFields = $this->_fastDescribe($targetTable);
            foreach ($sourceFields as $field => $definition) {
                if ($previous === false) {
                    reset($targetFields);
                    if (key($targetFields) !== $field) {
                        $this->changeColumnPosition($targetTable, $field, false, true);
                    }
                } else {
                    reset($targetFields);
                    $currentKey = key($targetFields);
                    // Search for column position in target table
                    while ($currentKey !== $field) {
                        if (next($targetFields) === false) {
                            $currentKey = false;
                            break;
                        }
                        $currentKey = key($targetFields);
                    }
                    if ($currentKey) {
                        $moved = prev($targetFields) !== false;
                        // If column positions diffrent
                        if (($moved && $previous !== key($targetFields)) || !$moved) {
                            $this->changeColumnPosition($targetTable, $field, $previous);
                        }
                    }
                }
                $previous = $field;
            }
            $this->_syncTableIndex(
                $sourceTable,
                $targetTable
            );

            if (isset($this->_tableContraintMap[$sourceTable])) {
                $this->_syncTableConstraint(
                    $sourceTable,
                    $targetTable,
                    $this->_tableContraintMap[$sourceTable][0],
                    $this->_tableContraintMap[$sourceTable][1]
                );
            }
        }

        return $this;
    }

    /**
     * Change columns position
     *
     * @param string $table
     * @param string $column
     * @param boolean $after
     * @param boolean $first
     * @return Enterprise_SalesArchive_Model_Resource_Setup
     */
    public function changeColumnPosition($table, $column, $after = false, $first = false)
    {
        $helper = Mage::getResourceHelper('enterprise_salesarchive');
        $helper->changeColumnPosition($table, $column, $after, $first);

        return $this;
    }

    /**
     * Syncronize table indexes
     *
     * @param string $sourceTable
     * @param string $targetTable
     * @return Enterprise_SalesArchive_Model_Resource_Setup
     */
    protected function _syncTableIndex($sourceTable, $targetTable)
    {
        $sourceIndex = $this->getConnection()->getIndexList($sourceTable);
        $targetIndex = $this->getConnection()->getIndexList($targetTable);
        foreach ($sourceIndex as $indexKey => $indexData) {
            $indexExists = false;
            foreach ($targetIndex as $targetIndexKey => $targetIndexData) {
                if (!$this->_checkIndexDifference($indexData, $targetIndexData)) {
                    $indexExists = true;
                    break;
                }
            }
            if (!$indexExists) {
                $newIndexName = $this->getConnection()->getIndexName(
                    $targetTable, $indexData['COLUMNS_LIST'], $indexData['INDEX_TYPE']
                );
                $this->getConnection()->addIndex(
                    $targetTable, $newIndexName, $indexData['COLUMNS_LIST'], $indexData['INDEX_TYPE']
                );
            }
        }

        return $this;
    }

    /**
     * Check column difference for synchronization
     *
     * @param array $sourceColumn
     * @param array $targetColumn
     * @return boolean
     */
    protected function _checkColumnDifference($sourceColumn, $targetColumn)
    {
        unset($sourceColumn['TABLE_NAME']);
        unset($targetColumn['TABLE_NAME']);

        return $sourceColumn !== $targetColumn;
    }

    /**
     * Check indicies difference for synchronization
     *
     * @param array $sourceIndex
     * @param array $targetIndex
     * @return boolean
     */
    protected function _checkIndexDifference($sourceIndex, $targetIndex)
    {
        return (strtoupper($sourceIndex['INDEX_TYPE']) != strtoupper($targetIndex['INDEX_TYPE'])
                || count(array_diff($sourceIndex['COLUMNS_LIST'], $targetIndex['COLUMNS_LIST'])) > 0);
    }

    /**
     * Check indexes difference for synchronization
     *
     * @param array $sourceConstraint
     * @param array $targetConstraint
     * @return boolean
     */
    protected function _checkConstraintDifference($sourceConstraint, $targetConstraint)
    {
        return ($sourceConstraint['COLUMN_NAME'] != $targetConstraint['COLUMN_NAME'] ||
                $sourceConstraint['REF_TABLE_NAME'] != $targetConstraint['REF_TABLE_NAME'] ||
                $sourceConstraint['REF_COLUMN_NAME'] != $targetConstraint['REF_COLUMN_NAME'] ||
                $sourceConstraint['ON_DELETE'] != $targetConstraint['ON_DELETE'] ||
                $sourceConstraint['ON_UPDATE'] != $targetConstraint['ON_UPDATE']);
    }

    /**
     * Synchronize tables foreign keys
     *
     * @param string $sourceTable
     * @param string $targetTable
     * @param string $sourceKey
     * @param string $targetKey
     * @return Enterprise_SalesArchive_Model_Resource_Setup
     */
    protected function _syncTableConstraint($sourceTable, $targetTable, $sourceKey, $targetKey)
    {
        $sourceConstraints = $this->getConnection()->getForeignKeys($sourceTable);
        $targetConstraints = $this->getConnection()->getForeignKeys($targetTable);

        $targetConstraintUsedInSource = array();
        foreach ($sourceConstraints as $sourceConstraint => $constraintInfo) {
            $targetConstraint = str_replace($sourceKey, $targetKey, $sourceConstraint);
            if ($sourceConstraint == $targetConstraint) {
                // Constraint have invalid prefix, we will have conflict in synchronizing
                continue;
            }

            if (!isset($targetConstraints[$targetConstraint]) ||
                $this->_checkConstraintDifference($constraintInfo, $targetConstraints[$targetConstraint])) {
                $this->getConnection()->addConstraint(
                    $targetConstraint,
                    $targetTable,
                    $constraintInfo['COLUMN_NAME'],
                    $constraintInfo['REF_TABLE_NAME'],
                    $constraintInfo['REF_COLUMN_NAME'],
                    $constraintInfo['ON_DELETE'],
                    $constraintInfo['ON_UPDATE']
                );
            }

            $targetConstraintUsedInSource[] = $targetConstraint;
        }

        $constraintToDelete = array_diff(array_keys($targetConstraints), $targetConstraintUsedInSource);

        foreach ($constraintToDelete as $constraint) {
            // Clear old not used constraints
            $this->getConnection()->dropForeignKey($targetTable, $constraint);
        }

        return $this;
    }
}
