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
 * Cms Hierarchy Context Menu
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 */
class Enterprise_Cms_Block_Hierarchy_Menu extends Mage_Core_Block_Template
{
    const TAG_UL    = 'ul';
    const TAG_OL    = 'ol';
    const TAG_LI    = 'li';

    /**
     * Allowed attributes for UL/OL/LI tags
     *
     * @var array
     */
    protected $_allowedListAttributes = array();

    /**
     * Allowed attributes for A tag
     *
     * @var array
     */
    protected $_allowedLinkAttributes = array();

    /**
     * Allowed attributes for SPAN tag (selected item)
     *
     * @var array
     */
    protected $_allowedSpanAttributes = array();

    /**
     * Total qty nodes in menu
     *
     * @var int
     */
    protected $_totalMenuNodes = 0;

    /**
     * Initialize allowed Tags attributes
     *
     */

    /**
     * Current Hierarchy Node Page Instance
     *
     * @var Enterprise_Cms_Model_Hierarchy_Node
     */
    protected $_node;

    protected function _construct()
    {
        parent::_construct();

        if ($this->getNodeId()) {
            $this->_node = Mage::getModel('enterprise_cms/hierarchy_node')
                ->load($this->getNodeId());
        } else {
            $this->_node = Mage::registry('current_cms_hierarchy_node');
        }

        $this->_loadNodeMenuParams();

        $this->_allowedListAttributes = array('start', 'value', 'compact', // %attrs
            'id', 'class', 'style', 'title', // %coreattrs
            'lang', 'dir', // %i18n
            'onclick', 'ondblclick', 'onmousedown', 'onmouseup', 'onmouseover', 'onmousemove',
            'onmouseout', 'onkeypress', 'onkeydown', 'onkeyup' // %events
        );
        $this->_allowedLinkAttributes = array(
            'charset', 'type', 'name', 'hreflang', 'rel', 'rev', 'accesskey', 'shape',
            'coords', 'tabindex', 'onfocus', 'onblur', // %attrs
            'id', 'class', 'style', 'title', // %coreattrs
            'lang', 'dir', // %i18n
            'onclick', 'ondblclick', 'onmousedown', 'onmouseup', 'onmouseover', 'onmousemove',
            'onmouseout', 'onkeypress', 'onkeydown', 'onkeyup' // %events
        );
        $this->_allowedSpanAttributes = array('id', 'class', 'style', 'title', // %coreattrs
            'lang', 'dir', // %i18n
            'onclick', 'ondblclick', 'onmousedown', 'onmouseup', 'onmouseover', 'onmousemove',
            'onmouseout', 'onkeypress', 'onkeydown', 'onkeyup' // %events
        );
    }

    /**
     * Add context menu params to block data
     *
     * @return Mage_Core_Block_Template
     */
    protected function _loadNodeMenuParams()
    {
        $this->setMenuEnabled(false);

        if ($this->_node instanceof Mage_Core_Model_Abstract) {
            $params = $this->_node->getMetadataContextMenuParams();
            if ($params !== null
                && isset($params['menu_visibility'])
                && $params['menu_visibility'] == 1)
            {
                $this->addData(array(
                    'down'      => isset($params['menu_levels_down']) ? $params['menu_levels_down'] : 0,
                    'ordered'   => isset($params['menu_ordered']) ? $params['menu_ordered'] : '0',
                    'list_type' => isset($params['menu_list_type']) ? $params['menu_list_type'] : '',
                    'menu_brief' => isset($params['menu_brief']) ? $params['menu_brief'] : '0',
                ));

                $this->setMenuEnabled(true);
            }
        }
    }

    /**
     * Return menu_brief flag for menu
     *
     * @return bool
     */
    public function isBrief()
    {
        return (bool)$this->_getData('menu_brief');
    }

    /**
     * Retrieve list container TAG
     *
     * @return string
     */
    public function getListContainer()
    {
        $ordered = 1;
        if ($this->hasData('ordered') && $this->getOrdered() !== '') {
            $ordered = $this->getOrdered();
        }
        return (int)$ordered ? self::TAG_OL : self::TAG_UL;
    }

    /**
     * Retrieve List container type attribute
     *
     * @return string
     */
    public function getListType()
    {
        if ($this->hasData('list_type')) {
            $type = $this->_getData('list_type');
            if ($this->getListContainer() == self::TAG_OL) {
                if (in_array($type, array('1','A','a','I','i'))) {
                    return $type;
                }
            } else if ($this->getListContainer() == self::TAG_UL) {
                if (in_array($type, array('disc', 'circle', 'square'))) {
                    return $type;
                }
            }
        }
        return false;
    }

    /**
     * Retrieve Node Replace pairs
     *
     * @param Enterprise_Cms_Model_Hierarchy_Node $node
     * @return array
     */
    protected function _getNodeReplacePairs($node)
    {
        return array(
            '__ID__'    => $node->getId(),
            '__LABEL__' => $node->getLabel(),
            '__HREF__'  => $node->getUrl()
        );
    }

    /**
     * Retrieve list begin tag
     *
     * @param bool $addStyles Whether to add css styles, type attribute etc. to tag or not
     * @return string
     */
    protected function _getListTagBegin($addStyles = true)
    {
        $templateKey = $addStyles ? '_list_template_styles' : '_list_template';
        $template = $this->_getData($templateKey);

        if (!$template) {
            $template = '<' . $this->getListContainer();


            if ($addStyles) {
                $class = 'cms-menu';

                $type = $this->getListType();
                if ($type) {
                    //$template .= ' type="'.$type.'"';
                    $class .= ' type-'.$type;
                }

                $template .= ' class="'.$class.'"';
            }

            foreach ($this->_allowedListAttributes as $attribute) {
                $value = $this->getData('list_' . $attribute);
                if (!empty($value)) {
                    $template .= ' '.$attribute.'="'.$this->escapeHtml($value).'"';
                }
            }
            if ($this->getData('list_props')) {
                $template .= ' ' . $this->getData('list_props');
            }
            $template .= '>';

            $this->setData($templateKey, $template);
        }

        return $template;
    }

    /**
     * Retrieve List end tag
     *
     * @return string
     */
    protected function _getListTagEnd()
    {
        return '</' . $this->getListContainer() . '>';
    }

    /**
     * Retrieve List Item begin tag
     *
     * @param Enterprise_Cms_Model_Hierarchy_Node $node
     * @param bool $hasChilds Whether item contains nested list or not
     * @return string
     */
    protected function _getItemTagBegin($node, $hasChilds = false)
    {
        $templateKey = $hasChilds ? '_item_template_childs' : '_item_template';
        $template = $this->_getData($templateKey);
        if (!$template) {
            $template = '<' . self::TAG_LI;
            if ($hasChilds) {
                $template .= ' class="parent"';
            }
            foreach ($this->_allowedListAttributes as $attribute) {
                $value = $this->getData('item_' . $attribute);
                if (!empty($value)) {
                    $template .= ' '.$attribute.'="'.$this->escapeHtml($value).'"';
                }
            }
            if ($this->getData('item_props')) {
                $template .= ' ' . $this->getData('item_props');
            }
            $template .= '>';

            $this->setData($templateKey, $template);
        }

        return strtr($template, $this->_getNodeReplacePairs($node));
    }

    /**
     * Retrieve List Item end tag
     *
     * @return string
     */
    protected function _getItemTagEnd()
    {
        return '</' . self::TAG_LI . '>';
    }

    /**
     * Retrieve Node label with link
     *
     * @param Enterprise_Cms_Model_Hierarchy_Node $node
     * @return string
     */
    protected function _getNodeLabel($node)
    {
        if ($this->_node && $this->_node->getId() == $node->getId()) {
            return $this->_getSpan($node);
        }
        return $this->_getLink($node);
    }

    /**
     * Retrieve Node label with link
     *
     * @param Enterprise_Cms_Model_Hierarchy_Node $node
     * @return string
     */
    protected function _getLink($node)
    {
        $template = $this->_getData('_link_template');
        if (!$template) {
            $template = '<a href="__HREF__"';
            foreach ($this->_allowedLinkAttributes as $attribute) {
                $value = $this->getData('link_' . $attribute);
                if (!empty($value)) {
                    $template .= ' '.$attribute.'="'.$this->escapeHtml($value).'"';
                }
            }
            $template .= '><span>__LABEL__</span></a>';
            $this->setData('_link_template', $template);
        }

        return strtr($template, $this->_getNodeReplacePairs($node));
    }

    /**
     * Retrieve Node label for current node
     *
     * @param Enterprise_Cms_Model_Hierarchy_Node $node
     * @return string
     */
    protected function _getSpan($node)
    {
        $template = $this->_getData('_span_template');
        if (!$template) {
            $template = '<strong';
            foreach ($this->_allowedSpanAttributes as $attribute) {
                $value = $this->getData('span_' . $attribute);
                if (!empty($value)) {
                    $template .= ' '.$attribute.'="'.$this->escapeHtml($value).'"';
                }
            }
            $template .= '>__LABEL__</strong>';
            $this->setData('_span_template', $template);
        }

        return strtr($template, $this->_getNodeReplacePairs($node));
    }

    /**
     * Retrieve tree slice array
     *
     * @return array
     */
    public function getTree()
    {
        if (!$this->hasData('_tree')) {
            $up   = $this->_getData('up');
            if (!abs(intval($up))) {
                $up = 0;
            }
            $down = $this->_getData('down');
            if (!abs(intval($down))) {
                $down = 0;
            }

            $tree = $this->_node
                ->setCollectActivePagesOnly(true)
                ->setCollectIncludedPagesOnly(true)
                ->setTreeMaxDepth($down)
                ->setTreeIsBrief($this->isBrief())
                ->getTreeSlice($up, 1);

            $this->setData('_tree', $tree);
        }
        return $this->_getData('_tree');
    }

    /**
     * Return total quantity of rendered menu node
     *
     * @return int
     */
    public function geMenuNodesQty()
    {
        return $this->_totalMenuNodes;
    }

    /**
     * Recursive draw menu
     *
     * @param array $tree
     * @param int $parentNodeId
     * @return string
     */
    public function drawMenu(array $tree, $parentNodeId = 0)
    {
        if (!isset($tree[$parentNodeId])) {
            return '';
        }

        $addStyles = ($parentNodeId == 0);
        $html = $this->_getListTagBegin($addStyles);

        foreach ($tree[$parentNodeId] as $nodeId => $node) {
            /* @var $node Enterprise_Cms_Model_Hierarchy_Node */
            $nested = $this->drawMenu($tree, $nodeId);
            $hasChilds = ($nested != '');
            $html .= $this->_getItemTagBegin($node, $hasChilds) . $this->_getNodeLabel($node);
            $html .= $nested;
            $html .= $this->_getItemTagEnd();

            $this->_totalMenuNodes++;
        }

        $html .= $this->_getListTagEnd();

        return $html;
    }

    protected function _toHtml()
    {
        if (!$this->_node || !$this->getMenuEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
}
