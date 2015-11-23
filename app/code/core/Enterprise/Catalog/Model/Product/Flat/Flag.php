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
 * Calatog Product Flat Flag Model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Product_Flat_Flag extends Mage_Catalog_Model_Product_Flat_Flag
{
    /**
     * Returns true if store's flat index has been built.
     *
     * @param int $storeId
     * @return bool
     */
    public function isStoreBuilt($storeId)
    {
        $key = 'is_store_built_' . (int)$storeId;
        $flagData = $this->getFlagData();
        if (!isset($flagData[$key])) {
            $flagData[$key] = false;
            $this->setFlagData($flagData);
        }
        return (bool)$flagData[$key] && $this->_isMetadataValid();
    }

    /**
     * Retrieve Catalog Product Flat Data is built flag
     *
     * @return bool
     */
    public function getIsBuilt()
    {
        $flagData = $this->getFlagData();
        if (!isset($flagData['is_built'])) {
            $flagData['is_built'] = false;
            $this->setFlagData($flagData);
        }
        return (bool)$flagData['is_built'] && $this->_isMetadataValid();
    }

    /**
     * Check is product flat metadata is valid
     *
     * @return bool
     */
    protected function _isMetadataValid()
    {
        $metadata = Mage::getModel('enterprise_mview/metadata')->load('catalog_product_flat', 'table_name');
        return $metadata->getStatus() != Enterprise_Mview_Model_Metadata::STATUS_INVALID;
    }
}
