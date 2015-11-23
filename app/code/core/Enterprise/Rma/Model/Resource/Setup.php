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
 * @package     Enterprise_Rma
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Reward resource setup model
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Model_Resource_Setup extends Mage_Sales_Model_Resource_Setup
{
    /**
     * Prepare RMA item attribute values to save in additional table
     *
     * @param array $attr
     * @return array
     */
    protected function _prepareValues($attr)
    {
        $data = parent::_prepareValues($attr);
        $data = array_merge($data, array(
            'is_visible'                => $this->_getValue($attr, 'visible', 1),
            'is_system'                 => $this->_getValue($attr, 'system', 1),
            'input_filter'              => $this->_getValue($attr, 'input_filter', null),
            'multiline_count'           => $this->_getValue($attr, 'multiline_count', 0),
            'validate_rules'            => $this->_getValue($attr, 'validate_rules', null),
            'data_model'                => $this->_getValue($attr, 'data', null),
            'sort_order'                => $this->_getValue($attr, 'position', 0)
        ));
        return $data;
    }

    /**
     * Retreive default RMA item entities
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        $entities = array(
            'rma_item'                           => array(
                'entity_model'                   => 'enterprise_rma/item',
                'attribute_model'                => 'enterprise_rma/item_attribute',
                'table'                          => 'enterprise_rma/item_entity',
                'increment_model'                => 'eav/entity_increment_numeric',
                'additional_attribute_table'     => 'enterprise_rma/item_eav_attribute',
                'increment_per_store'            => 1,
                'entity_attribute_collection'    => null,
                'increment_per_store'            => 1,
                'attributes'                     => array(
                    'rma_entity_id'          => array(
                        'type'               => 'static',
                        'label'              => 'RMA Id',
                        'input'              => 'text',
                        'required'           => true,
                        'visible'            => false,
                        'sort_order'         => 10,
                        'position'           => 10,
                    ),
                    'order_item_id'          => array(
                        'type'               => 'static',
                        'label'              => 'Order Item Id',
                        'input'              => 'text',
                        'required'           => true,
                        'visible'            => false,
                        'sort_order'         => 20,
                        'position'           => 20,
                    ),
                    'qty_requested'          => array(
                        'type'               => 'static',
                        'label'              => 'Qty of requested for RMA items',
                        'input'              => 'text',
                        'required'           => true,
                        'visible'            => false,
                        'sort_order'         => 30,
                        'position'           => 30,
                    ),
                    'qty_authorized'         => array(
                        'type'               => 'static',
                        'label'              => 'Qty of authorized items',
                        'input'              => 'text',
                        'visible'            => false,
                        'sort_order'         => 40,
                        'position'           => 40,
                    ),
                    'qty_approved'           => array(
                        'type'               => 'static',
                        'label'              => 'Qty of requested for RMA items',
                        'input'              => 'text',
                        'visible'            => false,
                        'sort_order'         => 50,
                        'position'           => 50,
                    ),
                    'status'                 => array(
                        'type'               => 'static',
                        'label'              => 'Status',
                        'input'              => 'select',
                        'source'             => 'enterprise_rma/item_attribute_source_status',
                        'visible'            => false,
                        'sort_order'         => 60,
                        'position'           => 60,
                        'adminhtml_only'     => 1,
                    ),
                    'product_name'           => array(
                        'type'               => 'static',
                        'label'              => 'Product Name',
                        'input'              => 'text',
                        'sort_order'         => 70,
                        'position'           => 70,
                        'visible'            => false,
                        'adminhtml_only'     => 1,
                    ),
                    'product_sku'            => array(
                        'type'               => 'static',
                        'label'              => 'Product SKU',
                        'input'              => 'text',
                        'sort_order'         => 80,
                        'position'           => 80,
                        'visible'            => false,
                        'adminhtml_only'     => 1,
                    ),
                    'resolution'             => array(
                        'type'               => 'int',
                        'label'              => 'Resolution',
                        'input'              => 'select',
                        'sort_order'         => 90,
                        'position'           => 90,
                        'source'             => 'eav/entity_attribute_source_table',
                        'system'             => false,
                        'option'             => array('values' => array('Exchange', 'Refund', 'Store Credit')),
                        'validate_rules'     => 'a:0:{}',
                    ),
                    'condition'              => array(
                        'type'               => 'int',
                        'label'              => 'Item Condition',
                        'input'              => 'select',
                        'sort_order'         => 100,
                        'position'           => 100,
                        'source'             => 'eav/entity_attribute_source_table',
                        'system'             => false,
                        'option'             => array('values' => array('Unopened', 'Opened', 'Damaged')),
                        'validate_rules'     => 'a:0:{}',
                    ),
                    'reason'                 => array(
                        'type'               => 'int',
                        'label'              => 'Reason to Return',
                        'input'              => 'select',
                        'sort_order'         => 110,
                        'position'           => 110,
                        'source'             => 'eav/entity_attribute_source_table',
                        'system'             => false,
                        'option'             => array('values' => array('Wrong Color', 'Wrong Size', 'Out of Service')),
                        'validate_rules'     => 'a:0:{}',
                    ),
                    'reason_other'           => array(
                        'type'               => 'varchar',
                        'label'              => 'Other',
                        'input'              => 'text',
                        'validate_rules'     => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'sort_order'         => 120,
                        'position'           => 120,
                    ),
                )
            ),
        );
        return $entities;
    }

    /**
     * Add RMA Item Attributes to Forms
     *
     * @return void
     */
    public function installForms()
    {
        $rma_item           = (int)$this->getEntityTypeId('rma_item');

        $attributeIds       = array();
        $select = $this->getConnection()->select()
            ->from(
                array('ea' => $this->getTable('eav/attribute')),
                array('entity_type_id', 'attribute_code', 'attribute_id'))
            ->where('ea.entity_type_id = ?', $rma_item);
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $attributeIds[$row['entity_type_id']][$row['attribute_code']] = $row['attribute_id'];
        }

        $data       = array();
        $entities   = $this->getDefaultEntities();
        $attributes = $entities['rma_item']['attributes'];
        foreach ($attributes as $attributeCode => $attribute) {
            $attributeId = $attributeIds[$rma_item][$attributeCode];
            $attribute['system'] = isset($attribute['system']) ? $attribute['system'] : true;
            $attribute['visible'] = isset($attribute['visible']) ? $attribute['visible'] : true;
            if ($attribute['system'] != true || $attribute['visible'] != false) {
                $usedInForms = array(
                    'default',
                );
                foreach ($usedInForms as $formCode) {
                    $data[] = array(
                        'form_code'     => $formCode,
                        'attribute_id'  => $attributeId
                    );
                }
            }
        }

        if ($data) {
            $this->getConnection()->insertMultiple($this->getTable('enterprise_rma/item_form_attribute'), $data);
        }
    }
}
