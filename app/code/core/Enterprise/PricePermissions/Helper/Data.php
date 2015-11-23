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
 * @package     Enterprise_PricePermissions
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Price Permissions Data Helper
 *
 * @category    Enterprise
 * @package     Enterprise_PricePermissions
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_PricePermissions_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Path to edit_product_price node in ACL
     *
     * Used to check if admin has permission to edit product price
     */
    const EDIT_PRODUCT_PRICE_ACL_PATH = 'catalog/products/read_product_price/edit_product_price';

    /**
     * Path to read_product_price node in ACL
     *
     * Used to check if admin has permission to read product price
     */
    const READ_PRODUCT_PRICE_ACL_PATH = 'catalog/products/read_product_price';

    /**
     * Path to edit_product_status node in ACL
     *
     * Used to check if admin has permission to edit product status
     */
    const EDIT_PRODUCT_STATUS_ACL_PATH = 'catalog/products/edit_product_status';

    /**
     * Path to default_product_price node in config
     */
    const DEFAULT_PRODUCT_PRICE_CONFIG_PATH = 'default/catalog/price/default_product_price';

    /**
     * Check if admin has permissions to read product price
     *
     * @return boolean
     */
    public function getCanAdminReadProductPrice()
    {
        return (boolean) Mage::getSingleton('admin/session')->isAllowed(self::READ_PRODUCT_PRICE_ACL_PATH);
    }

    /**
     * Check if admin has permissions to edit product price
     *
     * @return boolean
     */
    public function getCanAdminEditProductPrice()
    {
        return (boolean) Mage::getSingleton('admin/session')->isAllowed(self::EDIT_PRODUCT_PRICE_ACL_PATH);
    }

    /**
     * Check if admin has permissions to edit product ststus
     *
     * @return boolean
     */
    public function getCanAdminEditProductStatus()
    {
        return (boolean) Mage::getSingleton('admin/session')->isAllowed(self::EDIT_PRODUCT_STATUS_ACL_PATH);
    }

    /**
     * Retrieve value of the default product price as string
     *
     * @return string
     */
    public function getDefaultProductPriceString()
    {
        return (string) Mage::getConfig()->getNode(self::DEFAULT_PRODUCT_PRICE_CONFIG_PATH);
    }
}
