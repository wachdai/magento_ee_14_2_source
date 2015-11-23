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
 * @package     Enterprise_Logging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Logging event resource model
 *
 * @category    Enterprise
 * @package     Enterprise_Logging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Logging_Model_Resource_Event extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_logging/event', 'log_id');
    }

    /**
     * Convert data before save ip
     *
     * @param Mage_Core_Model_Abstract $event
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $event)
    {
        $event->setData('ip', inet_pton($event->getIp()));
        $event->setTime($this->formatDate($event->getTime()));
    }

    /**
     * Rotate logs - get from database and pump to CSV-file
     *
     * @param int $lifetime
     */
    public function rotate($lifetime)
    {
//        $this->beginTransaction();
//        try {
            $readAdapter  = $this->_getReadAdapter();
            $writeAdapter = $this->_getWriteAdapter();
            $table = $this->getTable('enterprise_logging/event');

            // get the latest log entry required to the moment
            $clearBefore = $this->formatDate(time() - $lifetime);

            $select = $readAdapter->select()
                ->from($this->getMainTable(), 'log_id')
                ->where('time < ?', $clearBefore)
                ->order('log_id DESC')
                ->limit(1);
            $latestLogEntry = $readAdapter->fetchOne($select);
            if ($latestLogEntry) {
                // make sure folder for dump file will exist
                $archive = Mage::getModel('enterprise_logging/archive');
                $archive->createNew();

                $expr = Mage::getResourceHelper('enterprise_logging')->getInetNtoaExpr('ip');
                $select = $readAdapter->select()
                    ->from($this->getMainTable())
                    ->where('log_id <= ?', $latestLogEntry)
                    ->columns($expr);

                $rows = $readAdapter->fetchAll($select);

                // dump all records before this log entry into a CSV-file
                $csv = fopen($archive->getFilename(), 'w');
                foreach ($rows as $row) {
                    fputcsv($csv, $row);
                }
                fclose($csv);

                $writeAdapter->delete($this->getMainTable(), array('log_id <= ?' => $latestLogEntry));
//                $this->commit();
            }
//        } catch (Exception $e) {
//            $this->rollBack();
//        }
    }

    /**
     * Select all values of specified field from main table
     *
     * @param string $field
     * @param bool $order
     * @return array
     */
    public function getAllFieldValues($field, $order = true)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->distinct(true)
            ->from($this->getMainTable(), $field);
        if (!is_null($order)) {
            $select->order($field . ($order ? '' : ' DESC'));
        }
        return $adapter->fetchCol($select);
    }

    /**
     * Get all admin usernames that are currently in event log table
     * Possible SQL-performance issue
     *
     * @return array
     */
    public function getUserNames()
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->distinct()
            ->from(array('admins' => $this->getTable('admin/user')), 'username')
            ->joinInner(
                array('events' => $this->getTable('enterprise_logging/event')),
                'admins.username = events.' . $adapter->quoteIdentifier('user'),
                array());
        return $adapter->fetchCol($select);
    }

    /**
     * Get event change ids of specified event
     *
     * @param int $eventId
     * @return array
     */
    public function getEventChangeIds($eventId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getTable('enterprise_logging/event_changes'), array('id'))
            ->where('event_id = ?', $eventId);
        return $adapter->fetchCol($select);
    }
}
