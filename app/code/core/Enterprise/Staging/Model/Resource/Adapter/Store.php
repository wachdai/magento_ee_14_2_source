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
 * Enter description here ...
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Resource_Adapter_Store extends Enterprise_Staging_Model_Resource_Adapter_Abstract
{
    /**
     * Create staging store views
     *
     * @param Enterprise_Staging_Model_Staging $staging
     * @param Enterprise_Staging_Model_Staging_Event $event
     * @return Enterprise_Staging_Model_Resource_Adapter_Store
     */
    public function createRun(Enterprise_Staging_Model_Staging $staging, $event = null)
    {
        parent::createRun($staging, $event);

        $websites       = $staging->getMapperInstance()->getWebsites();
        $masterWebsite  = $staging->getMasterWebsite();

        $defaultStoreId = null;
        if ($masterWebsite) {
            $masterDefaultGroup = $masterWebsite->getDefaultGroup();
            if ($masterDefaultGroup) {
                $defaultStoreId = $masterDefaultGroup->getDefaultStoreId();
            }
        }

        foreach ($websites as $website) {
            $stores = $website->getStores();
            foreach ($stores as $masterStoreId => $store) {
                $stagingStore = Mage::getModel('core/store');
                $stagingStore->setData('is_active', 1);
                $stagingStore->setData('is_staging', 1);
                $stagingStore->setData('code', $store->getCode());
                $stagingStore->setData('name', $store->getName());

                $stagingWebsite = $website->getStagingWebsite();
                if ($stagingWebsite) {
                    $stagingStore->setData('website_id', $website->getStagingWebsiteId());
                    $stagingStore->setData('group_id', $stagingWebsite->getDefaultGroupId());
                }

                if ($store->getGroupId()) {
                    $stagingStore->setData('group_id', $store->getGroupId());
                }

                if (!$stagingStore->getId()) {
                    $value = Mage::getModel('core/date')->gmtDate();
                    $stagingStore->setCreatedAt($value);
                } else {
                    $value = Mage::getModel('core/date')->gmtDate();
                    $stagingStore->setUpdatedAt($value);
                }

                $stagingStore->save();

                if ($stagingWebsite) {
                    $defaultGroup = $stagingWebsite->getDefaultGroup();
                    if ($defaultGroup) {
                        if (!$defaultGroup->getDefaultStoreId()
                            && (is_null($defaultStoreId) ||
                            ($stagingStore->getId() == $defaultStoreId))) {
                                $defaultGroup->setDefaultStoreId($stagingStore->getId());
                                $defaultGroup->save();
                        }
                    }
                }

                $store->setStagingStore($stagingStore);
                $store->setStagingStoreId($stagingStore->getId());
            }
        }

        return $this;
    }
}
