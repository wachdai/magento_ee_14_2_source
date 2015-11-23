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
class Enterprise_GiftCard_Model_Customer_Api extends Mage_Api_Model_Resource_Abstract
{

    /**
     * Retrieve GiftCard data
     *
     * @param string $code
     * @return array
     */
    public function info($code)
    {
        /** @var $card Enterprise_GiftCardAccount_Model_Giftcardaccount */
        $card = $this->_getGiftCard($code);

        try {
            $card->isValid(true, true, false, false);
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_valid');
        }

        return array(
            'balance' => $card->getBalance(),
            'expire_date' => $card->getDateExpires()
        );
    }

    /**
     * Redeem gift card balance to customer store credit
     *
     * @param string $code
     * @param int $customerId
     * @param int $storeId
     * @return boolean
     */
    public function redeem($code, $customerId, $storeId = null)
    {
        if (!Mage::helper('enterprise_customerbalance')->isEnabled()) {
            $this->_fault('redemption_disabled');
        }
        /** @var $card Enterprise_GiftCardAccount_Model_Giftcardaccount */
        $card = $this->_getGiftCard($code);

        Mage::app()->setCurrentStore(
            Mage::app()->getStore($storeId)
        );

        try {
            $card->setIsRedeemed(true)
                    ->redeem($customerId);
        } catch (Exception $e) {
            $this->_fault('unable_redeem', $e->getMessage());
        }
        return true;
    }

    /**
     * Load gift card by code
     *
     * @param string $code
     * @return Enterprise_GiftCardAccount_Model_Giftcardaccount
     */
    protected function _getGiftCard($code)
    {
        $card = Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
            ->loadByCode($code);
        if (!$card->getId()) {
            $this->_fault('not_exists');
        }
        return $card;
    }

}
