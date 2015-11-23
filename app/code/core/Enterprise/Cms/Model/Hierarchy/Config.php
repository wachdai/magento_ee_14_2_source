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
 * @package     Enterprise_Cms
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Cms Hierarchy Model for config processing
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 */
class Enterprise_Cms_Model_Hierarchy_Config
{
    const XML_PATH_CONTEXT_MENU_LAYOUTS = 'global/enterprise_cms/hierarchy/menu/layouts';

    /**
     * Menu layouts configuration
     * @var array
     */
    protected $_contextMenuLayouts = null;

    /**
     * Defalt code for menu layouts
     * @var string
     */
    protected $_defaultMenuLayoutCode;

    /**
     * Initialization for $_contextMenuLayouts
     *
     * @return Enterprise_Cms_Model_Hierarchy_Config
     */
    protected function _initContextMenuLayouts()
    {
        $config = Mage::getConfig()->getNode(self::XML_PATH_CONTEXT_MENU_LAYOUTS);
        if ($this->_contextMenuLayouts !== null || !$config) {
            return $this;
        }
        if (!is_array($this->_contextMenuLayouts)) {
            $this->_contextMenuLayouts = array();
        }
        foreach ($config->children() as $layoutCode => $layoutConfig) {
            $this->_contextMenuLayouts[$layoutCode] = new Varien_Object(array(
                'label'                 => Mage::helper('enterprise_cms')->__((string)$layoutConfig->label),
                'code'                  => $layoutCode,
                'layout_handle'         => (string)$layoutConfig->layout_handle,
                'is_default'            => (int)$layoutConfig->is_default,
                'page_layout_handles'   => (array)$layoutConfig->page_layout_handles,
            ));
            if ((bool)$layoutConfig->is_default) {
                $this->_defaultMenuLayoutCode = $layoutCode;
            }
        }
        return $this;
    }

    /**
     * Return available Context Menu layouts output
     *
     * @return array
     */
    public function getContextMenuLayouts()
    {
        $this->_initContextMenuLayouts();
        return $this->_contextMenuLayouts;
    }

    /**
     * Return Context Menu layout by its code
     *
     * @param string $layoutCode
     * @return Varien_Object|boolean
     */
    public function getContextMenuLayout($layoutCode)
    {
        $this->_initContextMenuLayouts();
        return isset($this->_contextMenuLayouts[$layoutCode]) ? $this->_contextMenuLayouts[$layoutCode] : false;
    }

    /**
     * Getter for $_defaultMenuLayoutCode
     *
     * @return string
     */
    public function getDefaultMenuLayoutCode()
    {
        $this->_initContextMenuLayouts();
        return $this->_defaultMenuLayoutCode;
    }
}
