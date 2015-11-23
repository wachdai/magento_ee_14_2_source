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
 * Grid column widget for rendering cells, which can be of text or select type
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_Items_Grid_Column_Renderer_Textinput
    extends Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_Items_Grid_Column_Renderer_Abstract
{
    /**
     * Renders quantity as integer
     *
     * @param Varien_Object $row
     * @return int|string
     */
    public function _getValue(Varien_Object $row)
    {
        $quantity = parent::_getValue($row);
        if (!$row->getIsQtyDecimal()) {
            $quantity = intval($quantity);
        }
        return $quantity;
    }

    /**
     * Renders column as input when it is editable
     *
     * @param   Varien_Object $row
     * @return  string
     */
    protected function _getEditableView(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        if (!$row->getIsQtyDecimal() && !is_null($value)) {
            $value = intval($value);
        }
        $class = 'input-text ' . $this->getColumn()->getValidateClass();
        $html = '<input type="text" ';
        $html .= 'name="items[' . $row->getId() . '][' . $this->getColumn()->getId() . ']" ';
        $html .= 'value="' . $value . '" ';
        if ($this->getStatusManager()->getAttributeIsDisabled($this->getColumn()->getId())) {
            $html .= ' disabled="disabled" ';
            $class .= ' disabled ';
        }
        $html .= 'class="' . $class . '" />';
        return $html;
    }
}
