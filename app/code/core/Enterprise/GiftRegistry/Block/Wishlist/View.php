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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Wishlist view block
 */
class Enterprise_GiftRegistry_Block_Wishlist_View extends Mage_Wishlist_Block_Customer_Wishlist
{
    /**
     * Prepare block layout, override wishlist block with different template
     *
     * @return Enterprise_GiftRegistry_Block_Wishlist_View
     */
    protected function _prepareLayout()
    {
        $outputEnabled = Mage::helper('core')->isModuleOutputEnabled($this->getModuleName());
        if ($outputEnabled) {
            $wrapper = $this->getLayout()->getBlock('my.account.wrapper');
            if ($wrapper) {
                $oldBlock = $this->getLayout()->getBlock('customer.wishlist');
                if ($oldBlock) {
                    $wrapper->unsetChild('customer.wishlist');
                    $this->setOptionsRenderCfgs($oldBlock->getOptionsRenderCfgs());
                }
                $wrapper->append($this, 'customer.wishlist');
            }
        }
        return parent::_prepareLayout();
    }

    /**
     * Return add url
     *
     * @return bool
     */
    public function getAddUrl()
    {
        return $this->getUrl('giftregistry/index/wishlist');
    }

    /**
     * Check whether module is available
     *
     * @return bool
     */
    public function getEnabled()
    {
        return  Mage::helper('enterprise_giftregistry')->isEnabled();
    }

    /**
     * Return list of current customer gift registries
     *
     * @return Enterprise_GiftRegistry_Model_Mysql4_GiftRegistry_Collection
     */
    public function getEntityValues()
    {
        return Mage::helper('enterprise_giftregistry')->getCurrentCustomerEntityOptions();
    }

    /**
     * Check if wishlist item can be added to gift registry
     *
     * @param Mage_Catalog_Model_Product $item
     * @return bool
     */
    public function checkProductType($item)
    {
        return Mage::helper('enterprise_giftregistry')->canAddToGiftRegistry($item);
    }
}
