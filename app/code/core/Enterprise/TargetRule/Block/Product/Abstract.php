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
 * @package     Enterprise_TargetRule
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * TargetRule abstract Products Block
 *
 * @category   Enterprise
 * @package    Enterprise_TargetRule
 */
abstract class Enterprise_TargetRule_Block_Product_Abstract extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Link collection
     *
     * @var null|Mage_Catalog_Model_Resource_Product_Collection
     */
    protected $_linkCollection = null;

    /**
     * Catalog Product List Item Collection array
     *
     * @var null|array
     */
    protected $_items = null;

    /**
     * Collection of custom selected items
     *
     * @var array
     */
    protected $_customItems = array();

    /**
     * Get link collection for specific target
     *
     * @abstract
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    abstract protected function _getTargetLinkCollection();

    /**
     * Get target rule products
     *
     * @abstract
     * @return array
     */
    abstract protected function _getTargetRuleProducts();

    /**
     * Retrieve Catalog Product List Type identifier
     *
     * @return int
     */
    abstract public function getProductListType();

    /**
     * Retrieve Maximum Number Of Product
     *
     * @return int
     */
    abstract public function getPositionLimit();

    /**
     * Retrieve Position Behavior
     *
     * @return int
     */
    abstract public function getPositionBehavior();

    /**
     * Return the behavior positions applicable to products based on the rule(s)
     *
     * @return array
     */
    public function getRuleBasedBehaviorPositions()
    {
        return array(
            Enterprise_TargetRule_Model_Rule::BOTH_SELECTED_AND_RULE_BASED,
            Enterprise_TargetRule_Model_Rule::RULE_BASED_ONLY,
        );
    }

    /**
     * Retrieve the behavior positions applicable to selected products
     *
     * @return array
     */
    public function getSelectedBehaviorPositions()
    {
        return array(
            Enterprise_TargetRule_Model_Rule::BOTH_SELECTED_AND_RULE_BASED,
            Enterprise_TargetRule_Model_Rule::SELECTED_ONLY,
        );
    }

    /**
     * Retrieve TargetRule data helper
     *
     * @return Enterprise_TargetRule_Helper_Data
     */
    public function getTargetRuleHelper()
    {
        return Mage::helper('enterprise_targetrule');
    }

    /**
     * Get link collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection|null
     */
    public function getLinkCollection()
    {
        if (is_null($this->_linkCollection)) {
            $this->_linkCollection = $this->_getTargetLinkCollection();

            if ($this->_linkCollection) {
                // Perform rotation mode
                $select = $this->_linkCollection->getSelect();
                $rotationMode = $this->getTargetRuleHelper()->getRotationMode($this->getProductListType());
                if ($rotationMode == Enterprise_TargetRule_Model_Rule::ROTATION_SHUFFLE) {
                    Mage::getResourceSingleton('enterprise_targetrule/index')->orderRand($select);
                } else {
                    $select->order('link_attribute_position_int.value ASC');
                }
            }
        }

        return $this->_linkCollection;
    }

    /**
     * Get linked products
     *
     * @return array
     */
    protected function _getLinkProducts()
    {
        $items = array();
        $linkCollection = $this->getLinkCollection();
        if ($linkCollection) {
            foreach ($linkCollection as $item) {
                $items[$item->getEntityId()] = $item;
            }
        }
        return $items;
    }

    /**
     * Whether rotation mode is set to "shuffle"
     *
     * @return bool
     */
    public function isShuffled()
    {
        $rotationMode = $this->getTargetRuleHelper()->getRotationMode($this->getProductListType());
        return $rotationMode == Enterprise_TargetRule_Model_Rule::ROTATION_SHUFFLE;
    }

    /**
     * Order product items
     *
     * @return array|null
     */
    protected function _orderProductItems()
    {
        if (!is_null($this->_items)) {

            foreach ($this->_items as $id => $item) {
                if (!isset($this->_customItems[$id])) {
                    continue;
                }
                unset($this->_items[$id]);
            }

            if ($this->isShuffled()) {
                $targetRuleItems = $this->_shuffleItems($this->_items);
                $this->_items = $this->_shuffleItems($this->_customItems);
                $this->_items += $targetRuleItems;
            } else {
                $this->_items += $this->_customItems;
                uasort($this->_items, array($this, 'compareItems'));
            }
            $this->_sliceItems();
        }
        return $this->_items;
    }

    /**
     * Return shuffled items
     *
     * @param array $items
     * @return array
     */
    protected function _shuffleItems(array $items = array())
    {
        $shuffledItems = array();
        $keys = array_keys($items);
        shuffle($keys);
        foreach ($keys as $id) {
            $shuffledItems[$id] = $items[$id];
        }
        return $shuffledItems;
    }

    /**
     * Compare two items for ordered list
     *
     * @param Varien_Object $item1
     * @param Varien_Object $item2
     * @return int
     */
    public function compareItems($item1, $item2)
    {
        // Prevent rule-based items to have any position
        if (is_null($item2->getPosition()) && !is_null($item1->getPosition())) {
            return -1;
        } elseif (is_null($item1->getPosition()) && !is_null($item2->getPosition())) {
            return 1;
        }
        $positionDiff = (int)$item1->getPosition() - (int)$item2->getPosition();
        if ($positionDiff != 0) {
            return $positionDiff;
        }
        return (int)$item1->getEntityId() - (int)$item2->getEntityId();
    }

    /**
     * Slice items to limit
     *
     * @return Enterprise_TargetRule_Block_Product_Abstract
     */
    protected function _sliceItems()
    {
        if (is_null($this->_items)) {
            return $this;
        }
        $i = 0;
        foreach ($this->_items as $id => $item) {
            ++$i;
            if ($i > $this->getPositionLimit()) {
                unset($this->_items[$id]);
            }
        }

        return $this;
    }

    /**
     * Retrieve Catalog Product List Items
     *
     * @return array
     */
    public function getItemCollection()
    {
        if (is_null($this->_items)) {
            $behavior   = $this->getPositionBehavior();

            $this->_customItems = array();
            $this->_items = array();

            if (in_array($behavior, $this->getRuleBasedBehaviorPositions())) {
                $this->_items = $this->_getTargetRuleProducts();
            }

            if (in_array($behavior, $this->getSelectedBehaviorPositions())) {
                foreach ($this->_getLinkProducts() as $id => $item) {
                    $this->_customItems[$id] = $item;
                }
            }
            $this->_orderProductItems();
        }

        return $this->_items;
    }
}
