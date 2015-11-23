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
 * TargetRule Product Index by Rule Product List Type Resource Model
 *
 * @category    Enterprise
 * @package     Enterprise_TargetRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_TargetRule_Model_Resource_Index extends Mage_Index_Model_Resource_Abstract
{
    /**
     * Increment value for generate unique bind names
     *
     * @var int
     */
    protected $_bindIncrement  = 0;

    /**
     * Factory instance
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Constructor for Enterprise_TargetRule_Model_Resource_Index
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('core/factory');
        parent::__construct();
    }

    /**
     * Initialize object
     */
    protected function _construct()
    {
        $this->_init('enterprise_targetrule/index', 'entity_id');
    }

    /**
     * Retrieve constant value overfill limit for product ids index
     *
     * @return int
     */
    public function getOverfillLimit()
    {
        return 20;
    }

    /**
     * Retrieve catalog product list index by type
     *
     * @param int $type
     * @return Enterprise_TargetRule_Model_Resource_Index_Abstract
     */
    public function getTypeIndex($type)
    {
        switch ($type) {
            case Enterprise_TargetRule_Model_Rule::RELATED_PRODUCTS:
                $model = 'related';
                break;

            case Enterprise_TargetRule_Model_Rule::UP_SELLS:
                $model = 'upsell';
                break;

            case Enterprise_TargetRule_Model_Rule::CROSS_SELLS:
                $model = 'crosssell';
                break;

            default:
                Mage::throwException(
                    Mage::helper('enterprise_targetrule')->__('Undefined Catalog Product List Type')
                );
        }

        return Mage::getResourceSingleton('enterprise_targetrule/index_' . $model);
    }

    /**
     * Retrieve array of defined product list type id
     *
     * @return array
     */
    public function getTypeIds()
    {
        return array(
            Enterprise_TargetRule_Model_Rule::RELATED_PRODUCTS,
            Enterprise_TargetRule_Model_Rule::UP_SELLS,
            Enterprise_TargetRule_Model_Rule::CROSS_SELLS
        );
    }

    /**
     * Retrieve product Ids
     *
     * @param Enterprise_TargetRule_Model_Index $object
     * @return array
     */
    public function getProductIds($object)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'customer_segment_id')
            ->where('type_id = :type_id')
            ->where('entity_id = :entity_id')
            ->where('store_id = :store_id')
            ->where('customer_group_id = :customer_group_id');

        $rotationMode = $this->_factory->getHelper('enterprise_targetrule')->getRotationMode($object->getType());

        $segmentsIds = array_merge(array(0), $this->_getSegmentsIdsFromCurrentCustomer());
        $bind = array(
            ':type_id'              => $object->getType(),
            ':entity_id'            => $object->getProduct()->getEntityId(),
            ':store_id'             => $object->getStoreId(),
            ':customer_group_id'    => $object->getCustomerGroupId()
        );

        $segmentsList = $adapter->fetchAll($select, $bind);

        $foundSegmentIndexes = array();
        foreach ($segmentsList as $segment) {
            $foundSegmentIndexes[] = $segment['customer_segment_id'];
        }

        $productIds = array();
        foreach ($segmentsIds as $segmentId) {
            if (in_array($segmentId, $foundSegmentIndexes)) {
                $productIds = array_merge($productIds,
                    $this->getTypeIndex($object->getType())->loadProductIdsBySegmentId($object, $segmentId));
            } else {
                $matchedProductIds = $this->_matchProductIdsBySegmentId($object, $segmentId);
                $productIds = array_merge($matchedProductIds, $productIds);
                $this->getTypeIndex($object->getType())
                    ->saveResultForCustomerSegments($object, $segmentId, $matchedProductIds);
                $this->saveFlag($object, $segmentId);
            }
        }
        $productIds = array_diff(array_unique($productIds), $object->getExcludeProductIds());

        if ($rotationMode == Enterprise_TargetRule_Model_Rule::ROTATION_SHUFFLE) {
             shuffle($productIds);
        }
        return $productIds;
    }

    /**
     * Match, save and return applicable product ids by segmentId object
     *
     * @param Enterprise_TargetRule_Model_Index $object
     * @param string $segmentId
     * @return array
     */
    protected function _matchProductIdsBySegmentId($object, $segmentId)
    {
        $limit = $object->getLimit() + $this->getOverfillLimit();
        $productIds = array();
        $ruleCollection = $object->getRuleCollection();
        if (Mage::helper('enterprise_customersegment')->isEnabled()) {
            $ruleCollection->addSegmentFilter($segmentId);
        }

        foreach ($ruleCollection as $rule) {
            /* @var $rule Enterprise_TargetRule_Model_Rule */
            if (count($productIds) >= $limit) {
                break;
            }
            if (!$rule->checkDateForStore($object->getStoreId())) {
                continue;
            }
            $excludeProductIds = array_merge(array($object->getProduct()->getEntityId()), $productIds);
            $resultIds = $this->_getProductIdsByRule($rule, $object, $rule->getPositionsLimit(), $excludeProductIds);
            $productIds = array_merge($productIds, $resultIds);
        }
        return $productIds;
    }

    /**
     * Match, save and return applicable product ids by index object
     *
     * @param Enterprise_TargetRule_Model_Index $object
     * @return array
     * @deprecated after 1.12.0.0
     */
    protected function _matchProductIds($object)
    {
        $limit      = $object->getLimit() + $this->getOverfillLimit();
        $productIds = $object->getExcludeProductIds();
        $ruleCollection = $object->getRuleCollection();
        foreach ($ruleCollection as $rule) {
            /* @var $rule Enterprise_TargetRule_Model_Rule */
            if (count($productIds) >= $limit) {
                break;
            }
            if (!$rule->checkDateForStore($object->getStoreId())) {
                continue;
            }

            $resultIds = $this->_getProductIdsByRule($rule, $object, $rule->getPositionsLimit(), $productIds);
            $productIds = array_merge($productIds, $resultIds);
        }

        return array_diff($productIds, $object->getExcludeProductIds());
    }

    /**
     * Retrieve found product ids by Rule action conditions
     * If rule has cached select - get it
     *
     * @param Enterprise_TargetRule_Model_Rule $rule
     * @param Enterprise_TargetRule_Model_Index $object
     * @param int $limit
     * @param array $excludeProductIds
     * @return mixed
     */
    protected function _getProductIdsByRule($rule, $object, $limit, $excludeProductIds = array())
    {
        $rule->afterLoad();

        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId($object->getStoreId())
            ->addPriceData($object->getCustomerGroupId());
        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInCatalogFilterToCollection($collection);

        $actionSelect = $rule->getActionSelect();
        $actionBind   = $rule->getActionSelectBind();

        if (is_null($actionSelect)) {
            $actionBind   = array();
            $actionSelect = $rule->getActions()->getConditionForCollection($collection, $object, $actionBind);
            $rule->setActionSelect((string)$actionSelect)
                ->setActionSelectBind($actionBind)
                ->save();
        }

        if ($actionSelect) {
            $collection->getSelect()->where($actionSelect);
        }
        if ($excludeProductIds) {
            $collection->addFieldToFilter('entity_id', array('nin' => $excludeProductIds));
        }

        $select = $collection->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('entity_id', 'e');
        $select->limit($limit);

        $bind   = $this->_prepareRuleActionSelectBind($object, $actionBind);
        $result = $this->_getReadAdapter()->fetchCol($select, $bind);

        return $result;
    }

    /**
     * Prepare bind array for product select
     *
     * @param Enterprise_TargetRule_Model_Index $object
     * @param array $actionBind
     * @return array
     */
    protected function _prepareRuleActionSelectBind($object, $actionBind)
    {
        $bind = array();
        if (!is_array($actionBind)) {
            $actionBind = array();
        }

        foreach ($actionBind as $bindData) {
            if (!is_array($bindData) || !array_key_exists('bind', $bindData) || !array_key_exists('field', $bindData)) {
                continue;
            }
            $k = $bindData['bind'];
            $v = $object->getProduct()->getDataUsingMethod($bindData['field']);

            if (!empty($bindData['callback'])) {
                $callbacks = $bindData['callback'];
                if (!is_array($callbacks)) {
                    $callbacks = array($callbacks);
                }
                foreach ($callbacks as $callback) {
                    if (is_array($callback)) {
                        $v = $this->$callback[0]($v, $callback[1]);
                    } else {
                        $v = $this->$callback($v);
                    }
                }
            }

            if (is_array($v)) {
                $v = join(',', $v);
            }

            $bind[$k] = $v;
        }

        return $bind;
    }

    /**
     * Save index flag by index object data
     *
     * @param Enterprise_TargetRule_Model_Index $object
     * @return Enterprise_TargetRule_Model_Resource_Index
     */
    public function saveFlag($object, $segmentId = null)
    {
        $data = array(
            'type_id'             => $object->getType(),
            'entity_id'           => $object->getProduct()->getEntityId(),
            'store_id'            => $object->getStoreId(),
            'customer_group_id'   => $object->getCustomerGroupId(),
            'customer_segment_id' => $segmentId,
            'flag'                => 1
        );

        $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $data);

        return $this;
    }

    /**
     * Retrieve new SELECT instance (used Read Adapter)
     *
     * @return Varien_Db_Select
     */
    public function select()
    {
        return $this->_getReadAdapter()->select();
    }

    /**
     * Retrieve SQL condition fragment by field, operator and value
     *
     * @param string $field
     * @param string $operator
     * @param int|string|array $value
     * @return string
     */
    public function getOperatorCondition($field, $operator, $value)
    {
        return Mage::getResourceModel('rule/rule_condition_sqlBuilder')
            ->getOperatorCondition($field, $operator, $value);
    }

    /**
     * Retrieve SQL condition fragment by field, operator and binded value
     * also modify bind array
     *
     * @param string $field
     * @param mixed $attribute
     * @param string $operator
     * @param array $bind
     * @param array $callback
     * @return string
     */
    public function getOperatorBindCondition($field, $attribute, $operator, &$bind, $callback = array())
    {
        $field = $this->_getReadAdapter()->quoteIdentifier($field);
        $bindName = ':targetrule_bind_' . $this->_bindIncrement ++;
        switch ($operator) {
            case '!=':
            case '>=':
            case '<=':
            case '>':
            case '<':
                $condition = sprintf('%s%s%s', $field, $operator, $bindName);
                break;
            case '{}':
                $condition  = sprintf('%s LIKE %s', $field, $bindName);
                $callback[] = 'bindLikeValue';
                break;

            case '!{}':
                $condition  = sprintf('%s NOT LIKE %s', $field, $bindName);
                $callback[] = 'bindLikeValue';
                break;

            case '[]':
            case '()':
                $condition = $this->getReadConnection()
                    ->prepareSqlCondition($bindName, array('finset' => new Zend_Db_Expr($field)));
                break;

            case '![]':
            case '!()':
                $condition = $this->getReadConnection()
                    ->prepareSqlCondition($bindName, array('finset' => new Zend_Db_Expr($field)));
                $condition = sprintf('NOT (%s)', $condition);
                break;

            default:
                $condition = sprintf('%s=%s', $field, $bindName);
                break;
        }

        $bind[] = array(
            'bind'      => $bindName,
            'field'     => $attribute,
            'callback'  => $callback
        );

        return $condition;
    }

    /**
     * Prepare bind value for LIKE condition
     * Callback method
     *
     * @param string $value
     * @return string
     */
    public function bindLikeValue($value)
    {
        return '%' . $value . '%';
    }

    /**
     * Prepare bind array of ids from string or array
     *
     * @param string|int|array $value
     * @return array
     */
    public function bindArrayOfIds($value)
    {
        if (!is_array($value)) {
            $value = explode(',', $value);
        }

        $value = array_map('trim', $value);
        $value = array_filter($value, 'is_numeric');

        return $value;
    }

    /**
     * Prepare bind value (percent of value)
     *
     * @param float $value
     * @param int $percent
     * @return float
     */
    public function bindPercentOf($value, $percent)
    {
        return round($value * ($percent / 100), 4);
    }

    /**
     * Remove index data from index tables
     *
     * @param int|null $typeId
     * @param Mage_Core_Model_Store|int|array|null $store
     * @return Enterprise_TargetRule_Model_Resource_Index
     */
    public function cleanIndex($typeId = null, $store = null)
    {
        $adapter = $this->_getWriteAdapter();

        if ($store instanceof Mage_Core_Model_Store) {
            $store = $store->getId();
        }

        if (is_null($typeId)) {
            foreach ($this->getTypeIds() as $typeId) {
                $this->getTypeIndex($typeId)->cleanIndex($store);
            }

            $where = (is_null($store)) ? '' : array('store_id IN(?)' => $store);
            $adapter->delete($this->getMainTable(), $where);
        } else {
            $where = array('type_id=?' => $typeId);
            if (!is_null($store)) {
                $where['store_id IN(?)'] = $store;
            }
            $adapter->delete($this->getMainTable(), $where);
            $this->getTypeIndex($typeId)->cleanIndex($store);
        }

        return $this;
    }

    /**
     * Remove index by product ids and type
     *
     * @param int|array|Varien_Db_Select $productIds
     * @param int $typeId
     * @return Enterprise_TargetRule_Model_Resource_Index
     */
    public function removeIndexByProductIds($productIds, $typeId = null)
    {
        $adapter = $this->_getWriteAdapter();

        $where = array(
            'entity_id IN(?)'   => $productIds
        );

        if (is_null($typeId)) {
            foreach ($this->getTypeIds() as $typeId) {
                $this->getTypeIndex($typeId)->removeIndex($productIds);
            }
        } else {
            $this->getTypeIndex($typeId)->removeIndex($productIds);
            $where['type_id=?'] = $typeId;
        }

        $adapter->delete($this->getMainTable(), $where);

        return $this;
    }

    /**
     * Remove target rule matched product index data by product id or/and rule id
     *
     * @param int $productId
     * @param int $ruleId
     *
     * @return Enterprise_TargetRule_Model_Resource_Index
     */
    public function removeProductIndex($productId = null, $ruleId = null)
    {
        /** @var $targetRule Enterprise_TargetRule_Model_Resource_Rule */
        $targetRule = Mage::getResourceSingleton('enterprise_targetrule/rule');
        $targetRule->unbindRuleFromEntity($ruleId, $productId, 'product');

        return $this;
    }

    /**
     * Bind target rule to specified product
     *
     * @param int $ruleId
     * @param int $productId
     * @param int $storeId
     *
     * @return Enterprise_TargetRule_Model_Resource_Index
     */
    public function saveProductIndex($ruleId, $productId, $storeId)
    {
        /** @var $targetRule Enterprise_TargetRule_Model_Resource_Rule */
        $targetRule = Mage::getResourceSingleton('enterprise_targetrule/rule');
        $targetRule->bindRuleToEntity($ruleId, $productId, 'product', false);

        return $this;
    }

    /**
     * Adds order by random to select object
     *
     * @param Varien_Db_Select $select
     * @param null $field
     * @return Enterprise_TargetRule_Model_Resource_Index
     */
    public function orderRand(Varien_Db_Select $select, $field = null)
    {
        $this->_getReadAdapter()->orderRand($select, $field);
        return $this;
    }

    /**
     * Get SegmentsIds From Current Customer
     *
     * @return array
     */
    protected function _getSegmentsIdsFromCurrentCustomer()
    {
        $segmentIds = array();
        if (Mage::helper('enterprise_customersegment')->isEnabled()) {
            $customer = Mage::registry('segment_customer');
            if (!$customer) {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
            }
            $websiteId = Mage::app()->getWebsite()->getId();

            if (!$customer->getId()) {
                $allSegmentIds = Mage::getSingleton('customer/session')->getCustomerSegmentIds();
                if ((is_array($allSegmentIds) && isset($allSegmentIds[$websiteId]))) {
                    $segmentIds = $allSegmentIds[$websiteId];
                }
            } else {
                $segmentIds = Mage::getSingleton('enterprise_customersegment/customer')
                    ->getCustomerSegmentIdsForWebsite($customer->getId(), $websiteId);
            }

            if (count($segmentIds)) {
                $segmentIds = Mage::getResourceModel('enterprise_customersegment/segment')
                    ->getActiveSegmentsByIds($segmentIds);
            }
        }
        return $segmentIds;
    }
}

