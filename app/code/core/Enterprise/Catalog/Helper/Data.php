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
 * Catalog Helper
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Configuration object
     *
     * @var Mage_Core_Model_Config
     */
    protected $_config = null;

    /**
     * Factory instance
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Set factory
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('core/factory');
    }

    /**
     * Retrieve category request path
     *
     * @param string $requestPath
     * @param int $storeId
     * @return string
     */
    public function getCategoryRequestPath($requestPath, $storeId)
    {
        if (empty($requestPath)) {
            return '';
        }
        /** @var $helper Mage_Catalog_Helper_Category */
        $helper    = $this->_factory->getHelper('catalog/category');
        $urlSuffix = $helper->getCategoryUrlSuffix($storeId);
        if ($urlSuffix) {
            $requestPath .= '.' . $urlSuffix;
        }
        return $requestPath;
    }

    /**
     * Retrieve product request path
     *
     * @param string $requestPath
     * @param int $storeId
     * @param int $categoryId
     * @return string
     */
    public function getProductRequestPath($requestPath, $storeId, $categoryId = null)
    {
        if (empty($requestPath)) {
            return '';
        }
        /** @var $helper Mage_Catalog_Helper_Product */
        $helper    = $this->_factory->getHelper('catalog/product');
        $urlSuffix = $helper->getProductUrlSuffix($storeId);
        if ($urlSuffix) {
            $requestPath .= '.' . $urlSuffix;
        }
        if (!is_null($categoryId)) {
            /** @var $category Mage_Catalog_Model_Category */
            $category = Mage::getModel('catalog/category')->load($categoryId)->setStoreId($storeId);
            $categoryUrlKey = $category->getRequestPath();
            $requestPath = ltrim($categoryUrlKey . '/' . $requestPath, '/');
        }
        return $requestPath;
    }
}
