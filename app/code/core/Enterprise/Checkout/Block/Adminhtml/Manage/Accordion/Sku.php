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
 * "Add by SKU" accordion
 *
 * @method string                                                   getHeaderText()
 * @method Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Sku setHeaderText()
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Sku extends Enterprise_Checkout_Block_Adminhtml_Sku_Abstract
{
    /**
     * Define accordion header
     */
    public function __construct()
    {
        parent::__construct();
        $this->setHeaderText(Mage::helper('enterprise_checkout')->__('Add to Shopping Cart by SKU'));
    }

    /**
     * Register grid with instance of AdminCheckout, register new list type and URL to fetch configure popup HTML
     *
     * @return string
     */
    public function getAdditionalJavascript()
    {
        // Origin of configure popup HTML
        $js = $this->getJsOrderObject() . ".addSourceGrid({htmlId: \"{$this->getId()}\", "
            . "listType: \"{$this->getListType()}\"});";
        $js .= $this->getJsOrderObject() . ".addNoCleanSource('{$this->getId()}');";
        $js .= 'addBySku.observeAddToCart();';
        return $js;
    }

    /**
     * Retrieve JavaScript AdminCheckout instance name
     *
     * @return string
     */
    public function getJsOrderObject()
    {
        return 'checkoutObj';
    }

    /**
     * Retrieve container ID for error grid
     *
     * @return string
     */
    public function getErrorGridId()
    {
        return 'checkout_errors';
    }

    /**
     * Retrieve file upload URL
     *
     * @return string
     */
    public function getFileUploadUrl()
    {
        return $this->getUrl('*/checkout/uploadSkuCsv');
    }

    /**
     * Retrieve context specific JavaScript
     *
     * @return string
     */
    public function getContextSpecificJs()
    {
        return 'Event.observe(window, \'load\', initSku);';
    }
}
