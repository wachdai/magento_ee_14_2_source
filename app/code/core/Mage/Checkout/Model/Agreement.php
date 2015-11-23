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
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Enter description here ...
 *
 * @method Mage_Checkout_Model_Resource_Agreement _getResource()
 * @method Mage_Checkout_Model_Resource_Agreement getResource()
 * @method string getName()
 * @method Mage_Checkout_Model_Agreement setName(string $value)
 * @method string getContent()
 * @method Mage_Checkout_Model_Agreement setContent(string $value)
 * @method string getContentHeight()
 * @method Mage_Checkout_Model_Agreement setContentHeight(string $value)
 * @method string getCheckboxText()
 * @method Mage_Checkout_Model_Agreement setCheckboxText(string $value)
 * @method int getIsActive()
 * @method Mage_Checkout_Model_Agreement setIsActive(int $value)
 * @method int getIsHtml()
 * @method Mage_Checkout_Model_Agreement setIsHtml(int $value)
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Model_Agreement extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('checkout/agreement');
    }
}
