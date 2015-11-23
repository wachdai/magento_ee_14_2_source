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
 * @category    Tests
 * @package     Tests_Functional
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement as Element;
use Magento\Mtf\Client\ElementInterface;

/**
 * Typified element class for Tree elements.
 */
class TreeElement extends Element
{
    /**
     * Css class for finding tree nodes.
     *
     * @var string
     */
    protected $nodeCssClass = '.x-tree-node > .x-tree-node-ct';

    /**
     * Css class for detecting tree nodes.
     *
     * @var string
     */
    protected $nodeSelector = '.x-tree-node';

    /**
     * Css class for fetching node's name.
     *
     * @var string
     */
    protected $nodeName = 'div > a';

    /**
     * Input check box xpath selector.
     */
    protected $inputCheck = '//li[div/a/span[contains(text(),"%s")] and @class="x-tree-node"]//input';

    /**
     * Root node for element.
     *
     * @var string
     */
    protected $rootNode = '.x-tree-root-node';

    /**
     * All checkboxes.
     *
     * @var string
     */
    protected $checkboxes = 'input';

    /**
     * Selected checkboxes.
     *
     * @var string
     */
    protected $selectedCheckboxes = '//input[@checked=""]/../a/span';

    /**
     * Get structure of the tree element.
     *
     * @return array
     */
    public function getStructure()
    {
        return $this->getNodeContent($this, $this->rootNode);
    }

    /**
     * Clear data for element.
     *
     * @param array $structureChunk
     * @return void
     */
    public function clear($structureChunk)
    {
        foreach ($structureChunk as $elements) {
            $checkboxes = $elements['element']->getElements($this->checkboxes, Locator::SELECTOR_CSS, 'checkbox');
            foreach ($checkboxes as $checkbox) {
                $checkbox->setValue('No');
            }
        }
    }

    /**
     * Get the value.
     *
     * @return array
     */
    public function getValue()
    {
        $this->eventManager->dispatchEvent(['get_value'], [(string)$this->getAbsoluteSelector()]);
        $checkboxes = $this->getElements($this->selectedCheckboxes, Locator::SELECTOR_XPATH);
        $values = [];
        foreach ($checkboxes as $checkbox) {
            $value = $checkbox->getText();
            preg_match('`(\w+) \(.*`', $value, $matches);
            $values[] = $matches[1];
        }
        return $values;
    }

    /**
     * keys method is not accessible in this class.
     * Throws exception if used.
     *
     * @param array $keys
     * @throws \BadMethodCallException
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function keys(array $keys)
    {
        throw new \BadMethodCallException('Not applicable for this class of elements (TreeElement)');
    }

    /**
     * Drag'n'drop method is not accessible in this class.
     * Throws exception if used.
     *
     * @param ElementInterface $target
     * @throws \BadMethodCallException
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function dragAndDrop(ElementInterface $target)
    {
        throw new \BadMethodCallException('Not applicable for this class of elements (TreeElement)');
    }

    /**
     * Click a tree element by its path (Node names) in tree
     *
     * @param string $path
     * @throws \InvalidArgumentException
     * @return void
     */
    public function setValue($path)
    {
        $pathChunkCounter = 0;
        $pathArray = explode('/', $path);
        $pathArrayLength = count($pathArray);
        $structureChunk = $this->getStructure(); //Set the root of a structure as a first structure chunk
        $structureForClear = $structureChunk;
        foreach ($pathArray as $pathChunk) {
            $structureChunk = $this->deep($pathChunk, $structureChunk);
            $structureChunk = ($pathChunkCounter == $pathArrayLength - 1) ?
                $structureChunk['element'] : $structureChunk['subnodes'];
            ++$pathChunkCounter;
        }
        if ($structureChunk) {
            $this->clear($structureForClear);
            $needleElement = $structureChunk->find($this->nodeName);
            $needleElement->click();
        } else {
            throw new \InvalidArgumentException('The path specified for tree is invalid');
        }
    }

    /**
     * Internal function for deeping in hierarchy of the tree structure
     * Return the nested array if it exists or object of class Element if this is the final part of structure
     *
     * @param string $pathChunk
     * @param array $structureChunk
     * @return array|Element||false
     */
    protected function deep($pathChunk, $structureChunk)
    {
        if (is_array($structureChunk)) {
            foreach ($structureChunk as $structureNode) {
                $pattern = '/' . $pathChunk . '\s\([\d]+\)|' . $pathChunk . '/';
                if (isset($structureNode) && preg_match($pattern, $structureNode['name'])) {
                    return $structureNode;
                }
            }
        }
        return false;
    }

    /**
     *  Recursive walks tree
     *
     * @param ElementInterface $node
     * @param string $parentCssClass
     * @return array
     */
    protected function getNodeContent(ElementInterface $node, $parentCssClass)
    {
        $nodeArray = [];
        $nodeList = [];
        $counter = 1;
        $newNode = $node->find($parentCssClass . ' > ' . $this->nodeSelector . ':nth-of-type(' . $counter . ')');
        //Get list of all children nodes to work with
        while ($newNode->isVisible()) {
            $nodeList[] = $newNode;
            ++$counter;
            $newNode = $node->find($parentCssClass . ' > ' . $this->nodeSelector . ':nth-of-type(' . $counter . ')');
        }
        //Write to array values of current node
        foreach ($nodeList as $currentNode) {
            /** @var Element $currentNode */
            $nodesNames = $currentNode->find($this->nodeName);
            $nodesContents = $currentNode->find($this->nodeCssClass);
            $text = ltrim($nodesNames->getText());
            $nodeArray[] = [
                'name' => $text,
                'element' => $currentNode,
                'subnodes' => $nodesContents->isVisible() ?
                    $this->getNodeContent($nodesContents, $this->nodeCssClass) : null
            ];
        }
        return $nodeArray;
    }
}
