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
 * Abstract Quote dependent container
 */
abstract class Enterprise_PageCache_Model_Container_Advanced_Quote
    extends Enterprise_PageCache_Model_Container_Advanced_Abstract
{
    /**
     * Cache tag prefix
     */
    const CACHE_TAG_PREFIX = 'quote_';

    /**
     * Get cache identifier
     *
     * @return string
     */
    public static function getCacheId()
    {
        $cookieCart = Enterprise_PageCache_Model_Cookie::COOKIE_CART;
        $cookieCustomer = Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER;
        return md5(Enterprise_PageCache_Model_Container_Advanced_Quote::CACHE_TAG_PREFIX
            . (array_key_exists($cookieCart, $_COOKIE) ? $_COOKIE[$cookieCart] : '')
            . (array_key_exists($cookieCustomer, $_COOKIE) ? $_COOKIE[$cookieCustomer] : ''));
    }

    /**
     * Get cache identifier
     *
     * @return string
     */
    protected function _getCacheId()
    {
        return Enterprise_PageCache_Model_Container_Advanced_Quote::getCacheId();
    }

    /**
     * Get container individual additional cache id
     *
     * @return string
     */
    protected function _getAdditionalCacheId()
    {
        return md5($this->_placeholder->getName() . '_' . $this->_placeholder->getAttribute('cache_id'));
    }
}
