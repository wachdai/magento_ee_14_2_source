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
 * RMA Item Widget Form Image File Element Block
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 */
class Enterprise_Rma_Block_Adminhtml_Rma_Edit_Item_Form_Element_Image extends Varien_Data_Form_Element_Abstract
{
    /**
     * Initialize Form Element
     *
     * @param array $attributes
     */
    public function _construct($attributes = array())
    {
        parent::_construct($attributes);
        $this->setType('file');
    }

    /**
     * Return Form Element HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $this->addClass('input-file');
        if ($this->getRequired()) {
            $this->removeClass('required-entry');
            $this->addClass('required-file');
        }

        $element = sprintf('<input id="%s" name="%s" %s />%s%s',
            $this->getHtmlId(),
            $this->getName(),
            $this->serialize($this->getHtmlAttributes()),
            $this->getAfterElementHtml(),
            $this->_getHiddenInput()
        );

        return $this->_getPreviewHtml() . $element . $this->_getDeleteCheckboxHtml();
    }

    /**
     * Return Delete CheckBox Label
     *
     * @return string
     */
    protected function _getDeleteCheckboxLabel()
    {
        return Mage::helper('adminhtml')->__('Delete Image');
    }

    /**
     * Return Delete File CheckBox HTML
     *
     * @return string
     */
    protected function _getDeleteCheckboxHtml()
    {
        $html = '';
        if ($this->getValue() && !$this->getRequired() && !is_array($this->getValue())) {
            $checkboxId = sprintf('%s_delete', $this->getHtmlId());
            $checkbox   = array(
                'type'  => 'checkbox',
                'name'  => sprintf('%s[delete]', $this->getName()),
                'value' => '1',
                'class' => 'checkbox',
                'id'    => $checkboxId
            );
            $label      = array(
                'for'   => $checkboxId
            );
            if ($this->getDisabled()) {
                $checkbox['disabled'] = 'disabled';
                $label['class'] = 'disabled';
            }

            $html .= '<span class="' . $this->_getDeleteCheckboxSpanClass() . '">';
            $html .= $this->_drawElementHtml('input', $checkbox) . ' ';
            $html .= $this->_drawElementHtml('label', $label, false) . $this->_getDeleteCheckboxLabel() . '</label>';
            $html .= '</span>';
        }
        return $html;
    }

    /**
     * Return Delete CheckBox SPAN Class name
     *
     * @return string
     */
    protected function _getDeleteCheckboxSpanClass()
    {
        return 'delete-image';
    }

    /**
     * Return File preview link HTML
     *
     * @return string
     */
    protected function _getPreviewHtml()
    {
        $html = '';
        if ($this->getValue() && !is_array($this->getValue())) {
            $url = $this->_getPreviewUrl();
            $imageId = sprintf('%s_image', $this->getHtmlId());
            $image   = array(
                'alt'    => Mage::helper('adminhtml')->__('View Full Size'),
                'title'  => Mage::helper('adminhtml')->__('View Full Size'),
                'src'    => $url,
                'class'  => 'small-image-preview v-middle',
                'height' => 22,
                'width'  => 22,
                'id'     => $imageId
            );
            $link    = array(
                'href'      => $url,
                'onclick'   => "imagePreview('{$imageId}'); return false;",
            );

            $html = sprintf('%s%s</a> ',
                $this->_drawElementHtml('a', $link, false),
                $this->_drawElementHtml('img', $image)
            );
        }
        return $html;
    }

    /**
     * Return Image URL
     *
     * @return string
     */
    protected function _getPreviewUrl()
    {
        if (is_array($this->getValue())) {
            return false;
        }
        return Mage::helper('adminhtml')->getUrl('adminhtml/rma/viewfile', array(
            'image'      => Mage::helper('core')->urlEncode($this->getValue()),
        ));
    }

    /**
     * Return Hidden element with current value
     *
     * @return string
     */
    protected function _getHiddenInput()
    {
        return $this->_drawElementHtml('input', array(
            'type'  => 'hidden',
            'name'  => sprintf('%s[value]', $this->getName()),
            'id'    => sprintf('%s_value', $this->getHtmlId()),
            'value' => $this->getEscapedValue()
        ));
    }

    /**
     * Return Element HTML
     *
     * @param string $element
     * @param array $attributes
     * @param boolean $closed
     * @return string
     */
    protected function _drawElementHtml($element, array $attributes, $closed = true)
    {
        $parts = array();
        foreach ($attributes as $k => $v) {
            $parts[] = sprintf('%s="%s"', $k, $v);
        }

        return sprintf('<%s %s%s>', $element, implode(' ', $parts), $closed ? ' /' : '');
    }

    /**
     * Return escaped value
     *
     * @param int $index
     * @return string
     */
    public function getEscapedValue($index = null)
    {
        if (is_array($this->getValue())) {
            return false;
        }
        $value = $this->getValue();
        if (is_array($value) && is_null($index)) {
            $index = 'value';
        }

        return parent::getEscapedValue($index);
    }
}
