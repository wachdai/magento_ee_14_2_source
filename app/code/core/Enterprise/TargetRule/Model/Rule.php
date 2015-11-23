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
 * TargetRule Rule Model
 *
 * @method Enterprise_TargetRule_Model_Resource_Rule _getResource()
 * @method Enterprise_TargetRule_Model_Resource_Rule getResource()
 * @method string getName()
 * @method Enterprise_TargetRule_Model_Rule setName(string $value)
 * @method string getFromDate()
 * @method Enterprise_TargetRule_Model_Rule setFromDate(string $value)
 * @method string getToDate()
 * @method Enterprise_TargetRule_Model_Rule setToDate(string $value)
 * @method int getIsActive()
 * @method Enterprise_TargetRule_Model_Rule setIsActive(int $value)
 * @method string getConditionsSerialized()
 * @method Enterprise_TargetRule_Model_Rule setConditionsSerialized(string $value)
 * @method string getActionsSerialized()
 * @method Enterprise_TargetRule_Model_Rule setActionsSerialized(string $value)
 * @method Enterprise_TargetRule_Model_Rule setPositionsLimit(int $value)
 * @method int getApplyTo()
 * @method Enterprise_TargetRule_Model_Rule setApplyTo(int $value)
 * @method int getSortOrder()
 * @method Enterprise_TargetRule_Model_Rule setSortOrder(int $value)
 * @method int getUseCustomerSegment()
 * @method Enterprise_TargetRule_Model_Rule setUseCustomerSegment(int $value)
 * @method string getActionSelect()
 * @method Enterprise_TargetRule_Model_Rule setActionSelect(string $value)
 * @method array getCustomerSegmentIds()
 * @method Enterprise_TargetRule_Model_Rule setCustomerSegmentIds(array $ids)
 *
 * @category    Enterprise
 * @package     Enterprise_TargetRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_TargetRule_Model_Rule extends Mage_Rule_Model_Abstract
{
    /**
     * Position behavior selectors
     */
    const BOTH_SELECTED_AND_RULE_BASED  = 0;
    const SELECTED_ONLY                 = 1;
    const RULE_BASED_ONLY               = 2;

    /**
     * Product list types
     */
    const RELATED_PRODUCTS              = 1;
    const UP_SELLS                      = 2;
    const CROSS_SELLS                   = 3;

    /**
     * Shuffle mode by default
     */
    const ROTATION_SHUFFLE              = 0;
    const ROTATION_NONE                 = 1;

    /**
     * Store default product positions limit
     */
    const POSITIONS_DEFAULT_LIMIT       = 20;

    /**
     * Path to default values
     *
     * @deprecated after 1.11.2.0
     */
    const XML_PATH_DEFAULT_VALUES       = 'catalog/enterprise_targetrule/';

    /**
     * Store matched products objects
     *
     * @var array
     */
    protected $_products;

    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $_productIds;

    /**
     * Store flags per store is applicable rule by date
     *
     * @var array
     */
    protected $_checkDateForStore = array();

    /**
     * Set resource model
     */
    protected function _construct()
    {
        $this->_init('enterprise_targetrule/rule');
    }

    /**
     * Reset action cached select if actions conditions has changed
     *
     * @return Enterprise_TargetRule_Model_Rule
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if ($this->dataHasChangedFor('actions_serialized')) {
            $this->setData('action_select', null);
            $this->setData('action_select_bind', null);
        }

        return $this;
    }

    /**
     * Getter for rule combine conditions instance
     *
     * @return Enterprise_TargetRule_Model_Rule_Condition_Combine
     */
    public function getConditionsInstance()
    {
        return Mage::getModel('enterprise_targetrule/rule_condition_combine');
    }

    /**
     * Getter for rule actions collection instance
     *
     * @return Enterprise_TargetRule_Model_Actions_Condition_Combine
     */
    public function getActionsInstance()
    {
        return Mage::getModel('enterprise_targetrule/actions_condition_combine');
    }

    /**
     * Get options for `Apply to` field
     *
     * @param bool $withEmpty
     *
     * @return array
     */
    public function getAppliesToOptions($withEmpty = false)
    {
        $result = array();
        if ($withEmpty) {
            $result[''] = Mage::helper('adminhtml')->__('-- Please Select --');
        }
        $result[Enterprise_TargetRule_Model_Rule::RELATED_PRODUCTS]
            = Mage::helper('enterprise_targetrule')->__('Related Products');
        $result[Enterprise_TargetRule_Model_Rule::UP_SELLS]
            = Mage::helper('enterprise_targetrule')->__('Up-sells');
        $result[Enterprise_TargetRule_Model_Rule::CROSS_SELLS]
            = Mage::helper('enterprise_targetrule')->__('Cross-sells');

        return $result;
    }

    /**
     * Retrieve array of product objects which are matched by rule
     *
     * @param $onlyId bool
     *
     * @return Enterprise_TargetRule_Model_Rule
     */
    public function prepareMatchingProducts($onlyId = false)
    {
        $productCollection = Mage::getResourceModel('catalog/product_collection');

        if (!$onlyId && !is_null($this->_productIds)) {
            $productCollection->addIdFilter($this->_productIds);
            $this->_products = $productCollection->getItems();
        } else {
            $this->setCollectedAttributes(array());
            $this->getConditions()->collectValidatedAttributes($productCollection);

            $this->_productIds = array();
            $this->_products   = array();
            Mage::getSingleton('core/resource_iterator')->walk(
                $productCollection->getSelect(),
                array(
                    array($this, 'callbackValidateProduct')
                ),
                array(
                    'attributes'    => $this->getCollectedAttributes(),
                    'product'       => Mage::getModel('catalog/product'),
                    'onlyId'        => (bool) $onlyId
                )
            );
        }

        return $this;
    }

    /**
     * Retrieve array of product objects which are matched by rule
     *
     * @deprecated
     *
     * @return array
     */
    public function getMatchingProducts()
    {
        if (is_null($this->_products)) {
            $this->prepareMatchingProducts();
        }

        return $this->_products;
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        if ($this->getConditions()->validate($product)) {
            $this->_productIds[] = $product->getId();
            if (!key_exists('onlyId', $args) || !$args['onlyId']) {
                $this->_products[] = $product;
            }
        }
    }

    /**
     * Retrieve array of product Ids that are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if (is_null($this->_productIds)) {
            $this->getMatchingProducts();
        }

        return $this->_productIds;
    }

    /**
     * Check if rule is applicable by date for specified store
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function checkDateForStore($storeId)
    {
        if (!isset($this->_checkDateForStore[$storeId])) {
            $this->_checkDateForStore[$storeId] = Mage::app()->getLocale()
                ->isStoreDateInInterval(null, $this->getFromDate(), $this->getToDate());
        }
        return $this->_checkDateForStore[$storeId];
    }

    /**
     * Get product positions for current rule
     *
     * @return int if positions limit is not set, then default limit will be returned
     */
    public function getPositionsLimit()
    {
        $limit = $this->getData('positions_limit');
        if (!$limit) {
            $limit = 20;
        }

        return $limit;
    }

    /**
     * Retrieve Action select bind array
     *
     * @return mixed
     */
    public function getActionSelectBind()
    {
        $bind = $this->getData('action_select_bind');
        if ($bind && is_string($bind)) {
            $bind = unserialize($bind);
        }

        return $bind;
    }

    /**
     * Set action select bind array or serialized string
     *
     * @param array|string $bind
     *
     * @return Enterprise_TargetRule_Model_Rule
     */
    public function setActionSelectBind($bind)
    {
        if (is_array($bind)) {
            $bind = serialize($bind);
        }
        return $this->setData('action_select_bind', $bind);
    }

    /**
     * Validate rule data
     *
     * @param Varien_Object $object
     *
     * @return bool|array - return true if validation passed successfully. Array with errors description otherwise
     */
    public function validateData(Varien_Object $object)
    {
        $result = parent::validateData($object);

        if (!is_array($result)) {
            $result = array();
        }

        $validator = new Zend_Validate_Regex(array('pattern' => '/^[a-z][a-z0-9_\/]{1,255}$/'));
        $actionArgsList = $object->getData('rule');
        if (is_array($actionArgsList) && isset($actionArgsList['actions'])) {
            foreach ($actionArgsList['actions'] as $actionArgsIndex => $actionArgs) {
                if (1 === $actionArgsIndex) {
                    continue;
                }
                if (!$validator->isValid($actionArgs['type'])
                    || (isset($actionArgs['attribute']) && !$validator->isValid($actionArgs['attribute']))
                ) {
                    $result[] = Mage::helper('enterprise_targetrule')->__('Attribute code is invalid. Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.');
                }
            }
        }

        return !empty($result) ? $result : true;
    }

    /**
     * Retrieve Customer Segment Relations
     *
     * @deprecated after 1.11.2.0
     *
     * @return array
     */
    public function getCustomerSegmentRelations()
    {
        return array();
    }

    /**
     * Set customer segment relations
     *
     * @deprecated after 1.11.2.0
     *
     * @param array|string $relations
     *
     * @return Enterprise_TargetRule_Model_Rule
     */
    public function setCustomerSegmentRelations($relations)
    {
        return $this;
    }

    /**
     * Callback function which called after transaction commit in resource model
     *
     * @return Enterprise_TargetRule_Model_Rule
     */
    public function afterCommitCallback()
    {
        parent::afterCommitCallback();

        /** @var \Mage_Index_Model_Indexer $indexer */
        $indexer = Mage::getSingleton('index/indexer');

        $typeId = (!$this->isObjectNew() && $this->getOrigData('apply_to') != $this->getData('apply_to'))
            ? null
            : $this->getData('apply_to');

        $indexer->processEntityAction(
            new Varien_Object(array('type_id' => $typeId)),
            Enterprise_TargetRule_Model_Index::ENTITY_TARGETRULE,
            Enterprise_TargetRule_Model_Index::EVENT_TYPE_CLEAN_TARGETRULES
        );
        return $this;
    }

    /**
     * Callback function which called after transaction commit in resource model
     *
     * @return Enterprise_TargetRule_Model_Rule
     */
    public function _afterDeleteCommit()
    {
        parent::_afterDeleteCommit();

        /** @var \Mage_Index_Model_Indexer $indexer */
        $indexer = Mage::getSingleton('index/indexer');
        $indexer->processEntityAction(
            new Varien_Object(array('type_id' => $this->getData('apply_to'))),
            Enterprise_TargetRule_Model_Index::ENTITY_TARGETRULE,
            Enterprise_TargetRule_Model_Index::EVENT_TYPE_CLEAN_TARGETRULES
        );
    }
}
