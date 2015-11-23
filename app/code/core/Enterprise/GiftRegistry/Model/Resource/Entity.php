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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Gift registry entity resource model
 *
 * @category    Enterprise
 * @package     Enterprise_GiftRegistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftRegistry_Model_Resource_Entity extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Event table name
     *
     * @var string
     */
    protected $_eventTable;

    /**
     * Assigning eventTable
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_giftregistry/entity', 'entity_id');
        $this->_eventTable = $this->getTable('enterprise_giftregistry/data');
    }

    /**
     * Converting some data to internal database format
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $customValues = $object->getCustomValues();
        $object->setCustomValues(serialize($customValues));
        return parent::_beforeSave($object);
    }

    /**
     * Fetching data from event table at same time as from entity table
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $this->_joinEventData($select);

        return $select;
    }

    /**
     * Join event table to select object
     *
     * @param Varien_Db_Select $select
     * @return Varien_Db_Select
     */
    protected function _joinEventData($select)
    {
        $joinCondition = sprintf('e.%1$s = %2$s.%1$s', $this->getIdFieldName(), $this->getMainTable());
        $select->joinLeft(array('e' => $this->_eventTable), $joinCondition, '*');
        return $select;
    }

    /**
     * Perform actions after object is loaded
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $object->setTypeById($object->getData('type_id'));
            $object->setCustomValues(unserialize($object->getCustomValues()));
        }
        return parent::_afterLoad($object);
    }

    /**
     * Perform action after object is saved - saving data to the eventTable
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $data = array();
        foreach ($object->getStaticTypeIds() as $code) {
            $objectData = $object->getData($code);
            if ($objectData) {
                $data[$code] = $objectData;
            }
        }

        if ($object->getId()) {
            $data['entity_id'] = (int)$object->getId();
            $this->_getWriteAdapter()->insertOnDuplicate($this->_eventTable, $data, array_keys($data));
        }
        return parent::_afterSave($object);
    }

    /**
     * Fetches typeId for entity
     *
     * @param int $entityId
     * @return string
     */
    public function getTypeIdByEntityId($entityId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'type_id')
            ->where($this->getIdFieldName() . ' = :entity_id');
        return $this->_getReadAdapter()->fetchOne($select, array(':entity_id' => $entityId));
    }

    /**
     * Fetches websiteId for entity
     *
     * @param int $entityId
     * @return string
     */
    public function getWebsiteIdByEntityId($entityId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'website_id')
            ->where($this->getIdFieldName() . ' = :entity_id');
        return $this->_getReadAdapter()->fetchOne($select,  array(':entity_id' => (int)$entityId));
    }

    /**
     * Set active entity filtered by customer
     *
     * @param int $customerId
     * @param int $entityId
     * @return Enterprise_GiftRegistry_Model_Resource_Entity
     */
    public function setActiveEntity($customerId, $entityId)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->update($this->getMainTable(),
            array('is_active'      => new Zend_Db_Expr('0')),
            array('customer_id =?' => (int)$customerId)
        );
        $adapter->update($this->getMainTable(),
            array('is_active' => new Zend_Db_Expr('1')),
            array('customer_id =?' => (int)$customerId, 'entity_id = ?' => (int)$entityId)
        );
        return $this;
    }

    /**
     * Load entity by gift registry item id
     *
     * @param Enterprise_GiftRegistry_Model_Entity $object
     * @param int $itemId
     * @return Enterprise_GiftRegistry_Model_Resource_Entity
     */
    public function loadByEntityItem($object, $itemId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(array('e' => $this->getMainTable()));
        $select->joinInner(
            array('i' => $this->getTable('enterprise_giftregistry/item')),
            'e.entity_id = i.entity_id AND i.item_id = :item_id',
            array()
        );

        $data = $adapter->fetchRow($select, array(':item_id' => (int)$itemId));
        if ($data) {
            $object->setData($data);
            $this->_afterLoad($object);
        }
        return $this;
    }

    /**
     * Load entity by url key
     *
     * @param Enterprise_GiftRegistry_Model_Entity $object
     * @param string $urlKey
     * @return Enterprise_GiftRegistry_Model_Resource_Entity
     */
    public function loadByUrlKey($object, $urlKey)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where('url_key = :url_key');

        $this->_joinEventData($select);

        $data = $adapter->fetchRow($select, array(':url_key' => $urlKey));
        if ($data) {
            $object->setData($data);
            $this->_afterLoad($object);
        }

        return $this;
    }
}
