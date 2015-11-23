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
 * Helper for fetching properties by product configurational item
 *
 * @category   Enterprise
 * @package    Enterprise_GiftCard
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftCard_Helper_Catalog_Product_Configuration extends Mage_Core_Helper_Abstract
    implements Mage_Catalog_Helper_Product_Configuration_Interface
{
    /**
     * Prepare custom option for display, returns false if there's no value
     *
     * @param string $code
     * @return mixed
     */
    public function prepareCustomOption(Mage_Catalog_Model_Product_Configuration_Item_Interface $item, $code)
    {
        $option = $item->getOptionByCode($code);
        if ($option) {
            $value = $option->getValue();
            if ($value) {
                return $this->escapeHtml($value);
            }
        }
        return false;
    }

    /**
     * Get gift card option list
     *
     * @return array
     */
    public function getGiftcardOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        $result = array();
        $value = $this->prepareCustomOption($item, 'giftcard_sender_name');
        if ($value) {
            $email = $this->prepareCustomOption($item, 'giftcard_sender_email');
            if ($email) {
                $value = "{$value} &lt;{$email}&gt;";
            }
            $result[] = array(
                'label' => $this->__('Gift Card Sender'),
                'value' => $value
            );
        }

        $value = $this->prepareCustomOption($item, 'giftcard_recipient_name');
        if ($value) {
            $email = $this->prepareCustomOption($item, 'giftcard_recipient_email');
            if ($email) {
                $value = "{$value} &lt;{$email}&gt;";
            }
            $result[] = array(
                'label' => $this->__('Gift Card Recipient'),
                'value' => $value
            );
        }

        $value = $this->prepareCustomOption($item, 'giftcard_message');
        if ($value) {
            $result[] = array(
                'label' => $this->__('Gift Card Message'),
                'value' => $value
            );
        }

        return $result;
    }

    /**
     * Retrieves product options list
     *
     * @param Mage_Catalog_Model_Product_Configuration_Item_Interface $item
     * @return array
     */
    public function getOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        return array_merge(
            $this->getGiftcardOptions($item),
            Mage::helper('catalog/product_configuration')->getCustomOptions($item)
        );
    }
}
