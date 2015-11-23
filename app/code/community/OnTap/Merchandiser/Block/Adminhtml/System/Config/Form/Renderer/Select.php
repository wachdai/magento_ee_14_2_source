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
 * @category    OnTap
 * @package     OnTap_Merchandiser
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class OnTap_Merchandiser_Block_Adminhtml_System_Config_Form_Renderer_Select extends Mage_Core_Block_Abstract
{
    /**
     * _construct
     *
     * @param array $attributes
     */
    public function _construct($attributes = array())
    {
        parent::_construct($attributes);
        $this->_prepareOptions();
    }

    /**
     * _toHtml
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getElementHtml();
    }

    /**
     * _escape
     *
     * @param mixed $string
     * @return string
     */
    protected function _escape($string)
    {
        return htmlspecialchars($string, ENT_COMPAT);
    }

    /**
     * getElementHtml
     *
     * @return string
     */
    public function getElementHtml()
    {
        $elementHTML = '<select class="'.$this->getClass().'" name="' .
            $this->getInputName() . '" style="width:'. $this->getwidth().'px" >';
        foreach ($this->getValues() as $attrValue => $attrName) {
            $elementHTML .= '<option value="'.$attrValue.'">'.$attrName.'</option>';
        }
        $elementHTML .= '</select>';
        return $elementHTML;
    }

    /**
     * _prepareOptions
     *
     * @return void
     */
    protected function _prepareOptions()
    {
        $optionValue = $this->getValues();
        if (empty($optionValue)) {
            $optionsAvailable = $this->getOptions();
            if (is_array($optionsAvailable)) {
                $optionValue = array();
                foreach ($optionsAvailable as $optValue => $optLabel) {
                    $optionValue[] = array('value' => $optValue, 'label' => $optLabel);
                }
            } elseif (is_string($optionsAvailable)) {
                $optionValue = array(array('value' => $optionsAvailable, 'label' => $optionsAvailable));
            }
            $this->setValues($optionValue);
        }
    }

    /**
     * getHtmlAttributes
     *
     * @return array
     */
    public function getHtmlAttributes()
    {
        return array('title', 'class', 'style', 'onclick', 'onchange', 'disabled', 'readonly', 'tabindex');
    }

    /**
     * addClass
     *
     * @param mixed $class
     * @return object
     */
    public function addClass($class)
    {
        $oldClass = $this->getClass();
        $this->setClass($oldClass . ' ' . $class);
        return $this;
    }
}
