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
 * Additional buttons on customer edit form
 *
 * @category    Enterprise
 * @package     Enterprise_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Block_Adminhtml_Customer_Edit_Buttons extends Mage_Adminhtml_Block_Customer_Edit
{
    /**
     * Add button to Shopping Cart Management etc.
     *
     * @return return
     */
    public function addButtons()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/enterprise_checkout/view')
            && !Mage::getSingleton('admin/session')->isAllowed('sales/enterprise_checkout/update')
            || Mage::app()->getStore()->getWebsiteId() == Mage::registry('current_customer')->getWebsiteId())
        {
            return $this;
        }
        $container = $this->getParentBlock();
        if ($container instanceof Mage_Adminhtml_Block_Template && $container->getCustomerId()) {
            $url = Mage::getSingleton('adminhtml/url')
               ->getUrl('*/checkout/index', array('customer' => $container->getCustomerId()));
            $container->addButton('manage_quote', array(
                'label' => Mage::helper('enterprise_checkout')->__('Manage Shopping Cart'),
                'onclick' => "setLocation('" . $url . "')",
            ), 0);
        }
        return $this;
    }
}
