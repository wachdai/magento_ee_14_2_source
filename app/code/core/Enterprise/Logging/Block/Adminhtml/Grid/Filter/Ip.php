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
 * @package     Enterprise_Logging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Ip-address grid filter
 * @deprecated since EE 1.14.2.0. See Replaced with Enterprise_Logging_Block_Adminhtml_Index_Grid::_ipFilterCallback
 */
class Enterprise_Logging_Block_Adminhtml_Grid_Filter_Ip extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    /**
     * Collection condition filter getter
     *
     * @return array
     */
    public function getCondition()
    {
        $value = $this->getValue();
        if (preg_match('/^(\d+\.){3}\d+$/', $value)
            || preg_match('/^(([0-9a-f]{1,4})?(:([0-9a-f]{1,4})?){1,}:([0-9a-f]{1,4}))(%[0-9a-z]+)?$/i', $value)
        ) {
            return inet_pton($value);
        }
        $expr = Mage::getResourceHelper('enterprise_logging')->getInetNtoaExpr();
        return array('field_expr' => $expr, 'like' => "%{$this->_escapeValue($value)}%");
    }
}
