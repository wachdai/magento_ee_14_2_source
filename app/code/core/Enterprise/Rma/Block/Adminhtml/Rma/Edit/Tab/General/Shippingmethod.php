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
 * Shipping Method Block at RMA page
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_General_Shippingmethod
    extends Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_General_Abstract
{

    /**
     * PSL Button statuses
     */
    const PSL_DISALLOWED    = 0;
    const PSL_ALLOWED       = 1;
    const PSL_DISABLED      = 2;

    /**
     * Variable to store RMA instance
     *
     * @var null|Enterprise_Rma_Model_Rma
     */
    protected $_rma = null;

    public function _construct()
    {
        $buttonStatus       = Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_General_Shippingmethod::PSL_DISALLOWED;
        if ((bool)($this->_getShippingAvailability() && $this->getRma()->isAvailableForPrintLabel())) {
            $buttonStatus   = Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_General_Shippingmethod::PSL_ALLOWED;
        } elseif((bool)$this->getRma()->getButtonDisabledStatus()) {
            $buttonStatus   = Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_General_Shippingmethod::PSL_DISABLED;
        }

        $this->setIsPsl($buttonStatus);
    }

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
     * Defines whether Shipping method settings allow to create shipping label
     *
     * @return bool
     */
    protected function _getShippingAvailability()
    {
        $carriers = Mage::helper('enterprise_rma')->getAllowedShippingCarriers($this->getRma()->getStoreId());
        return !empty($carriers);
    }

    /**
     * Retrieve shipment model instance
     *
     * @return Enterprise_Rma_Model_Shipping
     */
    public function getShipment()
    {
        return Mage::getModel('enterprise_rma/shipping')
            ->getShippingLabelByRma($this->getRma());
    }

    /**
     * Return price according to store
     *
     * @param  string $price
     * @return double
     */
    public function getShippingPrice($price)
    {
        return Mage::app()
            ->getStore($this->getRma()->getStoreId())
            ->convertPrice(
                Mage::helper('tax')->getShippingPrice(
                    $price
                ),
                true,
                false
            )
        ;
    }

    /**
     * Get packed products in packages
     *
     * @return array
     */
    public function getPackages()
    {
        $packages = $this->getShipment()->getPackages();
        if ($packages) {
            $packages = unserialize($packages);
        } else {
            $packages = array();
        }
        return $packages;
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
        $carrierCode= $this->getShipment()->getCarrierCode();
        if (!$carrierCode) {
            return false;
        }
        $address    = $order->getShippingAddress();
        $shipperAddressCountryCode  = $address->getCountryId();
        $recipientAddressCountryCode= Mage::helper('enterprise_rma')->getReturnAddressModel($storeId)->getCountryId();

        if (($carrierCode == 'fedex' || $carrierCode == 'dhl')
            && $shipperAddressCountryCode != $recipientAddressCountryCode) {
            return true;
        }
        return false;
    }

    /**
     * Get print label button html
     *
     * @return string
     */
    public function getPrintLabelButton()
    {
        $data['id'] = $this->getRma()->getId();
        $url        = $this->getUrl('*/rma/printLabel', $data);

        return $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('enterprise_rma')->__('Print Shipping Label'),
                'onclick' => 'setLocation(\'' . $url . '\')'
            ))
            ->toHtml();
    }

    /**
     * Show packages button html
     *
     * @return string
     */
    public function getShowPackagesButton()
    {
        return $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('enterprise_rma')->__('Show Packages'),
                'onclick' => 'showPackedWindow();'
            ))
            ->toHtml();
    }

    /**
     * Print button for creating pdf
     *
     * @return string
     */
    public function getPrintButton()
    {
        $data['id'] = $this->getRma()->getId();
        $url        = $this->getUrl('*/rma/printPackage', $data);

        return $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('enterprise_rma')->__('Print'),
                'onclick' => 'setLocation(\'' . $url . '\')'
            ))
            ->toHtml();
    }

    /**
     * Return name of container type by its code
     *
     * @param string $code
     * @return string
     */
    public function getContainerTypeByCode($code)
    {
        $carrierCode= $this->getShipment()->getCarrierCode();
        $carrier    = Mage::helper('enterprise_rma')->getCarrier($carrierCode, $this->getRma()->getStoreId());
        if ($carrier) {
            $containerTypes = $carrier->getContainerTypes();
            $containerType = !empty($containerTypes[$code]) ? $containerTypes[$code] : '';
            return $containerType;
        }
        return '';
    }

    /**
     * Return name of delivery confirmation type by its code
     *
     * @param string $code
     * @return string
     */
    public function getDeliveryConfirmationTypeByCode($code)
    {
        $storeId    = $this->getRma()->getStoreId();
        $countryId  = Mage::helper('enterprise_rma')->getReturnAddressModel($storeId)->getCountryId();
        $carrierCode= $this->getShipment()->getCarrierCode();
        $carrier    = Mage::helper('enterprise_rma')->getCarrier($carrierCode, $this->getRma()->getStoreId());
        if ($carrier) {
            $params = new Varien_Object(array('country_recipient' => $countryId));
            $confirmationTypes = $carrier->getDeliveryConfirmationTypes($params);
            $containerType = !empty($confirmationTypes[$code]) ? $confirmationTypes[$code] : '';
            return $containerType;
        }
        return '';
    }

    /**
     * Display formatted price
     *
     * @param float $price
     * @return string
     */
    public function displayPrice($price)
    {
        return $this->getRma()->getOrder()->formatPriceTxt($price);
    }

    /**
     * Get ordered qty of item
     *
     * @param int $itemId
     * @return int|null
     */
    public function getQtyOrderedItem($itemId)
    {
        if ($itemId) {
            return $this->getRma()->getOrder()->getItemById($itemId)->getQtyOrdered()*1;
        } else {
            return;
        }
    }

    /**
     * Return content types of package
     *
     * @return array
     */
    public function getContentTypes()
    {
        $order      = $this->getRma()->getOrder();
        $storeId    = $this->getRma()->getStoreId();
        $address    = $order->getShippingAddress();

        $carrierCode= $this->getShipment()->getCarrierCode();
        $carrier    = Mage::helper('enterprise_rma')->getCarrier($carrierCode, $storeId);

        $countryShipper = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $storeId);
        if ($carrier) {
            $params = new Varien_Object(array(
                'method'            => $carrier->getMethod(),
                'country_shipper'   => $countryShipper,
                'country_recipient' => $address->getCountryId(),
            ));
            return $carrier->getContentTypes($params);
        }
        return array();
    }

    /**
     * Return name of content type by its code
     *
     * @param string $code
     * @return string
     */
    public function getContentTypeByCode($code)
    {
        $contentTypes = $this->getContentTypes();
        if (!empty($contentTypes[$code])) {
            return $contentTypes[$code];
        }
        return '';
    }
}
