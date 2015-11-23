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
 * @package     Enterprise_CatalogPermissions
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Base helper
 *
 * @category   Enterprise
 * @package    Enterprise_CatalogPermissions
 */

class Enterprise_CatalogPermissions_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED = 'catalog/enterprise_catalogpermissions/enabled';
    const XML_PATH_GRANT_CATALOG_CATEGORY_VIEW = 'catalog/enterprise_catalogpermissions/grant_catalog_category_view';
    const XML_PATH_GRANT_CATALOG_PRODUCT_PRICE = 'catalog/enterprise_catalogpermissions/grant_catalog_product_price';
    const XML_PATH_GRANT_CHECKOUT_ITEMS = 'catalog/enterprise_catalogpermissions/grant_checkout_items';
    const XML_PATH_DENY_CATALOG_SEARCH = 'catalog/enterprise_catalogpermissions/deny_catalog_search';
    const XML_PATH_LANDING_PAGE = 'catalog/enterprise_catalogpermissions/restricted_landing_page';

    const GRANT_ALL             = 1;
    const GRANT_CUSTOMER_GROUP  = 2;
    const GRANT_NONE            = 0;

    /**
     * Retrieve config value for permission enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Check category permission is allowed
     *
     * @param Mage_Catalog_Model_Category $category
     * @return boolean
     */
    public function isAllowedCategory($category)
    {
        $options = new Varien_Object();
        $options->setCategory($category);
        $options->setIsAllowed(true);

        Mage::dispatchEvent('enterprise_catalog_permissions_is_allowed_category', array('options' => $options));

        return $options->getIsAllowed();
    }


    /**
     * Retrieve config value for category access permission
     *
     * @param int $customerGroupId
     * @param int $storeId
     * @return boolean
     */
    public function isAllowedCategoryView($storeId = null, $customerGroupId = null)
    {
        return $this->_getIsAllowedGrant(self::XML_PATH_GRANT_CATALOG_CATEGORY_VIEW, $storeId, $customerGroupId);
    }

    /**
     * Retrieve config value for product price permission
     *
     * @param int $customerGroupId
     * @param int $storeId
     * @return boolean
     */
    public function isAllowedProductPrice($storeId = null, $customerGroupId = null)
    {
        return $this->_getIsAllowedGrant(self::XML_PATH_GRANT_CATALOG_PRODUCT_PRICE, $storeId, $customerGroupId);
    }

    /**
     * Retrieve config value for checkout items permission
     *
     * @param int $customerGroupId
     * @param int $storeId
     * @return boolean
     */
    public function isAllowedCheckoutItems($storeId = null, $customerGroupId = null)
    {
        return $this->_getIsAllowedGrant(self::XML_PATH_GRANT_CHECKOUT_ITEMS, $storeId, $customerGroupId);
    }


    /**
     * Retrieve config value for catalog search availability
     *
     * @return boolean
     */
    public function isAllowedCatalogSearch()
    {
        $groups = trim(Mage::getStoreConfig(self::XML_PATH_DENY_CATALOG_SEARCH));

        if ($groups === '') {
            return true;
        }

        $groups = explode(',', $groups);

        return !in_array(Mage::getSingleton('customer/session')->getCustomerGroupId(), $groups);
    }

    /**
     * Retrieve landing page url
     *
     * @return string
     */
    public function getLandingPageUrl()
    {
        return $this->_getUrl('', array('_direct' => Mage::getStoreConfig(self::XML_PATH_LANDING_PAGE)));
    }

    /**
     * Retrieve is allowed grant from configuration
     *
     * @param string $configPath
     * @return boolean
     */
    protected function _getIsAllowedGrant($configPath, $storeId = null, $customerGroupId = null)
    {
        if (Mage::getStoreConfig($configPath, $storeId) == self::GRANT_CUSTOMER_GROUP) {
            $groups = trim(Mage::getStoreConfig($configPath . '_groups', $storeId));

            if ($groups === '') {
                return false;
            }

            $groups = explode(',', $groups);

            if ($customerGroupId === null) {
                $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            }

            return in_array(
                $customerGroupId,
                $groups
            );
        }

        return Mage::getStoreConfig($configPath) == self::GRANT_ALL;
    }
}
