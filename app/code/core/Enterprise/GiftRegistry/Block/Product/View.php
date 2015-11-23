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
 * Front end helper block to show giftregistry mark
 */
class Enterprise_GiftRegistry_Block_Product_View extends Mage_Catalog_Block_Product_View
{
    /**
     * Giftregistry param flag value in url option params
     * @var string
     */
    const FLAG = 'giftregistry';

    /**
     * Prepare layout
     *
     * @return Enterprise_GiftRegistry_Block_Product_View
     */
    protected function _prepareLayout()
    {
        $block = $this->getLayout()->getBlock('customize.button');
        if ($block && $this->_isGiftRegistryRedirect()) {
            $block->setTemplate('giftregistry/product/customize.phtml');
        }

        $block = $this->getLayout()->getBlock('product.info.addtocart');
        if ($block && $this->_isGiftRegistryRedirect()) {
            $block->setTemplate('giftregistry/product/addtocart.phtml');
            $block->setAddToGiftregistryUrl($this->getAddToGiftregistryUrl());
        }
        return parent::_prepareLayout();
    }

    /**
     * Return giftregistry add cart items url
     *
     * @return string
     */
    public function getAddToGiftregistryUrl()
    {
        return $this->getUrl('enterprise_giftregistry/index/cart',
            array('entity' => $this->getRequest()->getParam('entity')));
    }

    /**
     * Return gift registry redirect flag.
     *
     * @return bool
     */
    protected function _isGiftRegistryRedirect()
    {
        return $this->getRequest()->getParam('options') == self::FLAG;
    }
}
