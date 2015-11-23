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
 * RMA Helper
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Variable to contain country model
     *
     * @var Mage_Directory_Model_Country
     */
    protected $_countryModel = null;

    /**
     * Variable to contain order items collection for RMA creating
     *
     * @var Mage_Sales_Model_Resource_Order_Item_Collection
     */
    protected $_orderItems = null;

    /**
     * Allowed hash keys for shipment tracking
     *
     * @var array
     */
    protected $_allowedHashKeys = array('rma_id', 'track_id');

    /**
     * Checks whether RMA module is enabled for frontend in system config
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(Enterprise_Rma_Model_Rma::XML_PATH_ENABLED);
    }

    /**
     * Checks for ability to create RMA
     *
     * @param  int|Mage_Sales_Model_Order $order
     * @param  bool $forceCreate - @deprecated since version 1.13.2.0
     * @return bool
     */
    public function canCreateRma($order, $forceCreate = false)
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $items = $this->getOrderItems($order);
        if ($items->count()) {
            return true;
        }

        return false;
    }

    /**
     * Checks for ability to create RMA for admin user.
     * This function checks whether admin has access to order items
     *
     * @param  int|Mage_Sales_Model_Order $order
     * @return bool
     */
    public function canCreateRmaByAdmin($order)
    {
        $items = $this->getOrderItems($order, false, true);
        if ($items->count()) {
            return true;
        }

        return false;
    }

    /**
     * Gets available order items collection for RMA creating
     *
     * @param  int|Mage_Sales_Model_Order $orderId
     * @param  bool $onlyParents If needs only parent items (only for backend)
     * @param  bool $isAdmin whether need to check admin access to the product
     * @throws Mage_Core_Exception
     * @return Mage_Sales_Model_Resource_Order_Item_Collection
     */
    public function getOrderItems($orderId, $onlyParents = false, $isAdmin = false)
    {
        if ($orderId instanceof Mage_Sales_Model_Order) {
            $orderId = $orderId->getId();
        }
        if (!is_numeric($orderId)) {
            Mage::throwException($this->__('It isn\'t valid order'));
        }
        if (is_null($this->_orderItems) || !isset($this->_orderItems[$orderId])) {
            if (!$isAdmin) {
                $this->_orderItems[$orderId] = Mage::getResourceModel('enterprise_rma/item')->getOrderItems($orderId);
            } else {
                $this->_orderItems[$orderId] =
                        Mage::getResourceModel('enterprise_rma/item')->getOrderItemsForAdmin($orderId);
            }
        }

        if ($onlyParents) {
            foreach ($this->_orderItems[$orderId] as &$item) {
                if ($item->getParentItemId()) {
                    $this->_orderItems[$orderId]->removeItemByKey($item->getId());
                }
                if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    $productOptions = $item->getProductOptions();
                    $item->setName($productOptions['simple_name']);
                }
            }
        }

        return $this->_orderItems[$orderId];
    }

    /**
     * Get url for rma create
     *
     * @param  Mage_Sales_Model_Order $order
     * @return string
     */
    public function getReturnCreateUrl($order)
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::getUrl('rma/return/create', array('order_id' => $order->getId()));
        } else {
            return Mage::getUrl('rma/guest/create', array('order_id' => $order->getId()));
        }
    }

    /**
     * Get formatted return address
     *
     * @param string $formatCode
     * @param array $data - array of address data
     * @param int|null $storeId - Store Id
     * @return string
     */
    public function getReturnAddress($formatCode = 'html', $data = array(), $storeId = null)
    {
        if (empty($data)) {
            $data = $this->_getAddressData($storeId);
        }

        $format = null;

        if (isset($data['countryId'])) {
            $countryModel = $this->_getCountryModel()->load($data['countryId']);
            $format = $countryModel->getFormat($formatCode);
        }

        if (!$format) {
            $path = sprintf('%s%s', Mage_Customer_Model_Address_Config::XML_PATH_ADDRESS_TEMPLATE, $formatCode);
            $format = Mage::getStoreConfig($path, $storeId);
        }

        $formater = new Varien_Filter_Template();
        $formater->setVariables($data);
        return $formater->filter($format);
    }

    /**
     * Get return contact name
     *
     * @param int|null $storeId
     * @return Varien_Object
     */
    public function getReturnContactName($storeId = null)
    {
        $contactName = new Varien_Object();
        if (Mage::getStoreConfigFlag(Enterprise_Rma_Model_Rma::XML_PATH_USE_STORE_ADDRESS, $storeId)) {
            $admin = Mage::getSingleton('admin/session')->getUser();
            $contactName->setFirstName($admin->getFirstname());
            $contactName->setLastName($admin->getLastname());
            $contactName->setName($admin->getName());
        } else {
            $name = Mage::getStoreConfig(Enterprise_Rma_Model_Shipping::XML_PATH_CONTACT_NAME, $storeId);
            $contactName->setFirstName('');
            $contactName->setLastName($name);
            $contactName->setName($name);
        }
        return $contactName;
    }

    /**
     * Get return address model
     *
     * @param int|null $storeId
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getReturnAddressModel($storeId = null)
    {
        $addressModel = Mage::getModel('sales/quote_address');
        $addressModel->setData($this->_getAddressData($storeId));
        $addressModel->setCountryId($addressModel->getData('countryId'));
        $addressModel->setStreet($addressModel->getData('street1')."\n".$addressModel->getData('street2'));

        return $addressModel;
    }

    /**
     * Get return address array depending on config settings
     *
     * @param Mage_Core_Model_Store|null|int $store
     * @return array
     */
    protected function _getAddressData($store = null)
    {
        if (!$store) {
            $store = Mage::app()->getStore();
        }

        if (Mage::getStoreConfigFlag(Enterprise_Rma_Model_Rma::XML_PATH_USE_STORE_ADDRESS, $store)) {
            $data = array(
                'city'      => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_CITY, $store),
                'countryId' => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $store),
                'postcode'  => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ZIP, $store),
                'region_id' => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_REGION_ID, $store),
                'street2'   => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS2, $store),
                'street1'   => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS1, $store)
            );
        } else {
            $data = array(
                'city'      => Mage::getStoreConfig(Enterprise_Rma_Model_Shipping::XML_PATH_CITY, $store),
                'countryId' => Mage::getStoreConfig(Enterprise_Rma_Model_Shipping::XML_PATH_COUNTRY_ID, $store),
                'postcode'  => Mage::getStoreConfig(Enterprise_Rma_Model_Shipping::XML_PATH_ZIP, $store),
                'region_id' => Mage::getStoreConfig(Enterprise_Rma_Model_Shipping::XML_PATH_REGION_ID, $store),
                'street2'   => Mage::getStoreConfig(Enterprise_Rma_Model_Shipping::XML_PATH_ADDRESS2, $store),
                'street1'   => Mage::getStoreConfig(Enterprise_Rma_Model_Shipping::XML_PATH_ADDRESS1, $store),
                'firstname' => Mage::getStoreConfig(Enterprise_Rma_Model_Shipping::XML_PATH_CONTACT_NAME, $store)
            );
        }

        $data['country']    = $this->_getCountryModel()->loadByCode($data['countryId'])->getName();
        $region             = Mage::getModel('directory/region')->load($data['region_id']);
        $data['region_id']  = $region->getCode();
        $data['region']     = $region->getName();
        $data['company']    = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME, $store);
        $data['telephone']  = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_PHONE, $store);

        return $data;
    }

    /**
     * Get Country model
     *
     * @return Mage_Directory_Model_Country
     */
    protected function _getCountryModel()
    {
        if (is_null($this->_countryModel)) {
            $this->_countryModel = Mage::getModel('directory/country');
        }
        return $this->_countryModel;
    }

    /**
     * Get Contact Email Address title
     *
     * @return string
     */
    public function getContactEmailLabel()
    {
        return $this->__('Contact Email Address');
    }

    /**
     * Get key=>value array of "big four" shipping carriers with store-defined labels
     *
     * @param int|Mage_Core_Model_Store|null $store
     * @return array
     */
    public function getShippingCarriers($store = null)
    {
        $return = array();
        foreach (array('dhl', 'fedex', 'ups', 'usps') as $carrier) {
            $return[$carrier] = Mage::getStoreConfig('carriers/' . $carrier . '/title', $store);
        }
        return $return;
    }

    /**
     * Get key=>value array of enabled in website and enabled for RMA shipping carriers
     * from "big four" with their store-defined labels
     *
     * @param int|Mage_Core_Model_Store|null $store
     * @return array
     */
    public function getAllowedShippingCarriers($store = null)
    {
        $return = $this->getShippingCarriers($store);
        foreach (array_keys($return) as $carrier) {
            if (!Mage::getStoreConfig('carriers/' . $carrier . '/active_rma', $store)) {
                unset ($return[$carrier]);
            }
        }
        return $return;
    }

    /**
     * Retrieve carrier
     *
     * @param string $code Shipping method code
     * @param mixed $storeId
     * @return false|Mage_Usa_Model_Shipping_Carrier_Abstract
     */
    public function getCarrier($code, $storeId = null)
    {
        $data           = explode('_', $code, 2);
        $carrierCode    = $data[0];

        if (!Mage::getStoreConfig('carriers/' . $carrierCode . '/active_rma', $storeId)) {
            return false;
        }
        $className = Mage::getStoreConfig('carriers/'.$carrierCode.'/model', $storeId);
        if (!$className) {
            return false;
        }
        $obj = Mage::getModel($className);
        if ($storeId) {
            $obj->setStore($storeId);
        }
        return $obj;
    }

    /**
     * Shipping package popup URL getter
     *
     * @param $model Enterprise_Rma_Model_Rma
     * @param $action string
     * @return string
     */
    public function getPackagePopupUrlByRmaModel($model, $action = 'package')
    {
        $key    = 'rma_id';
        $method = 'getId';
        $param = array(
             'hash' => Mage::helper('core')->urlEncode("{$key}:{$model->$method()}:{$model->getProtectCode()}")
        );

         $storeId = is_object($model) ? $model->getStoreId() : null;
         $storeModel = Mage::app()->getStore($storeId);
         return $storeModel->getUrl('rma/tracking/'.$action, $param);
    }

    /**
     * Shipping tracking popup URL getter
     *
     * @param $track
     * @return string
     */
    public function getTrackingPopupUrlBySalesModel($track)
    {
        if ($track instanceof Enterprise_Rma_Model_Rma) {
            return $this->_getTrackingUrl('rma_id', $track);
        } elseif ($track instanceof Enterprise_Rma_Model_Shipping) {
            return $this->_getTrackingUrl('track_id', $track, 'getEntityId');
        }
    }

    /**
     * Retrieve tracking url with params
     *
     * @param  string $key
     * @param  Enterprise_Rma_Model_Shipping|Enterprise_Rma_Model_Rma $model
     * @param  string $method - option
     * @return string
     */
    protected function _getTrackingUrl($key, $model, $method = 'getId')
    {
         $param = array(
             'hash' => Mage::helper('core')->urlEncode("{$key}:{$model->$method()}:{$model->getProtectCode()}")
         );

         $storeId = is_object($model) ? $model->getStoreId() : null;
         $storeModel = Mage::app()->getStore($storeId);
         return $storeModel->getUrl('rma/tracking/popup', $param);
    }

    /**
     * Decode url hash
     *
     * @param  string $hash
     * @return array
     */
    public function decodeTrackingHash($hash)
    {
        $hash = explode(':', Mage::helper('core')->urlDecode($hash));
        if (count($hash) === 3 && in_array($hash[0], $this->_allowedHashKeys)) {
            return array('key' => $hash[0], 'id' => (int)$hash[1], 'hash' => $hash[2]);
        }
        return array();
    }

    /**
     * Get whether selected product is returnable
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int|null $storeId
     * @return bool
     */
    public function canReturnProduct($product, $storeId = null)
    {
        $isReturnable = $product->getIsReturnable();

        if (is_null($isReturnable)) {
            $isReturnable = Enterprise_Rma_Model_Product_Source::ATTRIBUTE_ENABLE_RMA_USE_CONFIG;
        }
        switch ($isReturnable) {
            case Enterprise_Rma_Model_Product_Source::ATTRIBUTE_ENABLE_RMA_YES:
                return true;
            case Enterprise_Rma_Model_Product_Source::ATTRIBUTE_ENABLE_RMA_NO:
                return false;
            default: //Use config and NULL
                return Mage::getStoreConfig(Enterprise_Rma_Model_Product_Source::XML_PATH_PRODUCTS_ALLOWED, $storeId);
        }
    }

    /**
     * Get formated date in store timezone
     *
     * @param   string $date
     * @return  string
     */
    public function getFormatedDate($date)
    {
        $storeDate = Mage::app()->getLocale()
            ->storeDate(Mage::app()->getStore(), Varien_Date::toTimestamp($date), true);

        return Mage::helper('core')
            ->formatDate($storeDate, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    /**
     * Retrieves RMA item name for backend
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return string
     */
    public function getAdminProductName($item)
    {
        $name   = $item->getName();
        $result = array();
        if ($options = $item->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }

            if (!empty($result)) {
                $implode = array();
                foreach ($result as $val) {
                    $implode[] =  isset($val['print_value']) ? $val['print_value'] : $val['value'];
                }
                return $name.' ('.implode(', ', $implode).')';
            }
        }
        return $name;
    }

    /**
     * Retrieves RMA item sku for backend
     *
     * @param  Mage_Sales_Model_Order_Item $item
     * @return string
     */
    public function getAdminProductSku($item)
    {
        $name = $item->getSku();
        if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $productOptions = $item->getProductOptions();

            return $productOptions['simple_sku'];
        }
        return $name;
    }

    /**
     * Parses quantity depending on isQtyDecimal flag
     *
     * @param float $quantity
     * @param Enterprise_Rma_Model_Item $item
     * @return int|float
     */
    public function parseQuantity($quantity, $item)
    {
        if (is_null($quantity)) {
             $quantity = $item->getOrigData('qty_requested');
        }
        if ($item->getIsQtyDecimal()) {
            return sprintf("%01.4f", $quantity);
        } else {
            return intval($quantity);
        }
    }

    /**
     * Get Qty by status
     *
     * @param Enterprise_Rma_Model_Item $item
     * @return int|float
     */
    public function getQty($item)
    {
        $qty = $item->getQtyRequested();

        if ($item->getQtyApproved()
            && ($item->getStatus() == Enterprise_Rma_Model_Rma_Source_Status::STATE_APPROVED)
        ) {
            $qty = $item->getQtyApproved();
        } elseif ($item->getQtyReturned()
            && ($item->getStatus() == Enterprise_Rma_Model_Rma_Source_Status::STATE_RECEIVED
                || $item->getStatus() == Enterprise_Rma_Model_Rma_Source_Status::STATE_REJECTED
            )
        ) {
            $qty = $item->getQtyReturned();
        } elseif ($item->getQtyAuthorized()
            && ($item->getStatus() == Enterprise_Rma_Model_Rma_Source_Status::STATE_AUTHORIZED)
        ) {
            $qty = $item->getQtyAuthorized();
        }

        return $this->parseQuantity($qty, $item);
    }
}
