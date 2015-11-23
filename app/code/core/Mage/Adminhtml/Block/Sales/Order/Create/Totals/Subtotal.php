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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Subtotal Total Row Renderer
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Create_Totals_Subtotal extends
    Mage_Adminhtml_Block_Sales_Order_Create_Totals_Default
{
    /**
     * Template file path
     *
     * @var string
     */
    protected $_template = 'sales/order/create/totals/subtotal.phtml';

    /**
     * Check if we need display both sobtotals
     *
     * @return bool
     */
    public function displayBoth()
    {
        // Check without store parameter - we will get admin configuration value
        $displayBoth = Mage::getSingleton('tax/config')->displayCartSubtotalBoth();

        // If trying to display the subtotal with and without taxes, need to ensure the information is present
        if ($displayBoth) {
            // Verify that the value for 'subtotal including tax' (or excluding tax) exists
            $value = $this->getTotal()->getValueInclTax();
            $displayBoth = isset( $value );
        }
        return $displayBoth;
    }
}
