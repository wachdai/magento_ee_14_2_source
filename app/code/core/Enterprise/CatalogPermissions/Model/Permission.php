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
 * Permission model
 *
 * @method Enterprise_CatalogPermissions_Model_Resource_Permission _getResource()
 * @method Enterprise_CatalogPermissions_Model_Resource_Permission getResource()
 * @method int getCategoryId()
 * @method Enterprise_CatalogPermissions_Model_Permission setCategoryId(int $value)
 * @method int getWebsiteId()
 * @method Enterprise_CatalogPermissions_Model_Permission setWebsiteId(int $value)
 * @method int getCustomerGroupId()
 * @method Enterprise_CatalogPermissions_Model_Permission setCustomerGroupId(int $value)
 * @method int getGrantCatalogCategoryView()
 * @method Enterprise_CatalogPermissions_Model_Permission setGrantCatalogCategoryView(int $value)
 * @method int getGrantCatalogProductPrice()
 * @method Enterprise_CatalogPermissions_Model_Permission setGrantCatalogProductPrice(int $value)
 * @method int getGrantCheckoutItems()
 * @method Enterprise_CatalogPermissions_Model_Permission setGrantCheckoutItems(int $value)
 *
 * @category    Enterprise
 * @package     Enterprise_CatalogPermissions
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CatalogPermissions_Model_Permission extends Mage_Core_Model_Abstract
{
    const PERMISSION_ALLOW = -1;
    const PERMISSION_DENY = -2;
    const PERMISSION_PARENT = 0;

    /**
     * Initialize model
     */
    protected function _construct()
    {
        $this->_init('enterprise_catalogpermissions/permission');
    }
}
