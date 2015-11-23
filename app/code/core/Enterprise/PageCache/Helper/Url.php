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
 * Url processing helper
 *
 * @category    Enterprise
 * @package     Enterprise_PageCache
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_PageCache_Helper_Url
{
    /**
     * Retrieve unique marker value
     *
     * @return string
     */
    protected static function _getSidMarker()
    {
        return Enterprise_PageCache_Helper_Data::wrapPlaceholderString('_SID_MARKER_');
    }

    /**
     * Replace all occurrences of session_id with unique marker
     *
     * @param  string $content
     * @return bool
     */
    public static function replaceSid(&$content)
    {
        if (!$content) {
            return false;
        }
        /** @var $session Mage_Core_Model_Session */
        $session = Mage::getSingleton('core/session');
        $replacementCount = 0;
        $content = str_replace(
            $session->getSessionIdQueryParam() . '=' . $session->getSessionId(),
            $session->getSessionIdQueryParam() . '=' . self::_getSidMarker(),
            $content, $replacementCount);
        return ($replacementCount > 0);
    }

    /**
     * Restore session_id from marker value
     *
     * @param string $content
     * @param string $sidValue
     * @return bool
     */
    public static function restoreSid(&$content, $sidValue)
    {
        if (!$content) {
            return false;
        }
        $replacementCount = 0;
        $content = str_replace(self::_getSidMarker(), $sidValue, $content, $replacementCount);
        return ($replacementCount > 0);
    }

    /**
     * Calculate UENC parameter value and replace it
     *
     * @param string $content
     * @return string
     */
    public static function replaceUenc($content)
    {
        $urlHelper = new Mage_Core_Helper_Url;
        $search = '/\/(' . Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED . ')\/[^\/]*\//';
        $replace = '/$1/' . $urlHelper->getEncodedUrl() . '/';
        $content = preg_replace($search, $replace, $content);
        return $content;
    }

    /**
     * Prepare request path tag
     *
     * @param string $path
     * @return string
     */
    public static function prepareRequestPathTag($path)
    {
        $path = trim((string)$path, '/ ');
        return Enterprise_PageCache_Model_Processor::REQUEST_PATH_PREFIX . md5($path);
    }
}
