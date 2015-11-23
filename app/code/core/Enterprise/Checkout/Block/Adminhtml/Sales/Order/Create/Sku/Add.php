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
 * Form for adding products by SKU
 *
 * @category    Enterprise
 * @package     Enterprise_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Block_Adminhtml_Sales_Order_Create_Sku_Add
    extends Enterprise_Checkout_Block_Adminhtml_Sku_Abstract
{
    /**
     * Returns JavaScript variable name of AdminCheckout or AdminOrder instance
     *
     * @return string
     */
    public function getJsOrderObject()
    {
        return 'order';
    }

    /**
     * Returns HTML ID of the error grid
     *
     * @return string
     */
    public function getErrorGridId()
    {
        return 'order_errors';
    }

    /**
     * Retrieve file upload URL
     *
     * @return string
     */
    public function getFileUploadUrl()
    {
        return $this->getUrl('*/sales_order_create/processData');
    }

    /**
     * Retrieve context specific JavaScript
     *
     * @return string
     */
    public function getContextSpecificJs()
    {
        return '
            var parentAreasLoaded = ' . $this->getJsOrderObject() . '.areasLoaded;
            ' . $this->getJsOrderObject() . '.areasLoaded = function () {
                initSku();
                parentAreasLoaded();
            };';
    }
}
