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
 * @package     Enterprise_CatalogInventory
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Enterprise stock index observer
 *
 * @category    Enterprise
 * @package     Enterprise_CatalogInventory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CatalogInventory_Model_Index_Observer extends Mage_CatalogInventory_Model_Observer
{
    /**
     * Process catalog inventory item save event
     *
     * @param Varien_Event_Observer $observer
     */
    public function processStockItemSaveEvent(Varien_Event_Observer $observer)
    {
        /** @var $helper Enterprise_CatalogInventory_Helper_Index */
        $helper = Mage::helper('enterprise_cataloginventory/index');
        if ($helper->isLivePriceAndStockReindexEnabled()) {
            $productId = $observer->getEvent()->getItem()->getProductId();

            // reindex stock
            $client = $this->_getClient($helper->getIndexerConfigValue('cataloginventory_stock', 'index_table'));
            $arguments = array(
                'value' => $productId,
            );
            $client->execute('enterprise_cataloginventory/index_action_refresh_row', $arguments);
        }
    }

    /**
     * Process shell reindex catalog product price refresh event
     *
     * @param Varien_Event_Observer $observer
     */
    public function processShellProductReindexEvent(Varien_Event_Observer $observer)
    {
        $client = $this->_getClient(
            Mage::helper('enterprise_index')->getIndexerConfigValue('cataloginventory_stock', 'index_table')
        );
        $client->execute('enterprise_cataloginventory/index_action_refresh');
    }

    /**
     * Get client
     *
     * @param string $metadataTableName
     * @return Enterprise_Mview_Model_Client
     */
    protected function _getClient($metadataTableName)
    {
        $client = Mage::getModel('enterprise_mview/client');
        $client->init($metadataTableName);
        return $client;
    }

    /**
     * Refresh stock index for specific stock items after succesful order placement
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogInventory_Model_Index_Observer
     */
    public function reindexQuoteInventory($observer)
    {
        foreach ($this->_itemsForReindex as $item) {
            $item->save();
        }
        $this->_itemsForReindex = array(); // Clear list of remembered items - we don't need it anymore
        return $this;
    }

    /**
     * Execute inventory index operations.
     *
     * @param Varien_Event_Observer $observer
     */
    public function processUpdateWebsiteForProduct(Varien_Event_Observer $observer)
    {
        /** @var $helper Enterprise_CatalogInventory_Helper_Index */
        $helper = Mage::helper('enterprise_cataloginventory/index');
        if (!$helper->isLivePriceAndStockReindexEnabled()) {
            return;
        }

        $client = $this->_getClient(
            Mage::helper('enterprise_index')->getIndexerConfigValue('cataloginventory_stock', 'index_table')
        );
        $client->execute('enterprise_cataloginventory/index_action_refresh_changelog');
    }
}
