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
 * TargetRule Product Index by Rule Product List Type Model
 *
 * @method Enterprise_TargetRule_Model_Resource_Index getResource()
 * @method Enterprise_TargetRule_Model_Index setEntityId(int $value)
 * @method int getTypeId()
 * @method Enterprise_TargetRule_Model_Index setTypeId(int $value)
 * @method int getFlag()
 * @method Enterprise_TargetRule_Model_Index setFlag(int $value)
 *
 * @category    Enterprise
 * @package     Enterprise_TargetRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_TargetRule_Model_Index extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Reindex products target-rules event type
     */
    const EVENT_TYPE_REINDEX_PRODUCTS = 'reindex_targetrules';

    /**
     * Clean target-rules event type
     */
    const EVENT_TYPE_CLEAN_TARGETRULES = 'clean_targetrule_index';

    /**
     * Product entity for indexers
     */
    const ENTITY_PRODUCT = 'targetrule_product';

    /**
     * Target-rule entity for indexers
     */
    const ENTITY_TARGETRULE = 'targetrule_entity';

    /**
     * Matched entities
     *
     * @var array
     */
    protected $_matchedEntities = array(
        self::ENTITY_PRODUCT => array(self::EVENT_TYPE_REINDEX_PRODUCTS),
        self::ENTITY_TARGETRULE => array(self::EVENT_TYPE_CLEAN_TARGETRULES)
    );

    /**
     * Whether the indexer should be displayed on process/list page
     *
     * @var bool
     */
    protected $_isVisible = false;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_targetrule/index');
    }

    /**
     * Retrieve resource instance
     *
     * @return Enterprise_TargetRule_Model_Mysql4_Index
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Set Catalog Product List identifier
     *
     * @param int $type
     * @return Enterprise_TargetRule_Model_Index
     */
    public function setType($type)
    {
        return $this->setData('type', $type);
    }

    /**
     * Retrieve Catalog Product List identifier
     *
     * @throws Mage_Core_Exception
     * @return int
     */
    public function getType()
    {
        $type = $this->getData('type');
        if (is_null($type)) {
            Mage::throwException(
                Mage::helper('enterprise_targetrule')->__('Undefined Catalog Product List Type')
            );
        }
        return $type;
    }

    /**
     * Set store scope
     *
     * @param int $storeId
     * @return Enterprise_TargetRule_Model_Index
     */
    public function setStoreId($storeId)
    {
        return $this->setData('store_id', $storeId);
    }

    /**
     * Retrieve store identifier scope
     *
     * @return int
     */
    public function getStoreId()
    {
        $storeId = $this->getData('store_id');
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        return $storeId;
    }

    /**
     * Set customer group identifier
     *
     * @param int $customerGroupId
     * @return Enterprise_TargetRule_Model_Index
     */
    public function setCustomerGroupId($customerGroupId)
    {
        return $this->setData('customer_group_id', $customerGroupId);
    }

    /**
     * Retrieve customer group identifier
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        $customerGroupId = $this->getData('customer_group_id');
        if (is_null($customerGroupId)) {
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
        return $customerGroupId;
    }

    /**
     * Set result limit
     *
     * @param int $limit
     * @return Enterprise_TargetRule_Model_Index
     */
    public function setLimit($limit)
    {
        return $this->setData('limit', $limit);
    }

    /**
     * Retrieve result limit
     *
     * @return int
     */
    public function getLimit()
    {
        $limit = $this->getData('limit');
        if (is_null($limit)) {
            $limit = Mage::helper('enterprise_targetrule')->getMaximumNumberOfProduct($this->getType());
        }
        return $limit;
    }

    /**
     * Set Product data object
     *
     * @param Varien_Object $product
     * @return Enterprise_TargetRule_Model_Index
     */
    public function setProduct(Varien_Object $product)
    {
        return $this->setData('product', $product);
    }

    /**
     * Retrieve Product data object
     *
     * @throws Mage_Core_Exception
     * @return Varien_Object
     */
    public function getProduct()
    {
        $product = $this->getData('product');
        if (!$product instanceof Varien_Object) {
            Mage::throwException(Mage::helper('enterprise_targetrule')->__('Please define product data object'));
        }
        return $product;
    }

    /**
     * Set product ids list be excluded
     *
     * @param int|array $productIds
     * @return Enterprise_TargetRule_Model_Index
     */
    public function setExcludeProductIds($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }
        return $this->setData('exclude_product_ids', $productIds);
    }

    /**
     * Retrieve Product Ids which must be excluded
     *
     * @return array
     */
    public function getExcludeProductIds()
    {
        $productIds = $this->getData('exclude_product_ids');
        if (!is_array($productIds)) {
            $productIds = array();
        }
        return $productIds;
    }

    /**
     * Retrieve related product Ids
     *
     * @return array
     */
    public function getProductIds()
    {
        return $this->_getResource()->getProductIds($this);
    }

    /**
     * Retrieve Rule collection by type and product
     *
     * @return Enterprise_TargetRule_Model_Mysql4_Rule_Collection
     */
    public function getRuleCollection()
    {
        /* @var $collection Enterprise_TargetRule_Model_Mysql4_Rule_Collection */
        $collection = Mage::getResourceModel('enterprise_targetrule/rule_collection');
        $collection->addApplyToFilter($this->getType())
            ->addProductFilter($this->getProduct()->getId())
            ->addIsActiveFilter()
            ->setPriorityOrder()
            ->setFlag('do_not_run_after_load', true);

        return $collection;
    }

    /**
     * Retrieve SELECT instance for conditions
     *
     * @return Varien_Db_Select
     */
    public function select()
    {
        return $this->_getResource()->select();
    }

    /**
     * Run processing by cron
     * Check store datetime and every day per store clean index cache
     *
     */
    public function cron()
    {
        $websites = Mage::app()->getWebsites();

        /** @var $indexer Mage_Index_Model_Indexer */
        $indexer = Mage::getSingleton('index/indexer');

        foreach ($websites as $website) {
            /* @var $website Mage_Core_Model_Website */
            $store = $website->getDefaultStore();
            $date  = Mage::app()->getLocale()->storeDate($store);
            if ($date->equals(0, Zend_Date::HOUR)) {
                $indexer->logEvent(
                    new Varien_Object(array('type_id' => null, 'store' => $website->getStoreIds())),
                    self::ENTITY_TARGETRULE,
                    self::EVENT_TYPE_CLEAN_TARGETRULES
                );
            }
        }
        $indexer->indexEvents(
            self::ENTITY_TARGETRULE,
            self::EVENT_TYPE_CLEAN_TARGETRULES
        );
    }

    /**
     * Get Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('enterprise_targetrule')->__('Target Rules');
    }

    /**
     * Register indexer required data inside event object
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        switch ($event->getType()) {
            case self::EVENT_TYPE_REINDEX_PRODUCTS:
                switch ($event->getEntity()) {
                    case self::ENTITY_PRODUCT:
                        $event->addNewData('product', $event->getDataObject());
                        break;
                }
                break;
            case self::EVENT_TYPE_CLEAN_TARGETRULES:
                switch ($event->getEntity()) {
                    case self::ENTITY_TARGETRULE:
                        $event->addNewData('params', $event->getDataObject());
                        break;
                }
                break;
        }
    }

    /**
     * Process event based on event state data
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        switch ($event->getType()) {
            case self::EVENT_TYPE_REINDEX_PRODUCTS:
                switch ($event->getEntity()) {
                    case self::ENTITY_PRODUCT:
                        $data = $event->getNewData();
                        if (!empty($data['product'])) {
                            $this->_reindex($data['product']);
                        }
                        break;
                }
                break;
            case self::EVENT_TYPE_CLEAN_TARGETRULES:
                switch ($event->getEntity()) {
                    case self::ENTITY_TARGETRULE:
                        $data = $event->getNewData();
                        if (!empty($data['params'])) {
                            $params = $data['params'];
                            $this->_cleanIndex($params->getTypeId(), $params->getStore());
                        }
                        break;
                }
                break;
        }
    }

    /**
     * Reindex targetrules
     *
     * @param Varien_Object $product
     * @return Enterprise_TargetRule_Model_Index
     */
    protected function _reindex($product)
    {
        if (!($product instanceof Mage_Catalog_Model_Product)) {
            $product = Mage::getModel('catalog/product')->load($product->getId());
        }

        $indexResource = $this->_getResource();

        // remove old cache index data
        $indexResource->removeIndexByProductIds($product->getId());

        // remove old matched product index
        $indexResource->removeProductIndex($product->getId());

        /** @var $ruleCollection Enterprise_TargetRule_Model_Resource_Rule_Collection */
        $ruleCollection = Mage::getResourceModel('enterprise_targetrule/rule_collection');
        $ruleCollection->removeProductFromRules($product->getId());

        foreach ($ruleCollection as $rule) {
            /** @var $rule Enterprise_TargetRule_Model_Rule */
            if ($rule->validate($product)) {
                $indexResource->saveProductIndex($rule->getId(), $product->getId(), $product->getStoreId());
            }
        }
        return $this;
    }

    /**
     * Remove targetrule's index
     *
     * @param int|null $typeId
     * @param Mage_Core_Model_Store|int|array|null $store
     * @return Enterprise_TargetRule_Model_Index
     */
    protected function _cleanIndex($typeId = null, $store = null)
    {
        $this->_getResource()->cleanIndex($typeId, $store);
        return $this;
    }
}
