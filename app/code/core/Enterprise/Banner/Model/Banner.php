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
 * Enter description here ...
 *
 * @method Enterprise_Banner_Model_Resource_Banner _getResource()
 * @method Enterprise_Banner_Model_Resource_Banner getResource()
 * @method string getName()
 * @method Enterprise_Banner_Model_Banner setName(string $value)
 * @method int getIsEnabled()
 * @method Enterprise_Banner_Model_Banner setIsEnabled(int $value)
 * @method Enterprise_Banner_Model_Banner setTypes(string $value)
 *
 * @category    Enterprise
 * @package     Enterprise_Banner
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Banner_Model_Banner extends Mage_Core_Model_Abstract
{
    /**
     * Representation value of enabled banner
     *
     */
    const STATUS_ENABLED = 1;

    /**
     * Representation value of disabled banner
     *
     */
    const STATUS_DISABLED  = 0;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'enterprise_banner';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getBanner() in this case
     *
     * @var string
     */
    protected $_eventObject = 'banner';

    /**
     * Store banner contents per store view
     *
     * @var array
     */
    protected $_contents = array();

    /**
     * Initialize cache tag
     */
    public function __construct()
    {
        $this->_cacheTag = 'banner';
        parent::__construct();
    }

    /**
     * Initialize banner model
     */
    protected function _construct()
    {
        $this->_init('enterprise_banner/banner');
    }

    /**
     * Retrieve array of sales rules id's for banner
     *
     * @return array
     */
    public function getRelatedSalesRule()
    {
        if (!$this->getId()) {
            return array();
        }
        $array = $this->getData('related_sales_rule');
        if (is_null($array)) {
            $array = $this->getResource()->getRelatedSalesRule($this->getId());
            $this->setData('related_sales_rule', $array);
        }
        return $array;
    }

    /**
     * Retrieve array of catalog rules id's for banner
     *
     * @return array
     */
    public function getRelatedCatalogRule()
    {
        if (!$this->getId()) {
            return array();
        }
        $array = $this->getData('related_catalog_rule');
        if (is_null($array)) {
            $array = $this->getResource()->getRelatedCatalogRule($this->getId());
            $this->setData('related_catalog_rule', $array);
        }
        return $array;
    }

    /**
     * Get banner content for specific store
     *
     * @param   Mage_Core_Model_Store|int|string $store
     * @return  string|bool
     */
    public function getStoreContent($store = null)
    {
        $storeId = Mage::app()->getStore($store)->getId();
        if ($this->hasStoreContents()) {
            $contents = $this->_getData('store_contents');
            if (isset($contents[$storeId])) {
                return $contents[$storeId];
            } elseif ($contents[0]) {
                return $contents[0];
            }
            return false;
        } elseif (!isset($this->_contents[$storeId])) {
            $this->_contents[$storeId] = $this->_getResource()->getStoreContent($this->getId(), $storeId);
        }
        return $this->_contents[$storeId];
    }

    /**
     * Get all existing banner contents
     *
     * @return array
     */
    public function getStoreContents()
    {
        if (!$this->hasStoreContents()) {
            $contents = $this->_getResource()->getStoreContents($this->getId());
            $this->setStoreContents($contents);
        }
        return $this->_getData('store_contents');
    }

    /**
     * Get banners ids by catalog rule id
     *
     * @param int $ruleId
     * @return array
     */
    public function getRelatedBannersByCatalogRuleId($ruleId)
    {
        if (!$this->hasRelatedCatalogRuleBanners()) {
            $banners = $this->_getResource()->getRelatedBannersByCatalogRuleId($ruleId);
            $this->setRelatedCatalogRuleBanners($banners);
        }
        return $this->_getData('related_catalog_rule_banners');
    }

    /**
     * Get banners ids by sales rule id
     *
     * @param int $ruleId
     * @return array
     */
    public function getRelatedBannersBySalesRuleId($ruleId)
    {
        if (!$this->hasRelatedSalesRuleBanners()) {
            $banners = $this->_getResource()->getRelatedBannersBySalesRuleId($ruleId);
            $this->setRelatedSalesRuleBanners($banners);
        }
        return $this->_getData('related_sales_rule_banners');
    }

    /**
     * Save banner content, bind banner to catalog and sales rules after banner save
     *
     * @return Enterprise_Banner_Model_Banner
     */
    protected function _afterSave()
    {
        if ($this->hasStoreContents()) {
            $this->_getResource()->saveStoreContents(
                $this->getId(),
                $this->getStoreContents(),
                $this->getStoreContentsNotUse()
            );
        }
        if ($this->hasBannerCatalogRules()) {
            $this->_getResource()->saveCatalogRules(
                $this->getId(),
                $this->getBannerCatalogRules()
            );
        }
        if ($this->hasBannerSalesRules()) {
            $this->_getResource()->saveSalesRules(
                $this->getId(),
                $this->getBannerSalesRules()
            );
        }
        if ($this->hasCustomerSegmentIds()) {
            $this->_getResource()->saveCustomerSegments(
                $this->getId(),
                $this->getCustomerSegmentIds()
            );
        }
        return parent::_afterSave();
    }

    /**
     * Validate some data before saving
     * @return Enterprise_Banner_Model_Banner
     */
    protected function _beforeSave()
    {
        if ('' == trim($this->getName())) {
            Mage::throwException(Mage::helper('enterprise_banner')->__('Name must not be empty.'));
        }
        $bannerContents = $this->getStoreContents();
        $flag = false;
        foreach ($bannerContents as $storeId => $content) {
            if ('' != trim($content)) {
                $flag = true;
                break;
            }
        }
        if (!$flag) {
            Mage::throwException(
                Mage::helper('enterprise_banner')->__('Please specify default content for at least one store view.')
            );
        }
        return parent::_beforeSave();
    }

    /**
     * Collect store ids in which current banner has content
     *
     * @return array
     */
    public function getStoreIds()
    {
        $contents = $this->getStoreContents();
        if (!$this->hasStoreIds()) {
            $this->setStoreIds(array_keys($contents));
        }
        return $this->_getData('store_ids');
    }

    /**
     * Make types getter always return array
     * @return array
     */
    public function getTypes()
    {
        $types = $this->_getData('types');
        if (is_array($types)) {
            return $types;
        }
        if (empty($types)) {
            $types = array();
        } else {
            $types = explode(',', $types);
        }
        $this->setData('types', $types);
        return $types;
    }
}
