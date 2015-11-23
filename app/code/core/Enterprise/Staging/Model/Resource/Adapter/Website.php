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
 * Staging website resource adapter
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Resource_Adapter_Website extends Enterprise_Staging_Model_Resource_Adapter_Abstract
{
    /**
     * Create staging websites
     *
     * @param Enterprise_Staging_Model_Staging $staging
     * @param Enterprise_Staging_Model_Staging_Event $event
     * @return Enterprise_Staging_Model_Resource_Adapter_Website
     */
    public function createRun(Enterprise_Staging_Model_Staging $staging, $event = null)
    {
        parent::createRun($staging, $event);

        $websites = $staging->getMapperInstance()->getWebsites();
        foreach ($websites as $website) {
            $masterWebsiteId = $website->getMasterWebsiteId();
            $masterWebsite   = Mage::app()->getWebsite($masterWebsiteId);

            $stagingWebsite  = Mage::getModel('core/website');

            $stagingWebsite->setData('is_staging', 1);
            $stagingWebsite->setData('code', $website->getCode());
            $stagingWebsite->setData('name', $website->getName());

            $stagingWebsite->setData('base_url', $website->getBaseUrl());
            $stagingWebsite->setData('base_secure_url', $website->getBaseSecureUrl());

            $stagingWebsite->setData('visibility', $website->getVisibility());

            $stagingWebsite->setData('master_login', $website->getMasterLogin());
            $password = trim($website->getMasterPassword());
            if ($password) {
                 if(Mage::helper('core/string')->strlen($password)<6){
                    throw new Enterprise_Staging_Exception(
                        Mage::helper('enterprise_staging')->__('The password must have at least 6 characters. Leading or trailing spaces will be ignored.')
                    );
                }
                $stagingWebsite->setData('master_password' , Mage::helper('core')->encrypt($password));
            }

            if (!$stagingWebsite->getId()) {
                $value = Mage::getModel('core/date')->gmtDate();
                $stagingWebsite->setCreatedAt($value);
            } else {
                $value = Mage::getModel('core/date')->gmtDate();
                $stagingWebsite->setUpdatedAt($value);
            }

            $stagingWebsite->save();

            if (Mage::getStoreConfigFlag('general/content_staging/create_entry_point')) {
                $entryPoint = Mage::getModel('enterprise_staging/entry')
                    ->setWebsite($stagingWebsite)->save();
            } else {
                $entryPoint = null;
            }

            $stagingWebsiteId = (int)$stagingWebsite->getId();

            $website->setStagingWebsite($stagingWebsite);
            $website->setStagingWebsiteId($stagingWebsiteId);

            $website->setMasterWebsite($masterWebsite);

            $this->_saveSystemConfig($staging, $stagingWebsite, $entryPoint);

            $staging->setMasterWebsiteId($masterWebsiteId)
                ->setStagingWebsiteId($stagingWebsiteId)
                ->setDontRunStagingProccess(true)
                ->save();

            Mage::dispatchEvent('staging_website_create_after', array(
                'old_website_id' => $masterWebsiteId, 'new_website_id' => $stagingWebsiteId)
            );
            // curently supports only one staging website
            break;
        }
        return $this;
    }

    /**
     * Update staging websites staging values (visibility, master_login and master_password)
     *
     * @param Enterprise_Staging_Model_Staging $staging
     * @param Enterprise_Staging_Model_Staging_Event $event
     * @return Enterprise_Staging_Model_Resource_Adapter_Website
     */
    public function updateRun(Enterprise_Staging_Model_Staging $staging, $event = null)
    {
        parent::updateRun($staging, $event);

        $websites = $staging->getMapperInstance()->getWebsites();
        foreach ($websites as $website) {
            $stagingWebsiteId = $website->getStagingWebsiteId();
            if ($stagingWebsiteId) {
                $stagingWebsite = Mage::app()->getWebsite($stagingWebsiteId);
            }
            if (!$stagingWebsite->getId() || !$stagingWebsite->getIsStaging()) {
                continue;
            }

            $stagingWebsite->setData('visibility', $website->getVisibility());

            $stagingWebsite->setData('master_login', $website->getMasterLogin());
            $password = trim($website->getMasterPassword());
            if ($password) {
                 if(Mage::helper('core/string')->strlen($password)<6){
                    throw new Enterprise_Staging_Exception(
                        Mage::helper('enterprise_staging')->__('The password must have at least 6 characters. Leading or trailing spaces will be ignored.')
                    );
                }
                $stagingWebsite->setData('master_password' , Mage::helper('core')->encrypt($password));
            }

            $stagingWebsite->save();

            break;
        }
        return $this;
    }

    /**
     * Save system config resource model
     *
     * @param Enterprise_Staging_Model_Staging $staging
     * @param Mage_Core_Model_Website $stagingWebsite
     * @param Enterprise_Staging_Model_Entry $entryPoint
     * @return Enterprise_Staging_Model_Resource_Adapter_Website
     */
    protected function _saveSystemConfig($staging, Mage_Core_Model_Website $stagingWebsite, $entryPoint = null)
    {
        $masterWebsite = $staging->getMasterWebsite();

        $unsecureBaseUrl = $stagingWebsite->getBaseUrl();
        $secureBaseUrl   = $stagingWebsite->getBaseSecureUrl();
        if ($entryPoint && $entryPoint->isAutomatic()) {
            $unsecureBaseUrl = $entryPoint->getBaseUrl($masterWebsite);
            $secureBaseUrl   = $entryPoint->getBaseUrl($masterWebsite, true);
        }

        $unsecureConf = Mage::getConfig()->getNode('default/web/unsecure');
        $secureConf = Mage::getConfig()->getNode('default/web/secure');

        if (!$masterWebsite->getIsStaging()) {
            $originalBaseUrl = (string) $masterWebsite->getConfig("web/unsecure/base_url");
        } else {
            $originalBaseUrl = (string) Mage::getConfig()->getNode("default/web/unsecure/base_url");
        }

        $this->_saveUrlsInSystemConfig($stagingWebsite, $originalBaseUrl, $unsecureBaseUrl, 'unsecure' , $unsecureConf);

        if (strpos($secureBaseUrl, 'https')!== false) {
            if (!$masterWebsite->getIsStaging()) {
                $originalBaseUrl = (string) $masterWebsite->getConfig("web/secure/base_url");
            } else {
                $originalBaseUrl = (string) Mage::getConfig()->getNode("default/web/secure/base_url");
            }
        }
        $this->_saveUrlsInSystemConfig($stagingWebsite, $originalBaseUrl, $secureBaseUrl, 'secure', $secureConf);

        return $this;
    }

    /**
     * Process core config data
     *
     * @param Mage_Core_Model_Website $stagingWebsite
     * @param string $originalBaseUrl
     * @param string $baseUrl
     * @param string $mode
     * @param Varien_Simplexml_Element $xmlConfig
     * @return Enterprise_Staging_Model_Resource_Adapter_Website
     */
    protected function _saveUrlsInSystemConfig($stagingWebsite, $originalBaseUrl, $baseUrl, $mode, $xmlConfig)
    {
        foreach ($xmlConfig->children() AS $nodeName => $nodeValue) {
            if ($mode == 'secure' || $mode == 'unsecure') {
                if ($nodeName == 'base_url' || $nodeName == 'base_web_url' || $nodeName == 'base_link_url') {
                    $nodeValue = $baseUrl;
                } elseif ($mode == 'unsecure') {
                    if (strpos($nodeValue, '{{unsecure_base_url}}') !== false) {
                        $nodeValue = str_replace('{{unsecure_base_url}}', $originalBaseUrl, $nodeValue);
                    }
                } elseif ($mode == 'secure') {
                    if (strpos($nodeValue, '{{secure_base_url}}') !== false) {
                        $nodeValue = str_replace('{{secure_base_url}}', $originalBaseUrl, $nodeValue);
                    }
                }
            }

            $config = Mage::getModel('core/config_data');
            $path = 'web/' . $mode . '/' . $nodeName;
            $config->setPath($path);
            $config->setScope('websites');
            $config->setScopeId($stagingWebsite->getId());
            $config->setValue($nodeValue);
            $config->save();
        }

        return $this;
    }
}
