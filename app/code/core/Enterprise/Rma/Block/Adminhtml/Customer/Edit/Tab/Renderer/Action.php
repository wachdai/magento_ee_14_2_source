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
 * Adminhtml customer orders grid action column item renderer
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Enterprise_Rma_Block_Adminhtml_Customer_Edit_Tab_Renderer_Action
    extends Mage_Adminhtml_Block_Sales_Reorder_Renderer_Action
{
    /**
     * Render field HRML for column
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $actions = array();
        if ($row->getIsReturnable()) {
            $actions[] = array(
                    '@' =>  array('href' => $this->getUrl('*/rma/new', array('order_id'=>$row->getId()))),
                    '#' =>  Mage::helper('enterprise_rma')->__('Return')
            );
        }
        $link1 = parent::render($row);
        $link2 = $this->_actionsToHtml($actions);
        $separator = $link1 && $link2 ? '<span class="separator">|</span>':'';
        return  $link1 . $separator . $link2;
    }
}
