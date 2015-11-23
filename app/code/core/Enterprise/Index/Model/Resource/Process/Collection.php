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
 * @package     Enterprise_Index
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Enterprise Index Process Collection
 *
 * @category    Enterprise
 * @package     Enterprise_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Enterprise_Index_Model_Resource_Process_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Community index type
     */
    const INDEX_TYPE_CE  = 'community';

    /**
     * Enterprise index type
     */
    const INDEX_TYPE_EE  = 'enterprise';

    /**
     * Array of excluded process codes
     *
     * @var array
     */
    protected $_excludeProcess = array();

    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_index/process');
    }

    /**
     * Add enterprise indexer collection based on mview functionality
     *
     * @return Enterprise_Index_Model_Resource_Process_Collection
     */
    public function initializeSelect()
    {
        $this->_select->reset();

        $countsSelect = $this->getConnection()
            ->select()
            ->from($this->getTable('index/process_event'), array('process_id', 'events' => 'COUNT(*)'))
            ->where('status=?', Mage_Index_Model_Process::EVENT_STATUS_NEW)
            ->group('process_id');

        $oldIndexersSelect =  $this->getConnection()
            ->select()
            ->from(array('main_table' => $this->getTable('index/process')),
                array(
                    'process_id',
                    'indexer_code',
                    'indexer_type' => new Zend_Db_Expr($this->getConnection()->quote(self::INDEX_TYPE_CE)),
                    'status',
                    'started_at',
                    'ended_at',
                    'mode',
                )
            )
            ->joinLeft(
                array('e' => $countsSelect),
                'e.process_id=main_table.process_id',
                array('events' => $this->getConnection()->getCheckSql(
                    $this->getConnection()->prepareSqlCondition('e.events', array('null' => null)), 0, 'e.events'
                ))
            )
            ->joinLeft(
                array('group_table' => $this->getTable('enterprise_mview/metadata_group')),
                'group_table.group_code = main_table.indexer_code' . $this->_getExcludeString(),
                array()
            )
            ->where('group_table.group_code IS NULL');

        $newIndexersSelect = $this->getConnection()
            ->select()
            ->from(array('metadata_table' => $this->getTable('enterprise_mview/metadata')),
                array(
                    'process_id' => new Zend_Db_Expr('UUID()'),
                    'indexer_code' => 'group_table.group_code',
                    'indexer_type' => new Zend_Db_Expr($this->getConnection()->quote(self::INDEX_TYPE_EE)),
                    'status' => $this->getConnection()->getCaseSql(
                        new Zend_Db_Expr('MIN(`metadata_table`.`status`)'),
                            array(
                                $this->getConnection()->quote(Enterprise_Mview_Model_Metadata::STATUS_VALID) =>
                                    $this->getConnection()->quote(Enterprise_Index_Model_Process::STATUS_PENDING),
                                $this->getConnection()->quote(Enterprise_Mview_Model_Metadata::STATUS_INVALID) =>
                                    $this->getConnection()->quote(Enterprise_Index_Model_Process::STATUS_SCHEDULED),
                                $this->getConnection()->quote(Enterprise_Mview_Model_Metadata::STATUS_IN_PROGRESS) =>
                                    $this->getConnection()->quote(Enterprise_Index_Model_Process::STATUS_RUNNING),
                            ),
                        $this->getConnection()->quote(Enterprise_Index_Model_Process::STATUS_REQUIRE_REINDEX)
                        ),
                    'started_at' => new Zend_Db_Expr('NULL'),
                    'ended_at' => new Zend_Db_Expr('NULL'),
                    'mode' => new Zend_Db_Expr('NULL'),
                    'events' => new Zend_Db_Expr('0'),
                )
            )
            ->joinInner(array('group_table' => $this->getTable('enterprise_mview/metadata_group')),
                '`group_table`.`group_id` = `metadata_table`.`group_id`' . $this->_getExcludeString(),
                array()
            )
            ->group('metadata_table.group_id');

        $this->_select = $this->getConnection()
            ->select()
            ->union(array($oldIndexersSelect,$newIndexersSelect), Zend_Db_Select::SQL_UNION_ALL);

        return $this;
    }

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset();

        $countSelect->from(array('main_select' => $this->getSelect()),array());
        $countSelect->columns('COUNT(*)');

        return $countSelect;
    }

    /**
     * Order by primary key desc
     *
     * @return Enterprise_Index_Model_Resource_Process_Collection
     */
    public function setDefaultOrder()
    {
        $this->getSelect()->reset(Zend_Db_Select::ORDER);

        return $this
            ->addOrder('indexer_type', self::SORT_ORDER_ASC)
            ->addOrder('indexer_code', self::SORT_ORDER_ASC);
    }

    /**
     * Add process to exclude
     *
     * @param string $code
     * @return $this
     */
    public function addExcludeProcessByCode($code)
    {
        $this->_excludeProcess[] = $code;
        return $this;
    }

    protected function _getExcludeString()
    {
        if (empty($this->_excludeProcess)) {
            return '';
        }

        return ' AND `group_table`.`group_code` NOT IN (' . $this->getConnection()->quote($this->_excludeProcess) . ')';
    }
}
