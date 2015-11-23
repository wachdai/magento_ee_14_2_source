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
 * GiftRegistry shipping Address block
 *
 * @category   Enterprise
 * @package    Enterprise_GiftRegistry
 * @author     Magento Core Team <core@magentocommerce.com>
 */
 class Enterprise_GiftRegistry_Block_Customer_Address_Edit extends Enterprise_GiftRegistry_Block_Customer_Edit_Abstract
{

    /**
     * Contains logged in customer
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;

    /**
     * Getter for entity object
     * @return Enterprise_GiftRegistry_Model_Entity
     */
    public function getEntity()
    {
        return Mage::registry('enterprise_giftregistry_entity');
    }

    /**
     * Getter for address object
     *
     * @return Mage_Customer_Model_Address
     */
    public function getAddress()
    {
        return Mage::registry('enterprise_giftregistry_address');
    }

    /**
     * Check customer has address
     *
     * @return bool
     */
    public function customerHasAddresses()
    {
        return count($this->getCustomer()->getAddresses());
    }

    /**
     * Return html select input element for Address (None/<address1>/<address2>/New/)
     *
     * @param string $domId
     * @return html
     */
    public function getAddressHtmlSelect($domId = 'address_type_or_id')
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array(array(
                'value' => Enterprise_GiftRegistry_Helper_Data::ADDRESS_NONE,
                'label' => Mage::helper('enterprise_giftregistry')->__('None')
            ));
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }
            $options[] = array(
                'value' => Enterprise_GiftRegistry_Helper_Data::ADDRESS_NEW,
                'label' => Mage::helper('enterprise_giftregistry')->__('New Address')
            );

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName('address_type_or_id')
                ->setId($domId)
                ->setClass('address-select')
                ->setOptions($options);

            return $select->getHtml();
        }
        return '';
    }

    /**
     * Get logged in customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (empty($this->_customer)) {
            $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        return $this->_customer;
    }

    /**
     * Checking customer loggin status
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
}
