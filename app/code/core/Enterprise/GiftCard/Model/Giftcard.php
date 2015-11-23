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

class Enterprise_GiftCard_Model_Giftcard extends Mage_Core_Model_Abstract
{
    const XML_PATH                    = 'giftcard/general/';
    const XML_PATH_EMAIL              = 'giftcard/email/';

    const XML_PATH_IS_REDEEMABLE      = 'giftcard/general/is_redeemable';
    const XML_PATH_LIFETIME           = 'giftcard/general/lifetime';
    const XML_PATH_ORDER_ITEM_STATUS  = 'giftcard/general/order_item_status';
    const XML_PATH_ALLOW_MESSAGE      = 'giftcard/general/allow_message';
    const XML_PATH_MESSAGE_MAX_LENGTH = 'giftcard/general/message_max_length';
    const XML_PATH_EMAIL_IDENTITY     = 'giftcard/email/identity';
    const XML_PATH_EMAIL_TEMPLATE     = 'giftcard/email/template';

    const TYPE_VIRTUAL  = 0;
    const TYPE_PHYSICAL = 1;
    const TYPE_COMBINED = 2;

    const OPEN_AMOUNT_DISABLED = 0;
    const OPEN_AMOUNT_ENABLED  = 1;
}
