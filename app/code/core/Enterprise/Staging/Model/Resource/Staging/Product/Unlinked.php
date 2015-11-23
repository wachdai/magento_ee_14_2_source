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
 * Staging unlinked product resource
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Resource_Staging_Product_Unlinked extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('enterprise_staging/staging_product_unlinked', 'product_id');
    }

    /**
     * Add products that must be unlinked on merge staging website with master
     *
     * @param  int|array $productIds
     * @param  int|array $websiteIds
     * @return Enterprise_Staging_Model_Resource_Staging_Product_Unlinked
     */
    public function addProductsUnlinkAssociations($productIds, $websiteIds)
    {
        if (empty($productIds) || empty($websiteIds)) {
            return $this;
        }
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }
        if (!is_array($websiteIds)) {
            $websiteIds = array($websiteIds);
        }

        $writeAdapter = $this->_getWriteAdapter();
        $writeAdapter->beginTransaction();

        try {
            foreach ($websiteIds as $websiteId) {
                foreach ($productIds as $productId) {
                    $writeAdapter->insertOnDuplicate($this->getMainTable(), array(
                        'product_id' => $productId,
                        'website_id' => $websiteId
                    ));
                }
            }

            $writeAdapter->commit();
        } catch (Exception $e) {
            $writeAdapter->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Remove products that must be unlinked on merge staging website with master
     *
     * @param  array $productIds
     * @param  array $websiteIds
     * @return Enterprise_Staging_Model_Resource_Staging_Product_Unlinked
     */
    public function removeProductsUnlinkAssociations($productIds, $websiteIds)
    {
        if (empty($productIds) || empty($websiteIds)) {
            return $this;
        }
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }
        if (!is_array($websiteIds)) {
            $websiteIds = array($websiteIds);
        }

        $writeAdapter = $this->_getWriteAdapter();
        $writeAdapter->beginTransaction();

        try {
            $writeAdapter->delete($this->getMainTable(), array(
                'product_id IN (?)' => $productIds,
                'website_id IN (?)' => $websiteIds
            ));

            $writeAdapter->commit();
        } catch (Exception $e) {
            $writeAdapter->rollBack();
            throw $e;
        }

        return $this;
    }
}
