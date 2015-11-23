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
 * TargetRule Action Product Attributes Condition Model
 *
 * @category Enterprise
 * @package  Enterprise_TargetRule
 * @author   Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_TargetRule_Model_Actions_Condition_Product_Attributes
    extends Enterprise_TargetRule_Model_Rule_Condition_Product_Attributes
{
    /**
     * Value type values: constant
     */
    const VALUE_TYPE_CONSTANT = 'constant';

    /**
     * Value type values: same_as
     */
    const VALUE_TYPE_SAME_AS  = 'same_as';

    /**
     * Value type values: child_of
     */
    const VALUE_TYPE_CHILD_OF       = 'child_of';

    /**
     * Define action type and default value
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_targetrule/actions_condition_product_attributes');
        $this->setValue(null);
        $this->setValueType(self::VALUE_TYPE_SAME_AS);
    }

    /**
     * Add special action product attributes
     *
     * @param array $attributes
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['type_id'] = Mage::helper('catalogrule')->__('Type');
    }

    /**
     * Retrieve value by option
     * Rewrite for Retrieve options by Product Type attribute
     *
     * @param mixed $option
     * @return string
     */
    public function getValueOption($option = null)
    {
        if (!$this->getData('value_option') && $this->getAttribute() == 'type_id') {
            $options = Mage::getSingleton('catalog/product_type')->getAllOption();
            $this->setData('value_option', $options);
        }
        return parent::getValueOption($option);
    }

    /**
     * Retrieve select option values
     * Rewrite Rewrite for Retrieve options by Product Type attribute
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        if (!$this->getData('value_select_options') && $this->getAttribute() == 'type_id') {
            $options = Mage::getSingleton('catalog/product_type')->getAllOptions();
            $this->setData('value_select_options', $options);
        }
        return parent::getValueSelectOptions();
    }

    /**
     * Retrieve input type
     * Rewrite for define input type for Product Type attribute
     *
     * @return string
     */
    public function getInputType()
    {
        $attributeCode = $this->getAttribute();
        if ($attributeCode == 'type_id') {
            return 'select';
        }
        return parent::getInputType();
    }

    /**
     * Retrieve value element type
     * Rewrite for define value element type for Product Type attribute
     *
     * @return string
     */
    public function getValueElementType()
    {
        $attributeCode = $this->getAttribute();
        if ($attributeCode == 'type_id') {
            return 'select';
        }
        return parent::getValueElementType();
    }

    /**
     * Retrieve model content as HTML
     * Rewrite for add value type chooser
     *
     * @return string
     */
    public function asHtml()
    {
        return Mage::helper('enterprise_targetrule')->__('Product') . ' '
            . $this->getTypeElementHtml() . $this->getAttributeElementHtml()
            . $this->getOperatorElementHtml() . $this->getValueTypeElementHtml() . $this->getValueElementHtml()
            . $this->getRemoveLinkHtml() . $this->getChooserContainerHtml();
    }

    /**
     * Returns options for value type select box
     *
     * @return array
     */
    public function getValueTypeOptions()
    {
        $options = array(
            array(
                'value' => self::VALUE_TYPE_CONSTANT,
                'label' => Mage::helper('enterprise_targetrule')->__('Constant Value')
            )
        );

        if ($this->getAttribute() == 'category_ids') {
            $options[] = array(
                'value' => self::VALUE_TYPE_SAME_AS,
                'label' => Mage::helper('enterprise_targetrule')->__('the Same as Matched Product Categories')
            );
            $options[] = array(
                'value' => self::VALUE_TYPE_CHILD_OF,
                'label' => Mage::helper('enterprise_targetrule')->__('the Child of the Matched Product Categories')
            );
        } else {
            $options[] = array(
                'value' => self::VALUE_TYPE_SAME_AS,
                'label' => Mage::helper('enterprise_targetrule')->__('Matched Product %s', $this->getAttributeName())
            );
        }

        return $options;
    }

    /**
     * Retrieve Value Type display name
     *
     * @return string
     */
    public function getValueTypeName()
    {
        $options = $this->getValueTypeOptions();
        foreach ($options as $option) {
            if ($option['value'] == $this->getValueType()) {
                return $option['label'];
            }
        }
        return '...';
    }

    /**
     * Retrieve Value Type Select Element
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getValueTypeElement()
    {
        $elementId  = $this->getPrefix().'__'.$this->getId().'__value_type';
        $element    = $this->getForm()->addField($elementId, 'select', array(
            'name'          => 'rule['.$this->getPrefix().']['.$this->getId().'][value_type]',
            'values'        => $this->getValueTypeOptions(),
            'value'         => $this->getValueType(),
            'value_name'    => $this->getValueTypeName(),
            'class'         => 'value-type-chooser',
        ))->setRenderer(Mage::getBlockSingleton('rule/editable'));
        return $element;
    }

    /**
     * Retrieve value type element HTML code
     *
     * @return string
     */
    public function getValueTypeElementHtml()
    {
        $element = $this->getValueTypeElement();
        return $element->getHtml();
    }

    /**
     * Load attribute property from array
     *
     * @param array $array
     * @return Enterprise_TargetRule_Model_Actions_Condition_Product_Attributes
     */
    public function loadArray($array)
    {
        parent::loadArray($array);

        if (isset($array['value_type'])) {
            $this->setValueType($array['value_type']);
        }
        return $this;
    }

    /**
     * Retrieve condition data as array
     *
     * @param array $arrAttributes
     * @return array
     */
    public function asArray(array $arrAttributes = array())
    {
        $array = parent::asArray($arrAttributes);
        $array['value_type'] = $this->getValueType();
        return $array;
    }

    /**
     * Retrieve condition data as string
     *
     * @param string $format
     * @return string
     */
    public function asString($format = '')
    {
        if (!$format) {
            $format = ' %s %s %s %s';
        }
        return sprintf(Mage::helper('enterprise_targetrule')->__('Target Product ') . $format,
           $this->getAttributeName(),
           $this->getOperatorName(),
           $this->getValueTypeName(),
           $this->getValueName()
        );
    }

    /**
     * Retrieve SELECT WHERE condition for product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param Enterprise_TargetRule_Model_Index                         $object
     * @param array                                                     $bind
     * @return Zend_Db_Expr
     */
    public function getConditionForCollection($collection, $object, &$bind)
    {
        /* @var $resource Enterprise_TargetRule_Model_Mysql4_Index */
        $attributeCode  = $this->getAttribute();
        $valueType      = $this->getValueType();
        $operator       = $this->getOperator();
        $resource       = $object->getResource();

        if ($attributeCode == 'category_ids') {
            $select = $object->select()
                ->from($resource->getTable('catalog/category_product'), 'COUNT(*)')
                ->where('product_id=e.entity_id');
            if ($valueType == self::VALUE_TYPE_SAME_AS) {
                $operator = ('!{}' == $operator) ? '!()' : '()';
                $where = $resource->getOperatorBindCondition('category_id', 'category_ids', $operator, $bind,
                    array('bindArrayOfIds'));
                $select->where($where);
            } else if ($valueType == self::VALUE_TYPE_CHILD_OF) {
                $concatenated = $resource->getReadConnection()->getConcatSql(array('tp.path', "'/%'"));
                $subSelect = $resource->select()
                    ->from(array('tc' => $resource->getTable('catalog/category')), 'entity_id')
                    ->join(
                        array('tp' => $resource->getTable('catalog/category')),
                        "tc.path ".($operator == '!()' ? 'NOT ' : '')."LIKE {$concatenated}",
                        array())
                    ->where($resource->getOperatorBindCondition('tp.entity_id', 'category_ids', '()', $bind,
                        array('bindArrayOfIds')));
                $select->where('category_id IN(?)', $subSelect);
            } else { //self::VALUE_TYPE_CONSTANT
                $value = $resource->bindArrayOfIds($this->getValue());
                $where = $resource->getOperatorCondition('category_id', $operator, $value);
                $select->where($where);
            }

            return new Zend_Db_Expr(sprintf('(%s) > 0', $select->assemble()));
        }

        if ($valueType == self::VALUE_TYPE_CONSTANT) {
            $useBind = false;
            $value = $this->getValue();
            // split value by commas into array for operators with multiple operands
            if (($operator == '()' || $operator == '!()') && is_string($value) && trim($value) != '') {
                $value = preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
            }
        } else { //self::VALUE_TYPE_SAME_AS
            $useBind = true;
        }

        $attribute = $this->getAttributeObject();
        if (!$attribute) {
            return false;
        }

        if ($attribute->isStatic()) {
            $field = "e.{$attributeCode}";
            if ($useBind) {
                $where = $resource->getOperatorBindCondition($field, $attributeCode, $operator, $bind);
            } else {
                $where = $resource->getOperatorCondition($field, $operator, $value);
            }
            $where = sprintf('(%s)', $where);
        } else if ($attribute->isScopeGlobal()) {
            $table  = $attribute->getBackendTable();
            $select = $object->select()
                ->from(array('table' => $table), 'COUNT(*)')
                ->where('table.entity_id = e.entity_id')
                ->where('table.attribute_id=?', $attribute->getId())
                ->where('table.store_id=?', 0);
            if ($useBind) {
                $select->where($resource->getOperatorBindCondition('table.value', $attributeCode, $operator, $bind));
            } else {
                $select->where($resource->getOperatorCondition('table.value', $operator, $value));
            }

            $select = $resource->getReadConnection()->getIfNullSql($select);
            $where = sprintf('(%s) > 0', $select);
        } else { //scope store and website
            $valueExpr = $resource->getReadConnection()->getCheckSql(
                'attr_s.value_id > 0',
                'attr_s.value',
                'attr_d.value'
            );
            $table  = $attribute->getBackendTable();
            $select = $object->select()
                ->from(array('attr_d' => $table), 'COUNT(*)')
                ->joinLeft(
                    array('attr_s' => $table),
                    $resource->getReadConnection()->quoteInto(
                        'attr_s.entity_id = attr_d.entity_id AND attr_s.attribute_id = attr_d.attribute_id'
                        . ' AND attr_s.store_id=?', $object->getStoreId()
                    ),
                    array())
                ->where('attr_d.entity_id = e.entity_id')
                ->where('attr_d.attribute_id=?', $attribute->getId())
                ->where('attr_d.store_id=?', 0);
            if ($useBind) {
                $select->where($resource->getOperatorBindCondition($valueExpr, $attributeCode, $operator, $bind));
            } else {
                $select->where($resource->getOperatorCondition($valueExpr, $operator, $value));
            }

            $where  = sprintf('(%s) > 0', $select);
        }
        return new Zend_Db_Expr($where);
    }
}
