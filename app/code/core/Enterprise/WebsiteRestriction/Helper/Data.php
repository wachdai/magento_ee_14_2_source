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
 * @package     Enterprise_WebsiteRestriction
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * WebsiteRestriction helper for translations
 *
 */
class Enterprise_WebsiteRestriction_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Website restriction settings
     */
    const XML_PATH_RESTRICTION_ENABLED            = 'general/restriction/is_active';
    const XML_PATH_RESTRICTION_MODE               = 'general/restriction/mode';
    const XML_PATH_RESTRICTION_LANDING_PAGE       = 'general/restriction/cms_page';
    const XML_PATH_RESTRICTION_HTTP_STATUS        = 'general/restriction/http_status';
    const XML_PATH_RESTRICTION_HTTP_REDIRECT      = 'general/restriction/http_redirect';
    const XML_NODE_RESTRICTION_ALLOWED_GENERIC    = 'frontend/enterprise/websiterestriction/full_action_names/generic';
    const XML_NODE_RESTRICTION_ALLOWED_REGISTER   = 'frontend/enterprise/websiterestriction/full_action_names/register';

    /**
     * Define if restriction is active
     *
     * @param Mage_Core_Model_Store|string|int $store
     * @return bool
     */
    public function getIsRestrictionEnabled($store = null)
    {
        return (bool)(int)Mage::getStoreConfig(self::XML_PATH_RESTRICTION_ENABLED, $store);
    }
}
