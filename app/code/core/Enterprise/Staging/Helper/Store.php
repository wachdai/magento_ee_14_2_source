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
 * Staging store helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Helper_Store extends Mage_Core_Helper_Url
{
    const XML_PATH_STAGING_CODE_SUFFIX   = 'global/enterprise/staging/staging_store_code_suffix';

    /**
     * Cache for store rewrite suffix
     *
     * @var array
     */
    protected $_stagingCodeSuffix = null;

    /**
     * Check if a store can be shown
     *
     * @param  Enterprise_Staging_Model_Staging_Store|int $store
     * @return boolean
     */
    public function canShow($store, $where = 'frontend')
    {
        if (is_int($store)) {
            $store = Mage::getModel('enterprise_staging/staging_store')->load($store);
        }
        /* @var $store Enterprise_Staging_Model_Staging_Store */

        if (!$store->getId()) {
            return false;
        }

        return $store->isVisibleOnFrontend();
    }

    /**
     * Retrieve store code sufix
     *
     * @return string
     */
    public function getStoreCodeSuffix()
    {
        if (is_null($this->_stagingCodeSuffix)) {
            $this->_stagingCodeSuffix = (string) Mage::getConfig()->getNode(self::XML_PATH_STAGING_CODE_SUFFIX);
        }
        return $this->_stagingCodeSuffix;
    }

    /**
     * Retrieve free (non-used) store code with code suffix (if specified in config)
     *
     * @param   string $code
     * @return  string
     */
    public function generateStoreCode($code)
    {
        return $this->getUnusedStoreCode($code) . $this->getStoreCodeSuffix();
    }

    /**
     * Retrieve free (non-used) store code
     *
     * @param   string $code
     * @return  string
     */
    public function getUnusedStoreCode($code)
    {
        if (empty($code)) {
            $code = '_';
        } elseif ($code == $this->getStoreCodeSuffix()) {
            $code = '_' . $this->getStoreCodeSuffix();
        }

        try {
            $store = Mage::app()->getStore($code);
        } catch (Exception $e) {
            $store = false;
        }
        if ($store) {
            // retrieve code suffix for staging stores
            $storeCodeSuffix = $this->getStoreCodeSuffix();

            $match = array();
            if (!preg_match('#^([0-9a-z_]+?)(_([0-9]+))?('.preg_quote($storeCodeSuffix).')?$#i', $code, $match)) {
                return $this->getUnusedStoreCode('_');
            }
            $code = $match[1].(isset($match[3])?'_'.($match[3]+1):'_1').(isset($match[4])?$match[4]:'');
            return $this->getUnusedStoreCode($code);
        } else {
            return $code;
        }
    }
}
