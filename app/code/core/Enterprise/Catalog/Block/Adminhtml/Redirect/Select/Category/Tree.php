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
 * @package     Enterprise_Catalog
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * UrlRedirects category tree select
 *
 * @category   Enterprise
 * @package    Enterprise_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Block_Adminhtml_Redirect_Select_Category_Tree
    extends Mage_Adminhtml_Block_Catalog_Category_Abstract
{
    /**
     * List of allowed category ids
     *
     * @var array|null
     */
    protected $_allowedCategoryIds = null;

    /**
     * Product Id
     * Product Id must be false by default because it should be equal NULL if it is not set
     *
     * @var int|bool
     */
    protected $_productId = false;

    /**
     * Retrieve product id
     *
     * @return int
     */
    public function getProductId()
    {
        if (false === $this->_productId) {
            //set NULL on empty for skip adding to URL
            $this->_productId = $this->getRequest()->getParam('product', null);
        }
        return $this->_productId;
    }

    /**
     * Output in JSON
     *
     * @return array|string
     */
    public function outputInJson()
    {
        return $this->getTreeArray($this->getRequest()->getParam('id'), true, 1);
    }

    /**
     * Get categories tree as recursive array
     *
     * @param int $parentId
     * @param bool $asJson
     * @param int $recursionLevel
     * @return array
     */
    public function getTreeArray($parentId = null, $asJson = false, $recursionLevel = 3)
    {
        $productId = $this->getProductId();
        if ($productId) {
            $product = Mage::getModel('catalog/product')->setId($productId);
            $this->_allowedCategoryIds = $product->getCategoryIds();
            unset($product);
        }

        $result = array();
        if ($parentId) {
            $category = Mage::getModel('catalog/category')->load($parentId);
            if (!empty($category)) {
                $tree = $this->_getNodesArray($this->getNode($category, $recursionLevel));
                if (!empty($tree) && !empty($tree['children'])) {
                    $result = $tree['children'];
                }
            }
        } else {
            $result = $this->_getNodesArray($this->getRoot(null, $recursionLevel));
        }

        if ($asJson) {
            /* @var $helper Mage_Core_Helper_Data */
            $helper = Mage::helper('core');
            return $helper->jsonEncode($result);
        }

        $this->_allowedCategoryIds = null;

        return $result;
    }

    /**
     * Get categories collection
     *
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getCategoryCollection()
    {
        $collection = $this->_getData('category_collection');
        if (null === $collection) {
            $collection = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect(array('name', 'is_active'))
                ->setLoadProductCount(true);
            $this->setData('category_collection', $collection);
        }

        return $collection;
    }

    /**
     * Convert categories tree to array recursively
     *
     * @param  Varien_Data_Tree_Node $node
     * @return array
     */
    protected function _getNodesArray($node)
    {
        $result = array(
            'id'             => (int)$node->getId(),
            'parent_id'      => (int)$node->getParentId(),
            'children_count' => (int)$node->getChildrenCount(),
            'is_active'      => (bool)$node->getIsActive(),
            'name'           => $node->getName(),
            'href'           => $this->getCategoryEditUrl($node),
            'level'          => (int)$node->getLevel(),
            'product_count'  => (int)$node->getProductCount()
        );

        if (is_array($this->_allowedCategoryIds) && !in_array($result['id'], $this->_allowedCategoryIds)) {
            $result['disabled'] = true;
        }

        if ($node->hasChildren()) {
            $result['children'] = array();
            foreach ($node->getChildren() as $childNode) {
                $result['children'][] = $this->_getNodesArray($childNode);
            }
        }
        $result['cls']      = ($result['is_active'] ? '' : 'no-') . 'active-category';
        $result['expanded'] = !empty($result['children']);

        return $result;
    }

    /**
     * Get URL to edit page with category ID
     *
     * @param Varien_Data_Tree_Node $node
     * @return string
     */
    public function getCategoryEditUrl($node)
    {
        return $this->getUrl(
            '*/*/edit',
            array('category_id' => $node->getId(), 'product' => $this->getProductId(), 'type' => 'category')
        );
    }

    /**
     * Get URL for categories tree ajax loader
     *
     * @return string
     */
    public function getLoadTreeUrl()
    {
        return $this->getUrl('*/*/categoriesJson');
    }

    public function getSkipCategoryButton()
    {
        $block = $this->getLayout()->createBlock('adminhtml/widget_button');
        $url = $this->getUrl('*/*/edit', array('_current' => true, 'type' => 'product'));
        $block->setData(array(
            'id'           => 'skip_category',
            'element_name' => 'skip_category',
            'label'        => $this->__('Skip Category Selection'),
            'on_click'     => sprintf("window.location.href = '%s';", $url)
        ));

        return $block->toHtml();
    }
}
