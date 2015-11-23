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
 * Url Rewrite Catalog observer
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Observer
{
    /**
     * Update on save category url rewrite xml path.
     */
    const XML_PATH_CATEGORY_URL_SUFFIX_UPDATE_ON_SAVE = 'default/index_management/index_options/category_url_rewrite';

    /**
     * Update on save product url rewrite xml path.
     */
    const XML_PATH_PRODUCT_URL_SUFFIX_UPDATE_ON_SAVE = 'default/index_management/index_options/product_url_rewrite';

    /**
     * Factory instance
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Initialize factory and config instances
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('core/factory');
        $this->_config = !empty($args['config']) ? $args['config'] : Mage::getConfig();
    }

    /**
     * Refresh url rewrite for given category
     *
     * @param Varien_Event_Observer $observer
     */
    public function refreshCategoryUrlRewrite(Varien_Event_Observer $observer)
    {
        if (!$this->_isUpdateOnSaveCategoryUrlRewriteFlag()) {
            return;
        }

        /** @var $category Mage_Catalog_Model_Category */
        $category = $observer->getEvent()->getCategory();
        // don't reindex on save if url key was changed or category was moved (for avoid 404 pages between cron runs)
        if ($category->getData('save_rewrites_history')) {
            return;
        }
        $affectedIds = $category->getAffectedCategoryIds();
        if (!is_object($category)
            || !$category->getId()
            || (!$category->dataHasChangedFor('url_key') && empty($affectedIds))
        ) {
            return;
        }

        $this->_executeCategoryRefreshRowAction($category);
    }

    /**
     * Delete url rewrite for a given category
     *
     * @param Varien_Event_Observer $observer
     */
    public function deleteCategoryUrlRewrite(Varien_Event_Observer $observer)
    {
        if (!$this->_isUpdateOnSaveCategoryUrlRewriteFlag()) {
            return;
        }

        /** @var $category Mage_Catalog_Model_Category */
        $category = $observer->getEvent()->getCategory();
        if (!is_object($category) || !$category->getId()) {
            return;
        }

        $this->_executeCategoryRefreshRowAction($category);
    }

    /**
     * Refresh url rewrite for a given product
     *
     * @param Varien_Event_Observer $observer
     */
    public function refreshProductUrlRewrite(Varien_Event_Observer $observer)
    {
        if (!$this->_isUpdateOnSaveProductUrlRewriteFlag()) {
            return;
        }

        /** @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();
        if (!is_object($product) || !$product->getId() || !$product->dataHasChangedFor('url_key')) {
            return;
        }

        $this->_executeProductRefreshRowAction($product);
    }

    /**
     * Delete url rewrite for given product
     *
     * @param Varien_Event_Observer $observer
     */
    public function deleteProductUrlRewrite(Varien_Event_Observer $observer)
    {
        if (!$this->_isUpdateOnSaveProductUrlRewriteFlag()) {
            return;
        }

        /** @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();
        if (!is_object($product) || !$product->getId()) {
            return;
        }

        $this->_executeProductRefreshRowAction($product);
    }

    /**
     * Execute product refresh action which refresh url_rewrite index
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _executeProductRefreshRowAction(Mage_Catalog_Model_Product $product)
    {
        /** @var $client Enterprise_Mview_Model_Client */
        $client = $this->_factory->getModel('enterprise_mview/client')->init('enterprise_url_rewrite_product');
        $client->execute('enterprise_catalog/index_action_url_rewrite_product_refresh_row', array(
            'product_id' => $product->getId()
        ));
    }

    /**
     * Execute category refresh action which refresh url_rewrite index
     *
     * @param Mage_Catalog_Model_Category $category
     */
    protected function _executeCategoryRefreshRowAction(Mage_Catalog_Model_Category $category)
    {
        /** @var $client Enterprise_Mview_Model_Client */
        $client = $this->_factory->getModel('enterprise_mview/client')->init('enterprise_url_rewrite_category');
        $client->execute('enterprise_catalog/index_action_url_rewrite_category_refresh_row', array(
            'category_id' => $category->getId()
        ));
    }

    /**
     * Return category url rewrite flag.
     *
     * Returns configuration flag which says whether or not category url rewrite should be updated once
     * save operation is triggered.
     *
     * @return bool
     */
    protected function _isUpdateOnSaveCategoryUrlRewriteFlag()
    {
        return (bool)(string)$this->_config->getNode(self::XML_PATH_CATEGORY_URL_SUFFIX_UPDATE_ON_SAVE);
    }

    /**
     * Return product url rewrite flag.
     *
     * Returns configuration flag which says whether or not product url rewrite should be updated once
     * save operation is triggered.
     *
     * @return bool
     */
    protected function _isUpdateOnSaveProductUrlRewriteFlag()
    {
        return (bool)(string)$this->_config->getNode(self::XML_PATH_PRODUCT_URL_SUFFIX_UPDATE_ON_SAVE);
    }
}
