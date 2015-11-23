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
 * Enterprise SalesArchive Mysql resource helper model
 *
 * @category    Enterprise
 * @package     Enterprise_SalesArchive
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_SalesArchive_Model_Resource_Helper_Mysql4 extends Mage_Core_Model_Resource_Helper_Mysql4
{
    /**
     * Change columns position
     *
     * @param string $table
     * @param string $column
     * @param boolean $after
     * @param boolean $first
     * @return Enterprise_SalesArchive_Model_Resource_Helper_Mysql4
     */
    public function changeColumnPosition($table, $column, $after = false, $first = false)
    {
        if ($after && $first) {
            if (is_string($after)) {
                $first = false;
            } else {
                $after = false;
            }
        } elseif (!$after && !$first) {
            // If no new position specified
            return $this;
        }

        if (!$this->_getWriteAdapter()->isTableExists($table)) {
            Mage::throwException(Mage::helper('enterprise_salesarchive')->__('Table not found'));
        }

        $columns = array();
        $adapter = $this->_getWriteAdapter();
        $description = $adapter->describeTable($table);
        foreach ($description as $columnDescription) {
            $columns[$columnDescription['COLUMN_NAME']] = $adapter->getColumnDefinitionFromDescribe($columnDescription);
        }

        if (!isset($columns[$column])) {
            Mage::throwException(Mage::helper('enterprise_salesarchive')->__('Column not found'));
        } elseif ($after && !isset($columns[$after])) {
            Mage::throwException(Mage::helper('enterprise_salesarchive')->__('Positioning column not found'));
        }

        if ($after) {
            $sql = sprintf(
                'ALTER TABLE %s MODIFY COLUMN %s %s AFTER %s',
                $adapter->quoteIdentifier($table),
                $adapter->quoteIdentifier($column),
                $columns[$column],
                $adapter->quoteIdentifier($after)
            );
        } else {
            $sql = sprintf(
                'ALTER TABLE %s MODIFY COLUMN %s %s FIRST',
                $adapter->quoteIdentifier($table),
                $adapter->quoteIdentifier($column),
                $columns[$column]
            );
        }

        $adapter->query($sql);

        return $this;
    }
}
