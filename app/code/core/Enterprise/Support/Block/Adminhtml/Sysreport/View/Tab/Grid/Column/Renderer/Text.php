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
 * @package     Enterprise_Support
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_Support_Block_Adminhtml_Sysreport_View_Tab_Grid_Column_Renderer_Text
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Data helper object
     *
     * @var Enterprise_Support_Helper_Data
     */
    protected $_dataHelper = null;

    /**
     * Html helper object
     *
     * @var Enterprise_Support_Helper_Html
     */
    protected $_htmlHelper = null;

    /**
     * Instantiate data helper
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_dataHelper = Mage::helper('enterprise_support');
        $this->_htmlHelper = Mage::helper('enterprise_support/html');
    }

    /**
     * Renders grid column
     *
     * @param Varien_Object $row
     * @return mixed
     */
    public function _getValue(Varien_Object $row)
    {
        $rawText = parent::_getValue($row);
        $text = $this->_dataHelper->getReportValueText($rawText);
        return $this->_htmlHelper->getGridCellHtml($text, $rawText);
    }
}
