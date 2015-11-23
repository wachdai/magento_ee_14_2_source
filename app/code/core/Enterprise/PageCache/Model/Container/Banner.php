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
 * @package     Enterprise_PageCache
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Banner widget container, renders and caches banner content.
 *
 * @category    Enterprise
 * @package     Enterprise_PageCache
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */
class Enterprise_PageCache_Model_Container_Banner
    extends Enterprise_PageCache_Model_Container_Abstract
{

    /**
     * Array of ids of banner chosen to be shown to user this time
     */
    protected $_bannersSelected = null;

    /**
     * Array of ids of banners already shown during current serie
     */
    protected $_bannersSequence = null;

    /**
     * Get cache additional identifiers from cookies.
     * Customers are differentiated because they can have different content of banners (due to template variables)
     * or different sets of banners targeted to their segment.
     *
     * @return string
     */
    protected function _getIdentifier()
    {
        return $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER, '');
    }

    /**
     * Returns cache identifier for informational data about customer banners
     *
     * @return string
     */
    protected function _getInfoCacheId()
    {
        return 'BANNER_INFORMATION_'
            . md5($this->_placeholder->getAttribute('cache_id')
            . '_' . $this->_getIdentifier());
    }

    /**
     * Saves informational cache, containing parameters used to show banners.
     * We don't use _saveCache() method internally, because it replaces sid in cache, that can be done only
     * after app is started, while this method can be called without app after rendering serie/shuffle banners.
     *
     * @param array $renderedParams
     * @return Enterprise_PageCache_Model_Container_Banner
     */
    protected function _saveInfoCache($renderedParams)
    {
        $data = serialize($renderedParams);
        $id = $this->_getInfoCacheId();
        $tags = array(Enterprise_PageCache_Model_Processor::CACHE_TAG);
        $lifetime = $this->_placeholder->getAttribute('cache_lifetime');
        if (!$lifetime) {
            $lifetime = false;
        }
        Enterprise_PageCache_Model_Cache::getCacheInstance()->save($data, $id, $tags, $lifetime);
        return $this;
    }

    /**
     * Loads informational cache, containing parameters used to show banners
     *
     * @return false|array
     */
    protected function _loadInfoCache()
    {
        $infoCacheId = $this->_getInfoCacheId();
        $data = $this->_loadCache($infoCacheId);
        if ($data === false) {
            return false;
        }
        return unserialize($data);
    }

    /**
     * Get cache identifier for banner block contents.
     * Used only after rendered banners are selected.
     *
     * @return string
     */
    protected function _getCacheId()
    {
        if ($this->_bannersSelected === null) {
            return false;
        }

        sort($this->_bannersSelected);
        return 'CONTAINER_BANNER_'
            . md5($this->_placeholder->getAttribute('cache_id')
            . '_' . $this->_getIdentifier())
            . '_' . implode(',', $this->_bannersSelected)
            . '_' .  $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::CUSTOMER_SEGMENT_IDS, '');
    }

    /**
     * Generates placeholder content before application was initialized and applies it to page content if possible.
     * First we get meta-data with list of prepared banner ids and shown ids. Then we select banners to render and
     * check whether we already have that content in cache.
     *
     * @param string $content
     * @return bool
     */
    public function applyWithoutApp(&$content)
    {
        // Load information about rendering process for current user
        $renderedParams = $this->_loadInfoCache();
        if ($renderedParams === false) {
            return false;
        }

        if (isset($renderedParams['bannersSequence'])) {
            $this->_bannersSequence = $renderedParams['bannersSequence'];
        }

        // Find a banner block to be rendered for this user
        $this->_bannersSelected = $this->_selectBannersToRender($renderedParams);
        if ($this->_bannersSelected) {
            $cacheId = $this->_getCacheId();
            $block = $this->_loadCache($cacheId);
        } else {
            // No banners to render - just fill with empty content
            $block = '';
        }

        if ($block !== false) {
            $this->_applyToContent($content, $block);
            return true;
        }
        return false;
    }

    /**
     * Selects the banners we want to show to the current customer.
     * The banners depend on the list of banner ids and rotation mode, that chooses banners to show from that list.
     *
     * @param array $renderedParams
     * @return array
     */
    protected function _selectBannersToRender($renderedParams)
    {
        $bannerIds = $renderedParams['bannerIds'];
        if (!$bannerIds) {
            return array();
        }

        $rotate = $this->_placeholder->getAttribute('rotate');
        switch ($rotate) {
            case Enterprise_Banner_Block_Widget_Banner::BANNER_WIDGET_RORATE_RANDOM:
                $bannerId = $bannerIds[array_rand($bannerIds, 1)];
                $result = array($bannerId);
                break;

            case Enterprise_Banner_Block_Widget_Banner::BANNER_WIDGET_RORATE_SERIES:
            case Enterprise_Banner_Block_Widget_Banner::BANNER_WIDGET_RORATE_SHUFFLE:
                $isShuffle = $rotate == Enterprise_Banner_Block_Widget_Banner::BANNER_WIDGET_RORATE_SHUFFLE;
                $bannerId = null;

                $bannersSequence = isset($renderedParams['bannersSequence']) ?
                    $renderedParams['bannersSequence'] :
                    array();
                if ($bannersSequence) {
                    $canShowIds = array_merge(array_diff($bannerIds, $bannersSequence), array());
                    if (!empty($canShowIds)) {
                        // Stil not whole serie is shown, choose the banner to show
                        $showKey = $isShuffle ? array_rand($canShowIds, 1) : 0;
                        $bannerId = $canShowIds[$showKey];
                        $bannersSequence[] = $bannerId;
                    }
                }

                // Start new serie (either no banners has been shown at all or whole serie has been shown)
                if (!$bannerId) {
                    $bannerKey = $isShuffle ? array_rand($bannerIds, 1) : 0;
                    $bannerId = $bannerIds[$bannerKey];
                    $bannersSequence = array($bannerId);
                }

                $renderedParams['bannersSequence'] = $bannersSequence;
                $this->_saveInfoCache($renderedParams); // So that serie progresses
                $result = array($bannerId);
                break;

            default:
                $result = $bannerIds;
        }

        return $result;
    }

    /**
     * Render banner block content
     *
     * @return string
     */
    protected function _renderBlock()
    {
        $block = $this->_getPlaceHolderBlock();
        $placeholder = $this->_placeholder;

        $parameters = array('name', 'types', 'display_mode', 'rotate', 'banner_ids', 'unique_id');
        foreach ($parameters as $parameter) {
            $value = $placeholder->getAttribute($parameter);
            $block->setData($parameter, $value);
        }

        /**
         * Ask block to render banners that we have selected. However block is not required to render that banners,
         * because something could change and these banners are not suitable any more (e.g. deleted, customer
         * changed his segment/group and so on) - in this case banner block will render suitable banner and return
         * new info options.
         */
        $suggestedParams = array();
        $suggestedParams['bannersSelected'] = $this->_bannersSelected;
        $suggestedParams['bannersSequence'] = $this->_bannersSequence;

        Mage::dispatchEvent('render_block', array('block' => $block, 'placeholder' => $this->_placeholder));

        $renderedInfo = $block->setSuggestedParams($suggestedParams)
            ->setTemplate($placeholder->getAttribute('template'))
            ->renderAndGetInfo();

        $renderedParams = $renderedInfo['params'];
        $this->_bannersSelected = $renderedParams['renderedBannerIds']; // Later _getCacheId() will use it
        unset($renderedParams['renderedBannerIds']); // We don't need it in cache info params
        $this->_saveInfoCache($renderedParams); // Save sequence params and possibly changed other params

        return $renderedInfo['html'];
    }
}
