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
 * Poll sidebar container
 */
class Enterprise_PageCache_Model_Container_Sidebar_Poll extends Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * Current Poll id
     */
    protected $_activePollId = null;


    /**
     * Get identifier from cookies
     *
     * @return string
     */
    protected function _getIdentifier()
    {
        $visitor = $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER, '');
        if (!$visitor) {
            $visitor = $_SERVER['REMOTE_ADDR'];
        }
        return $visitor;
    }

    /**
     * Get cache identifier
     *
     * @return string
     */
    protected function _getCacheId()
    {
        if ($this->_getPollToShow() === null) {
            return false;
        }

        return 'CONTAINER_POLL'
           . '_' . md5($this->_placeholder->getAttribute('cache_id')
           . '_' . $this->_getIdentifier()
           . '_' . $this->_getPollToShow());
    }

    /**
     * Returns cache identifier for informational data about customer banners
     *
     * @return string
     */
    protected function _getInfoCacheId()
    {
        return 'POLL_INFORMATION_'
            . '_' . md5($this->_placeholder->getAttribute('cache_id')
            . '_' . $this->_getIdentifier());
    }

    /**
     * Saves informational cache, containing parameters used to show poll.
     *
     * @param array $renderedParams
     * @return Enterprise_PageCache_Model_Container_Sidebar_Poll
     */
    protected function _saveInfoCache($renderedParams)
    {
        $data = serialize($renderedParams);
        $id = $this->_getInfoCacheId();
        $tags = array(Enterprise_PageCache_Model_Processor::CACHE_TAG);
        Enterprise_PageCache_Model_Cache::getCacheInstance()->save($data, $id, $tags);
        return $this;
    }

    /**
     * Loads informational cache, containing parameters used to show poll
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
     * Get poll id to show
     *
     * @return int|null|bool
     */
    protected function _getPollToShow()
    {
        if ($this->_activePollId === null) {
            $renderedParams = $this->_loadInfoCache();
            if (!$renderedParams) {
                return null;
            }

            //filter voted
            $voted = $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_POLL, '');
            if ($voted
                && in_array($voted, $renderedParams['active_ids'])
                && !in_array($voted, $renderedParams['voted_ids'])
            ) {
                return null;
            }

            $activeIds = array_diff($renderedParams['active_ids'], $renderedParams['voted_ids']);
            $randomKey = array_rand($activeIds);
            $this->_activePollId = isset($activeIds[$randomKey]) ? $activeIds[$randomKey] : false;
        }

        return $this->_activePollId;
    }

    /**
     * Render block content
     *
     * @return string
     */
    protected function _renderBlock()
    {
        $renderedParams = $this->_loadInfoCache();

        $templates = unserialize($this->_placeholder->getAttribute('templates'));

        $block = $this->_getPlaceHolderBlock();

        foreach ($templates as $type=>$template) {
            $block->setPollTemplate($template, $type);
        }

        if ($renderedParams) {
            if($this->_getPollToShow()) {
                $block->setPollId($this->_getPollToShow());
            } else {
                $voted = $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_POLL, '');
                if ($voted && in_array($voted, $renderedParams['active_ids'])) {
                    $renderedParams = array(
                        'active_ids' => $block->getActivePollsIds(),
                        'voted_ids' => $block->getVotedPollsIds(),
                    );
                    $this->_saveInfoCache($renderedParams);
                }
            }
        } else {
            $renderedParams = array(
                'active_ids' => $block->getActivePollsIds(),
                'voted_ids' => $block->getVotedPollsIds(),
            );
            $this->_saveInfoCache($renderedParams);
        }

        $content = $block->toHtml();

        if (is_null($this->_activePollId)) {
            $this->_activePollId = $block->getPollToShow();
        }

        return $content;
    }

    /**
     * Generate placeholder content before application was initialized and apply to page content if possible
     *
     * @param string $content
     * @return bool
     */
    public function applyWithoutApp(&$content)
    {
        $cacheId = $this->_getCacheId();
        if ($cacheId !== false) {
            $block = $this->_loadCache($cacheId);
            if ($block !== false) {
                $this->_applyToContent($content, $block);
            } else {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }
}
