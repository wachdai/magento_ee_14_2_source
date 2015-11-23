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
 * Catalog event listener class
 * The following events are used:
 *  -
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Observer
{
    /**
     * Base category target path.
     */
    const BASE_CATEGORY_TARGET_PATH = 'catalog/category/view/id/%d';

    /**
     * Base product target path.
     */
    const BASE_PRODUCT_TARGET_PATH  = 'catalog/product/view/id/%d';

    /**
     * Base path for product in category
     */
    const BASE_PRODUCT_CATEGORY_TARGET_PATH = 'catalog/product/view/id/%d/category/%d';

    /**
     * Factory instance
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * App model
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Constructor with parameters.
     *
     * Array of arguments with keys:
     *  - 'factory' Enterprise_Mview_Model_Factory
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('core/factory');
        $this->_app     = !empty($args['app']) ? $args['app'] : Mage::app();
    }

    /**
     * Save custom redirect for product
     *
     * @param Varien_Event_Observer $observer
     * @return void|null
     */
    public function saveCategoryCustomRedirect(Varien_Event_Observer $observer)
    {
        /** @var $category Mage_Catalog_Model_Category */
        $category = $observer->getEvent()->getCategory();
        if (!$category->getSaveRewritesHistory()) {
            return null;
        }
        $resource = $this->_factory->getModel('enterprise_catalog/category_redirect');
        $resource->saveCustomRedirects($category, $category->getStoreId());
    }

    /**
     * Save custom redirect for product
     *
     * @param Varien_Event_Observer $observer
     * @return void|null
     */
    public function saveProductCustomRedirect(Varien_Event_Observer $observer)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = clone $observer->getEvent()->getProduct();
        $originalStore = $product->getStoreId();

        if (!$product->getSaveRewritesHistory()) {
            return null;
        }

        if (Mage_Core_Model_Store::DEFAULT_CODE == $product->getStoreId()) {
            $storesToProcess = $product->getStoreIds();
        } else {
            $storesToProcess = array($product->getStoreId());
        }

        foreach ($storesToProcess as $storeId) {
            if ($storeId != Mage_Core_Model_Store::DEFAULT_CODE) {
                $product->setStoreId($storeId)->load($product->getId());

                if (($originalStore !== $storeId) && $product->getExistsStoreValueFlag('url_key')) {
                    continue;
                }
            }
            //process canonical urls withoput category
            $this->_createRedirectForProduct($product, $storeId);
            foreach ($product->getCategoryIds() as $categoryId) {
                $this->_createRedirectForProduct($product, $storeId, $categoryId);
            }
        }
    }

    /**
     * Create custom redirect for product in store and category
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $storeId
     * @param int|null $categoryId
     */
    protected function _createRedirectForProduct($product, $storeId, $categoryId = null)
    {
        /** @var $helper Enterprise_Catalog_Helper_Data */
        $helper = $this->_factory->getHelper('enterprise_catalog');

        $requestPath = $helper->getProductRequestPath($product->getRequestPath(), $storeId, $categoryId);
        if (!empty($requestPath)) {
            /** @var $redirect Enterprise_UrlRewrite_Model_Redirect */
            $redirect = $this->_factory->getModel('enterprise_urlrewrite/redirect')
                ->setIdentifier($requestPath)
                ->setTargetPath($this->_getProductTargetPath($product->getId(), $categoryId))
                ->setStoreId($storeId)
                ->setProductId($product->getId());
            if (null !== $categoryId) {
                $redirect->setCategoryId($categoryId);
            }

            if (!$redirect->exists()) {
                $redirect->save();
            }
        }
    }

    /**
     * @param int $productId
     * @param int|null $categoryId
     * @return string
     */
    protected function _getProductTargetPath($productId, $categoryId = null)
    {
        return empty($categoryId) ?
            sprintf(self::BASE_PRODUCT_TARGET_PATH, $productId) :
            sprintf(self::BASE_PRODUCT_CATEGORY_TARGET_PATH, $productId, $categoryId);
    }

    /**
     * Get product request path
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     * @deprecated since 1.13.0.2
     */
    protected function _getProductRequestPath(Mage_Catalog_Model_Product $product)
    {
        /**
         * Initialize request_path value
         */
        $product->getProductUrl();

        /** @var $helper Enterprise_Catalog_Helper_Data */
        $helper = $this->_factory->getHelper('enterprise_catalog');
        return $helper->getProductRequestPath($product->getRequestPath(), $product->getStoreId());
    }

    /**
     * Get category request path
     *
     * @param Mage_Catalog_Model_Category $category
     * @return string
     * @deprecated since 1.13.0.2
     */
    protected function _getCategoryRequestPath(Mage_Catalog_Model_Category $category)
    {
        /**
         * Initialize request_path value
         */
        $category->getUrl();

        /** @var $helper Enterprise_Catalog_Helper_Data */
        $helper = $this->_factory->getHelper('enterprise_catalog');
        return $helper->getCategoryRequestPath($category->getRequestPath(), $category->getStoreId());
    }

    /**
     * Save request and target paths via redirect model
     *
     * @param string $requestPath
     * @param string $targetPath
     * @return Enterprise_Catalog_Model_Observer
     * @deprecated since 1.13.0.2
     */
    protected function _saveRedirect($requestPath, $targetPath)
    {
        /* @var $model Enterprise_UrlRewrite_Model_Redirect */
        $model = $this->_factory->getModel('enterprise_urlrewrite/redirect');
        $model->setIdentifier($requestPath)
            ->setTargetPath($targetPath)
            ->save();
        return $this;
    }

    /**
     * Set form renderer for url_key attribute
     *
     * @param Varien_Event_Observer $observer
     */
    public function setFormRendererAttributeUrlkey(Varien_Event_Observer $observer)
    {
        $urlKey = $observer->getEvent()->getForm()->getElement('url_key');

        if ($urlKey instanceof Varien_Data_Form_Element_Abstract) {
            $urlKey->setRenderer(
                $this->_app->getFrontController()->getAction()->getLayout()
                    ->createBlock('enterprise_catalog/adminhtml_catalog_form_renderer_attribute_urlkey')
            );
        }
    }

    /**
     * Change status on Require Reindex for Product Attributes indexer
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     * @deprecated since 1.14.1.0
     */
    public function invalidateAttributeIndexer(Varien_Event_Observer $observer)
    {
        /** @var $process Mage_Index_Model_Process */
        $process = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_attribute');
        if ($process) {
            $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }
        return $this;
    }

    /**
     * Listener for after category/product delete event. Delete orphan redirects from index.
     *
     * @param Varien_Event_Observer $observer
     */
    public function deleteOrphanRedirects(Varien_Event_Observer $observer)
    {
        if ((string)$this->_app->getConfig()->getNode(
            Enterprise_UrlRewrite_Model_Index_Observer::XML_PATH_REDIRECT_URL_SUFFIX_UPDATE_ON_SAVE))
        {
            /** @var $client Enterprise_Mview_Model_Client */
            $client = $this->_factory->getModel('enterprise_mview/client')->init('enterprise_url_rewrite_redirect');
            $client->execute('enterprise_urlrewrite/index_action_url_rewrite_redirect_refresh_orphan');
        }
    }

    /**
     * Listener for before category delete event. Deletes category custom redirects.
     * Needed because of http://bugs.mysql.com/bug.php?id=11472
     * @param Varien_Event_Observer $observer
     */
    public function deleteCategoryCustomRedirects(Varien_Event_Observer $observer)
    {
        if ((string)$this->_app->getConfig()->getNode(
            Enterprise_UrlRewrite_Model_Index_Observer::XML_PATH_REDIRECT_URL_SUFFIX_UPDATE_ON_SAVE))
        {
            return;
        }

        /** @var Mage_Catalog_Model_Category $category */
        $category = $observer->getCategory();
        $ids = $category->getResource()->getChildrenIds($category, true);
        $ids[] = $category->getId();

        /** @var Enterprise_UrlRewrite_Model_Resource_Redirect $redirects */
        $redirects = $this->_factory->getResourceModel('enterprise_urlrewrite/redirect');
        $redirects->deleteByCategoryIds($ids);
    }

    /**
     * Listener for before product delete event. Deletes product custom redirects.
     * Needed because of http://bugs.mysql.com/bug.php?id=11472
     * @param Varien_Event_Observer $observer
     */
    public function deleteProductCustomRedirects(Varien_Event_Observer $observer)
    {
        if ((string)$this->_app->getConfig()->getNode(
            Enterprise_UrlRewrite_Model_Index_Observer::XML_PATH_REDIRECT_URL_SUFFIX_UPDATE_ON_SAVE))
        {
            return;
        }

        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getProduct();
        /** @var Enterprise_UrlRewrite_Model_Resource_Redirect $redirects */
        $redirects = $this->_factory->getResourceModel('enterprise_urlrewrite/redirect');
        $redirects->deleteByProductIds(array($product->getId()));
    }

    /**
     * Listener for product attribute duplication event.
     * @param Varien_Event_Observer $observer
     */
    public function removeUrlKey(Varien_Event_Observer $observer)
    {
        $observer->getProduct()->setData('url_key', false);
    }

    /**
     * Add Seo suffix to category's URL if doesn't exists.
     *
     * @param Varien_Event_Observer $observer
     */
    public function addSeoSuffixToCategoryUrl(Varien_Event_Observer $observer)
    {
        $seoSuffix = (string) Mage::app()->getStore()->getConfig(
            Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_URL_SUFFIX
        );
        $this->_addSuffixToUrl($observer->getCollection()->getItems(), $seoSuffix);
    }

    /**
     * Add Seo suffix to product's URL if doesn't exists.
     *
     * @param Varien_Event_Observer $observer
     */
    public function addSeoSuffixToProductUrl(Varien_Event_Observer $observer)
    {
        $seoSuffix = (string) Mage::app()->getStore()->getConfig(
            Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_SUFFIX
        );
        $this->_addSuffixToUrl($observer->getCollection()->getItems(), $seoSuffix);
    }

    /**
     * Iterate via items and add suffix to item's URL.
     *
     * @param $items
     * @param $seoSuffix
     */
    protected function _addSuffixToUrl($items, $seoSuffix)
    {
        foreach ($items as $item) {
            if ($item->getUrl() && strpos($item->getUrl(), $seoSuffix) === false) {
                $item->setUrl($item->getUrl() . '.' . $seoSuffix);
            }
        }
    }
}
