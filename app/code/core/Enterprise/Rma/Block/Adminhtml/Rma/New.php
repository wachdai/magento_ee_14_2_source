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

class Enterprise_Rma_Block_Adminhtml_Rma_New extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize RMA new page. Set management buttons
     *
     */
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_rma';
        $this->_blockGroup = 'enterprise_rma';

        parent::__construct();

        $this->_updateButton('reset', 'label', Mage::helper('enterprise_rma')->__('Cancel'));
        $this->_updateButton('reset', 'class', 'cancel');

        $orderId    = false;
        $link       = $this->getUrl('*/*/');

        if (Mage::registry('current_order') && Mage::registry('current_order')->getId()) {
            $order      = Mage::registry('current_order');
            $orderId    = $order->getId();

            $referer    = $this->getRequest()->getServer('HTTP_REFERER');

            if (strpos($referer, 'customer') !== false) {
                $link = $this->getUrl('*/customer/edit/',
                    array(
                        'id'  => $order->getCustomerId(),
                        'active_tab'=> 'orders'
                    )
                );
            }
        }

        if (Mage::helper('enterprise_rma')->canCreateRmaByAdmin($orderId)) {
            $this->_updateButton('reset', 'onclick', "setLocation('" . $link . "')");
            $this->_updateButton('save', 'label', Mage::helper('enterprise_rma')->__('Submit RMA'));
        } else {
            $this->_updateButton('reset', 'onclick', "setLocation('" . $link . "')");
            $this->_removeButton('save');
        }
        $this->_removeButton('back');
    }

    /**
     * Get header text for RMA edit page
     *
     * @return string
     */
    public function getHeaderText()
    {
        return $this->getLayout()->createBlock('enterprise_rma/adminhtml_rma_create_header')->toHtml();
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/save', array('order_id' => Mage::registry('current_order')->getId()));
    }
}
