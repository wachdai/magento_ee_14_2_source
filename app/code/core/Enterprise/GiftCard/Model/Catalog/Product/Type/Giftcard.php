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

class Enterprise_GiftCard_Model_Catalog_Product_Type_Giftcard extends Mage_Catalog_Model_Product_Type_Abstract
{
    const TYPE_GIFTCARD     = 'giftcard';

    /**
     * Whether product quantity is fractional number or not
     *
     * @var bool
     */
    protected $_canUseQtyDecimals  = false;

    /**
     * Product is configurable
     *
     * @var bool
     */
    protected $_canConfigure = true;

    /**
     * Check is gift card product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isGiftCard($product = null)
    {
        return true;
    }

    /**
     * Check if gift card type is combined
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isTypeCombined($product = null)
    {
        if ($this->getProduct($product)->getGiftcardType() == Enterprise_GiftCard_Model_Giftcard::TYPE_COMBINED) {
            return true;
        }
        return false;
    }

    /**
     * Check if gift card type is physical
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isTypePhysical($product = null)
    {
        if ($this->getProduct($product)->getGiftcardType() == Enterprise_GiftCard_Model_Giftcard::TYPE_PHYSICAL) {
            return true;
        }
        return false;
    }

    /**
     * Check if gift card type is virtual
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isTypeVirtual($product = null)
    {
        if ($this->getProduct($product)->getGiftcardType() == Enterprise_GiftCard_Model_Giftcard::TYPE_VIRTUAL) {
            return true;
        }
        return false;
    }

    /**
     * Check if gift card is virtual product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isVirtual($product = null)
    {
        if ($this->getProduct($product)->getGiftcardType() == Enterprise_GiftCard_Model_Giftcard::TYPE_VIRTUAL) {
            return true;
        }
        return false;
    }

    /**
     * Check if product is available for sale
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isSalable($product = null)
    {
        $amounts = $this->getProduct($product)->getPriceModel()->getAmounts($product);
        $open = $this->getProduct($product)->getAllowOpenAmount();

        if (!$open && !$amounts) {
            return false;
        }

        return parent::isSalable($product);
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Use standard preparation process and also add specific giftcard options.
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);

        if (is_string($result)) {
            return $result;
        }

        try {
            $amount = $this->_validate($buyRequest, $product, $processMode);
        } catch (Mage_Core_Exception $e) {
            return $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            return Mage::helper('enterprise_giftcard')->__('An error has occurred while preparing Gift Card.');
        }

        $product->addCustomOption('giftcard_amount', $amount, $product);
        $product->addCustomOption('giftcard_sender_name', $buyRequest->getGiftcardSenderName(), $product);
        $product->addCustomOption('giftcard_recipient_name', $buyRequest->getGiftcardRecipientName(), $product);
        if (!$this->isTypePhysical($product)) {
            $product->addCustomOption('giftcard_sender_email', $buyRequest->getGiftcardSenderEmail(), $product);
            $product->addCustomOption('giftcard_recipient_email', $buyRequest->getGiftcardRecipientEmail(), $product);
        }

        $messageAllowed = false;
        if ($product->getUseConfigAllowMessage()) {
            $messageAllowed = Mage::getStoreConfigFlag(Enterprise_GiftCard_Model_Giftcard::XML_PATH_ALLOW_MESSAGE);
        } else {
            $messageAllowed = (int) $product->getAllowMessage();
        }

        if ($messageAllowed) {
            $product->addCustomOption('giftcard_message', $buyRequest->getGiftcardMessage(), $product);
        }

        return $result;
    }

    /**
     * Validate Gift Card product, determine and return its amount
     *
     * @param Varien_Object $buyRequest
     * @param  $product
     * @param  $processMode
     * @return double|float|mixed
     */
    private function _validate(Varien_Object $buyRequest, $product, $processMode)
    {
        $product = $this->getProduct($product);
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);

        $allowedAmounts = array();
        foreach ($product->getGiftcardAmounts() as $value) {
            $allowedAmounts[] = Mage::app()->getStore()->roundPrice($value['website_value']);
        }

        $allowOpen = $product->getAllowOpenAmount();
        $minAmount = $product->getOpenAmountMin();
        $maxAmount = $product->getOpenAmountMax();


        $selectedAmount = $buyRequest->getGiftcardAmount();
        $customAmount = $buyRequest->getCustomGiftcardAmount();

        $rate = Mage::app()->getStore()->getCurrentCurrencyRate();
        if ($rate != 1) {
            if ($customAmount) {
                $customAmount = Mage::app()->getLocale()->getNumber($customAmount);
                if (is_numeric($customAmount) && $customAmount) {
                    $customAmount = Mage::app()->getStore()->roundPrice($customAmount/$rate);
                }
            }
        }

        $emptyFields = 0;
        if (!$buyRequest->getGiftcardRecipientName()) {
            $emptyFields++;
        }
        if (!$buyRequest->getGiftcardSenderName()) {
            $emptyFields++;
        }

        if (!$this->isTypePhysical($product)) {
            if (!$buyRequest->getGiftcardRecipientEmail()) {
                $emptyFields++;
            }
            if (!$buyRequest->getGiftcardSenderEmail()) {
                $emptyFields++;
            }
        }

        if (($selectedAmount == 'custom' || !$selectedAmount) && $allowOpen && $customAmount <= 0) {
            $emptyFields++;
        } else if (is_numeric($selectedAmount)) {
            if (!in_array($selectedAmount, $allowedAmounts)) {
                $emptyFields++;
            }
        } else if (count($allowedAmounts) != 1) {
            $emptyFields++;
        }

        if ($emptyFields > 1 && $isStrictProcessMode) {
            Mage::throwException(
                Mage::helper('enterprise_giftcard')->__('Please specify all the required information.')
            );
        }

        $amount = null;
        if (($selectedAmount == 'custom' || !$selectedAmount) && $allowOpen) {
            if ($customAmount <= 0 && $isStrictProcessMode) {
                Mage::throwException(
                    Mage::helper('enterprise_giftcard')->__('Please specify Gift Card amount.')
                );
            }
            if (!$minAmount || ($minAmount && $customAmount >= $minAmount)) {
                if (!$maxAmount || ($maxAmount && $customAmount <= $maxAmount)) {
                    $amount = $customAmount;
                } else if ($customAmount > $maxAmount && $isStrictProcessMode) {
                    $messageAmount = Mage::helper('core')->currency($maxAmount, true, false);
                    Mage::throwException(
                        Mage::helper('enterprise_giftcard')->__('Gift Card max amount is %s', $messageAmount)
                    );
                }
            } else if ($customAmount < $minAmount && $isStrictProcessMode) {
                $messageAmount = Mage::helper('core')->currency($minAmount, true, false);
                Mage::throwException(
                    Mage::helper('enterprise_giftcard')->__('Gift Card min amount is %s', $messageAmount)
                );
            }
        } else if (is_numeric($selectedAmount)) {
            if (in_array($selectedAmount, $allowedAmounts)) {
                $amount = $selectedAmount;
            }
        }
        if (is_null($amount)) {
            if (count($allowedAmounts) == 1) {
                $amount = array_shift($allowedAmounts);
            }
        }

        if (is_null($amount) && $isStrictProcessMode) {
            Mage::throwException(
                Mage::helper('enterprise_giftcard')->__('Please specify Gift Card amount.')
            );
        }

        if (!$buyRequest->getGiftcardRecipientName() && $isStrictProcessMode) {
            Mage::throwException(
                Mage::helper('enterprise_giftcard')->__('Please specify recipient name.')
            );
        }
        if (!$buyRequest->getGiftcardSenderName() && $isStrictProcessMode) {
            Mage::throwException(
                Mage::helper('enterprise_giftcard')->__('Please specify sender name.')
            );
        }

        if (!$this->isTypePhysical($product)) {
            if (!$buyRequest->getGiftcardRecipientEmail() && $isStrictProcessMode) {
                Mage::throwException(
                    Mage::helper('enterprise_giftcard')->__('Please specify recipient email.')
                );
            }
            if (!$buyRequest->getGiftcardSenderEmail() && $isStrictProcessMode) {
                Mage::throwException(
                    Mage::helper('enterprise_giftcard')->__('Please specify sender email.')
                );
            }
        }

        return $amount;
    }

    /**
     * Check if product can be bought
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Abstract
     * @throws Mage_Core_Exception
     */
    public function checkProductBuyState($product = null)
    {
        parent::checkProductBuyState($product);
        $product = $this->getProduct($product);
        $option = $product->getCustomOption('info_buyRequest');
        if ($option instanceof Mage_Sales_Model_Quote_Item_Option) {
            $buyRequest = new Varien_Object(unserialize($option->getValue()));
            $this->_validate($buyRequest, $product, self::PROCESS_MODE_FULL);
        }
        return $this;
    }


    /**
     * Sets flag that product has required options, because gift card always
     * has some required options, at least - recipient name
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Enterprise_GiftCard_Model_Catalog_Product_Type_Giftcard
     */
    public function beforeSave($product = null)
    {
        parent::beforeSave($product);
        $this->getProduct($product)->setTypeHasOptions(true);
        $this->getProduct($product)->setTypeHasRequiredOptions(true);
        return $this;
    }

    /**
     * Prepare selected options for giftcard
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  Varien_Object $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $options = array(
            'giftcard_amount'         => $buyRequest->getGiftcardAmount(),
            'custom_giftcard_amount'  => $buyRequest->getCustomGiftcardAmount(),
            'giftcard_sender_name'    => $buyRequest->getGiftcardSenderName(),
            'giftcard_sender_email'    => $buyRequest->getGiftcardSenderEmail(),
            'giftcard_recipient_name' => $buyRequest->getGiftcardRecipientName(),
            'giftcard_recipient_email' => $buyRequest->getGiftcardRecipientEmail(),
            'giftcard_message'        => $buyRequest->getGiftcardMessage()
        );

        return $options;
    }
}
