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
 * @package     Enterprise_WebsiteRestriction
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Website stub controller
 *
 * @category    Enterprise
 * @package     Enterprise_WebsiteRestriction
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_WebsiteRestriction_IndexController extends Mage_Core_Controller_Front_Action
{
    protected $_stubPageIdentifier = Enterprise_WebsiteRestriction_Helper_Data::XML_PATH_RESTRICTION_LANDING_PAGE;

    protected $_cacheKey;

    /**
     * Prefix for cache id
     */
    protected $_cacheKeyPrefix = 'RESTRICTION_LANGING_PAGE_';

    /**
     * Depricated, full action name used instead
     */
    protected $_layoutUpdate = 'restriction_index_stub';

    /**
     * Cache  will be ralted on configuration and website
     *
     * @var array
     */
    protected $_cacheTags = array(
        Mage_Core_Model_Website::CACHE_TAG,
        Mage_Core_Model_Config::CACHE_TAG
    );

    protected function _construct()
    {
        $this->_cacheKey = $this->_cacheKeyPrefix . Mage::app()->getWebsite()->getId();
    }

    /**
     * Display a pre-cached CMS-page if we have such or generate new one
     *
     */
    public function stubAction()
    {
        $cachedData = Mage::app()->loadCache($this->_cacheKey);
        if ($cachedData) {
            $this->getResponse()->setBody($cachedData);
        } else {
            /**
             * Generating page and save it to cache
             */
            $page = Mage::getModel('cms/page')
                ->load(Mage::getStoreConfig($this->_stubPageIdentifier), 'identifier');

            Mage::register('restriction_landing_page', $page);

            if ($page->getCustomTheme()) {
                if (Mage::app()->getLocale()
                    ->isStoreDateInInterval(null, $page->getCustomThemeFrom(), $page->getCustomThemeTo())
                ) {
                    list($package, $theme) = explode('/', $page->getCustomTheme());
                    Mage::getSingleton('core/design_package')
                        ->setPackageName($package)
                        ->setTheme($theme);
                }
            }

            $this->addActionLayoutHandles();

            if ($page->getRootTemplate()) {
                $this->getLayout()->helper('page/layout')
                    ->applyHandle($page->getRootTemplate());
            }

            $this->loadLayoutUpdates();

            $this->getLayout()->getUpdate()->addUpdate($page->getLayoutUpdateXml());
            $this->generateLayoutXml()->generateLayoutBlocks();

            if ($page->getRootTemplate()) {
                $this->getLayout()->helper('page/layout')
                    ->applyTemplate($page->getRootTemplate());
            }

            $this->renderLayout();

            Mage::app()->saveCache($this->getResponse()->getBody(), $this->_cacheKey, $this->_cacheTags);
        }
    }
}
