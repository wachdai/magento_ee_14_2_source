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
 * @category    OnTap
 * @package     OnTap_Merchandiser
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class OnTap_Merchandiser_Model_Search extends Mage_CatalogSearch_Model_Advanced
{
    /**
     * prepareProductCollection
     *
     * @param mixed $collection Varien_Data_Collection
     * @return object
     */
    public function prepareProductCollection($collection)
    {
        $collection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->setStore(Mage::app()->getStore())
            ->addStoreFilter();
        return $this;
    }

    /**
     * Retrieve array of attributes used in advanced search
     *
     * @return array
     */
    public function getAttributes()
    {
        /* @var $attributes Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection */
        $attributes = $this->getData('attributes');
        if (is_null($attributes)) {
            $product = Mage::getModel('catalog/product');
            $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($product->getResource()->getTypeId())
                ->addHasOptionsFilter()
                ->setCodeFilter(array('name', 'sku', 'visibility'))
                ->setOrder('main_table.attribute_id', 'asc')
                ->load();
            foreach ($attributes as $attribute) {
                $attribute->setEntity($product->getResource());
            }
            $this->setData('attributes', $attributes);
        }
        return $attributes;
    }

    /**
     * Prepare search condition for attribute
     *
     * @param mixed Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param mixed $value
     * @return array
     */
    protected function _prepareCondition($attribute, $value)
    {
        $condition = false;

        if (is_array($value)) {
            if (!empty($value['from']) || !empty($value['to'])) { // range
                $condition = $value;
            } else if ($attribute->getBackendType() == 'varchar') { // multiselect
                $condition = array('in_set' => $value);
            } else if (!isset($value['from']) && !isset($value['to'])) { // select
                $condition = array('in' => $value);
            }
        } else {
            if (strlen($value) > 0) {
                if (in_array($attribute->getBackendType(), array('varchar', 'text', 'static'))) {
                    $condition = array('like' => '%' . $value . '%'); // text search
                } else {
                    $condition = $value;
                }
            }
        }

        return $condition;
    }

    /**
     * Add advanced search filters to product collection
     *
     * @param   array $values
     * @return  object
     */
    public function addFilters($values)
    {
        $attributes     = $this->getAttributes();
        $hasConditions  = false;
        $allConditions  = array();

        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            if (!isset($values[$attribute->getAttributeCode()])) {
                continue;
            }
            $value = $values[$attribute->getAttributeCode()];
            $condition = $this->_prepareCondition($attribute, $value);
            if ($condition === false) {
                continue;
            }
            $this->_addSearchCriteria($attribute, $value);

            $table = $attribute->getBackend()->getTable();
            if ($attribute->getBackendType() == 'static') {
                $attributeId = $attribute->getAttributeCode();
            } else {
                $attributeId = $attribute->getId();
            }
            $allConditions[$table][$attributeId] = $condition;
            if ('visibility' == $attribute->getAttributeCode() && 1 < count($allConditions)) {
                $allConditions[$table][$attributeId]['useJoin'] = true;
            };
        }

        if ($allConditions) {
            $this->addFieldsToFilter($allConditions);
        } else if (!$hasConditions) {
            Mage::throwException(Mage::helper('catalogsearch')->__('Please specify at least one search term.'));
        }

        return $this;
    }

    /**
     * addFieldsToFilter
     *
     * @param mixed $fields
     * @return void
     */
    public function addFieldsToFilter($fields)
    {
        if ($fields) {
            $collection = $this->getProductCollection();
            $select = $collection->getSelect()
                ->distinct(true);
            $vCount = 1;
            foreach ($fields as $table => $conditions) {
                if ('catalog_product_entity' == $table) {
                    foreach ($conditions as $attrId => $condValue) {
                        $relation = array_keys($condValue);
                        $relation = $relation[0];
                        $value = array_values($condValue);
                        $value = $value[0];
                        $select->orWhere("{$attrId} {$relation} ?", $value);
                    }
                } else {
                    $alias = 'table'.$vCount++;
                    $conditionString = null;
                    foreach ($conditions as $attrId => $condValue) {
                        $useJoin = false;
                        if (isset($condValue['useJoin'])) {
                            $useJoin = $condValue['useJoin'];
                            unset($condValue['useJoin']);
                        }
                        $relation = array_keys($condValue);
                        $relation = $relation[0];
                        $value = array_values($condValue);
                        $value = $value[0];
                        if ($useJoin) {
                            if (is_array($value)) {
                                $value = "'".implode("', '", $value)."'";
                            }
                            $conditionString = " AND {$alias}.attribute_id={$attrId}";
                            $conditionString.= " AND {$alias}.value {$relation} ({$value})";
                        } else {
                            $whereAttrId = "{$alias}.attribute_id={$attrId}";
                            $whereValue = "{$alias}.value {$relation} (?)";
                            $select->orWhere("{$whereAttrId} AND {$whereValue}", $value);
                        }
                    }
                    $select->joinInner(array($alias => $table),
                        $alias.'.entity_id = e.entity_id'.$conditionString, null);
                }
            }
        }
    }

    /**
     * addCategoryFilter
     *
     * @param mixed $catId
     * @return object
     */
    public function addCategoryFilter($catId)
    {
        if (is_numeric($catId)) {
            $collection = $this->getProductCollection()
                ->setStore(Mage::app()->getStore())
                ->addStoreFilter();
            $select = $collection->getSelect()/*->distinct(true)*/;

            $catalogCategoryProduct = Mage::getSingleton('core/resource')->getTableName('catalog_category_product');

            $select->joinInner(array('cp' => $catalogCategoryProduct), 'cp.product_id = e.entity_id', null);
            $select->where("cp.category_id = ?", $catId);
            $select->order('cp.position');
        }
        return $this;
    }

    /**
     * Retrieve advanced search product collection
     *
     * @return Mage_CatalogSearch_Model_Mysql4_Advanced_Collection
     */
    public function getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $this->_productCollection = Mage::getResourceModel('catalogsearch/advanced_collection')
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addStoreFilter()
                ->setOrder('cp.position');
        }
        return $this->_productCollection;
    }
}
