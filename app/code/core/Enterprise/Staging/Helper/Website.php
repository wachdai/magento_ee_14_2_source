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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Staging website helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Helper_Website extends Mage_Core_Helper_Url
{
    const XML_PATH_STAGING_CODE_SUFFIX   = 'global/enterprise/staging/staging_website_code_suffix';

    /**
     * Cache for website rewrite suffix
     *
     * @var array
     */
    protected $_stagingCodeSuffix = null;

    /**
     * Check if a website can be shown
     *
     * @param  Enterprise_Staging_Model_Staging_Website|int $website
     * @return boolean
     */
    public function canShow($website, $where = 'frontend')
    {
        if (is_int($website)) {
            $website = Mage::getModel('enterprise_staging/staging_website')->load($website);
        }
        /* @var $website Enterprise_Staging_Model_Staging_Website */

        if (!$website->getId()) {
            return false;
        }

        return $website->isVisibleOnFrontend();
    }

    /**
     * Retrieve website code sufix
     *
     * @return string
     */
    public function getWebsiteCodeSuffix()
    {
        if (is_null($this->_stagingCodeSuffix)) {
            $this->_stagingCodeSuffix = (string) Mage::getConfig()->getNode(self::XML_PATH_STAGING_CODE_SUFFIX);
        }
        return $this->_stagingCodeSuffix;
    }

    /**
     * Retrieve free (non-used) website code with code suffix (if specified in config)
     *
     * @param   string $code
     * @return  string
     */
    public function generateWebsiteCode($code)
    {
        return $this->getUnusedWebsiteCode($code) . $this->getWebsiteCodeSuffix();
    }

    /**
     * Retrieve free (non-used) website code
     *
     * @param   string $code
     * @return  string
     */
    public function getUnusedWebsiteCode($code)
    {
        if (empty($code)) {
            $code = '_';
        } elseif ($code == $this->getWebsiteCodeSuffix()) {
            $code = '_' . $this->getWebsiteCodeSuffix();
        }

        try {
            $website = Mage::app()->getWebsite($code);
        } catch (Exception $e) {
            $website = false;
        }
        if ($website) {
            // retrieve code suffix for staging websites
            $websiteCodeSuffix = $this->getWebsiteCodeSuffix();

            $match = array();
            if (!preg_match('#^([0-9a-z_]+?)(_([0-9]+))?('.preg_quote($websiteCodeSuffix).')?$#i', $code, $match)) {
                return $this->getUnusedWebsiteCode('_');
            }
            $code = $match[1].(isset($match[3])?'_'.($match[3]+1):'_1').(isset($match[4])?$match[4]:'');
            return $this->getUnusedWebsiteCode($code);
        } else {
            return $code;
        }
    }
}
