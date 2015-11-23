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
 * @package     Enterprise_PromotionPermissions
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Promotion Permissions Data Helper
 *
 * @category    Enterprise
 * @package     Enterprise_PromotionPermissions
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_PromotionPermissions_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Path to node in ACL that specifies edit permissions for catalog rules
     *
     * Used to check if admin has permission to edit catalog rules
     */
    const EDIT_PROMO_CATALOGRULE_ACL_PATH = 'promo/catalog/edit';

    /**
     * Path to node in ACL that specifies edit permissions for sales rules
     *
     * Used to check if admin has permission to edit sales rules
     */
    const EDIT_PROMO_SALESRULE_ACL_PATH = 'promo/quote/edit';

    /**
     * Path to node in ACL that specifies edit permissions for reminder rules
     *
     * Used to check if admin has permission to edit reminder rules
     */
    const EDIT_PROMO_REMINDERRULE_ACL_PATH = 'promo/enterprise_reminder/edit';

    /**
     * Check if admin has permissions to edit catalog rules
     *
     * @return boolean
     */
    public function getCanAdminEditCatalogRules()
    {
        return (boolean) Mage::getSingleton('admin/session')->isAllowed(self::EDIT_PROMO_CATALOGRULE_ACL_PATH);
    }

    /**
     * Check if admin has permissions to edit sales rules
     *
     * @return boolean
     */
    public function getCanAdminEditSalesRules()
    {
        return (boolean) Mage::getSingleton('admin/session')->isAllowed(self::EDIT_PROMO_SALESRULE_ACL_PATH);
    }

    /**
     * Check if admin has permissions to edit reminder rules
     *
     * @return boolean
     */
    public function getCanAdminEditReminderRules()
    {
        return (boolean) Mage::getSingleton('admin/session')->isAllowed(self::EDIT_PROMO_REMINDERRULE_ACL_PATH);
    }
}
