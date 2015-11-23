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
 * Enterprise Catalog category url model
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Category_Url extends Mage_Catalog_Model_Category_Url
{
    /**
     * Get direct URL to category
     *
     * @param Mage_Catalog_Model_Category $category
     * @return string
     */
    protected function _getDirectUrl(Mage_Catalog_Model_Category $category)
    {
        /** @var $helper Enterprise_Catalog_Helper_Data */
        $helper      = $this->_factory->getHelper('enterprise_catalog');
        $requestPath = $helper->getCategoryRequestPath($category->getRequestPath(), $category->getStoreId());
        return $this->getUrlInstance()->getDirectUrl($requestPath);
    }

    /**
     * Retrieve request path
     *
     * @param Mage_Catalog_Model_Category $category
     * @return bool|string
     */
    protected function _getRequestPath(Mage_Catalog_Model_Category $category)
    {
        $rewrite = $this->getUrlRewrite();
        $rewrite->loadByCategory($category);
        if ($rewrite->getId()) {
            return $rewrite->getRequestPath();
        }
        return false;
    }

    /**
     * Retrieve Url rewrite instance
     *
     * @return Enterprise_Catalog_Model_Category
     */
    public function getUrlRewrite()
    {
        if (null === $this->_urlRewrite) {
            $this->_urlRewrite = $this->_factory->getModel('enterprise_catalog/category');
        }
        return $this->_urlRewrite;
    }

    /**
     * Load request path if it does not exist
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Enterprise_Catalog_Model_Category_Url
     * @deprecated since 1.13.0.2
     */
    protected function _loadRequestPath(Mage_Catalog_Model_Category $category)
    {
        if (!$category->getRequestPath()) {
            $category->setRequestPath(
                $this->_getRequestPath($category)
            );
        }
        return $this;
    }

    /**
     * Get full URL by request path if it exist or by ID-URL
     *
     * @param Mage_Catalog_Model_Category $category
     * @return string
     * @deprecated since 1.13.0.2
     */
    protected function _getFullUrl(Mage_Catalog_Model_Category $category)
    {
        return $category->getRequestPath()
            ? $this->_getDirectUrl($category) : $category->getCategoryIdUrl();
    }
}
