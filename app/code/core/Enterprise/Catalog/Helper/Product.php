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
 * Index product helper
 *
 * @category   Enterprise
 * @package    Enterprise_Catalog
 */
class Enterprise_Catalog_Helper_Product extends Mage_Core_Helper_Abstract
{
    /**
     * Path to list of attributes used for flat indexer
     */
    const XML_NODE_ATTRIBUTE_NODES  = 'global/catalog/product/flat/attribute_nodes';

    /**
     * Adapter for connecting to database
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * List of columns in flat product table
     *
     * @var null|array
     */
    protected $_columns;

    /**
     * List of attribute codes uses in flat product table
     *
     * @var null|array
     */
    protected $_attributeCodes;

    /**
     * List of entity types uses in flat product table
     *
     * @var null|array
     */
    protected $_entityTypeId;

    /**
     * List of indexes uses in flat product table
     *
     * @var null|array
     */
    protected $_indexes;

    /**
     * List of product types uses in flat product table
     *
     * @var null|array
     */
    protected $_productTypes;

    /**
     * Initialize object
     */
    public function __construct()
    {
        $this->_connection = Mage::getSingleton('core/resource')->getConnection(
            Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE
        );
    }


    /**
     * Retrieve catalog product flat columns array in old format (used before MMDB support)
     *
     * @return array
     */
    protected $_attributes;

    /**
     * Initialize object
     */
    public function getFlatColumnsOldDefinition()
    {
        $columns = array();
        $columns['entity_id'] = array(
            'type'      => 'int(10)',
            'unsigned'  => true,
            'is_null'   => false,
            'default'   => null,
            'extra'     => null
        );
        if ($this->getFlatHelper()->isAddChildData()) {
            $columns['child_id'] = array(
                'type'      => 'int(10)',
                'unsigned'  => true,
                'is_null'   => true,
                'default'   => null,
                'extra'     => null
            );
            $columns['is_child'] = array(
                'type'      => 'tinyint(1)',
                'unsigned'  => true,
                'is_null'   => false,
                'default'   => 0,
                'extra'     => null
            );
        }
        $columns['attribute_set_id'] = array(
            'type'      => 'smallint(5)',
            'unsigned'  => true,
            'is_null'   => false,
            'default'   => 0,
            'extra'     => null
        );
        $columns['type_id'] = array(
            'type'      => 'varchar(32)',
            'unsigned'  => false,
            'is_null'   => false,
            'default'   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            'extra'     => null
        );

        return $columns;
    }

    /**
     * Retrieve catalog product flat columns array in DDL format
     *
     * @return array
     */
    public function getFlatColumnsDdlDefinition()
    {
        $columns = array();
        $columns['entity_id'] = array(
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'    => null,
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => false,
            'primary'   => true,
            'comment'   => 'Entity Id'
        );
        if ($this->getFlatHelper()->isAddChildData()) {
            $columns['child_id'] = array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length'    => null,
                'unsigned'  => true,
                'nullable'  => true,
                'default'   => null,
                'primary'   => true,
                'comment'   => 'Child Id'
            );
            $columns['is_child'] = array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'length'    => 1,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Checks If Entity Is Child'
            );
        }
        $columns['attribute_set_id'] = array(
            'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'length'    => 5,
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
            'comment'   => 'Attribute Set Id'
        );
        $columns['type_id'] = array(
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'    => 32,
            'unsigned'  => false,
            'nullable'  => false,
            'default'   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            'comment'   => 'Type Id'
        );

        return $columns;
    }

    /**
     * Retrieve catalog product flat table columns array
     *
     * @return array
     */
    public function getFlatColumns()
    {
        if ($this->_columns === null) {
            if (Mage::helper('core')->useDbCompatibleMode()) {
                $this->_columns = $this->getFlatColumnsOldDefinition();
            } else {
                $this->_columns = $this->getFlatColumnsDdlDefinition();
            }

            foreach ($this->getAttributes() as $attribute) {
                /** @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
                $columns = $attribute
                    ->setFlatAddFilterableAttributes($this->getFlatHelper()->isAddFilterableAttributes())
                    ->setFlatAddChildData($this->getFlatHelper()->isAddChildData())
                    ->getFlatColumns();
                if ($columns !== null) {
                    $this->_columns = array_merge($this->_columns, $columns);
                }
            }

            $columnsObject = new Varien_Object();
            $columnsObject->setColumns($this->_columns);
            Mage::dispatchEvent('enterprise_catalog_product_flat_prepare_columns',
                array('columns' => $columnsObject)
            );
            $this->_columns = $columnsObject->getColumns();
        }

        return $this->_columns;
    }

    /**
     * Retrieve attribute codes using for flat
     *
     * @return array
     */
    public function getAttributeCodes()
    {
        if ($this->_attributeCodes === null) {
            $this->_attributeCodes = array();
            $systemAttributes      = array();

            $attributeNodes = Mage::getConfig()
                ->getNode(self::XML_NODE_ATTRIBUTE_NODES)
                ->children();

            foreach ($attributeNodes as $node) {
                $attributes = Mage::getConfig()->getNode((string)$node)->asArray();
                $attributes = array_keys($attributes);
                $systemAttributes = array_unique(array_merge($attributes, $systemAttributes));
            }

            $bind = array(
                'backend_type'      => Mage_Eav_Model_Entity_Attribute_Abstract::TYPE_STATIC,
                'entity_type_id'    => $this->getEntityTypeId()
            );

            $select = $this->_connection->select()
                ->from(array('main_table' => $this->getTable('eav/attribute')))
                ->join(
                    array('additional_table' => $this->getTable('catalog/eav_attribute')),
                    'additional_table.attribute_id = main_table.attribute_id'
                )
                ->where('main_table.entity_type_id = :entity_type_id');
            $whereCondition = array(
                'main_table.backend_type = :backend_type',
                $this->_connection->quoteInto('additional_table.is_used_for_promo_rules = ?', 1),
                $this->_connection->quoteInto('additional_table.used_in_product_listing = ?', 1),
                $this->_connection->quoteInto('additional_table.used_for_sort_by = ?', 1),
                $this->_connection->quoteInto('main_table.attribute_code IN(?)', $systemAttributes)
            );
            if ($this->getFlatHelper()->isAddFilterableAttributes()) {
               $whereCondition[] = $this->_connection->quoteInto('additional_table.is_filterable > ?', 0);
            }

            $select->where(implode(' OR ', $whereCondition));
            $attributesData = $this->_connection->fetchAll($select, $bind);
            Mage::getSingleton('eav/config')
                ->importAttributesData($this->getEntityType(), $attributesData);

            foreach ($attributesData as $data) {
                $this->_attributeCodes[$data['attribute_id']] = $data['attribute_code'];
            }
            unset($attributesData);
        }

        return $this->_attributeCodes;
    }

    /**
     * Retrieve entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return Mage_Catalog_Model_Product::ENTITY;
    }

    /**
     * Retrieve Catalog Entity Type Id
     *
     * @return int
     */
    public function getEntityTypeId()
    {
        if ($this->_entityTypeId === null) {
            $this->_entityTypeId = Mage::getResourceModel('catalog/config')
                ->getEntityTypeId();
        }
        return $this->_entityTypeId;
    }

    /**
     * Retrieve attribute objects for flat
     *
     * @return array
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = array();
            $attributeCodes    = $this->getAttributeCodes();
            $entity = Mage::getSingleton('eav/config')
                ->getEntityType($this->getEntityType())
                ->getEntity();

            foreach ($attributeCodes as $attributeCode) {
                $attribute = Mage::getSingleton('eav/config')
                    ->getAttribute($this->getEntityType(), $attributeCode)
                    ->setEntity($entity);
                try {
                    // check if exists source and backend model.
                    // To prevent exception when some module was disabled
                    $attribute->usesSource() && $attribute->getSource();
                    $attribute->getBackend();
                    $this->_attributes[$attributeCode] = $attribute;
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        return $this->_attributes;
    }

    /**
     * Retrieve loaded attribute by code
     *
     * @param string $attributeCode
     * @throws Mage_Core_Exception
     * @return Mage_Eav_Model_Entity_Attribute
     */
    public function getAttribute($attributeCode)
    {
        $attributes = $this->getAttributes();
        if (!isset($attributes[$attributeCode])) {
            $attribute = Mage::getModel('catalog/resource_eav_attribute')
                ->loadByCode($this->getEntityTypeId(), $attributeCode);
            if (!$attribute->getId()) {
                Mage::throwException(Mage::helper('enterprise_catalog')->__('Invalid attribute %s', $attributeCode));
            }
            $entity = Mage::getSingleton('eav/config')
                ->getEntityType($this->getEntityType())
                ->getEntity();
            $attribute->setEntity($entity);

            return $attribute;
        }

        return $attributes[$attributeCode];
    }


    /**
     * Retrieve catalog product flat table indexes array
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        if ($this->_indexes === null) {
            $this->_indexes = array();

            if ($this->getFlatHelper()->isAddChildData()) {
                $this->_indexes['PRIMARY'] = array(
                    'type'   => Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY,
                    'fields' => array('entity_id', 'child_id')
                );
                $this->_indexes['IDX_CHILD'] = array(
                    'type'   => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX,
                    'fields' => array('child_id')
                );
                $this->_indexes['IDX_IS_CHILD'] = array(
                    'type'   => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX,
                    'fields' => array('entity_id', 'is_child')
                );
            } else {
                $this->_indexes['PRIMARY'] = array(
                    'type'   => Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY,
                    'fields' => array('entity_id')
                );
            }
            $this->_indexes['IDX_TYPE_ID'] = array(
                'type'   => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX,
                'fields' => array('type_id')
            );
            $this->_indexes['IDX_ATTRIBUTE_SET'] = array(
                'type'   => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX,
                'fields' => array('attribute_set_id')
            );

            foreach ($this->getAttributes() as $attribute) {
                /** @var $attribute Mage_Eav_Model_Entity_Attribute */
                $indexes = $attribute
                    ->setFlatAddFilterableAttributes($this->getFlatHelper()->isAddFilterableAttributes())
                    ->setFlatAddChildData($this->getFlatHelper()->isAddChildData())
                    ->getFlatIndexes();
                if ($indexes !== null && !empty($indexes)) {
                    $this->_indexes = array_merge($this->_indexes, $indexes);
                }
            }

            $indexesObject = new Varien_Object();
            $indexesObject->setIndexes($this->_indexes);
            Mage::dispatchEvent('enterprise_catalog_product_flat_prepare_indexes', array(
                'indexes'   => $indexesObject
            ));
            $this->_indexes = $indexesObject->getIndexes();
        }

        return $this->_indexes;
    }

    /**
     * Retrieve Catalog Product Flat helper
     *
     * @return Mage_Catalog_Helper_Product_Flat
     */
    public function getFlatHelper()
    {
        return Mage::helper('catalog/product_flat');
    }

    /**
     * Retrieve Product Type Instances
     * as key - type code, value - instance model
     *
     * @return array
     */
    public function getProductTypeInstances()
    {
        if ($this->_productTypes === null) {
            $this->_productTypes = array();
            $productEmulator     = new Varien_Object();

            foreach (array_keys(Mage_Catalog_Model_Product_Type::getTypes()) as $typeId) {
                $productEmulator->setTypeId($typeId);
                $this->_productTypes[$typeId] = Mage::getSingleton('catalog/product_type')
                    ->factory($productEmulator);
            }
        }
        return $this->_productTypes;
    }

     /**
     * Get table structure for temporary eav tables
     *
     * @param array $attributes
     *
     * @return array
     */
    public function getTablesStructure(array $attributes)
    {
        $eavAttributes   = array();
        $flatColumnsList = $this->getFlatColumns();
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        foreach ($attributes as $attribute) {
            $eavTable = $attribute->getBackend()->getTable();
            $attributeCode = $attribute->getAttributeCode();
            if (isset($flatColumnsList[$attributeCode])) {
                $eavAttributes[$eavTable][$attributeCode] = $attribute;
            }
        }

        return $eavAttributes;
    }

    /**
     * Returns table name
     *
     * @param string|array $name
     * @return string
     */
    public function getTable($name)
    {
        return Mage::getSingleton('core/resource')->getTableName($name);
    }

    /**
     * Retrieve Catalog Product Flat Table name
     *
     * @param int $storeId
     * @return string
     */
    public function getFlatTableName($storeId)
    {
        return sprintf('%s_%s', $this->getTable('catalog/product_flat'), $storeId);
    }
}

