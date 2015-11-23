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
 * Gift registry observer model
 */
class Enterprise_GiftRegistry_Model_Observer
{
    /**
     * Module enabled flag
     * @var bool
     */
    protected $_isEnabled;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_isEnabled = Mage::helper('enterprise_giftregistry')->isEnabled();
    }

    /**
     * Check if giftregistry is enabled
     * @return bool
     */
    public function isGiftregistryEnabled()
    {
        return $this->_isEnabled;
    }

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Customer address data object before load processing
     * Set gift registry item id flag
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftRegistry_Model_Observer
     */
    public function addressDataBeforeLoad($observer)
    {
        $addressId = $observer->getEvent()->getValue();

        if (!is_numeric($addressId)) {
            $prefix = Mage::helper('enterprise_giftregistry')->getAddressIdPrefix();
            $registryItemId = str_replace($prefix, '', $addressId);
            $object = $observer->getEvent()->getDataObject();
            $object->setGiftregistryItemId($registryItemId);
            $object->setCustomerAddressId($addressId);
        }
        return $this;
    }

    /**
     * Customer address data object after load
     * Check gift registry item id flag and set shipping address data to object
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftRegistry_Model_Observer
     */
    public function addressDataAfterLoad($observer)
    {
        $object = $observer->getEvent()->getDataObject();

        if ($registryItemId = $object->getGiftregistryItemId()) {
            $model = Mage::getModel('enterprise_giftregistry/entity')
                ->loadByEntityItem($registryItemId);
            if ($model->getId()) {
                $object->setId(Mage::helper('enterprise_giftregistry')->getAddressIdPrefix() . $model->getId());
                $object->setCustomerId($this->_getSession()->getCustomer()->getId());
                $object->addData($model->exportAddress()->getData());
            }
        }
        return $this;
    }

    /**
     * Hide customer address on the frontend if it is gift registry shipping address
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftRegistry_Model_Observer
     */
    public function addressFormatFront($observer)
    {
        $this->_addressFormat($observer);
        return $this;
    }

    /**
     * Hide customer address in admin panel if it is gift registry shipping address
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftRegistry_Model_Observer
     */
    public function addressFormatAdmin($observer)
    {
        if (Mage::getDesign()->getArea() == 'frontend') {
            $this->_addressFormat($observer);
        }
        return $this;
    }

    /**
     * Hide customer address if it is gift registry shipping address
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftRegistry_Model_Observer
     */
    protected function _addressFormat($observer)
    {
        $type = $observer->getEvent()->getType();
        $address = $observer->getEvent()->getAddress();

        if ($address->getGiftregistryItemId()) {
            if (!$type->getPrevFormat()) {
                $type->setPrevFormat($type->getDefaultFormat());
            }
            $type->setDefaultFormat(Mage::helper('enterprise_giftregistry')->__("Ship to recipient's address."));
        } elseif ($type->getPrevFormat()) {
            $type->setDefaultFormat($type->getPrevFormat());
        }
        return $this;
    }

    /**
     * Copy gift registry item id flag from quote item to order item
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftRegistry_Model_Observer
     */
    public function convertItems($observer)
    {
        $orderItem = $observer->getEvent()->getOrderItem();
        $item = $observer->getEvent()->getItem();

        if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
            $registryItemId = $item->getQuoteItem()->getGiftregistryItemId();
        } else {
            $registryItemId = $item->getGiftregistryItemId();
        }

        if ($registryItemId) {
            $orderItem->setGiftregistryItemId($registryItemId);
        }
        return $this;
    }

    /**
     * After place order processing, update gift registry items fulfilled qty
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftRegistry_Model_Observer
     */
    public function orderPlaced($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $item = Mage::getModel('enterprise_giftregistry/item');
        $giftRegistries = array();
        $updatedQty = array();

        foreach ($order->getAllVisibleItems() as $orderItem) {
            if ($registryItemId = $orderItem->getGiftregistryItemId()) {
                $item->load($registryItemId);
                if ($item->getId()) {
                    $newQty = $item->getQtyFulfilled() + $orderItem->getQtyOrdered();
                    $item->setQtyFulfilled($newQty)->save();
                    $giftRegistries[] = $item->getEntityId();

                    $updatedQty[$registryItemId] = array(
                        'ordered' => $orderItem->getQtyOrdered(),
                        'fulfilled' => $newQty
                    );
                }
            }
        }

        $giftRegistries = array_unique($giftRegistries);
        if (count($giftRegistries)) {
            $entity = Mage::getModel('enterprise_giftregistry/entity');
            foreach ($giftRegistries as $registryId) {
                $entity->load($registryId);
                $entity->sendUpdateRegistryEmail($updatedQty);
            }
        }
        return $this;
    }

    /**
     * Save page body to cache storage
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftRegistry_Model_Observer
     */
    public function addGiftRegistryQuoteFlag(Varien_Event_Observer $observer)
    {
        if (!$this->isGiftregistryEnabled()) {
            return $this;
        }
        $product = $observer->getEvent()->getProduct();
        $quoteItem = $observer->getEvent()->getQuoteItem();

        $giftregistryItemId = $product->getGiftregistryItemId();
        if ($giftregistryItemId) {
            $quoteItem->setGiftregistryItemId($giftregistryItemId);

            $parent = $quoteItem->getParentItem();
            if ($parent) {
                $parent->setGiftregistryItemId($giftregistryItemId);
            }
        }
        return $this;
    }

    /**
     * Clean up gift registry items that belongs to the product.
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Cms_Model_Observer
     */
    public function deleteProduct(Varien_Event_Observer $observer)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();

        if ($product->getParentId()) {
            $productId = $product->getParentId();
        } else {
            $productId = $product->getId();
        }

        /** @var $grItem Enterprise_GiftRegistry_Model_Item */
        $grItem = Mage::getModel('Enterprise_GiftRegistry_Model_Item');
        /** @var $collection Enterprise_GiftRegistry_Model_Resource_Item_Collection */
        $collection = $grItem->getCollection()->addProductFilter($productId);

        foreach($collection->getItems() as $item) {
            $item->delete();
        }

        /** @var $options Enterprise_GiftRegistry_Model_Item_Option*/
        $options = Mage::getModel('Enterprise_GiftRegistry_Model_Item_Option');
        $optionCollection = $options->getCollection()->addProductFilter($productId);

        $itemsArray = array();
        foreach($optionCollection->getItems() as $optionItem) {
            $itemsArray[$optionItem->getItemId()]  = $optionItem->getItemId();
        }

        $collection = $grItem->getCollection()->addItemFilter(array_keys($itemsArray));

        foreach($collection->getItems() as $item) {
            $item->delete();
        }

        return $this;
    }
}
