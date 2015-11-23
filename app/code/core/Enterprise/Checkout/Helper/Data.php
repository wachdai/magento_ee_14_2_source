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
 * Enterprise Checkout Helper
 *
 * @category    Enterprise
 * @package     Enterprise_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Items for requiring attention grid (doesn't include sku-failed items)
     *
     * @var null|array
     */
    protected $_items;

    /**
     * Items for requiring attention grid (including sku-failed items)
     *
     * @var null|array
     */
    protected $_itemsAll;

    /**
     * Config path to Enable Order By SKU tab in the Customer account dashboard and Allowed groups
     */
    const XML_PATH_SKU_ENABLED = 'sales/product_sku/my_account_enable';
    const XML_PATH_SKU_ALLOWED_GROUPS = 'sales/product_sku/allowed_groups';

    /**
     * Status of item, that was added by SKU
     */
    const ADD_ITEM_STATUS_SUCCESS = 'success';
    const ADD_ITEM_STATUS_FAILED_SKU = 'failed_sku';
    const ADD_ITEM_STATUS_FAILED_OUT_OF_STOCK = 'failed_out_of_stock';
    const ADD_ITEM_STATUS_FAILED_QTY_ALLOWED = 'failed_qty_allowed';
    const ADD_ITEM_STATUS_FAILED_QTY_ALLOWED_IN_CART = 'failed_qty_allowed_in_cart';
    const ADD_ITEM_STATUS_FAILED_QTY_INVALID_NUMBER = 'failed_qty_invalid_number';
    const ADD_ITEM_STATUS_FAILED_QTY_INVALID_NON_POSITIVE = 'failed_qty_invalid_non_positive';
    const ADD_ITEM_STATUS_FAILED_QTY_INVALID_RANGE = 'failed_qty_invalid_range';
    const ADD_ITEM_STATUS_FAILED_QTY_INCREMENTS = 'failed_qty_increment';
    const ADD_ITEM_STATUS_FAILED_CONFIGURE = 'failed_configure';
    const ADD_ITEM_STATUS_FAILED_PERMISSIONS = 'failed_permissions';
    const ADD_ITEM_STATUS_FAILED_WEBSITE = 'failed_website';
    const ADD_ITEM_STATUS_FAILED_UNKNOWN = 'failed_unknown';
    const ADD_ITEM_STATUS_FAILED_EMPTY = 'failed_empty';
    const ADD_ITEM_STATUS_FAILED_DISABLED = 'failed_disabled';

    /**
     * Request parameter name, which indicates, whether file was uploaded
     */
    const REQUEST_PARAMETER_SKU_FILE_IMPORTED_FLAG = 'sku_file_uploaded';

    /**
     * Layout handle for sku failed items
     */
    const SKU_FAILED_PRODUCTS_HANDLE = 'sku_failed_products_handle';

    /**
     * Customer Groups that allow Order by SKU
     *
     * @var array|null
     */
    protected $_allowedGroups;

    /**
     * Contains session object to which data is saved
     *
     * @var Mage_Core_Model_Session_Abstract
     */
    protected $_session;

    /**
     * List of item statuses, that should be rendered by 'failed' template
     *
     * @var array
     */
    protected $_failedTemplateStatusCodes = array(
        self::ADD_ITEM_STATUS_FAILED_SKU,
        self::ADD_ITEM_STATUS_FAILED_PERMISSIONS
    );

    /**
     * Return session for affected items
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    public function getSession()
    {
        if (!$this->_session) {
            $sessionClassPath = Mage::app()->getStore()->isAdmin() ? 'adminhtml/session' : 'customer/session';
            $this->_session =  Mage::getSingleton($sessionClassPath);
        }

        return $this->_session;
    }

    /**
     * Sets session instance to use for saving data
     *
     * @param Mage_Core_Model_Session_Abstract $session
     */
    public function setSession(Mage_Core_Model_Session_Abstract $session)
    {
        $this->_session = $session;
    }

    /**
     * Retrieve error message for the item
     *
     * @param Varien_Object $item
     * @return string
     */
    public function getMessageByItem(Varien_Object $item)
    {
        $message = $this->getMessage($item->getCode());
        return $message ? $message : $item->getError();
    }

    /**
     * Retrieve message by specified error code
     *
     * @param string $code
     * @return string
     */
    public function getMessage($code)
    {
        switch ($code) {
            case self::ADD_ITEM_STATUS_FAILED_SKU:
                $message = $this->__('SKU not found in catalog.');
                break;
            case self::ADD_ITEM_STATUS_FAILED_OUT_OF_STOCK:
                $message = $this->__('Availability: Out of stock.');
                break;
            case self::ADD_ITEM_STATUS_FAILED_QTY_ALLOWED:
                $message = $this->__('Requested quantity is not available.');
                break;
            case self::ADD_ITEM_STATUS_FAILED_QTY_ALLOWED_IN_CART:
                $message = $this->__('The product cannot be added to cart in requested quantity.');
                break;
            case self::ADD_ITEM_STATUS_FAILED_CONFIGURE:
                $message = $this->__('Please specify the product\'s options.');
                break;
            case self::ADD_ITEM_STATUS_FAILED_PERMISSIONS:
                $message = $this->__('The product cannot be added to cart.');
                break;
            case self::ADD_ITEM_STATUS_FAILED_QTY_INVALID_NUMBER:
            case self::ADD_ITEM_STATUS_FAILED_QTY_INVALID_NON_POSITIVE:
            case self::ADD_ITEM_STATUS_FAILED_QTY_INVALID_RANGE:
                $message = $this->__('Please enter a valid number in the "Qty" field.');
                break;
            case self::ADD_ITEM_STATUS_FAILED_WEBSITE:
                $message = $this->__('The product is assigned to another website.');
                break;
            case self::ADD_ITEM_STATUS_FAILED_DISABLED:
                $message = $this->__('Disabled products cannot be added.');
                break;
            default:
                $message = '';
        }
        return $message;
    }

    /**
     * Check whether module enabled
     *
     * @return bool
     */
    public function isSkuEnabled()
    {
        $storeData = Mage::getStoreConfig(self::XML_PATH_SKU_ENABLED);
        return Enterprise_Checkout_Model_Cart_Sku_Source_Settings::NO_VALUE != $storeData;
    }

    /**
     * Check whether Order by SKU functionality applicable to the current customer
     *
     * @return bool
     */
    public function isSkuApplied()
    {
        $result = false;
        $data = Mage::getStoreConfig(self::XML_PATH_SKU_ENABLED);
        switch ($data) {
            case Enterprise_Checkout_Model_Cart_Sku_Source_Settings::YES_VALUE:
                $result = true;
                break;
            case Enterprise_Checkout_Model_Cart_Sku_Source_Settings::YES_SPECIFIED_GROUPS_VALUE:
                /** @var $customerSession Mage_Customer_Model_Session */
                $customerSession = Mage::getSingleton('customer/session');
                if ($customerSession) {
                    $groupId = $customerSession->getCustomerGroupId();
                    $result = $groupId === Mage_Customer_Model_Group::NOT_LOGGED_IN_ID
                        || in_array($groupId, $this->getSkuCustomerGroups());
                }
                break;
        }
        return $result;
    }

    /**
     * Retrieve Customer Groups that allow Order by SKU from config
     *
     * @return array
     */
    public function getSkuCustomerGroups()
    {
        if ($this->_allowedGroups === null) {
            $this->_allowedGroups = explode(',', trim(Mage::getStoreConfig(self::XML_PATH_SKU_ALLOWED_GROUPS)));
        }
        return $this->_allowedGroups;
    }

    /**
     * Get add by SKU failed items
     *
     * @param bool $all whether sku-failed items should be retrieved
     * @return array
     */
    public function getFailedItems($all = true)
    {
        if ($all && is_null($this->_itemsAll) || !$all && is_null($this->_items)) {
            $failedItems = Mage::getSingleton('enterprise_checkout/cart')->getFailedItems();
            $collection = Mage::getResourceSingleton('enterprise_checkout/product_collection')
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addUrlRewrite();
            $itemsToLoad = array();

            $quoteItemsCollection = is_null($this->_items) ? array() : $this->_items;

            foreach ($failedItems as $item) {
                if (is_null($this->_items) && !in_array($item['code'], $this->_failedTemplateStatusCodes)) {
                    $id = $item['item']['id'];
                    if (!isset($itemsToLoad[$id])) {
                        $itemsToLoad[$id] = array();
                    }
                    $itemToLoad = $item['item'];
                    $itemToLoad['code'] = $item['code'];
                    $itemToLoad['error'] = isset($item['item']['error']) ? $item['item']['error'] : '';
                    // Avoid collisions of product ID with quote item ID
                    unset($itemToLoad['id']);
                    $itemsToLoad[$id][] = $itemToLoad;
                } elseif ($all && in_array($item['code'], $this->_failedTemplateStatusCodes)) {
                    $item['item']['code'] = $item['code'];
                    $item['item']['product_type'] = 'undefined';
                    // Create empty quote item. Otherwise it won't be correctly treated inside failed.phtml
                    $collectionItem = Mage::getModel('sales/quote_item')
                        ->setProduct(Mage::getModel('catalog/product'))
                        ->addData($item['item']);
                    $quoteItemsCollection[] = $collectionItem;
                }
            }
            $ids = array_keys($itemsToLoad);
            if ($ids) {
                $collection->addIdFilter($ids);

                $quote = Mage::getSingleton('checkout/session')->getQuote();
                $emptyQuoteItem = Mage::getModel('sales/quote_item');

                /** @var $itemProduct Mage_Catalog_Model_Product */
                foreach ($collection->getItems() as $product) {
                    $itemsCount = count($itemsToLoad[$product->getId()]);
                    foreach ($itemsToLoad[$product->getId()] as $index => $itemToLoad) {
                        $itemProduct = ($index == $itemsCount - 1) ? $product : (clone $product);
                        $itemProduct->addData($itemToLoad);
                        if (!$itemProduct->getOptionsByCode()) {
                            $itemProduct->setOptionsByCode(array());
                        }
                        // Create a new quote item and import data to it
                        $quoteItem = clone $emptyQuoteItem;
                        $quoteItem->addData($itemProduct->getData())
                            ->setQuote($quote)
                            ->setProduct($itemProduct)
                            ->setOptions($itemProduct->getOptions())
                            ->setRedirectUrl($itemProduct->getUrlModel()->getUrl($itemProduct));

                        $itemProduct->setCustomOptions($itemProduct->getOptionsByCode());
                        if (Mage::helper('catalog')->canApplyMsrp($itemProduct)) {
                            $quoteItem->setCanApplyMsrp(true);
                            $itemProduct->setRealPriceHtml(
                                Mage::app()->getStore()->formatPrice(Mage::app()->getStore()->convertPrice(
                                    Mage::helper('tax')->getPrice($itemProduct, $itemProduct->getFinalPrice(), true)
                                ))
                            );
                            $itemProduct->setAddToCartUrl(Mage::helper('checkout/cart')->getAddUrl($itemProduct));
                        } else {
                            $quoteItem->setCanApplyMsrp(false);
                        }

                        /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                        $stockItem = Mage::getModel('cataloginventory/stock_item');
                        $stockItem->assignProduct($itemProduct);
                        $quoteItem->setStockItem($stockItem);

                        $quoteItemsCollection[] = $quoteItem;
                    }
                }
            }

            if ($all) {
                $this->_itemsAll = $quoteItemsCollection;
            } else {
                $this->_items = $quoteItemsCollection;
            }
        }
        return $all ? $this->_itemsAll : $this->_items;
    }

    /**
     * Get text of general error while file uploading
     *
     * @return string
     */
    public function getFileGeneralErrorText()
    {
        return $this->__('File cannot be uploaded.');
    }

    /**
     * Process SKU file uploading and get uploaded data
     *
     * @param Mage_Core_Model_Session_Abstract|null $session
     * @return array|bool
     */
    public function processSkuFileUploading($session)
    {
        /** @var $importModel Enterprise_Checkout_Model_Import */
        $importModel = Mage::getModel('enterprise_checkout/import');
        try {
            $importModel->uploadFile();
            $rows = $importModel->getRows();
            if (empty($rows)) {
                Mage::throwException($this->__('File is empty.'));
            }
            return $rows;
        } catch (Mage_Core_Exception $e) {
            if (!is_null($session)) {
                $session->addError($e->getMessage());
            }
        } catch (Exception $e) {
            if (!is_null($session)) {
                $session->addException($e, $this->getFileGeneralErrorText());
            }
        }
    }

    /**
     * Check whether SKU file was uploaded
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return bool
     */
    public function isSkuFileUploaded(Mage_Core_Controller_Request_Http $request)
    {
        return (bool)$request->getPost(self::REQUEST_PARAMETER_SKU_FILE_IMPORTED_FLAG);
    }

    /**
     * Get url of account SKU tab
     *
     * @return string
     */
    public function getAccountSkuUrl()
    {
        return Mage::getSingleton('core/url')->getUrl('enterprise_checkout/sku');
    }

    /**
     * Get text of message in case of empty SKU data error
     *
     * @return string
     */
    public function getSkuEmptyDataMessageText()
    {
        return $this->isSkuApplied()
            ? $this->__('You have not entered any product sku. Please <a href="%s">click here</a> to add product(s) by sku.', $this->getAccountSkuUrl())
            : $this->__('You have not entered any product sku.');
    }
}
