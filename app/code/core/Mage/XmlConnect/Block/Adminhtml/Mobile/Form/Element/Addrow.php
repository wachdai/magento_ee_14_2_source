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
 * Xmlconnect Add row form element
 *
 * @deprecated will be removed
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Form_Element_Addrow extends Varien_Data_Form_Element_Button
{
    /**
     * Render Element Html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getBeforeElementHtml() . '<button id="'.$this->getHtmlId() . '" name="' . $this->getName()
            . '" value="'.$this->getEscapedValue() . '" ' . $this->serialize($this->getHtmlAttributes()) . ' ><span>'
            . $this->getEscapedValue() . '</span></button>' . $this->getAfterElementHtml();
        return $html;
    }

    /**
     * Getter for "before_element_html"
     *
     * @return string
     */
    public function getBeforeElementHtml()
    {
        return $this->getData('before_element_html');
    }

    /**
     * Return label html code
     *
     * @param string $idSuffix
     * @return string
     */
    public function getLabelHtml($idSuffix = '')
    {
        if ($this->getLabel() !== null) {
            $html = '<label  for="' . $this->getHtmlId() . $idSuffix . '">' . $this->getLabel()
                . ($this->getRequired() ? ' <span class="required">*</span>' : '') . '</label>';
        } else {
            $html = '';
        }
        return $html;
    }

    /**
     * Overriding toHtml parent method
     * Adding addrow Block to element renderer
     *
     * @return string
     */
    public function toHtml()
    {
        $blockClassName = Mage::getConfig()->getBlockClassName('adminhtml/template');
        $jsBlock = Mage::getModel($blockClassName);
        $jsBlock->setTemplate('xmlconnect/form/element/addrow.phtml');
        $jsBlock->setOptions($this->getOptions());
        return parent::toHtml() . $jsBlock->toHtml();
    }
}
