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
 * @package     Enterprise_Rma
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Request Details Block at RMA page
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_General_Details
    extends Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_General_Abstract
{
    /**
     * Get order link (href address)
     *
     * @return string
     */
    public function getOrderLink()
    {
        return $this->getUrl('*/sales_order/view', array('order_id'=>$this->getOrder()->getId()));
    }

    /**
     * Get order increment id
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }

    /**
     * Get Link to Customer's Page
     *
     * Gets address for link to customer's page.
     * Returns null for guest-checkout orders
     *
     * @return string|null
     */
    public function getCustomerLink()
    {
        if ($this->getOrder()->getCustomerIsGuest()) {
            return false;
        }
        return $this->getUrl('*/customer/edit', array('id' => $this->getOrder()->getCustomerId()));
    }

    /**
     * Get Customer Email
     *
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->escapeHtml($this->getOrder()->getCustomerEmail());
    }

    /**
     * Get Customer Email
     *
     * @return string
     */
    public function getCustomerContactEmail()
    {
        return $this->escapeHtml($this->getRmaData('customer_custom_email'));
    }

}
