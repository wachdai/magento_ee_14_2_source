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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Staging unlinked product model
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Staging_Product_Unlinked extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('enterprise_staging/staging_product_unlinked');
    }

    /**
     * Check if specified websites ids are staging websites ids
     *
     * @param  array $websiteIds
     * @return array
     */
    protected function _prepareWebsiteIds($websiteIds)
    {
        if (!is_array($websiteIds)) {
            $websiteIds = array($websiteIds);
        }

        $stagingWebsiteIds = array();
        foreach ($websiteIds as $websiteId) {
            $website = Mage::app()->getWebsite($websiteId);
            if ($website && $website->getIsStaging()) {
                $stagingWebsiteIds[] = $websiteId;
            }
        }

        return $stagingWebsiteIds;
    }

    /**
     * Add products unlink associations to staging websites
     *
     * @param  int|array $productIds
     * @param  int|array $websiteIds
     * @return Enterprise_Staging_Model_Staging_Product_Unlinked
     */
    public function addProductsUnlinkAssociations($productIds, $websiteIds)
    {
        $websiteIds = $this->_prepareWebsiteIds($websiteIds);

        try {
            $this->_getResource()->addProductsUnlinkAssociations($productIds, $websiteIds);
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('enterprise_staging')->__('An error occurred while adding products that must be unlinked on merge with staging website.')
            );
        }

        return $this;
    }

    /**
     * Remove products unlink associations to staging websites
     *
     * @param  int|array $productIds
     * @param  int|array $websiteIds
     * @return Enterprise_Staging_Model_Staging_Product_Unlinked
     */
    public function removeProductsUnlinkAssociations($productIds, $websiteIds)
    {
        $websiteIds = $this->_prepareWebsiteIds($websiteIds);

        try {
            $this->_getResource()->removeProductsUnlinkAssociations($productIds, $websiteIds);
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('enterprise_staging')->__('An error occurred while removing products that must be unlinked on merge with staging website.')
            );
        }

        return $this;
    }
}
