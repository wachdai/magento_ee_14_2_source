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
 * Add by SKU errors accordion
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Sku_Errors
    extends Enterprise_Checkout_Block_Adminhtml_Sku_Errors_Abstract
{
    /**
     * Returns url to configure item
     *
     * @return string
     */
    public function getConfigureUrl()
    {
        $customer = Mage::registry('checkout_current_customer');
        $store = Mage::registry('checkout_current_store');
        $params = array(
            'customer'   => $customer->getId(),
            'store'    => $store->getId()
        );
        return $this->getUrl('*/checkout/configureProductToAdd', $params);
    }

    /**
     * Retrieve additional JavaScript for error grid
     *
     * @return string
     */
    public function getAdditionalJavascript()
    {
        return "addBySku.addErrorSourceGrid({htmlId: '{$this->getId()}', listType: '{$this->getListType()}'})";
    }

    /**
     * Returns current store model
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return Mage::registry('checkout_current_store');
    }

    /**
     * Get title of button, that adds products to shopping cart
     *
     * @return string
     */
    public function getAddButtonTitle()
    {
        return $this->__('Add to Shopping Cart');
    }
}
