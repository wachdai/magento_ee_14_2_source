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
 * @package     Enterprise_GiftWrapping
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Gift Wrapping Resource Model
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 */
class Enterprise_GiftWrapping_Model_Resource_Wrapping extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Wrapping websites table name
     *
     * @var string
     */
    protected $_websiteTable;

    /**
     * Wrapping stores data table name
     *
     * @var string
     */
    protected $_storeAttributesTable;

    /**
     * Intialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('enterprise_giftwrapping/wrapping', 'wrapping_id');
        $this->_websiteTable = $this->getTable('enterprise_giftwrapping/website');
        $this->_storeAttributesTable = $this->getTable('enterprise_giftwrapping/attribute');
    }

    /**
     * Add store data to wrapping data
     *
     * @param  Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_storeAttributesTable, array(
                'scope' => $adapter->getCheckSql('store_id = 0', $adapter->quote('default'), $adapter->quote('store')),
                'design'
            ))
            ->where('wrapping_id = ?', $object->getId())
            ->where('store_id IN (0,?)', $object->getStoreId());

        $data = $adapter->fetchAssoc($select);

        if (isset($data['store']) && is_array($data['store'])) {
            foreach ($data['store'] as $key => $value) {
                $object->setData($key, ($value !== null) ? $value : $data['default'][$key]);
                $object->setData($key . '_store', $value);
            }
        } else if (isset($data['default'])) {
            foreach ($data['default'] as $key => $value) {
                $object->setData($key, $value);
            }
        }
        return parent::_afterLoad($object);
    }

    /**
     * Get website ids associated to the gift wrapping
     *
     * @param  int $wrappingId
     * @return array
     */
    public function getWebsiteIds($wrappingId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->_websiteTable, 'website_id')
            ->where('wrapping_id = ?', $wrappingId);
        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Save wrapping per store view data
     *
     * @param  Enterprise_GiftWrapping_Model_Wrapping $wrapping
     * @return void
     */
    public function saveWrappingStoreData($wrapping)
    {
        $initialDesign = $wrapping->getDesign();
        //this check to prevent saving default data from store view
        if ($wrapping->hasData('is_default') && is_array($wrapping->getData('is_default'))) {
            foreach ($wrapping->getData('is_default') as $key => $value) {
                if ($value) {
                    $wrapping->setData($key, null);
                }
            }
        }

        if (!is_null($initialDesign)) {
            $this->_getWriteAdapter()->delete($this->_storeAttributesTable, array(
                'wrapping_id = ?' => $wrapping->getId(),
                'store_id = ?' => $wrapping->getStoreId()
            ));

            if ($wrapping->getDesign()) {
                $this->_getWriteAdapter()->insert($this->_storeAttributesTable, array(
                    'wrapping_id' => $wrapping->getId(),
                    'store_id'    => $wrapping->getStoreId(),
                    'design'      => $wrapping->getDesign()
                ));
            }
        }
    }

    /**
     * Save attached websites
     *
     * @param  Enterprise_GiftWrapping_Model_Wrapping $wrapping
     * @return void
     */
    public function saveWrappingWebsiteData($wrapping)
    {
        $websiteIds = $wrapping->getWebsiteIds();
        $this->_getWriteAdapter()->delete($this->_websiteTable, array(
            'wrapping_id = ?' => $wrapping->getId(),
        ));

        foreach ($websiteIds as $value) {
            $this->_getWriteAdapter()->insert($this->_websiteTable, array(
                'wrapping_id' => $wrapping->getId(),
                'website_id'  => $value
            ));
        }
    }
}
