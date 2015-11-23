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
 * Gift Wrapping Collection
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 */
class Enterprise_GiftWrapping_Model_Resource_Wrapping_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Intialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('enterprise_giftwrapping/wrapping');
        $this->_map['fields']['wrapping_id'] = 'main_table.wrapping_id';
    }

    /**
     * Redeclare after load method to add website IDs to items
     *
     * @return Enterprise_GiftWrapping_Model_Resource_Wrapping_Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getFlag('add_websites_to_result') && $this->_items) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('enterprise_giftwrapping/website'), array(
                    'wrapping_id',
                    'website_id'
                ))
                ->where('wrapping_id IN (?)', array_keys($this->_items));
            $websites = $this->getConnection()->fetchAll($select);
            foreach ($this->_items as $item) {
                $websiteIds = array();
                foreach ($websites as $website) {
                    if ($item->getId() == $website['wrapping_id']) {
                        $websiteIds[] = $website['website_id'];
                    }
                }
                if (count($websiteIds)) {
                    $item->setWebsiteIds($websiteIds);
                }
            }
        }
        return $this;
    }

    /**
     * Init flag for adding wrapping website ids to collection result
     *
     * @param  bool|null $flag
     * @return Enterprise_GiftWrapping_Model_Resource_Wrapping_Collection
     */
    public function addWebsitesToResult($flag = null)
    {
        $flag = ($flag === null) ? true : $flag;
        $this->setFlag('add_websites_to_result', $flag);
        return $this;
    }

    /**
     * Limit gift wrapping collection by specific website
     *
     * @param  int|array|Mage_Core_Model_Website $websiteId
     * @return Enterprise_GiftWrapping_Model_Resource_Wrapping_Collection
     */
    public function applyWebsiteFilter($websiteId)
    {
        if (!$this->getFlag('is_website_table_joined')) {
            $this->setFlag('is_website_table_joined', true);
            $this->getSelect()->joinInner(
                array('website' => $this->getTable('enterprise_giftwrapping/website')),
                'main_table.wrapping_id = website.wrapping_id',
                array()
            );
        }

        if ($websiteId instanceof Mage_Core_Model_Website) {
            $websiteId = $websiteId->getId();
        }
        $this->getSelect()->where('website.website_id IN (?)', $websiteId);

        return $this;
    }

    /**
     * Limit gift wrapping collection by status
     *
     * @return Enterprise_GiftWrapping_Model_Resource_Wrapping_Collection
     */
    public function applyStatusFilter()
    {
        $this->getSelect()->where('main_table.status = 1');
        return $this;
    }

    /**
     * Add specified field to collection filter
     * Redeclared in order to be able to limit collection by specific website
     * @see self::applyWebsiteFilter()
     *
     * @param  string $field
     * @param  mixed $condition
     * @return Enterprise_GiftWrapping_Model_Resource_Wrapping_Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'website_ids') {
            return $this->applyWebsiteFilter($condition);
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Convert collection to array for select options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array_merge(array(array(
            'value' => '',
            'label' => Mage::helper('enterprise_giftwrapping')->__('Please select')
        )), $this->_toOptionArray('wrapping_id', 'design'));
    }

     /* Add store attributes to collection
     *
     * @param  int $storeId
     * @return Enterprise_GiftWrapping_Model_Resource_Wrapping_Collection
     */
    public function addStoreAttributesToResult($storeId = 0)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select();
        $select->from(array('m' => $this->getMainTable()), array('*'));

        $select->joinLeft(
            array('d' => $this->getTable('enterprise_giftwrapping/attribute')),
            'd.wrapping_id = m.wrapping_id AND d.store_id = 0',
            array('')
        );

        $select->joinLeft(
            array('s' => $this->getTable('enterprise_giftwrapping/attribute')),
            's.wrapping_id = m.wrapping_id AND s.store_id = ' . $storeId,
            array('design' => $adapter->getIfNullSql('s.design', 'd.design'))
        );

        $this->getSelect()->reset()->from(
            array('main_table' => $select),
            array('*')
        );

        return $this;
    }
}
