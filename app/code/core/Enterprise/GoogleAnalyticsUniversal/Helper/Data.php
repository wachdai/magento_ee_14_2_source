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
 * @package     Enterprise_GoogleAnalyticsUniversal
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


class Enterprise_GoogleAnalyticsUniversal_Helper_Data extends Mage_GoogleAnalytics_Helper_Data
{

    const XML_PATH_CONTAINER_ID         = 'google/analytics/container_id';
    const XML_PATH_LIST_CATALOG_PAGE    = 'google/analytics/catalog_page_list_value';
    const XML_PATH_LIST_CROSSSELL_BLOCK = 'google/analytics/crosssell_block_list_value';
    const XML_PATH_LIST_UPSELL_BLOCK    = 'google/analytics/upsell_block_list_value';
    const XML_PATH_LIST_RELATED_BLOCK   = 'google/analytics/related_block_list_value';
    const XML_PATH_LIST_SEARCH_PAGE     = 'google/analytics/search_page_list_value';
    const XML_PATH_LIST_PROMOTIONS      = 'google/analytics/promotions_list_value';

    const TYPE_TAG_MANAGER = 'tag_manager';

    const GOOGLE_ANALYTICS_COOKIE_NAME             = 'add_to_cart';
    const GOOGLE_ANALYTICS_COOKIE_REMOVE_FROM_CART = 'remove_from_cart';

    const PRODUCT_QUANTITIES_BEFORE_ADDTOCART = 'prev_product_qty';
    /**
     * Whether GA Plus is ready to use
     *
     * @param mixed $store
     * @return bool
     */
    public function isGoogleAnalyticsAvailable($store = null)
    {
        $gapAccountId = Mage::getStoreConfig(self::XML_PATH_ACCOUNT, $store);
        $gtmAccountId = Mage::getStoreConfig(self::XML_PATH_CONTAINER_ID, $store);
        $accountType = Mage::getStoreConfig(self::XML_PATH_TYPE, $store);
        $enabled = false;
        switch ($accountType) {
            case self::TYPE_ANALYTICS:
            case self::TYPE_UNIVERSAL:
                if (!empty($gapAccountId)) {
                    $enabled = true;
                }
                break;
            case self::TYPE_TAG_MANAGER;
                if (!empty($gtmAccountId)) {
                    $enabled = true;
                }
                break;
        }
        return $enabled && Mage::getStoreConfigFlag(self::XML_PATH_ACTIVE, $store);
    }

    /**
     * Whether GTM is ready to use
     *
     * @param mixed $store
     * @return bool
     */
    public function isTagManagerAvailable($store = null)
    {
        $gtmAccountId = Mage::getStoreConfig(self::XML_PATH_CONTAINER_ID, $store);
        $accountType = Mage::getStoreConfig(self::XML_PATH_TYPE, $store);
        $enabled = ($accountType == self::TYPE_TAG_MANAGER) && !empty($gtmAccountId);
        return $enabled && Mage::getStoreConfigFlag(self::XML_PATH_ACTIVE, $store);
    }
}
