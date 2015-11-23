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
 * Banner Widget Block
 *
 * @category   Enterprise
 * @package    Enterprise_Banner
 */
class Enterprise_Banner_Block_Widget_Banner
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{
    /**
     * Display mode "fixed" flag
     *
     */
    const BANNER_WIDGET_DISPLAY_FIXED = 'fixed';

    /**
     * Display mode "salesrule" flag
     *
     */
    const BANNER_WIDGET_DISPLAY_SALESRULE = 'salesrule';

    /**
     * Display mode "catalogrule" flag
     *
     */
    const BANNER_WIDGET_DISPLAY_CATALOGRULE = 'catalogrule';

    /**
     * Rotation mode "series" flag: output one of banners sequentially per visitor session
     *
     */
    const BANNER_WIDGET_RORATE_SERIES = 'series';

    /**
     * Rotation mode "random" flag: output one of banners randomly
     *
     */
    const BANNER_WIDGET_RORATE_RANDOM = 'random';

    /**
     * Rotation mode "shuffle" flag: same as "series" but firstly randomize banners scope
     *
     */
    const BANNER_WIDGET_RORATE_SHUFFLE = 'shuffle';

    /**
     * No rotation: show all banners at once
     *
     */
    const BANNER_WIDGET_RORATE_NONE = '';

    /**
     * Store Banner resource instance
     *
     * @var Enterprise_Banner_Model_Mysql4_Banner
     */
    protected $_bannerResource = null;

    /**
     * Store visitor session instance
     *
     * @var Mage_Core_Model_Session
     */
    protected $_sessionInstance = null;

    /**
     * Store current store ID
     *
     * @var int
     */
    protected $_currentStoreId = null;

    /**
     * Stores information about process of selecting banners to render
     * E.g. list of banner ids for this user, rendered banner id(s) and so on.
     */
    protected $_renderedParams = array();

    /**
     * Define default template, load Banner resource, get session instance and set current store ID
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_bannerResource  = Mage::getResourceSingleton('enterprise_banner/banner');
        $this->_currentStoreId  = Mage::app()->getStore()->getId();
        $this->_sessionInstance = Mage::getSingleton('core/session');
    }

    /**
     * Set default display mode if its not set
     *
     * @return string
     */
    public function getDisplayMode()
    {
        if (!$this->_getData('display_mode')) {
            $this->setData('display_mode', self::BANNER_WIDGET_DISPLAY_FIXED);
        }
        return $this->_getData('display_mode');
    }

    /**
     * Retrive converted to an array and filtered parameter "banner_ids"
     *
     * @return array
     */
    public function getBannerIds()
    {
        if (!$this->_getData('banner_ids')) {
            $this->setData('banner_ids', array(0));
        } elseif (is_string($this->_getData('banner_ids'))) {
            $bannerIds = explode(',', $this->_getData('banner_ids'));
            foreach ($bannerIds as $_key => $_id) {
                $bannerIds[$_key] = (int)trim($_id);
            }
            $this->setData('banner_ids', $bannerIds);
            $enabledBannerIds = $this->_bannerResource->getExistingBannerIdsBySpecifiedIds($bannerIds);
            $this->setData('enabled_banner_ids', !empty($enabledBannerIds)? $enabledBannerIds : array(0));
        }

        return $this->_getData('banner_ids');
    }

    /**
     * Retrive array of enabled banners filtered from available banners
     *
     * @return array
     */
    public function getEnabledBannerIds()
    {
        if (!$this->hasData('enabled_banner_ids')) {
            $this->getBannerIds();
        }

        return $this->_getData('enabled_banner_ids');
    }

    /**
     * Retrieve right rotation mode or return null
     *
     * @return string|null
     */
    public function getRotate()
    {
        if (!$this->_getData('rotate') || ($this->_getData('rotate') != self::BANNER_WIDGET_RORATE_RANDOM &&
                                           $this->_getData('rotate') != self::BANNER_WIDGET_RORATE_SERIES &&
                                           $this->_getData('rotate') != self::BANNER_WIDGET_RORATE_SHUFFLE
                                           )) {
            $this->setData('rotate', null);
        }
        return $this->_getData('rotate');
    }

    /**
     * Set unique id of widget instance if its not set
     *
     * @return string
     */
    public function getUniqueId()
    {
        if (!$this->_getData('unique_id')) {
            $this->setData('unique_id', md5(implode('-', $this->getBannerIds())));
        }
        return $this->_getData('unique_id');
    }

    /**
     * Get banner(s) content to display
     *
     * @return array
     */
    public function getBannersContent()
    {
        $aplliedRules = null;
        $segmentIds = array();
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

        $this->_bannerResource->filterByTypes($this->getTypes());

        // Choose display mode
        switch ($this->getDisplayMode()) {

            case self::BANNER_WIDGET_DISPLAY_SALESRULE :
                if (Mage::getSingleton('checkout/session')->getQuoteId()) {
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    $aplliedRules = explode(',', $quote->getAppliedRuleIds());
                }
                $bannerIds = $this->_bannerResource->getSalesRuleRelatedBannerIds($segmentIds, $aplliedRules, false);
                $this->setBannerIds($bannerIds);
                $bannerIds = $this->_filterActive($bannerIds);
                $bannersContent = $this->_getBannersContent(!empty($bannerIds)? $bannerIds : array(0), $segmentIds);
                break;

            case self::BANNER_WIDGET_DISPLAY_CATALOGRULE :
                $bannerIds = $this->_bannerResource->getCatalogRuleRelatedBannerIds(
                    Mage::app()->getWebsite()->getId(),
                    Mage::getSingleton('customer/session')->getCustomerGroupId(),
                    $segmentIds,
                    false
                );
                $this->setBannerIds($bannerIds);
                $bannerIds = $this->_filterActive($bannerIds);
                $bannersContent = $this->_getBannersContent(!empty($bannerIds)? $bannerIds : array(0), $segmentIds);
                break;

            case self::BANNER_WIDGET_DISPLAY_FIXED :
            default :
                $bannersContent = $this->_getBannersContent($this->getEnabledBannerIds(), $segmentIds);
                break;
        }

        // Unset types filter from resource
        $this->_bannerResource->filterByTypes();

        // Filtering directives
        /** @var $helper Mage_Cms_Helper_Data */
        $helper = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();
        foreach ($bannersContent as $bannerId => $content) {
            $bannersContent[$bannerId] = $processor->filter($content);
        }

        return $bannersContent;
    }

    /**
     * Filter active banners
     *
     * @param array $bannerIds
     * @return array
     */
    protected function _filterActive($bannerIds)
    {
        return $this->_bannerResource->getExistingBannerIdsBySpecifiedIds($bannerIds);
    }

    /**
     * Retrieves suggested params for rendering the banner - array with following keys:
     * - 'bannersSelected' - array of banner ids suggested to render (null if not set)
     * - 'bannersSequence' - array of banner ids already shown to user (null if not set)
     * These parameters are set by cache when it needs to render some specific banners. However,
     * if parameters are not valid - they must be ignored, because block has fresh and up-to-date values
     * to check the banners that can be shown to user.
     *
     * @return array
     */
    public function getSuggestedParams()
    {
        $params = $this->getData('suggested_params');
        if (!$params) {
            $params = array();
        }

        // Ensure that option keys exist
        $keys = array('bannersSelected', 'bannersSequence');
        foreach ($keys as $key) {
            if (!isset($params[$key])) {
                $params[$key] = null;
            }
        }

        return $params;
    }

    /**
     * Get banners content by specified banners IDs depend on Rotation mode
     *
     * @param array $bannerIds
     * @param array $segmentIds
     * @return array
     */
    protected function _getBannersContent($bannerIds, $segmentIds = array())
    {
        $this->_setRenderedParam('bannerIds', $bannerIds)
            ->_setRenderedParam('renderedBannerIds', array());

        $content = array();
        if (!empty($bannerIds)) {
            $bannerResource = $this->_bannerResource;

            // Process suggested params
            $suggestedParams = $this->getSuggestedParams();
            $suggBannersSelected = $suggestedParams['bannersSelected'];
            $suggBannersSequence = $suggestedParams['bannersSequence'];

            // Choose banner depending on rotation mode
            switch ($this->getRotate()) {
                case self::BANNER_WIDGET_RORATE_RANDOM:
                    // Choose banner either as suggested or randomly
                    $bannerId = null;
                    if ($suggBannersSelected && count($suggBannersSelected) == 1) {
                        $suggBannerId = $suggBannersSelected[0];
                        if (array_search($suggBannerId, $bannerIds) !== false) {
                            $bannerId = $suggBannerId;
                        }
                    }
                    if ($bannerId === null) {
                        $bannerId = $bannerIds[array_rand($bannerIds, 1)];
                    }

                    $_content = $bannerResource->getStoreContent($bannerId, $this->_currentStoreId, $segmentIds);
                    if (!empty($_content)) {
                        $content[$bannerId] = $_content;
                    }
                    $this->_setRenderedParam('renderedBannerIds', array($bannerId));
                    break;

                case self::BANNER_WIDGET_RORATE_SHUFFLE:
                case self::BANNER_WIDGET_RORATE_SERIES:
                    $isShuffle = $this->getRotate() == self::BANNER_WIDGET_RORATE_SHUFFLE;
                    $bannerId = null;
                    $bannersSequence = null;

                    // Compose banner sequence either from suggested sequence or from user session data
                    if ($suggBannersSequence !== null) {
                        // Check that suggested sequence is valid - contains only banner ids from list
                        if (!array_diff($suggBannersSequence, $bannerIds)) {
                            $bannersSequence = $suggBannersSequence;
                        }
                    }
                    if ($bannersSequence === null) {
                        $bannersSequence = $this->_sessionInstance->_getData($this->getUniqueId());
                    }

                    // Check that we have suggested banner to render
                    $suggBannerId = null;
                    if ($suggBannersSelected && count($suggBannersSelected) == 1) {
                        $suggBannerId = $suggBannersSelected[0];
                    }

                    // If some banners were shown, get the list of unshown ones and choose banner to show
                    if ($bannersSequence) {
                        $canShowIds = array_merge(array_diff($bannerIds, $bannersSequence), array());
                        if (!empty($canShowIds)) {
                            // Stil not whole serie is shown, choose the banner to show
                            if ($suggBannerId && (array_search($suggBannerId, $canShowIds) !== false)) {
                                $bannerId = $suggBannerId;
                            } else {
                                $canShowKeys = array_keys($canShowIds);
                                $showKey = $isShuffle ? array_rand($canShowIds, 1) : $canShowKeys[0];
                                $bannerId = $canShowIds[$showKey];
                            }
                            $bannersSequence[] = $bannerId;
                        }
                    }

                    // Start new serie (either no banners has been shown at all or whole serie has been shown)
                    if (!$bannerId) {
                        if ($suggBannerId && (array_search($suggBannerId, $bannerIds) !== false)) {
                            $bannerId = $suggBannerId;
                        } else {
                            $bannerKeys = array_keys($bannerIds);
                            $bannerKey = $isShuffle ? array_rand($bannerIds, 1) : $bannerKeys[0];
                            $bannerId = $bannerIds[$bannerKey];
                        }
                        $bannersSequence = array($bannerId);
                    }

                    $this->_sessionInstance->setData($this->getUniqueId(), $bannersSequence);

                    $_content = $bannerResource->getStoreContent($bannerId, $this->_currentStoreId, $segmentIds);
                    if (!empty($_content)) {
                        $content[$bannerId] = $_content;
                    }
                    $this->_setRenderedParam('renderedBannerIds', array($bannerId))
                        ->_setRenderedParam('bannersSequence', $bannersSequence);
                    break;

                default:
                    // We must always render all available banners - so suggested values are ignored
                    $content = $bannerResource->getBannersContent($bannerIds, $this->_currentStoreId, $segmentIds);
                    $this->_setRenderedParam('renderedBannerIds', $bannerIds);
                    break;
            }
        }

        $this->_prepareCacheTags();
        return $content;
    }

    /**
     * Prepare cache tags based on renderedBannerIds param.
     *
     * @return Enterprise_Banner_Block_Widget_Banner
     */
    protected function _prepareCacheTags()
    {
        $banner = $this->_getFactory()->getModel('enterprise_banner/banner');
        foreach ($this->getBannerIds() as $bannerId) {
            $bannerCacheTags = $banner->setId($bannerId)->getCacheIdTags();
            $this->addCacheTag($bannerCacheTags);
        }

        return $this;
    }

    /**
     * Get cache key informative items that must be preserved in cache placeholders
     * for block to be rerendered by placeholder
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $items = array(
            'name' => $this->getNameInLayout(),
            'types' => $this->getTypes(),
            'display_mode' => $this->getDisplayMode(),
            'rotate' => (string) $this->getRotate(),
            'banner_ids' => implode(',', $this->getBannerIds()),
            'unique_id' => $this->getUniqueId()
        );

        $items = parent::getCacheKeyInfo() + $items;

        return $items;
    }

    /**
     * Clears information about rendering process parameters.
     *
     * @return Enterprise_Banner_Block_Widget_Banner
     */
    protected function _clearRenderedParams()
    {
        $this->_renderedParams = array();
        return $this;
    }

    /**
     * Returns parameters about last banner rendering that this block has performed.
     * Used to know the information about process this block implemented to choose banners depending on
     * customer and select one/all of them to render.
     *
     * @return array
     */
    protected function _getRenderedParams()
    {
        return $this->_renderedParams;
    }

    /**
     * Sets rendered param information
     *
     * @param string $key
     * @param mixed $value
     * @return Enterprise_Banner_Block_Widget_Banner
     */
    protected function _setRenderedParam($key, $value)
    {
        $this->_renderedParams[$key] = $value;
        return $this;
    }

    /**
     * Clears information about rendering process parameters and renders block (new parameters are filled
     * during this process).
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->_clearRenderedParams();
        return parent::_toHtml();
    }

    /**
     * Returns rendered html and information about data used to render the banners.
     * Used by cache placeholder to get html and additional data about it, so later cache placeholder
     * can make some actions (randomize banners) on its own.
     *
     * @return array
     */
    public function renderAndGetInfo()
    {
        $result = array(
            'html' => $this->toHtml(),
            'params' => $this->_getRenderedParams()
        );
        return $result;
    }
}
