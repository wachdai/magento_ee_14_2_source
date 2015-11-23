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
 * Sales archive operations model
 */
class Enterprise_SalesArchive_Model_Archive
{
    const ORDER     = 'order';
    const INVOICE   = 'invoice';
    const SHIPMENT  = 'shipment';
    const CREDITMEMO= 'creditmemo';

    /**
     * Archive entities definition
     * array(
     *  $entity => $entityModel
     * )
     * @var $_entities array
     */
    protected $_entities = array(
        self::ORDER     => 'sales/order',
        self::INVOICE   => 'sales/order_invoice',
        self::SHIPMENT  => 'sales/order_shipment',
        self::CREDITMEMO=> 'sales/order_creditmemo',
    );

    /**
     * Etities data getter
     *
     * @return string | array | false
     */
    public function getEntityModel($entity = null)
    {
        if ($entity) {
            return isset($this->_entities[$entity]) ? $this->_entities[$entity] : false;
        }
        return $this->_entities;
    }

    /**
     * Get archive resource model
     * @return Enterprise_SalesArchive_Model_Mysql4_Archive
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('enterprise_salesarchive/archive');
    }

    /**
     * Update grid records in archive
     *
     * @param string $archiveEntity
     * @param array $ids
     */
    public function updateGridRecords($archiveEntity, $ids)
    {
        $this->_getResource()->updateGridRecords($this, $archiveEntity, $ids);
        return $this;
    }

    /**
     * Retrieve ids in archive for specified entity
     *
     * @param string $archiveEntity
     * @param array $ids
     * @return array
     */
    public function getIdsInArchive($archiveEntity, $ids)
    {
        return $this->_getResource()->getIdsInArchive($archiveEntity, $ids);
    }

    /**
     * Detects archive entity by object class
     *
     * @param Varien_Object $object
     * @return string|boolean
     */
    public function detectArchiveEntity($object)
    {
        foreach ($this->_entities as $archiveEntity => $entityModel) {
            $className = Mage::getConfig()->getModelClassName($entityModel);
            $resourceClassName = Mage::getConfig()->getResourceModelClassName($entityModel);
            if ($object instanceof $className || $object instanceof $resourceClassName) {
                return $archiveEntity;
            }
        }
        return false;
    }

    /**
     * Archive orders
     *
     * @return Enterprise_SalesArchive_Model_Archive
     */
    public function archiveOrders()
    {
        $orderIds = $this->_getResource()->getOrderIdsForArchiveExpression();
        $this->_getResource()->beginTransaction();
        try {
            $this->_getResource()->moveToArchive($this, self::ORDER, 'entity_id', $orderIds);
            $this->_getResource()->moveToArchive($this, self::INVOICE, 'order_id', $orderIds);
            $this->_getResource()->moveToArchive($this, self::SHIPMENT, 'order_id', $orderIds);
            $this->_getResource()->moveToArchive($this, self::CREDITMEMO, 'order_id', $orderIds);
            $this->_getResource()->removeFromGrid($this, self::ORDER, 'entity_id', $orderIds);
            $this->_getResource()->removeFromGrid($this, self::INVOICE, 'order_id', $orderIds);
            $this->_getResource()->removeFromGrid($this, self::SHIPMENT, 'order_id', $orderIds);
            $this->_getResource()->removeFromGrid($this, self::CREDITMEMO, 'order_id', $orderIds);
            $this->_getResource()->commit();
        } catch (Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
        Mage::dispatchEvent(
            'enterprise_salesarchive_archive_archive_orders',
            array('order_ids' => $orderIds)
        );
        return $this;
    }

    /**
     * Archive orders, returns archived order ids
     *
     * @param array $orderIds
     * @return array
     */
    public function archiveOrdersById($orderIds)
    {
        $orderIds = $this->_getResource()->getOrderIdsForArchive($orderIds, false);

        if (!empty($orderIds)) {
            $this->_getResource()->beginTransaction();
            try {
                $this->_getResource()->moveToArchive($this, self::ORDER, 'entity_id', $orderIds);
                $this->_getResource()->moveToArchive($this, self::INVOICE, 'order_id', $orderIds);
                $this->_getResource()->moveToArchive($this, self::SHIPMENT, 'order_id', $orderIds);
                $this->_getResource()->moveToArchive($this, self::CREDITMEMO, 'order_id', $orderIds);
                $this->_getResource()->removeFromGrid($this, self::ORDER, 'entity_id', $orderIds);
                $this->_getResource()->removeFromGrid($this, self::INVOICE, 'order_id', $orderIds);
                $this->_getResource()->removeFromGrid($this, self::SHIPMENT, 'order_id', $orderIds);
                $this->_getResource()->removeFromGrid($this, self::CREDITMEMO, 'order_id', $orderIds);
                $this->_getResource()->commit();
            } catch (Exception $e) {
                $this->_getResource()->rollBack();
                throw $e;
            }
            Mage::dispatchEvent(
                'enterprise_salesarchive_archive_archive_orders',
                array('order_ids' => $orderIds)
            );
        }


        return $orderIds;
    }

    /**
     * Move all orders from archive grid tables to regular grid tables
     *
     * @return Enterprise_SalesArchive_Model_Archive
     */
    public function removeOrdersFromArchive()
    {
        $this->_getResource()->beginTransaction();
        try {
            $this->_getResource()->removeFromArchive($this, self::ORDER);
            $this->_getResource()->removeFromArchive($this, self::INVOICE);
            $this->_getResource()->removeFromArchive($this, self::SHIPMENT);
            $this->_getResource()->removeFromArchive($this, self::CREDITMEMO);
            $this->_getResource()->commit();
        } catch (Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Removes orders from archive and restore in orders grid tables,
     * returns restored order ids
     *
     * @param array $orderIds
     * @return array
     */
    public function removeOrdersFromArchiveById($orderIds)
    {
        $orderIds = $this->_getResource()->getIdsInArchive(self::ORDER, $orderIds);

        if (!empty($orderIds)) {
            $this->_getResource()->beginTransaction();
            try {
                $this->_getResource()->removeFromArchive($this, self::ORDER, 'entity_id', $orderIds);
                $this->_getResource()->removeFromArchive($this, self::INVOICE, 'order_id', $orderIds);
                $this->_getResource()->removeFromArchive($this, self::SHIPMENT, 'order_id', $orderIds);
                $this->_getResource()->removeFromArchive($this, self::CREDITMEMO, 'order_id', $orderIds);
                $this->_getResource()->commit();
            } catch (Exception $e) {
                $this->_getResource()->rollBack();
                throw $e;
            }

            Mage::dispatchEvent(
                'enterprise_salesarchive_archive_remove_orders_from_archive',
                array('order_ids' => $orderIds)
            );
        }

        return $orderIds;
    }

    /**
     * Find related to order entity ids for checking of new items in archive
     *
     * @param string $archiveEntity
     * @param array $ids
     * @return array
     */
    public function getRelatedIds($archiveEntity, $ids)
    {
        return $this->_getResource()->getRelatedIds($this, $archiveEntity, $ids);
    }
}
