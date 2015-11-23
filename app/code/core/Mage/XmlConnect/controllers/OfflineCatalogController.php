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
 * @package     Mage_XmlConnect
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * XmlConnect offline catalog controller
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_OfflineCatalogController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     *
     * @return null
     */
    public function indexAction()
    {
        /** @var $helper Mage_XmlConnect_Helper_OfflineCatalog */
        $helper = Mage::helper('xmlconnect/offlineCatalog');
        $key = $this->getRequest()->getParam('key', false);
        if (!$key || !$helper->setCurrentDeviceModel($key)) {
            $this->_forward('noRoute');
            return;
        }
        try {
            $this->loadLayout(false);
            Mage::getModel('xmlconnect/offlineCatalog')->exportData();
            $helper->renderXmlObject();
            Mage::getSingleton('core/session')->addSuccess($this->__('Offline catalog export complete.'));
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError($this->__('Offline catalog export failed.'));
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(
            array('result' => Mage::app()->getLayout()->getMessagesBlock()->getGroupedHtml())
        ));
    }
}
