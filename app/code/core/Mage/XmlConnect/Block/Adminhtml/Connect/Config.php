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
 * Admin application config renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Connect_Config extends Mage_Core_Block_Abstract
{
    /**
     * Render login form xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var Mage_XmlConnect_Model_Simplexml_Element $configXmlObj */
        $configXmlObj = Mage::getModel('xmlconnect/simplexml_element', '<configuration></configuration>');
        $this->_addLocalization($configXmlObj);
        $configXmlObj->addCustomChild('xmlconnect_version', Mage::getConfig()->getNode(
            Mage_XmlConnect_Model_Application::XML_PATH_MODULE_VERSION
        ));

        return $configXmlObj->asNiceXml();
    }

    /**
     * Add localization data to xml object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $xml
     * @return Mage_XmlConnect_Block_Adminhtml_Connect_Config
     */
    protected function _addLocalization(Mage_XmlConnect_Model_Simplexml_Element $xml)
    {
        /** @var $translateHelper Mage_XmlConnect_Helper_Translate */
        $translateHelper = Mage::helper('xmlconnect/translate');
        $xml->addCustomChild('localization', $this->getUrl('*/*/localization'), array(
            'hash' => sha1(serialize($translateHelper->getLocalizationArray()))
        ));
        return $this;
    }
}
