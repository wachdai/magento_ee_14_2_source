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
 * @package     Enterprise_CatalogEvent
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Catalog Event resource collection
 *
 * @category    Enterprise
 * @package     Enterprise_CatalogEvent
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CatalogEvent_Model_Resource_Event_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Whether category data was added to collection
     *
     * @var bool
     */
    protected $_categoryDataAdded  = false;

    /**
     * Whether collection should dispose of the closed events
     *
     * @var bool
     */
    protected $_skipClosed         = false;

    /**
     * Intialize collection
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_catalogevent/event');
    }

    /**
     * Redefining of standart field to filter adding, for aviability of
     * bit operations for display state
     *
     * @param unknown_type $field
     * @param null|string|array $condition
     * @return Enterprise_CatalogEvent_Model_Resource_Event_Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'display_state') {
            $field = $this->_getMappedField($field);
            if (is_array($condition) && isset($condition['eq'])) {
                $condition = $condition['eq'];
            }
            if (in_array((int) $condition, array(0, 1))) {
                $this->getSelect()->where('display_state = ?', (int)$condition);
                //$this->getSelect()->where('(' . $field . ' & ' . (int) $condition . ') = ' . (int) $condition);
            } else {
                $this->getSelect()->where('display_state=?', 0);
            }
            return $this;
        }
        if ($field == 'status') {
            $this->getSelect()->where(
                $this->_getConditionSql($this->_getStatusColumnExpr(), $condition)
            );
            return $this;
        }
        parent::addFieldToFilter($field, $condition);
        return $this;
    }

    /**
     * Add filter for visible events on frontend
     *
     * @return Enterprise_CatalogEvent_Model_Resource_Event_Collection
     */
    public function addVisibilityFilter()
    {
        $this->_skipClosed = true;
        $this->addFieldToFilter('status', array(
            'nin' => Enterprise_CatalogEvent_Model_Event::STATUS_CLOSED
        ));
        return $this;
    }

    /**
     * Set sort order
     *
     * @param string $field
     * @param string $direction
     * @param boolean $unshift
     * @return Enterprise_CatalogEvent_Model_Resource_Event_Collection
     */
    protected function _setOrder($field, $direction, $unshift = false)
    {
        if ($field == 'category_name' && $this->_categoryDataAdded) {
            $field = 'category_position';
        }
        return parent::setOrder($field, $direction, $unshift);
    }

    /**
     * Add category data to collection select (name, position)
     *
     * @return Enterprise_CatalogEvent_Model_Resource_Event_Collection
     */
    public function addCategoryData()
    {
        if (!$this->_categoryDataAdded) {
             $this->getSelect()
                ->joinLeft(array(
                    'category' => $this->getTable('catalog/category')),
                    'category.entity_id = main_table.category_id',
                    array('category_position' => 'position')
                 )
                ->joinLeft(array(
                    'category_name_attribute' => $this->getTable('eav/attribute')),
                    'category_name_attribute.entity_type_id = category.entity_type_id
                    AND category_name_attribute.attribute_code = \'name\'',
                    array()
                )
                ->joinLeft(array(
                    'category_varchar' => $this->getTable(array('catalog/category', 'varchar'))),
                    'category_varchar.entity_id = category.entity_id
                    AND category_varchar.attribute_id = category_name_attribute.attribute_id
                    AND category_varchar.store_id = 0',
                    array('category_name' => 'value')
                );
            $this->_map['fields']['category_name'] = 'category_varchar.value';
            $this->_map['fields']['category_position'] = 'category.position';
            $this->_categoryDataAdded = true;
        }
        return $this;
    }

    /**
     * Add sorting by status.
     * first will be open, then upcoming
     *
     * @return Enterprise_CatalogEvent_Model_Resource_Event_Collection
     */
    public function addSortByStatus()
    {
        $adapter = $this->getConnection();
        $columnExpr = $adapter->quoteInto($this->_getStatusColumnExpr() . ' = ?',
            Enterprise_CatalogEvent_Model_Event::STATUS_OPEN);

        $this->getSelect()
            ->order(array(
                $adapter->getCheckSql($columnExpr, 0, 1) . ' ASC',
                $adapter->getCheckSql($columnExpr, 'main_table.date_end', 'main_table.date_start') . ' ASC',
                'main_table.sort_order ASC')
            );

        return $this;
    }

    /**
     * Add image data
     *
     * @return Enterprise_CatalogEvent_Model_Resource_Event_Collection
     */
    public function addImageData()
    {
        $adapter = $this->getConnection();
        $this->getSelect()->joinLeft(
            array('event_image' => $this->getTable('enterprise_catalogevent/event_image')),
            implode(' AND ', array(
                'event_image.event_id = main_table.event_id',
                $adapter->quoteInto('event_image.store_id = ?', Mage::app()->getStore()->getId())
            )),
            array('image' =>
                $adapter->getCheckSql('event_image.image IS NULL', 'event_image_default.image', 'event_image.image')
            )
        )
        ->joinLeft(
            array('event_image_default' => $this->getTable('enterprise_catalogevent/event_image')),
            'event_image_default.event_id = main_table.event_id
            AND event_image_default.store_id = 0',
            array())
        ->group('main_table.event_id');

//        $this->_useAnalyticFunction = true;

        return $this;
    }

    /**
     * Limit collection by specified category paths
     *
     * @param array $allowedPaths
     * @return Enterprise_CatalogEvent_Model_Resource_Event_Collection
     */
    public function capByCategoryPaths($allowedPaths)
    {
        $this->addCategoryData();
        $paths = array();
        foreach ($allowedPaths as $path) {
            $paths[] = $this->getConnection()->quoteInto('category.path = ?', $path);
            $paths[] = $this->getConnection()->quoteInto('category.path LIKE ?', $path . '/%');
        }
        if ($paths) {
            $this->getSelect()->where(implode(' OR ', $paths));
        }
        return $this;
    }

    /**
     * Override _afterLoad() implementation
     *
     * @return Enterprise_CatalogEvent_Model_Resource_Event_Collection
     */
    protected function _afterLoad()
    {
        $events = parent::_afterLoad();
        foreach ($events->_items as $event) {
            if ($this->_skipClosed && $event->getStatus() == Enterprise_CatalogEvent_Model_Event::STATUS_CLOSED) {
                $this->removeItemByKey($event->getId());
            }
        }
        return $this;
    }

    /**
     * Reset collection
     *
     * @return Enterprise_CatalogEvent_Model_Resource_Event_Collection
     */
    protected function _reset()
    {
        $this->_skipClosed = false;
        return parent::_reset();
    }

    /**
     * Retrieve DB Expression for status column
     *
     * @return Zend_Db_Expr
     */
    protected function _getStatusColumnExpr()
    {
        $adapter    = $this->getConnection();
        $timeNow    = $this->getResource()->formatDate(true);
        $dateStart1 = $adapter->quoteInto('date_start <= ?', $timeNow);
        $dateEnd1   = $adapter->quoteInto('date_end >= ?', $timeNow);
        $dateStart2 = $adapter->quoteInto('date_start > ?', $timeNow);
        $dateEnd2   = $adapter->quoteInto('date_end > ?', $timeNow);

        return $adapter->getCaseSql('',
            array(
                "({$dateStart1} AND {$dateEnd1})" => $adapter->quote(Enterprise_CatalogEvent_Model_Event::STATUS_OPEN),
                "({$dateStart2} AND {$dateEnd2})" => $adapter
                    ->quote(Enterprise_CatalogEvent_Model_Event::STATUS_UPCOMING),
            ),
            $adapter->quote(Enterprise_CatalogEvent_Model_Event::STATUS_CLOSED)
        );
    }

    /**
     * Add status column based on dates
     *
     * @return Enterprise_CatalogEvent_Model_Resource_Event_Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->columns(array('status' => $this->_getStatusColumnExpr()));
        return $this;
    }
}
