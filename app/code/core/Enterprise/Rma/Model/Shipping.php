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
 * RMA Shipping Model
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Model_Shipping extends Mage_Core_Model_Abstract
{
    /**
     * Store address
     */
    const XML_PATH_ADDRESS1             = 'sales/enterprise_rma/address';
    const XML_PATH_ADDRESS2             = 'sales/enterprise_rma/address1';
    const XML_PATH_CITY                 = 'sales/enterprise_rma/city';
    const XML_PATH_REGION_ID            = 'sales/enterprise_rma/region_id';
    const XML_PATH_ZIP                  = 'sales/enterprise_rma/zip';
    const XML_PATH_COUNTRY_ID           = 'sales/enterprise_rma/country_id';
    const XML_PATH_CONTACT_NAME         = 'sales/enterprise_rma/store_name';

    /**
     * Constants - value of is_admin field in table
     */
    const IS_ADMIN_STATUS_USER_TRACKING_NUMBER          = 0;
    const IS_ADMIN_STATUS_ADMIN_TRACKING_NUMBER         = 1;
    const IS_ADMIN_STATUS_ADMIN_LABEL                   = 2;
    const IS_ADMIN_STATUS_ADMIN_LABEL_TRACKING_NUMBER   = 3;

    /**
     * Tracking info
     *
     * @var array
     */
    protected $_trackingInfo = array();

    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('enterprise_rma/shipping');
    }

    /**
     * Processing object before save data
     *
     * @return Enterprise_Rma_Model_Shipping
     */
    protected function _beforeSave()
    {
        if (is_null($this->getIsAdmin())) {
            $this->setIsAdmin(self::IS_ADMIN_STATUS_USER_TRACKING_NUMBER);
        }
        return $this;
    }
    /**
     * Prepare and do return of shipment
     *
     * @return Varien_Object
     */
    public function requestToShipment()
    {
        $shipmentStoreId    = $this->getRma()->getStoreId();
        $storeInfo          = new Varien_Object(Mage::getStoreConfig('general/store_information', $shipmentStoreId));

        /** @var $order Mage_Sales_Model_Order */
        $order              = Mage::getModel('sales/order')->load($this->getRma()->getOrderId());
        $shipperAddress     = $order->getShippingAddress();
        $recipientAddress   = Mage::helper('enterprise_rma')->getReturnAddressModel($this->getRma()->getStoreId());

        list($carrierCode, $shippingMethod) = explode('_', $this->getCode(), 2);

        $shipmentCarrier    = Mage::helper('enterprise_rma')->getCarrier($this->getCode(), $shipmentStoreId);
        $baseCurrencyCode   = Mage::app()->getStore($shipmentStoreId)->getBaseCurrencyCode();

        if (!$shipmentCarrier) {
            Mage::throwException(Mage::helper('enterprise_rma')->__('Invalid carrier: %s.', $carrierCode));
        }

        $shipperRegionCode  = Mage::getModel('directory/region')->load($shipperAddress->getRegionId())->getCode();

        $recipientRegionCode= $recipientAddress->getRegionId();

        $recipientContactName = Mage::helper('enterprise_rma')->getReturnContactName($this->getRma()->getStoreId());

        if (!$recipientContactName->getName()
            || !$recipientContactName->getLastName()
            || !$recipientAddress->getCompany()
            || !$storeInfo->getPhone()
            || !$recipientAddress->getStreet(-1)
            || !$recipientAddress->getCity()
            || !$shipperRegionCode
            || !$recipientAddress->getPostcode()
            || !$recipientAddress->getCountryId()
        ) {
            Mage::throwException(
                Mage::helper('enterprise_rma')->__('Insufficient information to create shipping label(s). Please verify your Store Information and Shipping Settings.')
            );
        }

        /** @var $request Mage_Shipping_Model_Shipment_Request */
        $request = Mage::getModel('shipping/shipment_return');
        $request->setOrderShipment($this);

        $request->setShipperContactPersonName($order->getCustomerName());
        $request->setShipperContactPersonFirstName($order->getCustomerFirstname());
        $request->setShipperContactPersonLastName($order->getCustomerLastname());

        $companyName = $shipperAddress->getCompany();
        if (empty($companyName)) {
            $companyName = $order->getCustomerName();
        }
        $request->setShipperContactCompanyName($companyName);
        $request->setShipperContactPhoneNumber($shipperAddress->getTelephone());
        $request->setShipperEmail($shipperAddress->getEmail());
        $request->setShipperAddressStreet($shipperAddress->getStreetFull());
        $request->setShipperAddressStreet1($shipperAddress->getStreet1());
        $request->setShipperAddressStreet2($shipperAddress->getStreet2());
        $request->setShipperAddressCity($shipperAddress->getCity());
        $request->setShipperAddressStateOrProvinceCode($shipperRegionCode);
        $request->setShipperAddressPostalCode($shipperAddress->getPostcode());
        $request->setShipperAddressCountryCode($shipperAddress->getCountryId());

        $request->setRecipientContactPersonName($recipientContactName->getName());
        $request->setRecipientContactPersonFirstName($recipientContactName->getFirstName());
        $request->setRecipientContactPersonLastName($recipientContactName->getLastName());
        $request->setRecipientContactCompanyName($recipientAddress->getCompany());
        $request->setRecipientContactPhoneNumber($storeInfo->getPhone());
        $request->setRecipientEmail($recipientAddress->getEmail());
        $request->setRecipientAddressStreet($recipientAddress->getStreet(-1));
        $request->setRecipientAddressStreet1($recipientAddress->getStreet(1));
        $request->setRecipientAddressStreet2($recipientAddress->getStreet2(2));
        $request->setRecipientAddressCity($recipientAddress->getCity());
        $request->setRecipientAddressStateOrProvinceCode($recipientRegionCode);
        $request->setRecipientAddressRegionCode($recipientRegionCode);
        $request->setRecipientAddressPostalCode($recipientAddress->getPostcode());
        $request->setRecipientAddressCountryCode($recipientAddress->getCountryId());

        $request->setShippingMethod($shippingMethod);
        $request->setPackageWeight($this->getWeight());
        $request->setPackages($this->getPackages());
        $request->setBaseCurrencyCode($baseCurrencyCode);
        $request->setStoreId($shipmentStoreId);

        $referenceData = 'RMA #'. $request->getOrderShipment()->getRma()->getIncrementId(). ' P';
        $request->setReferenceData($referenceData);

        return $shipmentCarrier->returnOfShipment($request);
    }

    /**
     * Retrieve detail for shipment track
     *
     * @return string
     */
    public function getNumberDetail()
    {
        $carrierInstance = Mage::getSingleton('shipping/config')->getCarrierInstance($this->getCarrierCode());
        if (!$carrierInstance) {
            $custom = array();
            $custom['title']  = $this->getCarierTitle();
            $custom['number'] = $this->getTrackNumber();
            return $custom;
        } else {
            $carrierInstance->setStore($this->getStore());
        }

        if (!$trackingInfo = $carrierInstance->getTrackingInfo($this->getTrackNumber())) {
            return Mage::helper('enterprise_rma')->__('No detail for number "%s"', $this->getTrackNumber());
        }

        return $trackingInfo;
    }

    /**
     * Retrieve hash code of current order
     *
     * @return string
     */
    public function getProtectCode()
    {
        if ($this->getRmaEntityId()) {
            $rma = Mage::getModel('enterprise_rma/rma')->load($this->getRmaEntityId());
        }

        return (string)$rma->getProtectCode();
    }

    /**
     * Retrieves shipping label for current rma
     *
     * @var Enterprise_Rma_Model_Rma|int $rma
     * @return string
     */
    public function getShippingLabelByRma($rma)
    {
        if (!is_int($rma)) {
            $rma = $rma->getId();
        }
        $label = $this->getCollection()
            ->addFieldToFilter('rma_entity_id', $rma)
            ->addFieldToFilter('is_admin', self::IS_ADMIN_STATUS_ADMIN_LABEL)
            ->getFirstItem();

        if ($label->getShippingLabel()) {
            $label->setShippingLabel(
                $this->getResource()->getReadConnection()->decodeVarbinary($label->getShippingLabel())
            );
        }

        return $label;
    }

    /**
     * Create Zend_Pdf_Page instance with image from $imageString. Supports JPEG, PNG, GIF, WBMP, and GD2 formats.
     *
     * @param string $imageString
     * @return Zend_Pdf_Page|bool
     */
    public function createPdfPageFromImageString($imageString)
    {
        $image = imagecreatefromstring($imageString);
        if (!$image) {
            return false;
        }

        $xSize = imagesx($image);
        $ySize = imagesy($image);
        $page = new Zend_Pdf_Page($xSize, $ySize);

        imageinterlace($image, 0);
        $tmpFileName = sys_get_temp_dir() . DS . 'shipping_labels_'
                     . uniqid(mt_rand()) . time() . '.png';
        imagepng($image, $tmpFileName);
        $pdfImage = Zend_Pdf_Image::imageWithPath($tmpFileName);
        $page->drawImage($pdfImage, 0, 0, $xSize, $ySize);
        unlink($tmpFileName);
        return $page;
    }
}
