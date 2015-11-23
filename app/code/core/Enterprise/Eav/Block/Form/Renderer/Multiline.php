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
 * @package     Enterprise_Eav
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * EAV entity Attribute Form Renderer Block for Multiply line
 *
 * @category    Enterprise
 * @package     Enterprise_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Eav_Block_Form_Renderer_Multiline extends Enterprise_Eav_Block_Form_Renderer_Abstract
{
    /**
     * Return original entity value
     * Value didn't escape and filter
     *
     * @return array
     */
    public function getValues()
    {
        $value = $this->getEntity()->getData($this->getAttributeObject()->getAttributeCode());
        if (!is_array($value)) {
            $value = explode("\n", $value);
        }
        return $value;
    }

    /**
     * Return count of lines for multiply line attribute
     *
     * @return int
     */
    public function getLineCount()
    {
        return $this->getAttributeObject()->getMultilineCount();
    }

    /**
     * Return array of validate classes
     *
     * @param boolean $withRequired
     * @return array
     */
    protected function _getValidateClasses($withRequired = true)
    {
        $classes    = parent::_getValidateClasses($withRequired);
        $rules      = $this->getAttributeObject()->getValidateRules();
        if (!empty($rules['min_text_length'])) {
            $classes[] = 'validate-length';
            $classes[] = 'minimum-length-' . $rules['min_text_length'];
        }
        if (!empty($rules['max_text_length'])) {
            if (!in_array('validate-length', $classes)) {
                $classes[] = 'validate-length';
            }
            $classes[] = 'maximum-length-' . $rules['max_text_length'];
        }

        return $classes;
    }

    /**
     * Return HTML class attribute value
     * Validate and rules
     *
     * @return string
     */
    public function getLineHtmlClass()
    {
        $classes = $this->_getValidateClasses(false);
        return empty($classes) ? '' : ' ' . implode(' ', $classes);
    }

    /**
     * Return filtered and escaped value
     *
     * @param int $index
     * @return string
     */
    public function getEscapedValue($index)
    {
        $values = $this->getValues();
        if (isset($values[$index])) {
            $value = $values[$index];
        } else {
            $value = '';
        }

        return $this->escapeHtml($this->_applyOutputFilter($value));
    }
}
