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
 * @package     Enterprise_Customer
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Customer observer
 *
 */
class Enterprise_Customer_Model_Observer
{
    const CONVERT_ALGORITM_SOURCE_TARGET_WITH_PREFIX = 1;
    const CONVERT_ALGORITM_SOURCE_WITHOUT_PREFIX     = 2;
    const CONVERT_ALGORITM_TARGET_WITHOUT_PREFIX     = 3;

    const CONVERT_TYPE_CUSTOMER             = 'customer';
    const CONVERT_TYPE_CUSTOMER_ADDRESS     = 'customer_address';

    /**
     * After load observer for quote
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function salesQuoteAfterLoad(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if ($quote instanceof Mage_Core_Model_Abstract) {
            Mage::getModel('enterprise_customer/sales_quote')
                ->load($quote->getId())
                ->attachAttributeData($quote);
        }

        return $this;
    }

    /**
     * After load observer for collection of quote address
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function salesQuoteAddressCollectionAfterLoad(Varien_Event_Observer $observer)
    {
        $collection = $observer->getEvent()->getQuoteAddressCollection();
        if ($collection instanceof Varien_Data_Collection_Db) {
            Mage::getModel('enterprise_customer/sales_quote_address')
                ->attachDataToCollection($collection);
        }

        return $this;
    }

    /**
     * After save observer for quote
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function salesQuoteAfterSave(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if ($quote instanceof Mage_Core_Model_Abstract) {
            Mage::getModel('enterprise_customer/sales_quote')
                ->saveAttributeData($quote);
        }

        return $this;
    }

    /**
     * After save observer for quote address
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function salesQuoteAddressAfterSave(Varien_Event_Observer $observer)
    {
        $quoteAddress = $observer->getEvent()->getQuoteAddress();
        if ($quoteAddress instanceof Mage_Core_Model_Abstract) {
            Mage::getModel('enterprise_customer/sales_quote_address')
                ->saveAttributeData($quoteAddress);
        }

        return $this;
    }

    /**
     * After load observer for order
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function salesOrderAfterLoad(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof Mage_Core_Model_Abstract) {
            Mage::getModel('enterprise_customer/sales_order')
                ->load($order->getId())
                ->attachAttributeData($order);
        }

        return $this;
    }

    /**
     * After load observer for collection of order address
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function salesOrderAddressCollectionAfterLoad(Varien_Event_Observer $observer)
    {
        $collection = $observer->getEvent()->getOrderAddressCollection();
        if ($collection instanceof Varien_Data_Collection_Db) {
            Mage::getModel('enterprise_customer/sales_order_address')
                ->attachDataToCollection($collection);
        }

        return $this;
    }

    /**
     * After save observer for order
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function salesOrderAfterSave(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof Mage_Core_Model_Abstract) {
            Mage::getModel('enterprise_customer/sales_order')
                ->saveAttributeData($order);
        }

        return $this;
    }

    /**
     * After save observer for order address
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function salesOrderAddressAfterSave(Varien_Event_Observer $observer)
    {
        $orderAddress = $observer->getEvent()->getAddress();
        if ($orderAddress instanceof Mage_Core_Model_Abstract) {
            Mage::getModel('enterprise_customer/sales_order_address')
                ->saveAttributeData($orderAddress);
        }

        return $this;
    }

    /**
     * Before save observer for customer attribute
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function enterpriseCustomerAttributeBeforeSave(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute instanceof Mage_Customer_Model_Attribute && $attribute->isObjectNew()) {
            /**
             * Check for maximum attribute_code length
             */
            $attributeCodeMaxLength = Mage_Eav_Model_Entity_Attribute::ATTRIBUTE_CODE_MAX_LENGTH - 9;
            $validate = Zend_Validate::is($attribute->getAttributeCode(), 'StringLength', array(
                'max' => $attributeCodeMaxLength
            ));
            if (!$validate) {
                throw Mage::exception('Mage_Eav',
                    Mage::helper('eav')->__('Maximum length of attribute code must be less then %s symbols', $attributeCodeMaxLength)
                );
            }
        }

        return $this;
    }

    /**
     * After save observer for customer attribute
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function enterpriseCustomerAttributeSave(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute instanceof Mage_Customer_Model_Attribute && $attribute->isObjectNew()) {
            Mage::getModel('enterprise_customer/sales_quote')
                ->saveNewAttribute($attribute);
            Mage::getModel('enterprise_customer/sales_order')
                ->saveNewAttribute($attribute);
        }

        return $this;
    }

    /**
     * After delete observer for customer attribute
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function enterpriseCustomerAttributeDelete(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute instanceof Mage_Customer_Model_Attribute && !$attribute->isObjectNew()) {
            Mage::getModel('enterprise_customer/sales_quote')
                ->deleteAttribute($attribute);
            Mage::getModel('enterprise_customer/sales_order')
                ->deleteAttribute($attribute);
        }

        return $this;
    }

    /**
     * After save observer for customer address attribute
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function enterpriseCustomerAddressAttributeSave(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute instanceof Mage_Customer_Model_Attribute && $attribute->isObjectNew()) {
            Mage::getModel('enterprise_customer/sales_quote_address')
                ->saveNewAttribute($attribute);
            Mage::getModel('enterprise_customer/sales_order_address')
                ->saveNewAttribute($attribute);
        }

        return $this;
    }

    /**
     * After delete observer for customer address attribute
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function enterpriseCustomerAddressAttributeDelete(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute instanceof Mage_Customer_Model_Attribute && !$attribute->isObjectNew()) {
            Mage::getModel('enterprise_customer/sales_quote_address')
                ->deleteAttribute($attribute);
            Mage::getModel('enterprise_customer/sales_order_address')
                ->deleteAttribute($attribute);
        }

        return $this;
    }

    /**
     * Observer for converting quote to order
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function coreCopyFieldsetSalesConvertQuoteToOrder(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset(
            $observer,
            self::CONVERT_ALGORITM_SOURCE_TARGET_WITH_PREFIX,
            self::CONVERT_TYPE_CUSTOMER
        );

        return $this;
    }

    /**
     * Observer for converting quote address to order address
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function coreCopyFieldsetSalesConvertQuoteAddressToOrderAddress(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset(
            $observer,
            self::CONVERT_ALGORITM_SOURCE_TARGET_WITH_PREFIX,
            self::CONVERT_TYPE_CUSTOMER_ADDRESS
        );

        return $this;
    }

    /**
     * Observer for converting order to quote
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function coreCopyFieldsetSalesCopyOrderToEdit(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset(
            $observer,
            self::CONVERT_ALGORITM_SOURCE_TARGET_WITH_PREFIX,
            self::CONVERT_TYPE_CUSTOMER
        );

        return $this;
    }

    /**
     * Observer for converting order billing address to quote billing address
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function coreCopyFieldsetSalesCopyOrderBillingAddressToOrder(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset(
            $observer,
            self::CONVERT_ALGORITM_SOURCE_TARGET_WITH_PREFIX,
            self::CONVERT_TYPE_CUSTOMER_ADDRESS
        );

        return $this;
    }

    /**
     * Observer for converting order shipping address to quote shipping address
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function coreCopyFieldsetSalesCopyOrderShippingAddressToOrder(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset(
            $observer,
            self::CONVERT_ALGORITM_SOURCE_TARGET_WITH_PREFIX,
            self::CONVERT_TYPE_CUSTOMER_ADDRESS
        );

        return $this;
    }

    /**
     * Observer for converting customer to quote
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function coreCopyFieldsetCustomerAccountToQuote(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset(
            $observer,
            self::CONVERT_ALGORITM_SOURCE_WITHOUT_PREFIX,
            self::CONVERT_TYPE_CUSTOMER
        );

        return $this;
    }

    /**
     * Observer for converting customer address to quote address
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function coreCopyFieldsetCustomerAddressToQuoteAddress(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset(
            $observer,
            self::CONVERT_ALGORITM_SOURCE_WITHOUT_PREFIX,
            self::CONVERT_TYPE_CUSTOMER_ADDRESS
        );

        return $this;
    }

    /**
     * Observer for converting quote address to customer address
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function coreCopyFieldsetQuoteAddressToCustomerAddress(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset(
            $observer,
            self::CONVERT_ALGORITM_SOURCE_WITHOUT_PREFIX,
            self::CONVERT_TYPE_CUSTOMER_ADDRESS
        );

        return $this;
    }

    /**
     * Observer for converting quote to customer
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Customer_Model_Observer
     */
    public function coreCopyFieldsetCheckoutOnepageQuoteToCustomer(Varien_Event_Observer $observer)
    {
        $this->_copyFieldset(
            $observer,
            self::CONVERT_ALGORITM_TARGET_WITHOUT_PREFIX,
            self::CONVERT_TYPE_CUSTOMER
        );

        return $this;
    }

    /**
     * CopyFieldset converts customer attributes from source object to target object
     *
     * @param Varien_Event_Observer $observer
     * @param int $algoritm
     * @param int $convertType
     * @return Enterprise_Customer_Model_Observer
     */
    protected function _copyFieldset(Varien_Event_Observer $observer, $algoritm, $convertType)
    {
        $source = $observer->getEvent()->getSource();
        $target = $observer->getEvent()->getTarget();

        if ($source instanceof Mage_Core_Model_Abstract && $target instanceof Mage_Core_Model_Abstract) {
            if ($convertType == self::CONVERT_TYPE_CUSTOMER) {
                $attributes = Mage::helper('enterprise_customer')->getCustomerUserDefinedAttributeCodes();
                $prefix     = 'customer_';
            } else if ($convertType == self::CONVERT_TYPE_CUSTOMER_ADDRESS) {
                $attributes = Mage::helper('enterprise_customer')->getCustomerAddressUserDefinedAttributeCodes();
                $prefix     = '';
            } else {
                return $this;
            }

            foreach ($attributes as $attribute) {
                switch ($algoritm) {
                    case self::CONVERT_ALGORITM_SOURCE_TARGET_WITH_PREFIX:
                        $sourceAttribute = $prefix . $attribute;
                        $targetAttribute = $prefix . $attribute;
                        break;
                    case self::CONVERT_ALGORITM_SOURCE_WITHOUT_PREFIX:
                        $sourceAttribute = $attribute;
                        $targetAttribute = $prefix . $attribute;
                        break;
                    case self::CONVERT_ALGORITM_TARGET_WITHOUT_PREFIX:
                        $sourceAttribute = $prefix . $attribute;
                        $targetAttribute = $attribute;
                        break;
                    default:
                        return $this;
                }
                $target->setData($targetAttribute, $source->getData($sourceAttribute));
            }
        }

        return $this;
    }
}
