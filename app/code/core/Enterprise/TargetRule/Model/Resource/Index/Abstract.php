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
 * @package     Enterprise_TargetRule
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * TargetRule Product List Abstract Indexer Resource Model
 *
 * @category    Enterprise
 * @package     Enterprise_TargetRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_TargetRule_Model_Resource_Index_Abstract extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Product List Type identifier
     *
     * @var int
     */
    protected $_listType;

    /**
     * Retrieve Product List Type identifier
     *
     * @throws Mage_Core_Exception
     *
     * @return int
     */
    public function getListType()
    {
        if (is_null($this->_listType)) {
            Mage::throwException(
                Mage::helper('enterprise_targetrule')->__('Product list type identifier does not defined')
            );
        }
        return $this->_listType;
    }

    /**
     * Set Product List identifier
     *
     * @param int $listType
     * @return Enterprise_TargetRule_Model_Resource_Index_Abstract
     */
    public function setListType($listType)
    {
        $this->_listType = $listType;
        return $this;
    }

    /**
     * Retrieve Product Resource instance
     *
     * @return Mage_Catalog_Model_Resource_Product
     */
    public function getProductResource()
    {
        return Mage::getResourceSingleton('catalog/product');
    }

    /**
     * Retrieve Product Table Name
     *
     * @return string
     */
    public function getProductTable()
    {
        if (empty($this->_mainTable)) {
            Mage::throwException(Mage::helper('core')->__('Empty main table name'));
        }
        return $this->getTable($this->_mainTable . '_product');
    }

    /**
     * Loads product IDs by Index object and customer segment ID
     *
     * @param Enterprise_TargetRule_Model_Index $object
     * @param int $segmentId
     * @return array
     */
    public function loadProductIdsBySegmentId($object, $segmentId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('i' => $this->getMainTable()), array())
            ->joinInner(
                array('p' => $this->getProductTable()),
                'i.targetrule_id = p.targetrule_id',
                'product_id'
            )
            ->where('i.entity_id = ?', $object->getProduct()->getEntityId())
            ->where('i.store_id = ?', $object->getStoreId())
            ->where('i.customer_group_id = ?', $object->getCustomerGroupId())
            ->where('i.customer_segment_id = ?', $segmentId);

        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Load Product Ids by Index object
     *
     * @param Enterprise_TargetRule_Model_Index $object
     * @return array
     * @deprecated after 1.12.0.0
     */
    public function loadProductIds($object)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'product_ids')
            ->where('entity_id = :entity_id')
            ->where('store_id = :store_id')
            ->where('customer_group_id = :customer_group_id');
        $bind = array(
            ':entity_id'         => $object->getProduct()->getEntityId(),
            ':store_id'          => $object->getStoreId(),
            ':customer_group_id' => $object->getCustomerGroupId()
        );
        $value  = $this->_getReadAdapter()->fetchOne($select, $bind);
        if (!empty($value)) {
            $productIds = explode(',', $value);
        } else {
            $productIds = array();
        }

        return $productIds;
    }

    /**
     * Clean products off the index
     *
     * @param int $targetruleId
     * @return Enterprise_TargetRule_Model_Resource_Index_Abstract
     */
    public function deleteIndexProducts($targetruleId)
    {
        $this->_getWriteAdapter()->delete($this->getProductTable(), array('targetrule_id = ?' => $targetruleId));

        return $this;
    }

    /**
     * Save product IDs for index
     *
     * @param int $targetruleId
     * @param string|array $productIds
     * @return Enterprise_TargetRule_Model_Resource_Index_Abstract
     */
    public function saveIndexProducts($targetruleId, $productIds)
    {
        if (is_string($productIds)) {
            $productIds = explode(',', $productIds);
        }

        if (count($productIds) > 0) {
            $data = array();
            foreach ($productIds as $productId) {
                $data[] = array(
                    'targetrule_id' => $targetruleId,
                    'product_id'    => $productId,
                );
            }
            $this->_getWriteAdapter()->insertMultiple($this->getProductTable(), $data);
        }

        return $this;
    }

    /**
     * Save matched product Ids by customer segments
     *
     * @param Enterprise_TargetRule_Model_Index $object
     * @param int $segmentId
     * @param string|array $productIds
     * @return Enterprise_TargetRule_Model_Resource_Index_Abstract
     */
    public function saveResultForCustomerSegments($object, $segmentId, $productIds)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'targetrule_id')
            ->where('entity_id = ?', $object->getProduct()->getEntityId())
            ->where('store_id = ?', $object->getStoreId())
            ->where('customer_group_id = ?', $object->getCustomerGroupId())
            ->where('customer_segment_id = ?', $segmentId);
        $targetruleId = $this->_getReadAdapter()->fetchOne($select);
        if (!$targetruleId) {
            $data = array(
                'entity_id'           => $object->getProduct()->getEntityId(),
                'store_id'            => $object->getStoreId(),
                'customer_group_id'   => $object->getCustomerGroupId(),
                'customer_segment_id' => $segmentId,
            );
            $this->_getWriteAdapter()->insert($this->getMainTable(), $data);
            $targetruleId = $this->_getWriteAdapter()->lastInsertId();
        } else {
            $this->deleteIndexProducts($targetruleId);
        }
        $this->saveIndexProducts($targetruleId, $productIds);

        return $this;
    }

    /**
     * Save matched product Ids
     *
     * @param Enterprise_TargetRule_Model_Index $object
     * @param string $value
     * @return Enterprise_TargetRule_Model_Resource_Index_Abstract
     * @deprecated after 1.12.0.0
     */
    public function saveResult($object, $value)
    {
        $adapter = $this->_getWriteAdapter();
        $data    = array(
            'entity_id'         => $object->getProduct()->getEntityId(),
            'store_id'          => $object->getStoreId(),
            'customer_group_id' => $object->getCustomerGroupId(),
            'product_ids'       => $value
        );

        $adapter->insertOnDuplicate($this->getMainTable(), $data, array('product_ids'));

        return $this;
    }

    /**
     * Remove index by product ids
     *
     * @param Varien_Db_Select|array $entityIds
     * @return Enterprise_TargetRule_Model_Resource_Index_Abstract
     */
    public function removeIndex($entityIds)
    {
        $this->_getWriteAdapter()->delete($this->getMainTable(), array(
            'entity_id IN (?)' => $entityIds
        ));

        return $this;
    }

    /**
     * Remove all data from index
     *
     * @param Mage_Core_Model_Store|int|array $store
     * @return Enterprise_TargetRule_Model_Resource_Index_Abstract
     */
    public function cleanIndex($store = null)
    {
        if (is_null($store)) {
            $this->_getWriteAdapter()->delete($this->getMainTable());
            return $this;
        }
        if ($store instanceof Mage_Core_Model_Store) {
            $store = $store->getId();
        }
        $where = array('store_id IN(?)' => $store);
        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);

        return $this;
    }
}
