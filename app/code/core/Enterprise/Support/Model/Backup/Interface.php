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
 * @package     Enterprise_Support
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

interface Enterprise_Support_Model_Backup_Interface
{
    /**
     * Add Item
     *
     * @param Enterprise_Support_Model_Backup_Item_Abstract $item
     *
     * @return Enterprise_Support_Model_Backup_Interface
     */
    public function addItem(Enterprise_Support_Model_Backup_Item_Abstract $item);

    /**
     * Get Items
     *
     * @return array
     */
    public function getItems();

    /**
     * Get Item
     *
     * @param $key
     *
     * @return Enterprise_Support_Model_Backup_Item_Abstract|bool
     */
    public function getItem($key);

    /**
     * Run commands from each items
     *
     * @return string
     */
    public function run();

    /**
     * Validate
     */
    public function validate();

    /**
     * Update Status Backup
     */
    public function updateStatus();
}
