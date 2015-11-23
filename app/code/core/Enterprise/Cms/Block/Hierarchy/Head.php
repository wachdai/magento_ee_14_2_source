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
 * Cms Hierarchy Head Block
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 */
class Enterprise_Cms_Block_Hierarchy_Head extends Mage_Core_Block_Abstract
{
    /**
     * Prepare Global Layout
     *
     * @return Enterprise_Cms_Block_Hieararchy_Head
     */
    protected function _prepareLayout()
    {
        /* @var $node Enterprise_Cms_Model_Hierarchy_Node */
        $node      = Mage::registry('current_cms_hierarchy_node');
        /* @var $head Mage_Page_Block_Html_Head */
        $head      = $this->getLayout()->getBlock('head');

        if (Mage::helper('enterprise_cms/hierarchy')->isMetadataEnabled() && $node && $head) {
            $treeMetaData = $node->getTreeMetaData();
            if (is_array($treeMetaData)) {
                /* @var $linkNode Enterprise_Cms_Model_Hierarchy_Node */

                if ($treeMetaData['meta_cs_enabled']) {
                    $linkNode = $node->getMetaNodeByType(Enterprise_Cms_Model_Hierarchy_Node::META_NODE_TYPE_CHAPTER);
                    if ($linkNode->getId()) {
                        $head->addLinkRel(Enterprise_Cms_Model_Hierarchy_Node::META_NODE_TYPE_CHAPTER, $linkNode->getUrl());
                    }

                    $linkNode = $node->getMetaNodeByType(Enterprise_Cms_Model_Hierarchy_Node::META_NODE_TYPE_SECTION);
                    if ($linkNode->getId()) {
                        $head->addLinkRel(Enterprise_Cms_Model_Hierarchy_Node::META_NODE_TYPE_SECTION, $linkNode->getUrl());
                    }
                }

                if ($treeMetaData['meta_next_previous']) {
                    $linkNode = $node->getMetaNodeByType(Enterprise_Cms_Model_Hierarchy_Node::META_NODE_TYPE_NEXT);
                    if ($linkNode->getId()) {
                        $head->addLinkRel(Enterprise_Cms_Model_Hierarchy_Node::META_NODE_TYPE_NEXT, $linkNode->getUrl());
                    }

                    $linkNode = $node->getMetaNodeByType(Enterprise_Cms_Model_Hierarchy_Node::META_NODE_TYPE_PREVIOUS);
                    if ($linkNode->getId()) {
                        $head->addLinkRel(Enterprise_Cms_Model_Hierarchy_Node::META_NODE_TYPE_PREVIOUS, $linkNode->getUrl());
                    }
                }

                if ($treeMetaData['meta_first_last']) {
                    $linkNode = $node->getMetaNodeByType(Enterprise_Cms_Model_Hierarchy_Node::META_NODE_TYPE_FIRST);
                    if ($linkNode->getId()) {
                        $head->addLinkRel(Enterprise_Cms_Model_Hierarchy_Node::META_NODE_TYPE_FIRST, $linkNode->getUrl());
                    }
                }
            }
        }

        return parent::_prepareLayout();
    }
}
