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
 * @package     Enterprise_Checkout
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Adminhtml grid product name column renderer
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Block_Adminhtml_Manage_Grid_Renderer_Product extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Render product name to add Configure link
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $rendered       =  parent::render($row);
        $listType = $this->getColumn()->getGrid()->getListType();
        if ($row instanceof Mage_Catalog_Model_Product) {
            $product = $row;
        } else if (($row instanceof Mage_Wishlist_Model_Item) || ($row instanceof Mage_Sales_Model_Order_Item)) {
            $product = $row->getProduct();
        }
        if ($product->canConfigure()) {
            $style = '';
            $prodAttributes = sprintf('list_type = "%s" item_id = %s', $listType, $row->getId());
        } else {
            $style = 'style="color: #CCC;"';
            $prodAttributes = 'disabled="disabled"';
        }
        return sprintf('<a href="javascript:void(0)" %s class="f-right" %s>%s</a>',
            $style, $prodAttributes, Mage::helper('sales')->__('Configure')) . $rendered;
    }
}
