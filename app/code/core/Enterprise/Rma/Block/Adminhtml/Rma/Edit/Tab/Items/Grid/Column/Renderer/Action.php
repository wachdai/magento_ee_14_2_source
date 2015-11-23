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
class Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_Items_Grid_Column_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    /**
     * Renders column
     *
     * Shows link in one row instead of select element in parent class
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $actions = $this->getColumn()->getActions();
        if ( empty($actions) || !is_array($actions) ) {
            return '&nbsp;';
        }

        $out = '<input type="hidden" id="h' . $row->getId() . '" name="h' . $row->getId() . '" value="' . $row->getId()
            . '" class="rowId" />';
        $out .= '<input type="hidden" name="items[' . $row->getId() . '][order_item_id]" value="'
            . $row->getOrderItemId() . '" />';
        $separator = '';
        foreach ($actions as $action) {
            if (!(isset($action['status_depended'])
                && (($row->getStatus() === Enterprise_Rma_Model_Rma_Source_Status::STATE_APPROVED)
                    ||($row->getStatus() === Enterprise_Rma_Model_Rma_Source_Status::STATE_DENIED)
                    ||($row->getStatus() === Enterprise_Rma_Model_Rma_Source_Status::STATE_REJECTED)))) {
                $out .= $separator . $this->_toLinkHtml($action, $row);
                $separator = '<span class="separator">|</span>';
            }
        }
        return $out;
    }
}
