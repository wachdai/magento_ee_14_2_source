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
 * @package     Enterprise_PricePermissions
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Price Permissions Observer
 *
 * @category    Enterprise
 * @package     Enterprise_PricePermissions
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_PricePermissions_Model_Observer
{
    /**
     * Instance of http request
     *
     * @var Mage_Core_Controller_Request_Http
     */
    protected $_request;

    /**
     * Edit Product Price flag
     *
     * @var boolean
     */
    protected $_canEditProductPrice;

    /**
     * Read Product Price flag
     *
     * @var boolean
     */
    protected $_canReadProductPrice;

    /**
     * Edit Product Status flag
     *
     * @var boolean
     */
    protected $_canEditProductStatus;

    /**
     * String representation of the default product price
     *
     * @var string
     */
    protected $_defaultProductPriceString;

    /**
     * Price Permissions Observer class constructor
     *
     * Sets necessary data
     */
    public function __construct()
    {
        $this->_request = Mage::app()->getRequest();
        // Set all necessary flags
        $this->_canEditProductPrice = true;
        $this->_canReadProductPrice = true;
        $this->_canEditProductStatus = true;
    }

    /**
     * Reinit stores only with allowed scopes
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminControllerPredispatch($observer)
    {
        /* @var $session Mage_Admin_Model_Session */
        $session = Mage::getSingleton('admin/session');

        // load role with true websites and store groups
        if ($session->isLoggedIn() && $session->getUser()->getRole()) {
            // Set all necessary flags
            $this->_canEditProductPrice = Mage::helper('enterprise_pricepermissions')->getCanAdminEditProductPrice();
            $this->_canReadProductPrice = Mage::helper('enterprise_pricepermissions')->getCanAdminReadProductPrice();
            $this->_canEditProductStatus = Mage::helper('enterprise_pricepermissions')->getCanAdminEditProductStatus();
            // Retrieve value of the default product price
            $this->_defaultProductPriceString = Mage::helper('enterprise_pricepermissions')
                    ->getDefaultProductPriceString();
        }
    }

    /**
     * Handle core_block_abstract_to_html_before event
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function coreBlockAbstractToHtmlBefore($observer)
    {
         /** @var $block Mage_Core_Block_Abstract */
        $block = $observer->getBlock();
        $blockNameInLayout = $block->getNameInLayout();
        switch ($blockNameInLayout) {
            // Handle product Recurring Profile tab
            case 'adminhtml_recurring_profile_edit_form' :
                if (!Mage::registry('product')->isObjectNew()) {
                    if (!$this->_canReadProductPrice) {
                        $block->setProductEntity(Mage::getModel('catalog/product'));
                    }
                }
                if (!$this->_canEditProductPrice) {
                    $block->setIsReadonly(true);
                }
                break;
            case 'adminhtml_recurring_profile_edit_form_dependence' :
                if (!$this->_canEditProductPrice) {
                    $block->addConfigOptions(array('can_edit_price' => false));
                    if (!$this->_canReadProductPrice) {
                        $dependenceValue = (Mage::registry('product')->getIsRecurring()) ? '0' : '1';
                        // Override previous dependence value
                        $block->addFieldDependence('product[recurring_profile]', 'product[is_recurring]',
                            $dependenceValue);
                    }
                }
                break;
            // Handle MAP functionality for bundle products
            case 'adminhtml.catalog.product.edit.tab.attributes' :
                if (!$this->_canEditProductPrice) {
                    $block->setCanEditPrice(false);
                }
                break;
        }
    }

    /**
     * Handle adminhtml_block_html_before event
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function adminhtmlBlockHtmlBefore($observer)
    {
        /** @var $block Mage_Adminhtml_Block_Template */
        $block = $observer->getBlock();
        $blockNameInLayout = $block->getNameInLayout();
        switch ($blockNameInLayout) {
            // Handle general product grid, related, upsell, crosssell tabs
            case 'product.grid' :
            case 'admin.product.grid' :
                if (!$this->_canEditProductStatus) {
                    $block->getMassactionBlock()->removeItem('status');
                }
            case 'catalog.product.edit.tab.related' :
            case 'catalog.product.edit.tab.upsell' :
            case 'catalog.product.edit.tab.crosssell' :
            case 'category.product.grid' :
                if (!$this->_canReadProductPrice) {
                    $this->_removeColumnFromGrid($block, 'price');
                }
                break;
            // Handle prices on Shopping Cart Tab of customer
            case 'admin.customer.view.cart' :
                if (!$this->_canReadProductPrice) {
                    $this->_removeColumnFromGrid($block, 'price');
                    $this->_removeColumnFromGrid($block, 'total');
                }
                break;
            // Handle prices on Manage Shopping Cart page (Enterprise_Checkout module)
            case 'products' :
            case 'wishlist' :
            case 'compared' :
            case 'rcompared' :
            case 'rviewed' :
            case 'ordered' :
            case 'checkout.accordion.products' :
            case 'checkout.accordion.wishlist' :
            case 'checkout.accordion.compared' :
            case 'checkout.accordion.rcompared' :
            case 'checkout.accordion.rviewed' :
            case 'checkout.accordion.ordered' :
                if (!$this->_canReadProductPrice) {
                    $this->_removeColumnFromGrid($block, 'price');
                }
                break;
            case 'checkout.items' :
            case 'items' :
                if (!$this->_canReadProductPrice) {
                    $block->setCanReadPrice(false);
                }
                break;
            // Handle Downloadable Links tab of downloadable products
            case 'catalog.product.edit.tab.downloadable.links' :
                if (!$this->_canEditProductPrice) {
                    $block->setCanEditPrice(false);
                }
                if (!$this->_canReadProductPrice) {
                    $block->setCanReadPrice(false);
                }
                break;
            // Handle price column at Associated Products tab of configurable products
            case 'admin.product.edit.tab.super.config.grid' :
                if (!$this->_canReadProductPrice) {
                    $this->_removeColumnFromGrid($block, 'price');
                }
                break;
            // Handle price column at Associated Products tab of grouped products
            case 'catalog.product.edit.tab.super.group' :
                if (!$this->_canReadProductPrice) {
                    $this->_removeColumnFromGrid($block, 'price');
                }
                break;
            case 'product_tabs' :
                if (!$this->_canEditProductPrice) {
                    $block->setTabData('configurable', 'can_edit_price', false);
                }
                if (!$this->_canReadProductPrice) {
                    $block->setTabData('configurable', 'can_read_price', false);
                }
                break;
            // Handle Custom Options tab of products
            case 'admin.product.options' :
                if (!$this->_canEditProductPrice) {
                    $optionsBoxBlock = $block->getChild('options_box');
                    if (!is_null($optionsBoxBlock)) {
                        $optionsBoxBlock->setCanEditPrice(false);
                        if (!$this->_canReadProductPrice) {
                            $optionsBoxBlock->setCanReadPrice(false);
                        }
                    }
                }
                break;
            // Handle product grid on Bundle Items tab of bundle products
            case 'adminhtml.catalog.product.edit.tab.bundle.option.search.grid' :
                if (!$this->_canReadProductPrice) {
                    $this->_removeColumnFromGrid($block, 'price');
                }
                break;
            // Handle Price tab of bundle product
            case 'adminhtml.catalog.product.bundle.edit.tab.attributes.price' :
                if (!$this->_canEditProductPrice) {
                    $block->setCanEditPrice(false);
                    $block->setDefaultProductPrice($this->_defaultProductPriceString);
                }
                if (!$this->_canReadProductPrice) {
                    $block->setCanReadPrice(false);
                }
                break;
            // Handle selection prices of bundle product with fixed price
            case 'adminhtml.catalog.product.edit.tab.bundle.option' :
                $selectionTemplateBlock = $block->getChild('selection_template');
                if (!$this->_canReadProductPrice) {
                    $block->setCanReadPrice(false);
                    if (!is_null($selectionTemplateBlock)) {
                        $selectionTemplateBlock->setCanReadPrice(false);
                    }
                }
                if (!$this->_canEditProductPrice) {
                    $block->setCanEditPrice(false);
                    if (!is_null($selectionTemplateBlock)) {
                        $selectionTemplateBlock->setCanEditPrice(false);
                    }
                }
                break;
            case 'adminhtml.catalog.product.edit.tab.attributes':
                // Hide price elements if needed
                $this->_hidePriceElements($block);
                break;
            // Handle quick creation of simple product in configurable product
            case 'catalog.product.edit.tab.super.config.simple' :
                /** @var $form Varien_Data_Form */
                $form = $block->getForm();
                if (!is_null($form)) {
                    if (!$this->_canEditProductStatus) {
                        $statusElement = $form->getElement('simple_product_status');
                        if (!is_null($statusElement)) {
                            $statusElement->setValue(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                            $statusElement->setReadonly(true, true);
                        }
                    }
                }
                break;
        }

        // Handle prices that are shown when admin reviews customers shopping cart
        if (stripos($blockNameInLayout, 'customer_cart_') === 0) {
            if (!$this->_canReadProductPrice) {
                if ($block->getParentBlock()->getNameInLayout() == 'admin.customer.carts') {
                    $this->_removeColumnFromGrid($block, 'price');
                    $this->_removeColumnFromGrid($block, 'total');
                }
            }
        }
    }

    /**
     * Handle catalog_product_load_after event
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function catalogProductLoadAfter(Varien_Event_Observer $observer)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getDataObject();

        if (!$this->_canEditProductPrice) {
            // Lock price attributes of product in order not to let administrator to change them
            $product->lockAttribute('price');
            $product->lockAttribute('special_price');
            $product->lockAttribute('tier_price');
            $product->lockAttribute('group_price');
            $product->lockAttribute('special_from_date');
            $product->lockAttribute('special_to_date');
            $product->lockAttribute('is_recurring');
            $product->lockAttribute('cost');
            // For bundle product
            $product->lockAttribute('price_type');
            // Gift Card attributes
            $product->lockAttribute('open_amount_max');
            $product->lockAttribute('open_amount_min');
            $product->lockAttribute('allow_open_amount');
            $product->lockAttribute('giftcard_amounts');
            // For MAP fields
            $product->lockAttribute('msrp_enabled');
            $product->lockAttribute('msrp_display_actual_price_type');
            $product->lockAttribute('msrp');
        }
        if (!$this->_canEditProductStatus) {
            $product->lockAttribute('status');
        }
    }

    /**
     * Handle catalog_product_save_before event
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function catalogProductSaveBefore(Varien_Event_Observer $observer)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getDataObject();
        if ($product->isObjectNew() && !$this->_canEditProductStatus) {
            $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
        }
    }

    /**
     * Handle adminhtml_catalog_product_edit_prepare_form event
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function adminhtmlCatalogProductEditPrepareForm(Varien_Event_Observer $observer)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::registry('product');
        if ($product->isObjectNew()) {
            $form = $observer->getEvent()->getForm();
            // Disable Status drop-down if needed
            if (!$this->_canEditProductStatus) {
                $statusElement = $form->getElement('status');
                if (!is_null($statusElement)) {
                    $statusElement->setValue(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                    $statusElement->setReadonly(true, true);
                }
            }
        }
    }

    /**
     * Handle catalog_product_before_save event
     *
     * Handle important product data before saving a product
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function catalogProductPrepareSave($observer)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();

        if (!$this->_canEditProductPrice) {
            // Handle Custom Options of Product
            $originalOptions = $product->getOptions();
            $options = $product->getData('product_options');
            if (is_array($options)) {

                $originalOptionsAssoc = array();
                if (is_array($originalOptions)) {

                    foreach ($originalOptions as $originalOption) {
                        /** @var $originalOption Mage_Catalog_Model_Product_Option */
                        $originalOptionAssoc = array();
                        $originalOptionAssoc['id'] = $originalOption->getOptionId();
                        $originalOptionAssoc['option_id'] = $originalOption->getOptionId();
                        $originalOptionAssoc['type'] = $originalOption->getType();
                        $originalOptionGroup = $originalOption->getGroupByType();
                        if ($originalOptionGroup != Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                            $originalOptionAssoc['price'] = $originalOption->getPrice();
                            $originalOptionAssoc['price_type'] = $originalOption->getPriceType();
                        } else {
                            $originalOptionAssoc['values'] = array();
                            foreach ($originalOption->getValues() as $value) {
                                /** @var $value Mage_Catalog_Model_Product_Option_Value */
                                $originalOptionAssoc['values'][$value->getOptionTypeId()] = array(
                                    'price' => $value->getPrice(),
                                    'price_type' => $value->getPriceType()
                                );
                            }
                        }
                        $originalOptionsAssoc[$originalOption->getOptionId()] = $originalOptionAssoc;
                    }
                }

                foreach ($options as $optionId => &$option) {
                    // For old options
                    if (isset($originalOptionsAssoc[$optionId])
                        && $originalOptionsAssoc[$optionId]['type'] == $option['type']
                    ) {
                        if (!isset($option['values'])) {
                            $option['price'] = $originalOptionsAssoc[$optionId]['price'];
                            $option['price_type'] = $originalOptionsAssoc[$optionId]['price_type'];
                        } elseif (is_array($option['values'])) {
                            foreach ($option['values'] as &$value) {
                                if (isset($originalOptionsAssoc[$optionId]['values'][$value['option_type_id']])) {
                                    $originalValue =
                                        $originalOptionsAssoc[$optionId]['values'][$value['option_type_id']];
                                    $value['price'] = $originalValue['price'];
                                    $value['price_type'] = $originalValue['price_type'];
                                } else {
                                    // Set zero price for new selections of old custom option
                                    $value['price'] = '0';
                                    $value['price_type'] = 0;
                                }
                            }
                        }
                        // Set price to zero and price type to fixed for new options
                    } else {
                        if (!isset($option['values'])) {
                            $option['price'] = '0';
                            $option['price_type'] = 0;
                        } elseif (is_array($option['values'])) {
                            foreach ($option['values'] as &$value) {
                                $value['price'] = '0';
                                $value['price_type'] = 0;
                            }
                        }
                    }
                }
                $product->setData('product_options', $options);
            }

            // Handle recurring profile data (replace it with original)
            $originalRecurringProfile = $product->getOrigData('recurring_profile');
            $product->setRecurringProfile($originalRecurringProfile);

            // Handle data received from Associated Products tab of configurable product
            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $originalAttributes = $product->getTypeInstance(true)
                    ->getConfigurableAttributesAsArray($product);
                // Organize main information about original product attributes in assoc array form
                $originalAttributesMainInfo = array();
                if (is_array($originalAttributes)) {
                    foreach ($originalAttributes as $originalAttribute) {
                        $originalAttributesMainInfo[$originalAttribute['id']] = array();
                        foreach ($originalAttribute['values'] as $value) {
                            $originalAttributesMainInfo[$originalAttribute['id']][$value['value_index']] = array(
                                'is_percent'    => $value['is_percent'],
                                'pricing_value' => $value['pricing_value']
                            );
                        }
                    }
                }
                $attributeData = $product->getConfigurableAttributesData();
                if (is_array($attributeData)) {
                    foreach ($attributeData as &$data) {
                        $id = $data['id'];
                        foreach ($data['values'] as &$value) {
                            $valueIndex = $value['value_index'];
                            if (isset($originalAttributesMainInfo[$id][$valueIndex])) {
                                $value['pricing_value'] =
                                    $originalAttributesMainInfo[$id][$valueIndex]['pricing_value'];
                                $value['is_percent'] = $originalAttributesMainInfo[$id][$valueIndex]['is_percent'];
                            } else {
                                $value['pricing_value'] = 0;
                                $value['is_percent'] = 0;
                            }
                        }
                    }
                    $product->setConfigurableAttributesData($attributeData);
                }
            }

            // Handle seletion data of bundle products
            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $bundleSelectionsData = $product->getBundleSelectionsData();
                if (is_array($bundleSelectionsData)) {
                    // Retrieve original selections data
                    $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);

                    $optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
                    $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
                        $product->getTypeInstance(true)->getOptionsIds($product), $product);

                    $origBundleOptions = $optionCollection->appendSelections($selectionCollection);
                    $origBundleOptionsAssoc = array();
                    foreach ($origBundleOptions as $origBundleOption) {
                        $optionId = $origBundleOption->getOptionId();
                        $origBundleOptionsAssoc[$optionId] = array();
                        if ($origBundleOption->getSelections()) {
                            foreach ($origBundleOption->getSelections() as $selection) {
                                $selectionProductId = $selection->getProductId();
                                $origBundleOptionsAssoc[$optionId][$selectionProductId] = array(
                                    'selection_price_type' => $selection->getSelectionPriceType(),
                                    'selection_price_value' => $selection->getSelectionPriceValue()
                                );
                            }
                        }
                    }
                    // Keep previous price and price type for selections
                    foreach ($bundleSelectionsData as &$bundleOptionSelections) {
                        foreach ($bundleOptionSelections as &$bundleOptionSelection) {
                            if (!isset($bundleOptionSelection['option_id'])
                                || !isset($bundleOptionSelection['product_id'])
                            ) {
                                continue;
                            }
                            $optionId = $bundleOptionSelection['option_id'];
                            $selectionProductId = $bundleOptionSelection['product_id'];
                            $isDeleted = $bundleOptionSelection['delete'];
                            if (isset($origBundleOptionsAssoc[$optionId][$selectionProductId]) && !$isDeleted) {
                                $bundleOptionSelection['selection_price_type'] =
                                    $origBundleOptionsAssoc[$optionId][$selectionProductId]['selection_price_type'];
                                $bundleOptionSelection['selection_price_value'] =
                                    $origBundleOptionsAssoc[$optionId][$selectionProductId]['selection_price_value'];
                            } else {
                                // Set zero price for new bundle selections and options
                                $bundleOptionSelection['selection_price_type'] = 0;
                                $bundleOptionSelection['selection_price_value'] = 0;
                            }
                        }
                    }
                    $product->setData('bundle_selections_data', $bundleSelectionsData);
                }
            }

            // Handle data received from Downloadable Links tab of downloadable products
            if ($product->getTypeId() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {

                $downloadableData = $product->getDownloadableData();
                if (is_array($downloadableData) && isset($downloadableData['link'])) {
                    $originalLinks = $product->getTypeInstance(true)->getLinks($product);
                    foreach ($downloadableData['link'] as $id => &$downloadableDataItem) {
                        $linkId = $downloadableDataItem['link_id'];
                        if (isset($originalLinks[$linkId]) && !$downloadableDataItem['is_delete']) {
                            $originalLink = $originalLinks[$linkId];
                            $downloadableDataItem['price'] = $originalLink->getPrice();
                        } else {
                            // Set zero price for new links
                            $downloadableDataItem['price'] = 0;
                        }
                    }
                    $product->setDownloadableData($downloadableData);
                }
            }

            if ($product->isObjectNew()) {
                // For new products set default price
                if (!($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE
                    && $product->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC)
                ) {
                    $product->setPrice((float) $this->_defaultProductPriceString);
                    // Set default amount for Gift Card product
                    if ($product->getTypeId() == Enterprise_GiftCard_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD
                    ) {
                        $storeId = (int) $this->_request->getParam('store', 0);
                        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
                        $product->setGiftcardAmounts(array(
                            array(
                                'website_id' => $websiteId,
                                'price'      => $this->_defaultProductPriceString,
                                'delete'     => ''
                            )
                        ));
                    }
                }
                // New products are created without recurring profiles
                $product->setIsRecurring(false);
                $product->unsRecurringProfile();
                // Add MAP default values
                $product->setMsrpEnabled(
                    Mage_Catalog_Model_Product_Attribute_Source_Msrp_Type_Enabled::MSRP_ENABLE_USE_CONFIG);
                $product->setMsrpDisplayActualPriceType(
                    Mage_Catalog_Model_Product_Attribute_Source_Msrp_Type_Price::TYPE_USE_CONFIG);
            }
        }
    }

    /**
     * Handle adminhtml_catalog_product_form_prepare_excluded_field_list event
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function adminhtmlCatalogProductFormPrepareExcludedFieldList($observer)
    {
        /** @var $block Mage_Adminhtml_Block_Catalog_Product_Edit_Action_Attribute_Tab_Attributes */
        $block = $observer->getEvent()->getObject();
        $excludedFieldList = array();

        if (!$this->_canEditProductPrice) {
            $excludedFieldList = array(
                'price', 'special_price', 'tier_price', 'group_price', 'special_from_date', 'special_to_date',
                'is_recurring', 'cost', 'price_type', 'open_amount_max', 'open_amount_min', 'allow_open_amount',
                'giftcard_amounts', 'msrp_enabled', 'msrp_display_actual_price_type', 'msrp'
            );
        }
        if (!$this->_canEditProductStatus) {
            $excludedFieldList[] = 'status';
        }

        $block->setFormExcludedFieldList(array_merge($block->getFormExcludedFieldList(), $excludedFieldList));
    }

    /**
     * Handle catalog_product_attribute_update_before event
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function catalogProductAttributeUpdateBefore($observer)
    {
        /** @var $block Mage_Adminhtml_Block_Catalog_Product_Edit_Action_Attribute_Tab_Attributes */
        $attributesData = $observer->getEvent()->getAttributesData();
        $excludedAttributes = array();

        if (!$this->_canEditProductPrice) {
            $excludedAttributes = array(
                'price', 'special_price', 'tier_price', 'group_price', 'special_from_date', 'special_to_date',
                'is_recurring', 'cost', 'price_type', 'open_amount_max', 'open_amount_min', 'allow_open_amount',
                'giftcard_amounts', 'msrp_enabled', 'msrp_display_actual_price_type', 'msrp'
            );
        }
        if (!$this->_canEditProductStatus) {
            $excludedAttributes[] = 'status';
        }
        foreach ($excludedAttributes as $excludedAttributeCode) {
            if (isset($attributesData[$excludedAttributeCode])) {
                unset($attributesData[$excludedAttributeCode]);
            }
        }

        $observer->getEvent()->setAttributesData($attributesData);
    }

    /**
     * Remove column from grid
     *
     * @param Mage_Adminhtml_Block_Widget_Grid $block
     * @param string $columnId
     * @return Mage_Adminhtml_Block_Widget_Grid|bool
     */
    protected function _removeColumnFromGrid($block, $column)
    {
        if (!$block instanceof Mage_Adminhtml_Block_Widget_Grid) {
            return false;
        }
        return $block->removeColumn($column);
    }

    /**
     * Hide price elements on Price Tab of Product Edit Page if needed
     *
     * @param Mage_Core_Block_Abstract $block
     * @return void
     */
    protected function _hidePriceElements($block)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::registry('product');
        $form = $block->getForm();
        $group = $block->getGroup();
        $fieldset = null;
        if (!is_null($form) && !is_null($group)) {
            $fieldset = $form->getElement('group_fields' . $group->getId());
        }

        if (!is_null($product) && !is_null($form) && !is_null($group) && !is_null($fieldset)) {
            $priceElementIds = array(
                'special_price',
                'tier_price',
                'group_price',
                'special_from_date',
                'special_to_date',
                'cost',
                // GiftCard attributes
                'open_amount_max',
                'open_amount_min',
                'allow_open_amount',
                'giftcard_amounts',
                // MAP attributes
                'msrp_enabled',
                'msrp_display_actual_price_type',
                'msrp'
            );

            // Leave price element for bundle product active in order to change/view price type when product is created
            if (Mage::registry('product')->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                array_push($priceElementIds, 'price');
            }

            // Remove price elements or disable them if needed
            foreach ($priceElementIds as &$priceId) {
                if (!$this->_canReadProductPrice) {
                    $fieldset->removeField($priceId);
                } elseif (!$this->_canEditProductPrice) {
                    $priceElement = $form->getElement($priceId);
                    if (!is_null($priceElement)) {
                        $priceElement->setReadonly(true, true);
                    }
                }
            }

            if (!$this->_canEditProductPrice) {
                // Handle Recurring Profile tab
                if ($form->getElement('recurring_profile')) {
                    $form->getElement('recurring_profile')->setReadonly(true, true)->getForm()
                        ->setReadonly(true, true);
                }
            }

            if ($product->isObjectNew()) {
                if (!$this->_canEditProductPrice) {
                    // For each type of products accept except Bundle products, set default value for price if allowed
                    $priceElement = $form->getElement('price');
                    if (!is_null($priceElement)
                        && $this->_canReadProductPrice
                        && (Mage::registry('product')->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
                    ) {
                        $priceElement->setValue($this->_defaultProductPriceString);
                    }
                    // For giftcard products set default amount
                    $amountsElement = $form->getElement('giftcard_amounts');
                    if (!is_null($amountsElement)) {
                        $storeId = (int) $this->_request->getParam('store', 0);
                        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
                        $amountsElement->setValue(array(
                            array(
                                'website_id'    => $websiteId,
                                'value'         => $this->_defaultProductPriceString,
                                'website_value' => (float) $this->_defaultProductPriceString
                            )
                        ));
                    }
                }
            }
        }
    }
}
