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
 * Gift Registry Item list render block for configurable product
 *
 * @category    Enterprise
 * @package     Enterprise_GiftRegistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class  Enterprise_GiftRegistry_Block_Items_Renderer_Configurable
    extends Mage_Checkout_Block_Cart_Item_Renderer_Configurable
{
    /**
     * Get html for MAP product enabled
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return string
     */
    public function getMsrpHtml($item)
    {
        $product = $item->getProduct();

        $block = $this->_preparePriceBlock($product);
        $html = $block->setDisplayMinimalPrice(true)->toHtml();
        $product->setRealPriceHtml($html);

        return $this->_getPriceContent($product);
    }
}
