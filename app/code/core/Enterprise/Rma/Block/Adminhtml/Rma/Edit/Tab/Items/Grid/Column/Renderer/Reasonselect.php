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
 * Grid column widget for rendering action grid cells
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_Items_Grid_Column_Renderer_Reasonselect
    extends Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_Items_Grid_Column_Renderer_Abstract
{
    /**
     * Renders column as select when it is editable
     *
     * @param   Varien_Object $row
     * @return  string
     */
    protected function _getEditableView(Varien_Object $row)
    {
        /** @var $rmaItemAttribute Enterprise_Rma_Model_Item_Attribute */
        $rmaItemAttribute = Mage::getModel('enterprise_rma/item_form')
            ->setFormCode('default')
            ->getAttribute('reason_other');

        $selectName = 'items[' . $row->getId() . '][' . $this->getColumn()->getId() . ']';
        $html = '<select name="' . $selectName . '" class="action-select reason required-entry">'
            . '<option value=""></option>';

        $selectedIndex = $row->getData($this->getColumn()->getIndex());
        foreach ($this->getColumn()->getOptions() as $val => $label){
            $selected = isset($selectedIndex) && $val == $selectedIndex ? ' selected="selected"' : '';
            $html .= '<option value="' . $val . '"' . $selected . '>' . $label . '</option>';
        }

        if ($rmaItemAttribute && $rmaItemAttribute->getId()) {
            $selected = $value == 0 && $row->getReasonOther() != '' ? ' selected="selected"' : '';
            $html .= '<option value="other"' . $selected . '>' . $rmaItemAttribute->getStoreLabel() . '</option>';
        }

        $html .= '</select>';
        $html .= '<input type="text" '
            . 'name="items[' . $row->getId() . '][reason_other]" '
            . 'value="' . $this->escapeHtml($row->getReasonOther()) . '" '
            . 'maxlength="255" '
            . 'class="input-text ' . $this->getColumn()->getInlineCss() . '" '
            . 'style="display:none" />';

        return $html;
    }

    /**
     * Renders column as select when it is not editable
     *
     * @param   Varien_Object $row
     * @return  string
     */
    protected function _getNonEditableView(Varien_Object $row)
    {
        /** @var $rmaItemAttribute Enterprise_Rma_Model_Item_Attribute */
        $rmaItemAttribute = Mage::getModel('enterprise_rma/item_form')
            ->setFormCode('default')
            ->getAttribute('reason_other');
        $value = $row->getData($this->getColumn()->getIndex());

        if ($value == 0 && $row->getReasonOther() != '') {
            $html = $rmaItemAttribute && $rmaItemAttribute->getId()
                ? $rmaItemAttribute->getStoreLabel() . ':&nbsp;'
                : '';

            if (strlen($row->getReasonOther()) > 18) {
                $html .= '<a class="item_reason_other">'
                    . $this->escapeHtml(substr($row->getReasonOther() , 0, 15)) . '...'
                    . '</a>';

                $html .= '<input type="hidden" '
                    . 'name="items[' . $row->getId() . '][' . $rmaItemAttribute->getAttributeCode() . ']" '
                    . 'value="' . $this->escapeHtml($row->getReasonOther()) . '" />';
            } else {
                $html .= $this->escapeHtml($row->getReasonOther());
            }
        } else {
            $html = $this->escapeHtml($this->_getValue($row));
        }

        return $html;
    }
}
