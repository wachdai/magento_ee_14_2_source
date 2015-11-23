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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Grid column widget for rendering action grid cells
 *
 * @category   Enterprise
 * @package    Enterprise_Staging
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Widget_Grid_Column_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    /**
     * Renders column
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        switch ($this->getColumn()->getLinkType()) {
            case 'url':
                return $this->_renderUrl($row);
                break;
            case 'actions':
            default :
                return $this->_renderActions($row);
                break;
        }
    }

    protected function _renderActions(Varien_Object $row)
    {
        $actions = $this->getColumn()->getActions();
        if ( empty($actions) || !is_array($actions) ) {
            return '&nbsp';
        }

        $renderType = $this->getColumn()->getRenderType();

        if (!$this->getColumn()->getNoLink() && 'link' == $renderType) {
            $out = array();
            foreach ($actions as $action){
                if ( is_array($action) ) {
                    $out[] = $this->_toLinkHtml($action, $row);
                }
            }
            return implode(' | ', $out);
        }

        $out = '<select class="action-select" onchange="varienGridAction.execute(this);">'
             . '<option value=""></option>';
        $i = 0;
        foreach ($actions as $action) {
            if (!$this->_validateAction($row, $action)) {
                continue;
            }
            $i++;
            if ( is_array($action) ) {
                $out .= $this->_toOptionHtml($action, $row);
            }
        }
        $out .= '</select>';
        return $out;
    }

    protected function _renderUrl(Varien_Object $row)
    {
        $href = $row->getData($this->getColumn()->getIndex());
        if ($this->getColumn()->getTitle()) {
            if ($this->getColumn()->getIndex() == $this->getColumn()->getTitle()) {
                $title = $href;
            } else {
                $title = $this->getColumn()->getTitle();
            }
        } else {
            $title = $this->__('click here');
        }

        if ($this->getColumn()->getLength() && strlen($title) > $this->getColumn()->getLength()) {
            $title = substr($title, 0, $this->getColumn()->getLength()) . '...';
        }

        return '<a href="'.$href.'" target="_blank" title="'.$href.'">'.$title.'</a>';
    }

    protected function _validateAction(Varien_Object $row, $action)
    {
        $validate = isset($action['validate']) ? $action['validate'] : false;
        if ($validate) {
            foreach ($validate as $field => $condition) {
                $args = isset($condition['args']) ? $condition['args'] : array();
                if ($field == '__method_callback') {
                    if (isset($condition['method'])) {
                        $method = $condition['method'];
                        if (method_exists($row, $method)) {
                            if (!call_user_func_array(array($row, $method), $args)) {
                                return false;
                            }
                        }
                    }
                } else {
                    $classBaseName = isset($condition['class_base_name']) ? $condition['class_base_name'] : 'NotEmpty';
                    $value = $row->getData($field);
                    if (!Zend_Validate::is($value , $classBaseName, $args)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
