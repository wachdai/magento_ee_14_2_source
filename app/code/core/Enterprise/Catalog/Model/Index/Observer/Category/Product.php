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
 * Enterprise index observer
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Observer_Category_Product
{
    /**
     * Path to category/product indexer mode
     */
    const XML_PATH_LIVE_CATEGORY_PRODUCT_REINDEX_ENABLED = 'index_management/index_options/category_product';

    /**
     * Factory instance
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Store instance
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'factory' Mage_Core_Model_Factory
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('core/factory');
        $this->_store = !empty($args['store']) ? $args['store'] : Mage::app()->getStore();
    }

    /**
     * Process category/product refresh upon product save event
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Catalog_Model_Index_Observer_Category_Product
     */
    public function processProductSaveEvent(Varien_Event_Observer $observer)
    {
        if (!$this->_isLiveCategoryProductReindexEnabled()) {
            return $this;
        }

        //Category/Product refresh
        $client = $this->_getClient('catalog_category_product_index');
        $client->execute('enterprise_catalog/index_action_catalog_category_product_refresh_row', array(
            'value' => $observer->getEvent()->getProduct()->getId(),
        ));

        return $this;
    }

    /**
     * Execute price and category product index operations.
     *
     * @param Varien_Event_Observer $observer
     */
    public function processUpdateWebsiteForProduct(Varien_Event_Observer $observer)
    {
        if (!$this->_isLiveCategoryProductReindexEnabled()) {
            return;
        }

        $client = $this->_getClient(
            Mage::helper('enterprise_index')->getIndexerConfigValue('catalog_product_price', 'index_table')
        );
        $client->execute('enterprise_catalog/index_action_product_price_refresh_changelog');

        $client = $this->_getClient('catalog_category_product_index');
        $client->execute('enterprise_catalog/index_action_catalog_category_product_refresh_changelog');
    }

    /**
     * Retrieves category/product indexer mode
     *
     * @return boolean
     */
    protected function _isLiveCategoryProductReindexEnabled()
    {
        return (bool)(string)$this->_store->getConfig(self::XML_PATH_LIVE_CATEGORY_PRODUCT_REINDEX_ENABLED);
    }

    /**
     * Process category/product refresh upon category save event
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Catalog_Model_Index_Observer_Category_Product
     */
    public function processCategorySaveEvent(Varien_Event_Observer $observer)
    {
        /** @var $category Mage_Catalog_Model_Category */
        $category = $observer->getEvent()->getCategory();
        $categoryId = $category->getId();
        if (!$this->_isLiveCategoryProductReindexEnabled() || $categoryId == Mage_Catalog_Model_Category::TREE_ROOT_ID)
        {
            return $this;
        }
        $parentPathIds = array();
        /** @var $parent Mage_Catalog_Model_Category */
        $parent = $observer->getEvent()->getParent();
        if ($parent instanceof Mage_Catalog_Model_Category) {
            $parentPathIds = $parent->getPathIds();
        }
        //Category/Product refresh
        $client = $this->_getClient('catalog_category_product_cat');
        $client->execute('enterprise_catalog/index_action_catalog_category_product_category_refresh_row', array(
            'value' => array_merge($parentPathIds, $category->getPathIds(), array($categoryId))
        ));

        return $this;
    }

    /**
     * Process shell reindex catalog category/product refresh event
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Catalog_Model_Index_Observer_Category_Product
     */
    public function processShellCategoryProductReindexEvent(Varien_Event_Observer $observer)
    {
        $client = $this->_getClient('catalog_category_product_index');
        $client->execute('enterprise_catalog/index_action_catalog_category_product_refresh');

        return $this;
    }

    /**
     * Get client
     *
     * @param string $metadataTableName
     * @return Enterprise_Mview_Model_Client
     */
    protected function _getClient($metadataTableName)
    {
        /** @var $client Enterprise_Mview_Model_Client */
        $client = $this->_factory->getModel('enterprise_mview/client', array(array('factory' => $this->_factory)));
        $client->init($metadataTableName);
        return $client;
    }
}
