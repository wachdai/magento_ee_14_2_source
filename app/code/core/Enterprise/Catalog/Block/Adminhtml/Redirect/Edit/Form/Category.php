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
 * Category edit form
 *
 * @category   Enterprise
 * @package    Enterprise_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Block_Adminhtml_Redirect_Edit_Form_Category
    extends Enterprise_UrlRewrite_Block_Adminhtml_UrlRedirect_Edit_Form
{
    /**
     * Load extend data from category or product
     *
     * @param Enterprise_UrlRewrite_Model_Redirect $redirect
     * @return Enterprise_Catalog_Block_Adminhtml_Redirect_Edit_Form_Category
     */
    protected function _loadRedirectData(Enterprise_UrlRewrite_Model_Redirect $redirect)
    {
        $categoryId = (int)$this->getRequest()->getParam('category_id');

        if ($categoryId) {
            return $this->_prepareCategoryFormData($categoryId, $redirect);
        }
        return $this;
    }

    /**
     * Prepare category form data
     *
     * @param int $categoryId
     * @param Enterprise_UrlRewrite_Model_Redirect $redirect
     * @return Enterprise_Catalog_Block_Adminhtml_Redirect_Edit_Form_Category
     */
    protected function _prepareCategoryFormData($categoryId, Enterprise_UrlRewrite_Model_Redirect $redirect)
    {
        $category = $this->_getCategory($categoryId);

        //load request_path
        $category->getUrl();
        $targetPath = 'catalog/category/view/id/' . $category->getId();


        if ($category->getRequestPath()) {
            /** @var $helper Enterprise_Catalog_Helper_Data */
            $helper      = $this->_factory->getHelper('enterprise_catalog');
            $requestPath = $helper->getCategoryRequestPath($category->getRequestPath(), $category->getStoreId());
        } else {
            $requestPath = $targetPath;
        }

        $redirect->setIdentifier($requestPath);
        $redirect->setTargetPath($targetPath);
        return $this;
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
