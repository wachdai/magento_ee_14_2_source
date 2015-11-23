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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Gift registry type data resource model
 *
 * @category    Enterprise
 * @package     Enterprise_GiftRegistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftRegistry_Model_Resource_Type extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Info table name
     *
     * @var string
     */
    protected $_infoTable;

    /**
     * Label table name
     *
     * @var string
     */
    protected $_labelTable;

    /**
     * Initialization. Set main entity table name and primary key field name.
     * Set label and info tables
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_giftregistry/type', 'type_id');
        $this->_infoTable  = $this->getTable('enterprise_giftregistry/info');
        $this->_labelTable = $this->getTable('enterprise_giftregistry/label');
    }

    /**
     * Add store date to registry type data
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $adapter = $this->_getReadAdapter();

        $scopeCheckExpr = $adapter->getCheckSql(
            'store_id = 0',
            $adapter->quote('default'),
            $adapter->quote('store')
        );
        $storeIds       = array(Mage_Core_Model_App::ADMIN_STORE_ID);
        if ($object->getStoreId()) {
            $storeIds[] = (int)$object->getStoreId();
        }
        $select = $adapter->select()
            ->from($this->_infoTable, array(
                'scope' => $scopeCheckExpr,
                'label',
                'is_listed',
                'sort_order'
            ))
            ->where('type_id = ?', (int)$object->getId())
            ->where('store_id IN (?)', $storeIds);

        $data = $adapter->fetchAssoc($select);

        if (isset($data['store']) && is_array($data['store'])) {
            foreach ($data['store'] as $key => $value) {
                $object->setData($key, ($value !== null) ? $value : $data['default'][$key]);
                $object->setData($key . '_store', $value);
            }
        } elseif (isset($data['default']) && is_array($data['default'])) {
            foreach ($data['default'] as $key => $value) {
                $object->setData($key, $value);
            }
        }

        return parent::_afterLoad($object);
    }

    /**
     * Save registry type per store view data
     *
     * @param Enterprise_GiftRegistry_Model_Type $type
     * @return Enterprise_GiftRegistry_Model_Resource_Type
     */
    public function saveTypeStoreData($type)
    {
        $this->_getWriteAdapter()->delete($this->_infoTable, array(
            'type_id = ?'  => (int)$type->getId(),
            'store_id = ?' => (int)$type->getStoreId()
        ));

        $this->_getWriteAdapter()->insert($this->_infoTable, array(
            'type_id'   => (int)$type->getId(),
            'store_id'  => (int)$type->getStoreId(),
            'label'     => $type->getLabel(),
            'is_listed' => (int)$type->getIsListed(),
            'sort_order'=> (int)$type->getSortOrder()
        ));

        return $this;
    }

    /**
     * Save store data
     *
     * @param Enterprise_GiftRegistry_Model_Type $type
     * @param array $data
     * @param string $optionCode
     * @return Enterprise_GiftRegistry_Model_Resource_Type
     */
    public function saveStoreData($type, $data, $optionCode = '')
    {
        $adapter = $this->_getWriteAdapter();
        if (isset($data['use_default'])) {
            $adapter->delete($this->_labelTable, array(
                'type_id = ?'        => (int)$type->getId(),
                'attribute_code = ?' => $data['code'],
                'store_id = ?'       => (int)$type->getStoreId(),
                'option_code = ?'    => $optionCode
            ));
        } else {
            $values = array(
                'type_id'        => (int)$type->getId(),
                'attribute_code' => $data['code'],
                'store_id'       => (int)$type->getStoreId(),
                'option_code'    => $optionCode,
                'label'          => $data['label']
            );
            $adapter->insertOnDuplicate($this->_labelTable, $values, array('label'));
        }

        return $this;
    }

    /**
     * Get attribute store data
     *
     * @param Enterprise_GiftRegistry_Model_Type $type
     * @return null|array
     */
    public function getAttributesStoreData($type)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->_labelTable, array('attribute_code', 'option_code', 'label'))
            ->where('type_id = :type_id')
            ->where('store_id = :store_id');
        $bind = array(
            ':type_id'  => (int)$type->getId(),
            ':store_id' => (int)$type->getStoreId()
        );
        return $this->_getReadAdapter()->fetchAll($select, $bind);
    }

    /**
     * Delete attribute store data
     *
     * @param int $typeId
     * @param string $attributeCode
     * @param string $optionCode
     * @return Enterprise_GiftRegistry_Model_Resource_Type
     */
    public function deleteAttributeStoreData($typeId, $attributeCode, $optionCode = null)
    {
        $where = array(
            'type_id = ?'        => (int)$typeId,
            'attribute_code = ?' => $attributeCode
        );

        if ($optionCode !== null) {
            $where['option_code = ?'] = $optionCode;
        }

        $this->_getWriteAdapter()->delete($this->_labelTable, $where);
        return $this;
    }

    /**
     * Delete attribute values
     *
     * @param int $typeId
     * @param string $attributeCode
     * @param bool $personValue
     * @return Enterprise_GiftRegistry_Model_Resource_Type
     */
    public function deleteAttributeValues($typeId, $attributeCode, $personValue = false)
    {
        $entityTable = $this->getTable('enterprise_giftregistry/entity');
        $select      = $this->_getReadAdapter()->select();
        $select->from(array('e' => $entityTable), array('entity_id'))
            ->where('type_id = ?', (int)$typeId);

        if ($personValue) {
            $table = $this->getTable('enterprise_giftregistry/person');
        } else {
            $table = $this->getTable('enterprise_giftregistry/data');
        }

        $this->_getWriteAdapter()->update($table,
            array($attributeCode => new Zend_Db_Expr('NULL')),
            array('entity_id IN (?)' => $select)
        );

        return $this;
    }
}
