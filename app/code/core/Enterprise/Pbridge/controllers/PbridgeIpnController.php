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
 * @package     Enterprise_Pbridge
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Index controller
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pbridge_PbridgeIpnController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index Action.
     * Forward to result action
     *
     * @return void
     */
    public function indexAction()
    {
        $ipn = Mage::getModel('enterprise_pbridge/payment_method_pbridge_ipn');

        $ipn->setIpnFormData($this->getRequest()->getPost())
            ->processIpnRequest();
        exit;
    }

    /**
     * Payone IPN action
     */
    public function payoneAction()
    {
        /** @var Enterprise_Pbridge_Model_Payment_Method_Payone_Ipn $ipn */
        $ipn = Mage::getModel('enterprise_pbridge/payment_method_payone_ipn');
        $ipn->setIpnFormData($this->getRequest()->getPost())
            ->processIpnRequest();
        exit;
    }
}
