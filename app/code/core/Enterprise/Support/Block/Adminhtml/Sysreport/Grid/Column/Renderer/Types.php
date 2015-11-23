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

class Enterprise_Support_Block_Adminhtml_Sysreport_Grid_Column_Renderer_Types
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render system report types row
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $result = array();
        $types = explode(',', $row->getReportTypes());
        $typeToLabelMap = Mage::helper('enterprise_support')->getSysReportTypeToLabelMap();
        foreach ($types as $type) {
            $result[] = isset($typeToLabelMap[$type]) ? $typeToLabelMap[$type] : $type;
        }
        if (sizeof($typeToLabelMap) == sizeof($result)) {
            return '<strong>' . Mage::helper('enterprise_support')->__('All') . '</strong>';
        }
        return $result ? implode(', ', $result) : $row->getReportTypes();
    }
}
