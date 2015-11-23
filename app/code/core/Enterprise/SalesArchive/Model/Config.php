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
 * Order archive config model
 *
 */
class Enterprise_SalesArchive_Model_Config
{
    const XML_PATH_ARCHIVE_ACTIVE = 'sales/enterprise_salesarchive/active';
    const XML_PATH_ARCHIVE_AGE = 'sales/enterprise_salesarchive/age';
    const XML_PATH_ARCHIVE_ORDER_STATUSES = 'sales/enterprise_salesarchive/order_statuses';

    /**
     * Check archiving activity
     *
     * @return boolean
     */
    public function isArchiveActive()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ARCHIVE_ACTIVE);
    }

    /**
     * Retrieve archive age
     *
     * @return int
     */
    public function getArchiveAge()
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_ARCHIVE_AGE);
    }

    /**
     * Retrieve order statuses for archiving
     *
     * @return array
     */
    public function getArchiveOrderStatuses()
    {
        $statuses = Mage::getStoreConfig(self::XML_PATH_ARCHIVE_ORDER_STATUSES);

        if (empty($statuses)) {
            return array();
        }

        return explode(',', $statuses);
    }

    /**
     * Check order archiveablility for single archiving
     *
     * @param Mage_Sales_Model_Order $order
     * @param boolean $checkAge check order age for archive
     * @return boolean
     */
    public function isOrderArchiveable($order, $checkAge = false)
    {
        if (in_array($order->getStatus(), $this->getArchiveOrderStatuses())) {
            if ($checkAge) {
                $now = Mage::app()->getLocale()->storeDate();
                $updated = Mage::app()->getLocale()->storeDate($order->getUpdatedAt());

            }

            return true;
        }

        return false;
    }
}
