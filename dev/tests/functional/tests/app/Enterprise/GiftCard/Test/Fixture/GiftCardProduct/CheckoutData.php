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
 * @category    Tests
 * @package     Tests_Functional
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

namespace Enterprise\GiftCard\Test\Fixture\GiftCardProduct;

/**
 * Data for fill product form on frontend.
 *
 * Data keys:
 *  - preset (Checkout data verification preset name)
 */
class CheckoutData extends \Mage\Catalog\Test\Fixture\CatalogProductSimple\CheckoutData
{
    /**
     * Get preset array.
     *
     * @param string $name
     * @return array|null
     */
    protected function getPreset($name)
    {
        $presets = [
            'default' => [
                'options' => [
                    'giftcard_options' => [
                        'giftcard_amount' => 'option_key_1',
                        'giftcard_sender_name' => 'Sender_name_%isolation%',
                        'giftcard_sender_email' => 'Sender_name_%isolation%@example.com',
                        'giftcard_recipient_name' => 'Recipient_name_%isolation%',
                        'giftcard_recipient_email' => 'Recipient_name_%isolation%@example.com'
                    ],
                ],
                'qty' => '1',
                'cartItem' => [
                    'price' => 150.00,
                    'subtotal' => 150.00,
                 ],
            ],
        ];
        return isset($presets[$name]) ? $presets[$name] : null;
    }
}
