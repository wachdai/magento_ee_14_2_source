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
 * Catalog Product Url model
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Product_Url extends Mage_Catalog_Model_Product_Url
{
    /**
     * Retrieve request path
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $categoryId
     * @return bool|string
     */
    protected function _getRequestPath($product, $categoryId)
    {
        $rewrite = $this->getUrlRewrite();
        $rewrite->loadByProduct($product);
        return $rewrite->getId() ? $rewrite->getRequestPath() : false;
    }

    /**
     * Retrieve Url rewrite instance
     *
     * @return Enterprise_Catalog_Model_Product
     */
    public function getUrlRewrite()
    {
        if (null === $this->_urlRewrite) {
            $this->_urlRewrite = $this->_factory->getModel('enterprise_catalog/product');
        }
        return $this->_urlRewrite;
    }

    /**
     * Retrieve product URL based on requestPath param
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $requestPath
     * @param array $routeParams
     *
     * @return string
     */
    protected function _getProductUrl($product, $requestPath, $routeParams)
    {
        $categoryId = $this->_getCategoryIdForUrl($product, $routeParams);

        if (!empty($requestPath)) {
            if ($categoryId) {
                $category = $this->_factory->getModel('catalog/category', array('disable_flat' => true))
                    ->load($categoryId);
                if ($category->getId()) {
                    $categoryRewrite = $this->_factory->getModel('enterprise_catalog/category')
                        ->loadByCategory($category);
                    if ($categoryRewrite->getId()) {
                        $requestPath = $categoryRewrite->getRequestPath() . '/' . $requestPath;
                    }
                }
            }
            $product->setRequestPath($requestPath);

            $storeId = $this->getUrlInstance()->getStore()->getId();
            $requestPath = $this->_factory->getHelper('enterprise_catalog')
                ->getProductRequestPath($requestPath, $storeId);

            return $this->getUrlInstance()->getDirectUrl($requestPath, $routeParams);
        }

        $routeParams['id'] = $product->getId();
        $routeParams['s'] = $product->getUrlKey();
        if ($categoryId) {
            $routeParams['category'] = $categoryId;
        }
        return $this->getUrlInstance()->getUrl('catalog/product/view', $routeParams);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param array $params
     * @return int|null
     */
    protected function _getCategoryIdForUrl($product, $params)
    {
        if (!$this->_store->getConfig(Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_USE_CATEGORY))
        {
            return null;
        }
        return parent::_getCategoryIdForUrl($product, $params);
    }


}
