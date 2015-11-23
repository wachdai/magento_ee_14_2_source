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
 * @package     Enterprise_GiftCard
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_GiftCard_Model_Observer extends Mage_Core_Model_Abstract
{
    const ATTRIBUTE_CODE = 'giftcard_amounts';

    /**
     * Cache for loaded invoices
     *
     * @var array
     */
    protected $_loadedInvoices = array();

    /**
     * Set attribute renderer on catalog product edit page
     *
     * @param Varien_Event_Observer $observer
     */
    public function setAmountsRendererInForm(Varien_Event_Observer $observer)
    {
        //adminhtml_catalog_product_edit_prepare_form
        $form = $observer->getEvent()->getForm();
        $elem = $form->getElement(self::ATTRIBUTE_CODE);

        if ($elem) {
            $elem->setRenderer(Mage::app()->getLayout()->createBlock('enterprise_giftcard/adminhtml_renderer_amount'));
        }
    }

    /**
     * Set giftcard amounts field as not used in mass update
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateExcludedFieldList(Varien_Event_Observer $observer)
    {
        //adminhtml_catalog_product_form_prepare_excluded_field_list

        $block = $observer->getEvent()->getObject();
        $list = $block->getFormExcludedFieldList();
        $list[] = self::ATTRIBUTE_CODE;
        $block->setFormExcludedFieldList($list);
    }

    /**
     * Append gift card additional data to order item options
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftCard_Model_Observer
     */
    public function appendGiftcardAdditionalData(Varien_Event_Observer $observer)
    {
        //sales_convert_quote_item_to_order_item

        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();
        $keys = array(
            'giftcard_sender_name',
            'giftcard_sender_email',
            'giftcard_recipient_name',
            'giftcard_recipient_email',
            'giftcard_message',
        );
        $productOptions = $orderItem->getProductOptions();
        foreach ($keys as $key) {
            if ($option = $quoteItem->getProduct()->getCustomOption($key)) {
                $productOptions[$key] = $option->getValue();
            }
        }

        $product = $quoteItem->getProduct();
        // set lifetime
        $lifetime = 0;
        if ($product->getUseConfigLifetime()) {
            $lifetime = Mage::getStoreConfig(
                Enterprise_GiftCard_Model_Giftcard::XML_PATH_LIFETIME,
                $orderItem->getStore()
            );
        } else {
            $lifetime = $product->getLifetime();
        }
        $productOptions['giftcard_lifetime'] = $lifetime;

        // set is_redeemable
        $isRedeemable = 0;
        if ($product->getUseConfigIsRedeemable()) {
            $isRedeemable = Mage::getStoreConfigFlag(
                Enterprise_GiftCard_Model_Giftcard::XML_PATH_IS_REDEEMABLE,
                $orderItem->getStore()
            );
        } else {
            $isRedeemable = (int) $product->getIsRedeemable();
        }
        $productOptions['giftcard_is_redeemable'] = $isRedeemable;

        // set email_template
        $emailTemplate = 0;
        if ($product->getUseConfigEmailTemplate()) {
            $emailTemplate = Mage::getStoreConfig(
                Enterprise_GiftCard_Model_Giftcard::XML_PATH_EMAIL_TEMPLATE,
                $orderItem->getStore()
            );
        } else {
            $emailTemplate = $product->getEmailTemplate();
        }
        $productOptions['giftcard_email_template'] = $emailTemplate;
        $productOptions['giftcard_type'] = $product->getGiftcardType();

        $orderItem->setProductOptions($productOptions);

        return $this;
    }

    /**
     * Return the qty of newly paid invoice items for gift card.
     * This method depends on giftcard_paid_invoice_items field in product options array.
     * It also update the field with the newly paid invoice items
     *
     * @param Mage_Sales_Model_Order_Item $item giftcard order item
     * @return int qty of newly paid invoice items
     */
    protected function _getAndUpdatePaidInvoiceItems(Mage_Sales_Model_Order_Item $item)
    {
        $newlyPaidInvoiceItemQty = 0;
        $options = $item->getProductOptions();

        $paidInvoiceItems =
                (isset($options['giftcard_paid_invoice_items']) ? $options['giftcard_paid_invoice_items'] : array());
        // find invoice for this order item
        $invoiceItemCollection = Mage::getResourceModel('sales/order_invoice_item_collection')
                ->addFieldToFilter('order_item_id', $item->getId());

        foreach ($invoiceItemCollection as $invoiceItem) {
            $invoiceId = $invoiceItem->getParentId();
            if (isset($this->_loadedInvoices[$invoiceId])) {
                $invoice = $this->_loadedInvoices[$invoiceId];
            } else {
                $invoice = Mage::getModel('sales/order_invoice')
                        ->load($invoiceId);
                $this->_loadedInvoices[$invoiceId] = $invoice;
            }
            // check, if this order item has been paid
            if ($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_PAID &&
                    !in_array($invoiceItem->getId(), $paidInvoiceItems)
            ) {
                $newlyPaidInvoiceItemQty += $invoiceItem->getQty();
                $paidInvoiceItems[] = $invoiceItem->getId();
            }
        }
        $options['giftcard_paid_invoice_items'] = $paidInvoiceItems;
        $item->setProductOptions($options);

        return $newlyPaidInvoiceItemQty;
    }

    /**
     * return whether a giftcard is redeemable
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return int
     */
    protected function _isGiftCardRedeemable($item)
    {
        $options = $item->getProductOptions();
        $isRedeemable = 0;
        if (isset($options['giftcard_is_redeemable'])) {
            $isRedeemable = $options['giftcard_is_redeemable'];
        }
        return $isRedeemable;
    }

    /**
     * return lifetime of a giftcard
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return int
     */
    protected function _getGiftCardLifeTime($item)
    {
        $options = $item->getProductOptions();
        $lifetime = 0;
        if (isset($options['giftcard_lifetime'])) {
            $lifetime = $options['giftcard_lifetime'];
        }
        return $lifetime;
    }

    /**
     * Create giftcard accounts and update the giftcard_created_codes in product option
     * Returns whether there is an error in creating giftcard
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @param int $numAccounts number of accounts to create
     * @return boolean whether there is failure when creating gift card accounts
     */
    protected function _createGiftCardAccounts(Mage_Sales_Model_Order_Item $item, $numAccounts)
    {
        $options = $item->getProductOptions();
        $isRedeemable = $this->_isGiftcardRedeemable($item);
        $lifetime = $this->_getGiftcardLifeTime($item);

        $amount = $item->getBasePrice();
        $websiteId = Mage::app()->getStore($item->getOrder()->getStoreId())->getWebsiteId();

        $createdCodes = isset($options['giftcard_created_codes']) ? $options['giftcard_created_codes'] : array();
        $data = new Varien_Object();
        $data->setWebsiteId($websiteId)
                ->setAmount($amount)
                ->setLifetime($lifetime)
                ->setIsRedeemable($isRedeemable)
                ->setOrderItem($item);

        $hasFailedCodes = false;
        for ($i = 0; $i < $numAccounts; $i++) {
            try {
                $code = new Varien_Object();
                Mage::dispatchEvent('enterprise_giftcardaccount_create', array('request' => $data, 'code' => $code));
                $createdCodes[] = $code->getCode();
            } catch (Mage_Core_Exception $e) {
                $hasFailedCodes = true;
            }
        }
        $options['giftcard_created_codes'] = $createdCodes;
        $item->setProductOptions($options);

        return $hasFailedCodes;
    }

    /**
     * Return an array of created giftcard code
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return array
     */
    protected function _getCreatedGiftCardCodes($item)
    {
        $options = $item->getProductOptions();
        $codes = isset($options['giftcard_created_codes']) ? $options['giftcard_created_codes'] : array();
        return $codes;
    }

    /**
     * Send email to giftcard recipient
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @param int $numGiftCardsToSend
     * @return void
     */
    protected function _sendEmail($item, $numGiftCardsToSend)
    {
        if ($numGiftCardsToSend <= 0)
        {
            return;
        }

        $options = $item->getProductOptions();
        $createdGiftCardCodes =
                isset($options['giftcard_created_codes']) ? $options['giftcard_created_codes'] : array();
        $sentCodes = isset($options['giftcard_sent_codes']) ? $options['giftcard_sent_codes'] : array();
        $sentCodesQty = count($sentCodes);
        $availableCodes = ($sentCodesQty > 0) ? array_diff($createdGiftCardCodes, $sentCodes) : $createdGiftCardCodes;

        if (count($availableCodes) <= 0) {
            return;
        }

        $newlySentCodes = array();
        $numNewlySentCodes = 0;
        foreach ($availableCodes as $code) {
            $newlySentCodes[] = $code;
            $sentCodes[] = $code;
            $numNewlySentCodes++;
            if ($numNewlySentCodes == $numGiftCardsToSend) {
                break;
            }
        }

        $sender = $item->getProductOptionByCode('giftcard_sender_name');
        $senderName = $item->getProductOptionByCode('giftcard_sender_name');
        $senderEmail = $item->getProductOptionByCode('giftcard_sender_email');
        if ($senderEmail) {
            $sender = "$sender <$senderEmail>";
        }
        $isRedeemable = $this->_isGiftCardRedeemable($item);
        $amount = $item->getBasePrice();
        $store = Mage::app()->getStore($item->getOrder()->getStoreId());
        $codeList = Mage::helper('enterprise_giftcard')->getEmailGeneratedItemsBlock()
                ->setCodes($newlySentCodes)
                ->setIsRedeemable($isRedeemable)
                ->setStore($store);
        $balance = Mage::app()->getLocale()->currency(
                        $store->getBaseCurrencyCode())->toCurrency($amount);

        $templateData = array(
            'name' => $item->getProductOptionByCode('giftcard_recipient_name'),
            'email' => $item->getProductOptionByCode('giftcard_recipient_email'),
            'sender_name_with_email' => $sender,
            'sender_name' => $senderName,
            'gift_message' => $item->getProductOptionByCode('giftcard_message'),
            'giftcards' => $codeList->toHtml(),
            'balance' => $balance,
            'is_multiple_codes' => 1 < $numNewlySentCodes,
            'store' => $store,
            'store_name' => $store->getName(), //@deprecated after 1.4.0.0-beta1
            'is_redeemable' => $isRedeemable,
        );

        $email = Mage::getModel('core/email_template')
                ->setDesignConfig(array('store' => $store->getId()));
        $email->sendTransactional(
                $item->getProductOptionByCode('giftcard_email_template'),
                Mage::getStoreConfig(Enterprise_GiftCard_Model_Giftcard::XML_PATH_EMAIL_IDENTITY, $store->getId()),
                $item->getProductOptionByCode('giftcard_recipient_email'),
                $item->getProductOptionByCode('giftcard_recipient_name'),
                $templateData
        );

        $options['giftcard_sent_codes'] = $sentCodes;

        if ($email->getSentSuccess()) {
            $options['email_sent'] = 1;
        }
        $item->setProductOptions($options);
        return;
    }

    /**
     * Generate gift card accounts after order save
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftCard_Model_Observer
     */
    public function generateGiftCardAccounts(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $requiredStatus = Mage::getStoreConfig(
            Enterprise_GiftCard_Model_Giftcard::XML_PATH_ORDER_ITEM_STATUS,
            $order->getStore()
        );

        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == Enterprise_GiftCard_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD) {
                //get how many additional giftcards are paid
                $newlyPaidInvoiceItemQty = $this->_getAndUpdatePaidInvoiceItems($item);

                switch ($requiredStatus) {
                    case Mage_Sales_Model_Order_Item::STATUS_INVOICED:
                        $newGiftCardAccountQty = $newlyPaidInvoiceItemQty;
                        break;
                    default:
                        $newGiftCardAccountQty = $item->getQtyOrdered();
                        $newGiftCardAccountQty -= count($this->_getCreatedGiftCardCodes($item));
                        break;
                }

                //Create giftcard accounts
                $hasFailedCodes = false;
                if ($newGiftCardAccountQty > 0) {
                    $hasFailedCodes = $this->_createGiftCardAccounts($item, $newGiftCardAccountQty);
                }

                $codes = $this->_getCreatedGiftCardCodes($item);
                $goodCodes = count($codes);
                if ($newlyPaidInvoiceItemQty && $goodCodes
                        && $item->getProductOptionByCode('giftcard_recipient_email')) {
                    $this->_sendEmail($item, $newlyPaidInvoiceItemQty);
                }

                $item->save();

                if ($hasFailedCodes) {
                    $url = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/giftcardaccount');
                    $message = Mage::helper('enterprise_giftcard')->__('Some of Gift Card Accounts were not generated properly. You can create Gift Card Accounts manually <a href="%s">here</a>.', $url);

                    Mage::getSingleton('adminhtml/session')->addError($message);
                }
            }
        }

        return $this;
    }

    /**
     * Process `giftcard_amounts` attribute afterLoad logic on loading by collection
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftCard_Model_Observer
     */
    public function loadAttributesAfterCollectionLoad(Varien_Event_Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();

        foreach ($collection as $item) {
            if (Enterprise_GiftCard_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD == $item->getTypeId()) {
                $attribute = $item->getResource()->getAttribute('giftcard_amounts');
                if ($attribute->getId()) {
                    $attribute->getBackend()->afterLoad($item);
                }
            }
        }
        return $this;
    }

    /**
     * Initialize product options renderer with giftcard specific params
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftCard_Model_Observer
     */
    public function initOptionRenderer(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        $block->addOptionsRenderCfg('giftcard', 'enterprise_giftcard/catalog_product_configuration');
        return $this;
    }
}
