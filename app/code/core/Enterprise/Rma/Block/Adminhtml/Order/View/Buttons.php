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
 * @package     Enterprise_Rma
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Additional buttons on order view page
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Block_Adminhtml_Order_View_Buttons extends Mage_Adminhtml_Block_Sales_Order_View
{
    /**
     * Add button to Shopping Cart Management etc.
     *
     * @return return
     */
    public function addButtons()
    {
        $container = $this->getParentBlock();
        if ($container instanceof Mage_Adminhtml_Block_Template && $container->getOrderId()) {
            $isReturnable = Mage::helper('enterprise_rma')->canCreateRmaByAdmin($container->getOrder());
            if ($isReturnable) {
                $url = Mage::getSingleton('adminhtml/url')
                   ->getUrl('*/rma/new', array('order_id' => $container->getOrderId()));
                $order = 35;
                if (isset($this->_buttons[0]['send_notification']['sort_order'])) {
                    $order = $this->_buttons[0]['send_notification']['sort_order'] + 5;
                }
                $container->addButton('create_rma', array(
                    'label' => Mage::helper('enterprise_rma')->__('Create RMA'),
                    'onclick' => "setLocation('" . $url . "')",
                ), 0, $order);
            }
        }
        return $this;
    }
}
