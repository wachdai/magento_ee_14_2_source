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
 * @package     Enterprise_Banner
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Banner resource module
 *
 * @category    Enterprise
 * @package     Enterprise_Banner
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Banner_Model_Resource_Banner extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Sales rule table name
     *
     * @var string
     */
    protected $_salesRuleTable;

    /**
     * Catalog rule table name
     *
     * @var string
     */
    protected $_catalogRuleTable;

    /**
     * Contents table name
     *
     * @var string
     */
    protected $_contentsTable;

    /**
     * Customer segment relation table name
     *
     * @var string
     */
    protected $_customerSegmentTable;

    /**
     * Define if joining related sales rule to banner is already preformed
     *
     * @var bool
     */
    protected $_isSalesRuleRelatedToBanner       = false;

    /**
     * Define if joining related catalog rule to banner is already preformed
     *
     * @var bool
     */
    protected $_isCatalogRuleRelatedToBanner     = false;

    /**
     * Whether to filter banners by specified types
     *
     * @var array
     */
    protected $_bannerTypesFilter                = array();

    /**
     * Initialize banner resource model
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_banner/banner', 'banner_id');
        $this->_salesRuleTable       = $this->getTable('enterprise_banner/salesrule');
        $this->_catalogRuleTable     = $this->getTable('enterprise_banner/catalogrule');
        $this->_contentsTable        = $this->getTable('enterprise_banner/content');
        $this->_customerSegmentTable = $this->getTable('enterprise_banner/customersegment');
    }

    /**
     * Set filter by specified types
     *
     * @param string|array $types
     * @return Enterprise_Banner_Model_Resource_Banner
     */
    public function filterByTypes($types = array())
    {
        $this->_bannerTypesFilter = Mage::getSingleton('enterprise_banner/config')->explodeTypes($types);
        return $this;
    }

    /**
     * Save banner contents for different store views
     *
     * @param int $bannerId
     * @param array $contents
     * @param array $notuse
     * @return Enterprise_Banner_Model_Resource_Banner
     */
    public function saveStoreContents($bannerId, $contents, $notuse = array())
    {
        $deleteContentsByStores = array();
        if (!is_array($notuse)) {
            $notuse = array();
        }
        $adapter = $this->_getWriteAdapter();

        foreach ($contents as $storeId => $content) {
            if (!empty($content)) {
                $adapter->insertOnDuplicate(
                    $this->_contentsTable,
                    array('banner_id' => $bannerId, 'store_id' => $storeId, 'banner_content' => $content),
                    array('banner_content')
                );
            } else {
                $deleteContentsByStores[] = $storeId;
            }
        }
        if (!empty($deleteContentsByStores) || !empty($notuse)) {
            $condition = array(
                'banner_id = ?'   => $bannerId,
                'store_id IN (?)' => array_merge($deleteContentsByStores, array_keys($notuse)),
            );
            $adapter->delete($this->_contentsTable, $condition);
        }
        return $this;
    }

    /**
     * Delete unchecked catalog rules
     *
     * @param int $bannerId
     * @param array $rules
     * @return Enterprise_Banner_Model_Resource_Banner
     */
    public function saveCatalogRules($bannerId, $rules)
    {
        $adapter = $this->_getWriteAdapter();
        if (empty($rules)) {
            $rules = array(0);
        } else {
            foreach ($rules as $ruleId) {
                $adapter->insertOnDuplicate(
                    $this->_catalogRuleTable,
                    array('banner_id' => $bannerId, 'rule_id' => $ruleId),
                    array('rule_id')
                );
            }
        }
        $condition = array(
            'banner_id=?'        => $bannerId,
            'rule_id NOT IN (?)' => $rules
        );
        $adapter->delete($this->_catalogRuleTable, $condition);
        return $this;
    }

    /**
     * Delete unchecked sale rules
     *
     * @param int $bannerId
     * @param array $rules
     * @return Enterprise_Banner_Model_Resource_Banner
     */
    public function saveSalesRules($bannerId, $rules)
    {
        $adapter = $this->_getWriteAdapter();
        if (empty($rules)) {
            $rules = array(0);
        } else {
            foreach ($rules as $ruleId) {
                $adapter->insertOnDuplicate(
                    $this->_salesRuleTable,
                    array('banner_id' => $bannerId, 'rule_id' => $ruleId),
                    array('rule_id')
                );
            }
        }
        $adapter->delete($this->_salesRuleTable,
            array('banner_id=?' => $bannerId, 'rule_id NOT IN (?)' => $rules)
        );
        return $this;
    }

    /**
     * Get all existing banner contents
     *
     * @param int $bannerId
     * @return array
     */
    public function getStoreContents($bannerId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_contentsTable, array('store_id', 'banner_content'))
            ->where('banner_id=?', $bannerId);
        return $adapter->fetchPairs($select);
    }

    /**
     * Get banner content by specific store id
     *
     * @param int $bannerId
     * @param int $storeId
     * @param array $segmentIds
     * @return string
     */
    public function getStoreContent($bannerId, $storeId, $segmentIds = array())
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(array('main' => $this->_contentsTable), 'banner_content')
            ->where('main.banner_id=?', $bannerId)
            ->where('main.store_id IN(?)', array($storeId, 0))
            ->order('main.store_id DESC');
        $select->joinLeft(
            array('banner_segments' => $this->getTable('enterprise_banner/customersegment')),
            'main.banner_id = banner_segments.banner_id',
            array()
        );
        if (empty($segmentIds)) {
            $select->where('banner_segments.segment_id IS NULL');
        } else {
            $select->joinLeft(
                array('customer_segments' => $this->getTable('enterprise_customersegment/segment')),
                'customer_segments.segment_id = banner_segments.segment_id',
                array()
            );
            $condition = 'banner_segments.segment_id IS NULL OR '
                . '(banner_segments.segment_id IN (?) AND customer_segments.is_active = 1)';
            $select->where($condition, $segmentIds);
        }

        if ($this->_bannerTypesFilter) {
            $select->joinInner(
                array('b' => $this->getTable('enterprise_banner/banner')),
                'main.banner_id = b.banner_id'
            );
            $filter = array();
            foreach ($this->_bannerTypesFilter as $type) {
                $filter[] = $adapter->prepareSqlCondition('b.types', array('finset' => $type));
            }
            $select->where(implode(' OR ', $filter));
        }

        return $adapter->fetchOne($select);
    }

    /**
     * Get sales rule that associated to banner
     *
     * @param int $bannerId
     * @return array
     */
    public function getRelatedSalesRule($bannerId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_salesRuleTable, array())
            ->where('banner_id = ?', $bannerId);
            if (!$this->_isSalesRuleRelatedToBanner) {
                $select->join(
                    array('rules' => $this->getTable('salesrule/rule')),
                    $this->_salesRuleTable . '.rule_id = rules.rule_id',
                    array('rule_id')
                );
                $this->_isSalesRuleRelatedToBanner = true;
            }
        $rules = $adapter->fetchCol($select);
        return $rules;
    }

    /**
     * Get catalog rule that associated to banner
     *
     * @param int $bannerId
     * @return array
     */
    public function getRelatedCatalogRule($bannerId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_catalogRuleTable, array())
            ->where('banner_id = ?', $bannerId);
            if (!$this->_isCatalogRuleRelatedToBanner) {
                $select->join(
                    array('rules' => $this->getTable('catalogrule/rule')),
                    $this->_catalogRuleTable . '.rule_id = rules.rule_id',
                    array('rule_id')
                );
                $this->_isCatalogRuleRelatedToBanner = true;
            }

        $rules = $adapter->fetchCol($select);
        return $rules;
    }

    /**
     * Get banners that associated to catalog rule
     *
     * @param int $ruleId
     * @return array
     */
    public function getRelatedBannersByCatalogRuleId($ruleId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_catalogRuleTable, array('banner_id'))
            ->where('rule_id = ?', $ruleId);
        return $adapter->fetchCol($select);
    }

    /**
     * Get banners that associated to sales rule
     *
     * @param int $ruleId
     * @return array
     */
    public function getRelatedBannersBySalesRuleId($ruleId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_salesRuleTable, array('banner_id'))
            ->where('rule_id = ?', $ruleId);
        return $adapter->fetchCol($select);
    }

    /**
     * Bind specified banners to catalog rule by rule id
     *
     * @param int $ruleId
     * @param array $banners
     * @return Enterprise_Banner_Model_Resource_Banner
     */
    public function bindBannersToCatalogRule($ruleId, $banners)
    {
        $adapter = $this->_getWriteAdapter();
        foreach ($banners as $bannerId) {
            $adapter->insertOnDuplicate(
                $this->_catalogRuleTable,
                array('banner_id' => $bannerId, 'rule_id' => $ruleId),
                array('rule_id')
            );
        }

        if (empty($banners)) {
            $banners = array(0);
        }

        $adapter->delete($this->_catalogRuleTable,
            array('rule_id = ?' => $ruleId, 'banner_id NOT IN (?)' => $banners)
        );
        return $this;
    }

    /**
     * Bind specified banners to sales rule by rule id
     *
     * @param int $ruleId
     * @param array $banners
     * @return Enterprise_Banner_Model_Resource_Banner
     */
    public function bindBannersToSalesRule($ruleId, $banners)
    {
        $adapter = $this->_getWriteAdapter();
        foreach ($banners as $bannerId) {
            $adapter->insertOnDuplicate(
                $this->_salesRuleTable,
                array('banner_id' => $bannerId, 'rule_id' => $ruleId),
                array('rule_id')
            );
        }

        if (empty($banners)) {
            $banners = array(0);
        }

        $adapter->delete($this->_salesRuleTable,
            array('rule_id = ?' => $ruleId, 'banner_id NOT IN (?)' => $banners)
        );
        return $this;
    }

    /**
     * Get real existing banner ids by specified ids
     *
     * @param array $bannerIds
     * @param bool $isActive if true then only active banners will be get
     * @return array
     */
    public function getExistingBannerIdsBySpecifiedIds($bannerIds, $isActive = true)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('banner_id'))
            ->where('banner_id IN (?)', $bannerIds);
        if ($isActive) {
            $select->where('is_enabled = ?', (int)$isActive);
        }
        return array_intersect($bannerIds, $adapter->fetchCol($select));
    }

    /**
     * Get banners content per store view
     *
     * @param array $bannerIds
     * @param int $storeId
     * @param array $segmentIds
     * @return array
     */
    public function getBannersContent($bannerIds, $storeId, $segmentIds = array())
    {
        $content = array();
        foreach ($bannerIds as $_id) {
            $_content = $this->getStoreContent($_id, $storeId, $segmentIds);
            if (!empty($_content)) {
                $content[$_id] = $_content;
            }
        }
        return $content;
    }

    /**
     * Get banners IDs that related to sales rule and satisfy conditions
     *
     * @param array $matchedCustomerSegments
     * @param array $aplliedRules
     * @param bool $enabledOnly
     * @return array
     */
    public function getSalesRuleRelatedBannerIds($matchedCustomerSegments, $aplliedRules, $enabledOnly = true)
    {
        $adapter = $this->_getReadAdapter();
        $collection = Mage::getResourceModel('enterprise_banner/salesrule_collection');
        $collection->resetColumns()
               ->addBannersFilter($aplliedRules, $enabledOnly)
               ->addCustomerSegmentFilter($matchedCustomerSegments);
        return $adapter->fetchCol($collection->getSelect());
    }

    /**
     * Get banners IDs that related to sales rule and satisfy conditions
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @param array $matchedCustomerSegments
     * @param bool $enabledOnly
     * @return array
     */
    public function getCatalogRuleRelatedBannerIds(
        $websiteId, $customerGroupId, $matchedCustomerSegments, $enabledOnly = true
    ) {
        $adapter = $this->_getReadAdapter();
        $collection = Mage::getResourceModel('enterprise_banner/catalogrule_collection');
        $collection->resetSelect()
               ->addAppliedRuleFilter($websiteId, $customerGroupId)
               ->addBannersFilter($enabledOnly)
               ->addCustomerSegmentFilter($matchedCustomerSegments);
        return $adapter->fetchCol($collection->getSelect());
    }

    /**
     * Bind banner to customer segments
     *
     * @param int $bannerId
     * @param array $segments
     * @return Enterprise_Banner_Model_Resource_Banner
     */
    public function saveCustomerSegments($bannerId, $segments)
    {
        if (is_string($segments)) {
            $segments = array();
        }
        $adapter = $this->_getWriteAdapter();
        foreach ($segments as $segmentId) {
            $adapter->insertOnDuplicate(
                $this->_customerSegmentTable,
                array('banner_id' => $bannerId, 'segment_id' => $segmentId),
                array('banner_id')
            );
        }

        if (empty($segments)) {
            $segments = array(0);
        }

        $adapter->delete($this->_customerSegmentTable,
            array('banner_id = ?' => $bannerId, 'segment_id NOT IN (?)' => $segments)
        );
        return $this;
    }

    /**
     * Add customer segment ids to banner data, cast banner types to array
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->_customerSegmentTable)
            ->where('banner_id = ?', $object->getId());

        if ($data = $read->fetchAll($select)) {
            $segmentsArray = array();
            foreach ($data as $row) {
                $segmentsArray[] = $row['segment_id'];
            }
            $object->setData('customer_segment_ids', $segmentsArray);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Prepare banner types for saving
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Enterprise_Banner_Model_Resource_Banner
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $types = $object->getTypes();
        if (empty($types)) {
            $types = null;
        } elseif (is_array($types)) {
            $types = implode(',', $types);
        }
        if (empty($types)) {
            $types = null;
        }
        $object->setTypes($types);
        return parent::_beforeSave($object);
    }
}
