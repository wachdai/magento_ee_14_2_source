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
 * Difference columns renderer
 *
 */
class Enterprise_Logging_Block_Adminhtml_Grid_Renderer_Details
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render the grid cell value
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $html = '-';
        $columnData = $row->getData($this->getColumn()->getIndex());
        try {
            $dataArray = unserialize($columnData);
            if (is_bool($dataArray)) {
                $html = $dataArray ? 'true' : 'false';
            }
            elseif (is_array($dataArray)) {
                if (isset($dataArray['general'])) {
                    if (!is_array($dataArray['general'])) {
                        $dataArray['general'] = array($dataArray['general']);
                    }
                    $html = $this->escapeHtml(implode(', ', $dataArray['general']));
                }
                /**
                 *  [additional] => Array
                 *          (
                 *               [Mage_Sales_Model_Order] => Array
                 *                  (
                 *                      [68] => Array
                 *                          (
                 *                              [increment_id] => 100000108,
                 *                              [grand_total] => 422.01
                 *                          )
                 *                      [94] => Array
                 *                          (
                 *                              [increment_id] => 100000121,
                 *                              [grand_total] => 492.77
                 *                          )
                 *
                 *                  )
                 *
                 *          )
                 */
                if (isset($dataArray['additional'])) {
                    $html .= '<br /><br />';
                    foreach ($dataArray['additional'] as $modelName => $modelsData) {
                        foreach ($modelsData as $mdoelId => $data) {
                            $html .= $this->escapeHtml(implode(', ', $data));
                        }
                    }
                }
            } else {
                $html = $columnData;
            }
        } catch (Exception $e) {
            $html = $columnData;
        }
        return $html;
    }
}
