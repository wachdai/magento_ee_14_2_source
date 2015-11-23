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
 * Product edit form
 *
 * @category   Enterprise
 * @package    Enterprise_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Block_Adminhtml_Redirect_Edit_Form_Product
    extends Enterprise_UrlRewrite_Block_Adminhtml_UrlRedirect_Edit_Form
{
    /**
     * Load extend data from category or product
     *
     * @param Enterprise_UrlRewrite_Model_Redirect $redirect
     * @return Enterprise_Catalog_Block_Adminhtml_Redirect_Edit_Form_Product
     */
    protected function _loadRedirectData(Enterprise_UrlRewrite_Model_Redirect $redirect)
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $categoryId = (int)$this->getRequest()->getParam('category_id');
            return $this->_prepareProductFormData($productId, $categoryId, $redirect);
        }
        return $this;
    }

    /**
     * Prepare product form data
     *
     * @param int $productId
     * @param int $categoryId
     * @param Enterprise_UrlRewrite_Model_Redirect $redirect
     * @return Enterprise_Catalog_Block_Adminhtml_Redirect_Edit_Form_Product
     */
    protected function _prepareProductFormData($productId, $categoryId, $redirect)
    {
        $product = $this->_getProduct($productId);

        //load request_path
        $product->getUrlModel()->getUrl($product);

        /** @var $helper Enterprise_Catalog_Helper_Data */
        $helper      = $this->_factory->getHelper('enterprise_catalog');
        $requestPath = $helper->getProductRequestPath($product->getRequestPath(), $product->getStoreId());
        if (empty($requestPath)) {
            $requestPath = 'product-' . $product->getId();
        }

        $targetPath = 'catalog/product/view/id/' . $product->getId();
        if ($categoryId) {
            $category = $this->_getCategory($categoryId);

            $storeIds = $category->getStoreIds();
            //unset default store
            unset($storeIds[$category->getStoreId()]);
            $category->setStoreId(array_pop($storeIds));

            /** @var $categoryRewrite Enterprise_Catalog_Model_Category */
            $categoryRewrite = Mage::getModel('enterprise_catalog/category');
            $categoryRewrite->loadByCategory($category);
            if ($categoryRewrite->getId()) {
                $requestPath = $categoryRewrite->getRequestPath() . '/' . $requestPath;
            }
            $targetPath .= '/category/' . $categoryId;
        }

        $redirect->setIdentifier($requestPath);
        $redirect->setTargetPath($targetPath);
        return $this;
    }

    /**
     * Retrieve product instance by specified id
     *
     * @param int $productId
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct($productId)
    {
        return Mage::getModel('catalog/product')->load($productId);
    }

    /**
     * Retrieve category instance by specified id
     *
     * @param int $categoryId
     * @return Mage_Catalog_Model_Category
     */
    protected function _getCategory($categoryId)
    {
        return Mage::getModel('catalog/category')->load($categoryId);
    }
}
