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
 * @package     Mage_Customer
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Customer dashboard addresses section
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Customer_Block_Account_Dashboard_Address extends Mage_Core_Block_Template
{
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    public function getPrimaryShippingAddressHtml()
    {
        $address = $this->getCustomer()->getPrimaryShippingAddress();

        if( $address instanceof Varien_Object ) {
            return $address->format('html');
        } else {
            return Mage::helper('customer')->__('You have not set a default shipping address.');
        }
    }

    public function getPrimaryBillingAddressHtml()
    {
        $address = $this->getCustomer()->getPrimaryBillingAddress();

        if( $address instanceof Varien_Object ) {
            return $address->format('html');
        } else {
            return Mage::helper('customer')->__('You have not set a default billing address.');
        }
    }

    public function getPrimaryShippingAddressEditUrl()
    {
        return Mage::getUrl('customer/address/edit', array('id'=>$this->getCustomer()->getDefaultShipping()));
    }

    public function getPrimaryBillingAddressEditUrl()
    {
        return Mage::getUrl('customer/address/edit', array('id'=>$this->getCustomer()->getDefaultBilling()));
    }

    public function getAddressBookUrl()
    {
        return $this->getUrl('customer/address/');
    }
}
