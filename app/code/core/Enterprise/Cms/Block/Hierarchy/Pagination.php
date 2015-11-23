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
 * Cms Widget Pagination Block
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 */
class Enterprise_Cms_Block_Hierarchy_Pagination extends Mage_Core_Block_Template
{
    /**
     * Current Hierarchy Node Page Instance
     *
     * @var Enterprise_Cms_Model_Hierarchy_Node
     */
    protected $_node;

    /**
     * Define default template and settings
     *
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->getNodeId()) {
            $this->_node = Mage::getModel('enterprise_cms/hierarchy_node')
                ->load($this->getNodeId());
        } else {
            $this->_node = Mage::registry('current_cms_hierarchy_node');
        }

        $this->setData('sequence', 1);
        $this->setData('outer', 1);
        $this->setData('frame', 10);
        $this->setData('jump', 0);
        $this->setData('use_node_labels', 0);

        $this->_loadNodePaginationParams();
    }

    /**
     * Add context menu params to block data
     *
     * @return Mage_Core_Block_Template
     */
    protected function _loadNodePaginationParams()
    {
        $this->setPaginationEnabled(false);

        if ($this->_node instanceof Mage_Core_Model_Abstract) {
            $params = $this->_node->getMetadataPagerParams();
            if ($params !== null
                && isset($params['pager_visibility'])
                && $params['pager_visibility'] == Enterprise_Cms_Helper_Hierarchy::METADATA_VISIBILITY_YES)
            {
                $this->addData(array(
                    'jump' => isset($params['pager_jump']) ? $params['pager_jump'] : 0,
                    'frame' => isset($params['pager_frame']) ? $params['pager_frame'] : 0,
                ));

                $this->setPaginationEnabled(true);
            }
        }
    }

    /**
     * Use Node label instead of numeric pages
     *
     * @return bool
     */
    public function getUseNodeLabels()
    {
        return $this->_getData('use_node_labels') > 0;
    }

    /**
     * Can show Previous and Next links
     *
     * @return bool
     */
    public function canShowSequence()
    {
        return $this->_getData('sequence') > 0;
    }

    /**
     * Can show First and Last links
     *
     * @return bool
     */
    public function canShowOuter()
    {
        return $this->getJump() > 0 && $this->_getData('outer') > 0;
    }

    /**
     * Retrieve how many links to pages to show as one frame in the pagination widget.
     *
     * @return int
     */
    public function getFrame()
    {
        return abs(intval($this->_getData('frame')));
    }

    /**
     * Retrieve whether to show link to page number current + y
     * that extends frame size if applicable
     *
     * @return int
     */
    public function getJump()
    {
        return abs(intval($this->_getData('jump')));
    }

    /**
     * Retrieve node label or number
     *
     * @param Enterprise_Cms_Model_Hierarchy_Node $node
     * @param string $custom instead of page number
     * @return string
     */
    public function getNodeLabel(Enterprise_Cms_Model_Hierarchy_Node $node, $custom = null)
    {
        if ($this->getUseNodeLabels()) {
            return $node->getLabel();
        }
        if (!is_null($custom)) {
            return $custom;
        }
        return $node->getPageNumber();
    }

    /**
     * Can show First page
     *
     * @return bool
     */
    public function canShowFirst()
    {
        return $this->getCanShowFirst();
    }

    /**
     * Retrieve First node page
     *
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function getFirstNode()
    {
        return $this->_getData('first_node');
    }

    /**
     * Can show Last page
     *
     * @return bool
     */
    public function canShowLast()
    {
        return $this->getCanShowLast();
    }

    /**
     * Retrieve First node page
     *
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function getLastNode()
    {
        return $this->_getData('last_node');
    }

/**
     * Can show Previous  page link
     *
     * @return bool
     */
    public function canShowPrevious()
    {
        return $this->getPreviousNode() !== null;
    }

    /**
     * Retrieve Previous  node page
     *
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function getPreviousNode()
    {
        return $this->_getData('previous_node');
    }

    /**
     * Can show Next page link
     *
     * @return bool
     */
    public function canShowNext()
    {
        return $this->getNextNode() !== null;
    }

    /**
     * Retrieve Next node page
     *
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function getNextNode()
    {
        return $this->_getData('next_node');
    }

    /**
     * Can show Previous Jump page link
     *
     * @return bool
     */
    public function canShowPreviousJump()
    {
        return $this->getJump() > 0 && $this->getCanShowPreviousJump();
    }

    /**
     * Retrieve Previous Jump node page
     *
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function getPreviousJumpNode()
    {
        return $this->_getData('previous_jump');
    }

    /**
     * Can show Next Jump page link
     *
     * @return bool
     */
    public function canShowNextJump()
    {
        return $this->getJump() > 0 && $this->getCanShowNextJump();
    }

    /**
     * Retrieve Next Jump node page
     *
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function getNextJumpNode()
    {
        return $this->_getData('next_jump');
    }

    /**
     * Is Show Previous and Next links
     *
     * @return bool
     */
    public function isShowOutermost()
    {
        return $this->_getData('outermost') > 1;
    }

    /**
     * Retrieve Nodes collection array
     *
     * @return array
     */
    public function getNodes()
    {
        if (!$this->hasData('_nodes')) {

            // initialize nodes
            $nodes    = $this->_node
                ->setCollectActivePagesOnly(true)
                ->getParentNodeChildren();

            $flags    = array(
                'previous' => false,
                'next'     => false
            );
            $count    = count($nodes);
            $previous = null;
            $next     = null;
            $first    = null;
            $last     = null;
            $current  = 0;
            foreach ($nodes as $k => $node) {
                $node->setPageNumber($k + 1);
                $node->setIsCurrent(false);
                if (is_null($first)) {
                    $first = $node;
                }
                if ($flags['next']) {
                    $next = $node;
                    $flags['next'] = false;
                }
                if ($node->getId() == $this->_node->getId()) {
                    $flags['next'] = true;
                    $flags['previous'] = true;
                    $current = $k;
                    $node->setIsCurrent(true);
                }
                if (!$flags['previous']) {
                    $previous = $node;
                }
                $last = $node;
            }

            $this->setPreviousNode($previous);
            $this->setFirstNode($first);
            $this->setLastNode($last);
            $this->setNextNode($next);
            $this->setCanShowNext($next !== null);

            // calculate pages frame range
            if ($this->getFrame() > 0) {
                $middleFrame = ceil($this->getFrame() / 2);

                if ($count > $this->getFrame() && $current < $middleFrame) {
                    $start = 0;
                } else {
                    $start = $current - $middleFrame + 1;
                    if (($start + 1 + $this->getFrame()) > $count) {
                        $start = $count - $this->getFrame();
                    }
                }
                if ($start > 0) {
                    $this->setCanShowFirst(true);
                } else {
                    $this->setCanShowFirst(false);
                }
                $end = $start + $this->getFrame();
                if ($end < $count) {
                    $this->setCanShowLast(true);
                } else {
                    $this->setCanShowLast(false);
                }
            } else {
                $this->setCanShowFirst(false);
                $this->setCanShowLast(false);
                $start = 0;
                $end   = $count;
            }

            $this->setCanShowPreviousJump(false);
            $this->setCanShowNextJump(false);
            if ($start > 1) {
                $this->setCanShowPreviousJump(true);
                if ($start - 1 > $this->getJump() * 2) {
                    $jump = $start - $this->getJump();
                } else {
                    $jump = ceil(($start - 1) / 2);
                }
                $this->setPreviousJump($nodes[$jump]);
            }
            if ($count - 1 > $end) {
                $this->setCanShowNextJump(true);
                $difference = $count - $end - 1;
                if ($difference < ($this->getJump() * 2)) {
                    $jump = $end + ceil($difference / 2) - 1;
                } else {
                    $jump = $end + $this->getJump() - 1;
                }
                $this->setNextJump($nodes[$jump]);
            }

            $this->setRangeStart($start);
            $this->setRangeEnd($end);

            $this->setData('_nodes', $nodes);
        }
        return $this->_getData('_nodes');
    }

    /**
     * Retrieve nodes in range
     *
     * @return array
     */
    public function getNodesInRange()
    {
        $range = array();
        $nodes = $this->getNodes();
        foreach ($nodes as $k => $node) {
            if ($k >= $this->getRangeStart() && $k < $this->getRangeEnd()) {
                $range[] = $node;
            }
        }
        return $range;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_node || !$this->getPaginationEnabled()) {
            return '';
        }

        // collect nodes to output pagination in template
        $nodes = $this->getNodes();

        // don't display pagination with one page
        if (count($nodes) <= 1) {
            return '';
        }

        return parent::_toHtml();
    }
}
