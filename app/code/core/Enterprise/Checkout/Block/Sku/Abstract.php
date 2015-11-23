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
 * Abstract block of form with SKUs data
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 */
abstract class Enterprise_Checkout_Block_Sku_Abstract
    extends Mage_Core_Block_Template
{
    /**
     * Retrieve form action URL
     *
     * @return string
     */
    abstract public function getFormAction();

    /**
     * Get request parameter name of SKU file imported flag
     *
     * @return string
     */
    public function getRequestParameterSkuFileImportedFlag()
    {
        return Enterprise_Checkout_Helper_Data::REQUEST_PARAMETER_SKU_FILE_IMPORTED_FLAG;
    }

    /**
     * Check whether form should be multipart
     *
     * @return bool
     */
    public function getIsMultipart()
    {
        return false;
    }

    /**
     * Get link to "Order by SKU" on customer's account page
     *
     * @return string
     */
    public function getLink()
    {
        $data = $this->getData();
        if (empty($data['link_display']) || empty($data['link_text'])) {
            return '';
        }

        /** @var $helper Enterprise_Checkout_Helper_Data */
        $helper = Mage::helper('enterprise_checkout');
        if (!$helper->isSkuEnabled() || !$helper->isSkuApplied()) {
            return '';
        }

        return '<a href="' . $helper->getAccountSkuUrl() . '">'
            . $this->escapeHtml($data['link_text']) . '</a>';
    }
}
