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
 * @package     Enterprise_PageCache
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Placeholder container for catalog product lists
 */
abstract class Enterprise_PageCache_Model_Container_List_Abstract
    extends Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * Render block that was not cached
     *
     * @return false|string
     */
    protected function _renderBlock()
    {
        $productId = $this->_getProductId();
        if ($productId && !Mage::registry('product')) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product) {
                Mage::register('product', $product);
            }
        }

        if (Mage::registry('product')) {
            $block = $this->_getBlock();
            Mage::dispatchEvent('render_block', array('block' => $block, 'placeholder' => $this->_placeholder));
            return $block->toHtml();
        }

        return '';
    }

    /**
     * Retrieve cache id
     *
     * @return string
     */
    protected function _getCacheId()
    {
        $cookieCart = Enterprise_PageCache_Model_Cookie::COOKIE_CART;
        return md5(
            $this->_placeholder->getName()
                . '_' . $this->_getProductId()
        );
    }

    /**
     * Get block
     *
     * @return Mage_Core_Block_Abstract
     */
    abstract protected function _getBlock();
}
