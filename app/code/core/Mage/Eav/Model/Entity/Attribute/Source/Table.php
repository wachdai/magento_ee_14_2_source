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
 * @category    Mage
 * @package     Mage_Eav
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


class Mage_Eav_Model_Entity_Attribute_Source_Table extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Default values for option cache
     *
     * @var array
     */
    protected $_optionsDefault = array();

    /**
     * List of preloaded options per attribute
     *
     * @var array
     */
    protected $_preloadedOptions = array();

    /**
     * List of stores where default values already preloaded
     *
     * @var array
     */
    protected $_preloadedOptionsStores = array();

    /**
     * List of preloaded options for each option id
     *
     * @var array
     */
    protected $_preloadedOptionHash = array();

    /**
     * Retrieve store options from preloaded hashes
     *
     * @param int $storeId
     * @param int $attributeId
     * @return array
     */
    protected function _getPreloadedOptions($storeId, $attributeId, $type)
    {
        $this->_preloadOptions($storeId);

        $key = $this->_getCombinedKey($storeId, $attributeId, $type);

        if (isset($this->_preloadedOptions[$key])) {
            return $this->_preloadedOptions[$key];
        }

        return array();
    }

    /**
     * Preloads values for option values on the first call
     *
     * @param int $storeId
     */
    protected function _preloadOptions($storeId)
    {
        if (isset($this->_preloadedOptionsStores[$storeId])) {
            return;
        }
        $this->_preloadedOptionsStores[$storeId] = true;
        $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setPositionOrder('asc')
            ->setStoreFilter($storeId);
        // This one allows to limit selection of options, based on frontend criteria.
        // E.g. if not all the attribute options are needed for the current page
        Mage::dispatchEvent('eav_entity_attribute_source_table_preload_options', array(
            'collection' => $collection,
            'store_id' => $storeId
        ));
        $options = $collection->getData();

        foreach ($options as $option) {
            $optionKey = $this->_getCombinedKey($storeId, $option['option_id'], 'store');
            $storeKey = $this->_getCombinedKey($storeId, $option['attribute_id'], 'store');
            $defaultKey = $this->_getCombinedKey($storeId, $option['attribute_id'], 'default');

            $this->_preloadedOptionHash[$optionKey] = $option['value'];
            $this->_preloadedOptions[$storeKey][] = array(
                'value' => $option['option_id'],
                'label' => $option['value']
            );
            $this->_preloadedOptions[$defaultKey][] = array(
                'value' => $option['option_id'],
                'label' => $option['default_value']
            );
        }
    }


    /**
     * Retrieve Full Option values array
     *
     * @param bool $withEmpty       Add empty option to array
     * @param bool $defaultValues
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        $storeId = $this->getAttribute()->getStoreId();
        if (!is_array($this->_options)) {
            $this->_options = array();
        }
        if (!is_array($this->_optionsDefault)) {
            $this->_optionsDefault = array();
        }
        if (!isset($this->_options[$storeId])) {
            $this->_options[$storeId] = $this->_getPreloadedOptions(
                $storeId,
                $this->getAttribute()->getId(),
                'store'
            );
            $this->_optionsDefault[$storeId] = $this->_getPreloadedOptions(
                $storeId,
                $this->getAttribute()->getId(),
                'default'
            );
        }
        $options = ($defaultValues ? $this->_optionsDefault[$storeId] : $this->_options[$storeId]);
        if ($withEmpty) {
            array_unshift($options, array('label' => '', 'value' => ''));
        }
        return $options;
    }

    /**
     * Returns option key for hash generation
     *
     * @param int $storeId
     * @param int $optionId
     * @param string $type
     * @return string
     */
    protected function _getCombinedKey($storeId, $optionId, $type)
    {
        return $storeId . '|' . $optionId . '|' . $type;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $storeId = $this->getAttribute()->getStoreId();
        $this->_preloadOptions($storeId);
        $isMultiple = false;
        if (strpos($value, ',')) {
            $isMultiple = true;
            $value = explode(',', $value);
        }


        if ($isMultiple) {
            $values = array();
            foreach ($value as $item) {
                $key = $this->_getCombinedKey($storeId, $item, 'store');
                if (isset($this->_preloadedOptionHash[$key])) {
                    $values[] = $this->_preloadedOptionHash[$key];
                }
            }
            return $values;
        }

        $key = $this->_getCombinedKey($storeId, $value, 'store');

        if (isset($this->_preloadedOptionHash[$key])) {
            return $this->_preloadedOptionHash[$key];
        }
        return false;
    }

    /**
     * Add Value Sort To Collection Select
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @param string $dir
     *
     * @return Mage_Eav_Model_Entity_Attribute_Source_Table
     */
    public function addValueSortToCollection($collection, $dir = Varien_Db_Select::SQL_ASC)
    {
        $valueTable1    = $this->getAttribute()->getAttributeCode() . '_t1';
        $valueTable2    = $this->getAttribute()->getAttributeCode() . '_t2';
        $collection->getSelect()
            ->joinLeft(
                array($valueTable1 => $this->getAttribute()->getBackend()->getTable()),
                "e.entity_id={$valueTable1}.entity_id"
                . " AND {$valueTable1}.attribute_id='{$this->getAttribute()->getId()}'"
                . " AND {$valueTable1}.store_id=0",
                array())
            ->joinLeft(
                array($valueTable2 => $this->getAttribute()->getBackend()->getTable()),
                "e.entity_id={$valueTable2}.entity_id"
                . " AND {$valueTable2}.attribute_id='{$this->getAttribute()->getId()}'"
                . " AND {$valueTable2}.store_id='{$collection->getStoreId()}'",
                array()
            );
        $valueExpr = $collection->getSelect()->getAdapter()
            ->getCheckSql("{$valueTable2}.value_id > 0", "{$valueTable2}.value", "{$valueTable1}.value");

        Mage::getResourceModel('eav/entity_attribute_option')
            ->addOptionValueToCollection($collection, $this->getAttribute(), $valueExpr);

        $collection->getSelect()
            ->order("{$this->getAttribute()->getAttributeCode()} {$dir}");

        return $this;
    }

    /**
     * Retrieve Column(s) for Flat
     *
     * @return array
     */
    public function getFlatColums()
    {
        $columns = array();
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $isMulti = $this->getAttribute()->getFrontend()->getInputType() == 'multiselect';

        if (Mage::helper('core')->useDbCompatibleMode()) {
            $columns[$attributeCode] = array(
                'type'      => $isMulti ? 'varchar(255)' : 'int',
                'unsigned'  => false,
                'is_null'   => true,
                'default'   => null,
                'extra'     => null
            );
            if (!$isMulti) {
                $columns[$attributeCode . '_value'] = array(
                    'type'      => 'varchar(255)',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
            }
        } else {
            $type = ($isMulti) ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_INTEGER;
            $columns[$attributeCode] = array(
                'type'      => $type,
                'length'    => $isMulti ? '255' : null,
                'unsigned'  => false,
                'nullable'   => true,
                'default'   => null,
                'extra'     => null,
                'comment'   => $attributeCode . ' column'
            );
            if (!$isMulti) {
                $columns[$attributeCode . '_value'] = array(
                    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                    'length'    => 255,
                    'unsigned'  => false,
                    'nullable'  => true,
                    'default'   => null,
                    'extra'     => null,
                    'comment'   => $attributeCode . ' column'
                );
            }
        }

        return $columns;
    }

    /**
     * Retrieve Indexes for Flat
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        $indexes = array();

        $index = sprintf('IDX_%s', strtoupper($this->getAttribute()->getAttributeCode()));
        $indexes[$index] = array(
            'type'      => 'index',
            'fields'    => array($this->getAttribute()->getAttributeCode())
        );

        $sortable   = $this->getAttribute()->getUsedForSortBy();
        if ($sortable && $this->getAttribute()->getFrontend()->getInputType() != 'multiselect') {
            $index = sprintf('IDX_%s_VALUE', strtoupper($this->getAttribute()->getAttributeCode()));

            $indexes[$index] = array(
                'type'      => 'index',
                'fields'    => array($this->getAttribute()->getAttributeCode() . '_value')
            );
        }

        return $indexes;
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('eav/entity_attribute_option')
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
