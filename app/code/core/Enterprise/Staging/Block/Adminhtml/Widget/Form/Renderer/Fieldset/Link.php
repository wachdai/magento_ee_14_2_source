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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Staging link element renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Widget_Form_Renderer_Fieldset_Link extends Mage_Adminhtml_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    protected function _construct()
    {
        $this->setTemplate('widget/form/renderer/fieldset/element.phtml');
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($this);

        $this->getType($element->getType());
        $this->setLabelHtml($this->_getLabelHtml($element));
        $this->setElementHtml($this->_getElementHtml($element));

        return $this->toHtml();
    }

    protected function _getLabelHtml($element)
    {
        return $element->getLabelHtml();
    }

    protected function _getElementHtml($element)
    {
        $link = $element->getValue();
        if ($element->getTitle()) {
            $title = $element->getTitle();
        } else {
            $title = $link;
        }

        if ($element->getLength() && strlen($title) > $element->getLength()) {
            $title = substr($title, 0, $element->getLength()) . '...';
        }

        $html = $element->getBold() ? '<strong>' : '';
        $html.= '<a href="'.$link.'" target="_stagingWebsite">'.$title.'</a>';
        $html.= $element->getBold() ? '</strong>' : '';
        $html.= $element->getAfterElementHtml();
        return $html;
    }
}
