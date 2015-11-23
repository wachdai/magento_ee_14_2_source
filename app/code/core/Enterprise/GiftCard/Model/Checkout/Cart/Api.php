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

/**
 * GiftCard api
 *
 * @category   Enterprise
 * @package    Enterprise_GiftCard
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Enterprise_GiftCard_Model_Checkout_Cart_Api extends Mage_Checkout_Model_Api_Resource
{
    /**
     * List gift cards account belonging to quote
     *
     * @param  string $quoteId
     * @param null|string $storeId
     * @return array
     */
    public function items($quoteId, $storeId = null)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $this->_getQuote($quoteId, $storeId);

        $giftcardsList = Mage::helper('enterprise_giftcardaccount')->getCards($quote);
        // map short names of giftcard account attributes to long
        foreach($giftcardsList as $id => $card) {
            $giftcardsList[$id] = array(
                'giftcardaccount_id' => $card['i'],
                'code' => $card['c'],
                'used_amount' => $card['a'],
                'base_amount' => $card['ba'],
            );
        }
        return $giftcardsList;
    }

    /**
     * Add gift card account to quote
     *
     * @param string $giftcardAccountCode
     * @param  string $quoteId
     * @param null|string $storeId
     * @return bool
     */
    public function add($giftcardAccountCode, $quoteId, $storeId = null)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $this->_getQuote($quoteId, $storeId);

        /** @var $giftcardAccount Enterprise_GiftCardAccount_Model_Giftcardaccount */
        $giftcardAccount = Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                ->loadByCode($giftcardAccountCode);
        if (!$giftcardAccount->getId()) {
            $this->_fault('giftcard_account_not_found_by_code');
        }
        try {
            $giftcardAccount->addToCart(true, $quote);
        } catch (Exception $e) {
            $this->_fault('save_error', $e->getMessage());
        }

        return true;
    }

    /**
     * Remove gift card account to quote
     *
     * @param string $giftcardAccountCode
     * @param  string $quoteId
     * @param null|string $storeId
     * @return bool
     */
    public function remove($giftcardAccountCode, $quoteId, $storeId = null)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $this->_getQuote($quoteId, $storeId);

        /** @var $giftcardAccount Enterprise_GiftCardAccount_Model_Giftcardaccount */
        $giftcardAccount = Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                ->loadByCode($giftcardAccountCode);
        if (!$giftcardAccount->getId()) {
            $this->_fault('giftcard_account_not_found_by_code');
        }
        try {
            $giftcardAccount->removeFromCart(true, $quote);
        } catch (Exception $e) {
            $this->_fault('save_error', $e->getMessage());
        }

        return true;
    }
}
