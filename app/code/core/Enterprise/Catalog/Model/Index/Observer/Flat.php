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
 * Enterprise flat index observer
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method Enterprise_Index_Model_Metadata getMetadata()
 */
class Enterprise_Catalog_Model_Index_Observer_Flat
{
    /**
     * Path to flat product indexer mode
     */
    const XML_PATH_LIVE_PRODUCT_REINDEX_ENABLED = 'index_management/index_options/product_flat';

    /**
     * Path to flat category indexer mode
     */
    const XML_PATH_LIVE_CATEGORY_REINDEX_ENABLED = 'index_management/index_options/category_flat';

    /**
     * Process product save event
     *
     * @param Varien_Event_Observer $observer
     */
    public function processProductSaveEvent(Varien_Event_Observer $observer)
    {
        /** @var $helper Enterprise_Index_Helper_Data */
        $helper      = Mage::helper('enterprise_index');
        if ($this->_isLiveProductReindexEnabled()) {
            $productId = $observer->getEvent()->getProduct()->getId();
            $client = $this->_getClient($helper->getIndexerConfigValue('catalog_product_flat', 'index_table'));
            $arguments = array(
                'value'      => $productId,
            );
            $client->execute('enterprise_catalog/index_action_product_flat_refresh_row', $arguments);
        }
    }

    /**
     * Process shell reindex catalog product flat refresh event
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Mview_Model_Client
     */
    public function processShellProductReindexEvent(Varien_Event_Observer $observer)
    {
        $client = $this->_getClient(
            Mage::helper('enterprise_index')->getIndexerConfigValue('catalog_product_flat', 'index_table')
        );
        return $client->execute('enterprise_catalog/index_action_product_flat_refresh');
    }

    /**
     * Process shell reindex catalog category flat refresh event
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Mview_Model_Client
     */
    public function processShellCategoryReindexEvent(Varien_Event_Observer $observer)
    {
        $client = $this->_getClient(
            Mage::helper('enterprise_index')->getIndexerConfigValue('catalog_category_flat', 'index_table')
        );
        return $client->execute('enterprise_catalog/index_action_category_flat_refresh');
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
     * Process category save event
     *
     * @param Varien_Event_Observer $observer
     */
    public function processCategorySaveEvent(Varien_Event_Observer $observer)
    {
        if ($this->_isLiveCategoryReindexEnabled()) {
            $categoryId = $observer->getEvent()->getCategory()->getId();
            $client = $this->_getClient(
                Mage::helper('enterprise_index')->getIndexerConfigValue('catalog_category_flat', 'index_table')
            );
            $arguments = array(
                'value' => $categoryId
            );
            $client->execute('enterprise_catalog/index_action_category_flat_refresh_row', $arguments);
        }
    }

    /**
     * Process category move event
     *
     * @param Varien_Event_Observer $observer
     */
    public function processCategoryMoveEvent(Varien_Event_Observer $observer)
    {
        if ($this->_isLiveCategoryReindexEnabled()) {
            $client = $this->_getClient(
                Mage::helper('enterprise_index')->getIndexerConfigValue('catalog_category_flat', 'index_table')
            );
            $client->execute('enterprise_catalog/index_action_category_flat_refresh_changelog');
        }
    }

    /**
     * Retrieves flat product indexer mode
     *
     * @return boolean
     */
    protected function _isLiveProductReindexEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_LIVE_PRODUCT_REINDEX_ENABLED);
    }

    /**
     * Retrieves flat category indexer mode
     *
     * @return boolean
     */
    protected function _isLiveCategoryReindexEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_LIVE_CATEGORY_REINDEX_ENABLED);
    }

    /**
     * Executes product re-index after mass update action
     *
     * @param Varien_Event_Observer $observer
     */
    public function processMassUpdateAction(Varien_Event_Observer $observer)
    {
        if (!$this->_isLiveProductReindexEnabled()) {
            return;
        }

        /** @var $action Mage_Adminhtml_Catalog_Product_Action_AttributeController */
        $action = $observer->getControllerAction();
        $attributesData     = $action->getRequest()->getParam('attributes', array());
        $websiteRemoveData  = $action->getRequest()->getParam('remove_website_ids', array());
        $websiteAddData     = $action->getRequest()->getParam('add_website_ids', array());
        $productIds = Mage::helper('adminhtml/catalog_product_edit_action_attribute')->getProductIds();
        if (($attributesData || $websiteAddData || $websiteRemoveData) && !empty($productIds)) {
            try {
                /** @var $helper Enterprise_Index_Helper_Data */
                $helper      = Mage::helper('enterprise_index');
                $client = $this->_getClient($helper->getIndexerConfigValue('catalog_product_flat', 'index_table'));
                $arguments = array(
                    'value'      => $productIds,
                );
                $client->execute('enterprise_catalog/index_action_product_flat_refresh_rows', $arguments);
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('enterprise_catalog')->__('An error occured while reindexing the products.')
                );
            }
        }
    }

    /**
     * Executes product re-index after mass status update
     *
     * @param Varien_Event_Observer $observer
     */
    public function processMassStatusUpdate(Varien_Event_Observer $observer)
    {
        if ($this->_isLiveCategoryReindexEnabled()) {
            $productIds = $observer->getEvent()->getProductIds();
            if (!empty($productIds)) {
                try {
                    /** @var $helper Enterprise_Index_Helper_Data */
                    $helper      = Mage::helper('enterprise_index');
                    $client = $this->_getClient($helper->getIndexerConfigValue('catalog_product_flat', 'index_table'));
                    $arguments = array(
                        'value'      => $productIds,
                    );
                    $client->execute('enterprise_catalog/index_action_product_flat_refresh_rows', $arguments);
                } catch (Exception $e) {
                    Mage::logException($e);
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('enterprise_catalog')->__('An error occured while reindexing the products.')
                    );
                }
            }
        }
    }
}
