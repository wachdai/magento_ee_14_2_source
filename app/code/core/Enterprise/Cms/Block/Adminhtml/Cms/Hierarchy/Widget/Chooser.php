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
 * Cms Pages Hierarchy Grid Block
 *
 * @method Enterprise_Cms_Block_Adminhtml_Cms_Hierarchy_Widget_Chooser setScope(string $value)
 * @method Enterprise_Cms_Block_Adminhtml_Cms_Hierarchy_Widget_Chooser setScopeId(int $value)
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 */
class Enterprise_Cms_Block_Adminhtml_Cms_Hierarchy_Widget_Chooser extends Mage_Adminhtml_Block_Template
{
    /**
     * Prepare chooser element HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element Form Element
     * @return Varien_Data_Form_Element_Abstract
     */
    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $uniqueId = Mage::helper('core')->uniqHash($element->getId());
        $sourceUrl = $this->getUrl('*/cms_hierarchy_widget/chooser', array('uniq_id' => $uniqueId));

        $chooser = $this->getLayout()->createBlock('widget/adminhtml_widget_chooser')
            ->setElement($element)
            ->setTranslationHelper($this->getTranslationHelper())
            ->setConfig($this->getConfig())
            ->setFieldsetId($this->getFieldsetId())
            ->setSourceUrl($sourceUrl)
            ->setUniqId($uniqueId);


        if ($element->getValue()) {
            $node = Mage::getModel('enterprise_cms/hierarchy_node')->load($element->getValue());
            if ($node->getId()) {
                $chooser->setLabel($node->getLabel());
            }
        }

        $radioHtml = Mage::getBlockSingleton('enterprise_cms/adminhtml_cms_hierarchy_widget_radio')
            ->setUniqId($uniqueId)
            ->toHtml();

        $element->setData('after_element_html', $chooser->toHtml() . $radioHtml);

        return $element;
    }

    /**
     * Return JS+HTML to initialize tree
     *
     * @return string
     */
    public function getTreeHtml()
    {
        $chooserJsObject = $this->getId();
        $html = '
            <div id="tree' . $this->getId() . '" class="cms-tree tree x-tree"></div>
            <script type="text/javascript">

            function clickNode(node) {
                $("tree-container").insert({before: node.text});
                $("' . $this->getId() . '").value = node.id;
                treeRoot.collapse();
            }

            var nodes = ' . $this->getNodesJson() . ';

            if (nodes.length > 0) {
                var tree' . $this->getId() . ' = new Ext.tree.TreePanel("tree' . $this->getId() . '", {
                    animate: false,
                    enableDD: false,
                    containerScroll: true,
                    rootVisible: false,
                    lines: true
                });

                var treeRoot' . $this->getId() . ' = new Ext.tree.AsyncTreeNode({
                    text: \'' . Mage::helper('core')->quoteEscape($this->__("Root")) . '\',
                    id: "root",
                    allowDrop: true,
                    allowDrag: false,
                    expanded: true,
                    cls: "cms_node_root"
                });

                tree' . $this->getId() . '.setRootNode(treeRoot' . $this->getId() . ');

                for (var i = 0; i < nodes.length; i++) {
                    var cls = nodes[i].page_id ? "cms_page" : "cms_node";
                    var node = new Ext.tree.TreeNode({
                        id: nodes[i].node_id,
                        text: nodes[i].label,
                        cls: cls,
                        expanded: nodes[i].page_exists,
                        allowDrop: false,
                        allowDrag: false,
                        page_id: nodes[i].page_id
                    });
                    if (parentNode = tree' . $this->getId() . '.getNodeById(nodes[i].parent_node_id)) {
                        parentNode.appendChild(node);
                    } else {
                        treeRoot' . $this->getId() . '.appendChild(node);
                    }
                }

                tree' . $this->getId() . '.addListener("click", function (node, event) {
                    ' . $chooserJsObject . '.setElementValue(node.id);
                    ' . $chooserJsObject . '.setElementLabel(node.text);
                    ' . $chooserJsObject . '.close();
                });
                tree' . $this->getId() . '.render();
                treeRoot' . $this->getId() . '.expand();
            }
            else {
                $("tree' . $this->getId() . '").innerHTML = \''
            . Mage::helper('core')->quoteEscape($this->__('No Nodes available'))
            . '\';
            }
            </script>
        ';
        return $html;
    }

    /**
     * Retrieve Hierarchy JSON string
     *
     * @return string
     */
    public function getNodesJson()
    {
        return Mage::helper('core')->jsonEncode($this->getNodes());
    }

    /**
     * Prepare hierarchy nodes for tree building
     *
     * @return array
     */
    public function getNodes()
    {
        $nodes = array();
        /** @var $hierarchyNode Enterprise_Cms_Model_Hierarchy_Node */
        $hierarchyNode = Mage::getModel('enterprise_cms/hierarchy_node');
        $hierarchyNode->setScope($this->getScope());
        $hierarchyNode->setScopeId($this->getScopeId());

        $nodeHeritage = $hierarchyNode->getHeritage();
        unset($hierarchyNode);
        return $nodeHeritage->getNodesData();
    }
}
