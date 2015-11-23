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
 * Shipment packaging
 *
 * @category    Enterprise
 * @package     Enterprise_RMA
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_General_Shipping_Packaging extends Mage_Adminhtml_Block_Template
{
    /**
     * Variable to store RMA instance
     *
     * @var null|Enterprise_Rma_Model_Rma
     */
    protected $_rma = null;

    /**
     * Declare rma instance
     *
     * @return  Enterprise_Rma_Model_Item
     */
    public function getRma()
    {
        if (is_null($this->_rma)) {
            $this->_rma = Mage::registry('current_rma');
        }
        return $this->_rma;
    }

    /**
     * Retrieve carrier
     *
     * @return string
     */
    public function getCarrier()
    {
        return Mage::helper('enterprise_rma')->getCarrier(
            $this->getRequest()->getParam('method'),
            $this->getRma()->getStoreId()
        );
    }

    /**
     * Retrieve carrier method
     *
     * @return null|string
     */
    public function getCarrierMethod()
    {
        $code = explode('_', $this->getRequest()->getParam('method'), 2);

        if (is_array($code) && isset($code[1])) {
            return $code[1];
        } else {
            return null;
        }
    }

    /**
     * Return container types of carrier
     *
     * @return array
     */
    public function getContainers()
    {
        $order      = $this->getRma()->getOrder();
        $storeId    = $this->getRma()->getStoreId();
        $address    = $order->getShippingAddress();
        $carrier    = $this->getCarrier();

        $countryRecipient = Mage::helper('enterprise_rma')->getReturnAddressModel($storeId)->getCountryId();
        if ($carrier) {
            $params = new Varien_Object(array(
                'method' => $this->getCarrierMethod(),
                'country_shipper' => $address->getCountryId(),
                'country_recipient' => $countryRecipient,
            ));
            return $carrier->getContainerTypes($params);
        }
        return array();
    }

    /**
     * Can display customs value
     *
     * @return bool
     */
    public function displayCustomsValue()
    {
        $storeId    = $this->getRma()->getStoreId();
        $order      = $this->getRma()->getOrder();
        $address                        = $order->getShippingAddress();
        $shipperAddressCountryCode      = $address->getCountryId();
        $recipientAddressCountryCode    = Mage::helper('enterprise_rma')
            ->getReturnAddressModel($storeId)->getCountryId();

        if ($shipperAddressCountryCode != $recipientAddressCountryCode) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return delivery confirmation types of current carrier
     *
     * @return array
     */
    public function getDeliveryConfirmationTypes()
    {
        $storeId    = $this->getRma()->getStoreId();
        $code       = $this->getRequest()->getParam('method');
        if (!empty($code)) {
            list($carrierCode, $methodCode) = explode('_', $code, 2);
            $carrier    = Mage::helper('enterprise_rma')->getCarrier($carrierCode, $storeId);
            $countryId  = Mage::helper('enterprise_rma')->getReturnAddressModel($storeId)->getCountryId();
            $params = new Varien_Object(array('country_recipient' => $countryId));

            if ($carrier && is_array($carrier->getDeliveryConfirmationTypes($params))) {
                return $carrier->getDeliveryConfirmationTypes($params);
            }
        }
        return array();
    }

    /**
     * Check whether girth is allowed for current carrier
     *
     * @return bool
     */
    public function isGirthAllowed()
    {
        $storeId    = $this->getRma()->getStoreId();
        $code       = $this->getRequest()->getParam('method');
        $girth      = false;
        if (!empty($code)) {
            list($carrierCode, $methodCode) = explode('_', $code, 2);
            $carrier    = Mage::helper('enterprise_rma')->getCarrier($carrierCode, $storeId);
            $countryId  = Mage::helper('enterprise_rma')->getReturnAddressModel($storeId)->getCountryId();

            $girth = $carrier->isGirthAllowed($countryId);
        }
        return $girth;
    }

    /**
     * Return girth status
     *
     * @return bool
     */
    public function isGirthEnabled()
    {
        $code       = $this->getRequest()->getParam('method');
        $girth      = false;
        if (!empty($code)) {
            $girth = (Mage::helper('usa')->displayGirthValue($code) && $this->isGirthAllowed()) ? 1 : 0;
        }

        return $girth;
    }

    /**
     * Return content types of package
     *
     * @return array
     */
    public function getContentTypes()
    {
        $storeId    = $this->getRma()->getStoreId();
        $code       = $this->getRequest()->getParam('method');
        if (!empty($code)) {
            list($carrierCode, $methodCode) = explode('_', $code, 2);
            $carrier    = Mage::helper('enterprise_rma')->getCarrier($carrierCode, $storeId);
            $countryId  = Mage::helper('enterprise_rma')->getReturnAddressModel($storeId)->getCountryId();

            $order              = Mage::getModel('sales/order')->load($this->getRma()->getOrderId());
            $shipperAddress     = $order->getShippingAddress();
             if ($carrier) {
                $params = new Varien_Object(array(
                    'method'            => $methodCode,
                    'country_shipper'   => $shipperAddress->getCountryId(),
                    'country_recipient' => $countryId,
                ));
                return $carrier->getContentTypes($params);
            }
        }

        return array();
    }

    /**
     * Return customizable containers status
     *
     * @return bool
     */
    public function getCustomizableContainersStatus()
    {
        $storeId = $this->getRma()->getStoreId();
        $code    = $this->getRequest()->getParam('method');
        $carrier = Mage::helper('enterprise_rma')->getCarrier($code, $storeId);
        if ($carrier) {
            $getCustomizableContainers =  $carrier->getCustomizableContainerTypes();

            if (in_array(key($this->getContainers()),$getCustomizableContainers)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return shipping carrier usps source sizes
     *
     * @return array
     */
    public function getShippingCarrierUspsSourceSize()
    {
        return Mage::getModel('usa/shipping_carrier_usps_source_size')->toOptionArray();
    }

    /**
     * Check size and girth parameter
     *
     * @return array
     */
    public function checkSizeAndGirthParameter()
    {
        $storeId = $this->getRma()->getStoreId();
        $code    = $this->getRequest()->getParam('method');
        $carrier = Mage::helper('enterprise_rma')->getCarrier($code, $storeId);

        $girthEnabled   = false;
        $sizeEnabled    = false;
        $regular        = $this->getShippingCarrierUspsSourceSize();
        if ($carrier && isset($regular[0]['value'])) {
            if ($regular[0]['value'] == Mage_Usa_Model_Shipping_Carrier_Usps::SIZE_LARGE
                && in_array(
                    key($this->getContainers()),
                    array(
                        Mage_Usa_Model_Shipping_Carrier_Usps::CONTAINER_NONRECTANGULAR,
                        Mage_Usa_Model_Shipping_Carrier_Usps::CONTAINER_VARIABLE,
                    )
                )
            ) {
                $girthEnabled = true;
            }

            if (in_array(
                key($this->getContainers()),
                array(
                    Mage_Usa_Model_Shipping_Carrier_Usps::CONTAINER_NONRECTANGULAR,
                    Mage_Usa_Model_Shipping_Carrier_Usps::CONTAINER_RECTANGULAR,
                    Mage_Usa_Model_Shipping_Carrier_Usps::CONTAINER_VARIABLE,
                )
            )) {
                $sizeEnabled = true;
            }
        }

        return array($girthEnabled, $sizeEnabled);
    }
}
