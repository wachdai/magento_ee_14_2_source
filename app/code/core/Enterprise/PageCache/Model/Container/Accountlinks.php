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
 * Account links container
 */
class Enterprise_PageCache_Model_Container_Accountlinks extends Enterprise_PageCache_Model_Container_Customer
{
    /**
     * Get identifier from cookies
     *
     * @return string
     */
    protected function _getIdentifier()
    {
        $cacheId = $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER, '')
            . '_'
            . $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER_LOGGED_IN, '')
            . '_'
            . $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::PERSISTENT_COOKIE_NAME, '');
        return $cacheId;
    }

    /**
     * Get cache identifier
     *
     * @return string
     */
    protected function _getCacheId()
    {
        return 'CONTAINER_LINKS_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
    }

    /**
     * Render block content
     *
     * @return string
     */
    protected function _renderBlock()
    {
        $block = $this->_getPlaceHolderBlock();
        $block->setNameInLayout($this->_placeholder->getAttribute('name'));

        if (!$this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER)
            || $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER_LOGGED_IN)
        ) {
            $links = $this->_placeholder->getAttribute('links');
            if ($links) {
                $links = unserialize(base64_decode($links));
                foreach ($links as $position => $linkInfo) {
                    $block->addLink($linkInfo['label'], $linkInfo['url'], $linkInfo['title'], false, array(), $position,
                            $linkInfo['li_params'], $linkInfo['a_params'], $linkInfo['before_text'],
                            $linkInfo['after_text']);
                }
            }
        } else {
            Mage::dispatchEvent('render_block_accountlinks', array(
                'block' => $block,
                'placeholder' => $this->_placeholder,
            ));
        }
        Mage::dispatchEvent('render_block', array('block' => $block, 'placeholder' => $this->_placeholder));

        return $block->toHtml();
    }
}
