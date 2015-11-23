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
 * @package     Enterprise_Catalog
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Enterprise price index observer
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Observer_Price
{
    /**
     * Resource
     *
     * @var Mage_Core_Model_Resource_Abstract
     */
    protected $_resource;

    /**
     * Connection
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_resource = isset($args['resource']) ? $args['resource'] : Mage::getSingleton('core/resource');
        $this->_connection = isset($args['connection'])
            ? $args['connection']
            : $this->_resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
    }

    /**
     * Process catalog inventory item save event
     * Process inventory save event instead catalog product save event due to correlation with stock indexer
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Mview_Model_Client
     */
    public function processStockItemSaveEvent(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('enterprise_cataloginventory/index');
        if ($helper->isLivePriceAndStockReindexEnabled()) {
            $productId = $observer->getEvent()->getItem()->getProductId();
            $client = $this->_getClient($helper->getIndexerConfigValue('catalog_product_price', 'index_table'));
            $arguments = array(
                'value' => $productId,
            );
            $client->execute('enterprise_catalog/index_action_product_price_refresh_row', $arguments);
        }
    }

    /**
     * Process product save event (use for processing product delete)
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Mview_Model_Client
     */
    public function processProductSaveEvent(Varien_Event_Observer $observer)
    {
        /** @var $helper Enterprise_CatalogInventory_Helper_Index */
        $helper = Mage::helper('enterprise_cataloginventory/index');
        if ($helper->isLivePriceAndStockReindexEnabled()) {
            $productId = $observer->getEvent()->getProduct()->getId();
            $client = $this->_getClient($helper->getIndexerConfigValue('catalog_product_price', 'index_table'));
            $arguments = array(
                'value' => $productId,
            );
            $client->execute('enterprise_catalog/index_action_product_price_refresh_row', $arguments);
        }
    }

    /**
     * Refresh product prices after apply catalog price rules
     *
     * @param Varien_Event_Observer $observer
     */
    public function processCatalogPriceRulesApplyEvent(Varien_Event_Observer $observer)
    {
        /** @var $helper Enterprise_CatalogInventory_Helper_Index */
        $helper = Mage::helper('enterprise_cataloginventory/index');
        if ($helper->isLivePriceAndStockReindexEnabled()) {
            $client = $this->_getClient($helper->getIndexerConfigValue('catalog_product_price', 'index_table'));
            $client->execute('enterprise_catalog/index_action_product_price_refresh_changelog');
        }
    }

    /**
     * Process shell reindex catalog product price refresh event
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Mview_Model_Client
     */
    public function processShellProductReindexEvent(Varien_Event_Observer $observer)
    {
        $client = $this->_getClient(
            Mage::helper('enterprise_index')->getIndexerConfigValue('catalog_product_price', 'index_table')
        );
        $client->execute('enterprise_catalog/index_action_product_price_refresh');
    }

    /**
     * Add products to changelog with price which depends on date
     *
     * @return void
     */
    public function refreshSpecialPrices()
    {
        $connection = $this->_connection;
        foreach (Mage::app()->getStores(true) as $store) {
            $timestamp = Mage::app()->getLocale()->storeTimeStamp($store);
            $currDate = Varien_Date::formatDate($timestamp, false);
            $currDateExpr = $connection->quote($currDate);

            // timestamp is locale based
            if (date(Zend_Date::HOUR_SHORT, $timestamp) == '00') {
                $format = '%Y-%m-%d %H:%i:%s';
                $this->_refreshSpecialPriceByStore(
                    $store->getId(), 'special_from_date', $connection->getDateFormatSql($currDateExpr, $format)
                );

                $dateTo = $connection->getDateAddSql($currDateExpr, -1, Varien_Db_Adapter_Interface::INTERVAL_DAY);
                $this->_refreshSpecialPriceByStore(
                    $store->getId(), 'special_to_date', $connection->getDateFormatSql($dateTo, $format)
                );
            }

        }
    }

    /**
     * Add products to changelog by conditions
     *
     * @param int $storeId
     * @param string $attrCode
     * @param Zend_Db_Expr $attrConditionValue
     */
    protected function _refreshSpecialPriceByStore($storeId, $attrCode, $attrConditionValue)
    {
        $attribute   = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode);
        $attributeId = $attribute->getAttributeId();

        $select = $this->_connection->select()
            ->from($this->_getTable(array('catalog/product', 'datetime')), array('entity_id'))
            ->where('attribute_id = ?', $attributeId)
            ->where('store_id = ?', $storeId)
            ->where('value = ?', $attrConditionValue);

        $client = $this->_getClient(
            Mage::helper('enterprise_index')->getIndexerConfigValue('catalog_product_price', 'index_table')
        );
        $query = $select->insertFromSelect($client->getMetadata()->changelog_name, array('entity_id'), false);
        $this->_connection->query($query);
    }

    /**
     * Returns table name for given entity
     * @param $entityName
     * @return string
     */
    protected function _getTable($entityName)
    {
        return $this->_resource->getTableName($entityName);
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
}
