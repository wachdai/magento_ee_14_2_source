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
 * Staging group resource adapter
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Resource_Adapter_Group extends Enterprise_Staging_Model_Resource_Adapter_Abstract
{
    /**
     * Create run
     *
     * @param Enterprise_Staging_Model_Staging $staging
     * @param Enterprise_Staging_Model_Staging_Event $event
     * @return Enterprise_Staging_Model_Resource_Adapter_Group
     */
    public function createRun(Enterprise_Staging_Model_Staging $staging, $event = null)
    {
        parent::createRun($staging, $event);

        $websites = $staging->getMapperInstance()->getWebsites();

        $createdStoreGroups = array();
        foreach ($websites as $website) {
            $stores = $website->getStores();

            foreach ($stores as $store) {
                $realStore = Mage::app()->getStore($store->getMasterStoreId());
                if (!$realStore) {
                    continue;
                }
                if (array_key_exists($realStore->getGroupId(), $createdStoreGroups)) {
                    $store->setGroupId($createdStoreGroups[$realStore->getGroupId()]);
                    continue;
                }

                $realStoreGroup = $realStore->getGroup();

                $rootCategory = (int) $realStoreGroup->getRootCategoryId();

                $stagingGroup = Mage::getModel('core/store_group');
                $stagingGroup->setData('website_id', $website->getStagingWebsiteId());
                $stagingGroup->setData('root_category_id', $rootCategory);
                $stagingGroup->setData('name', $realStoreGroup->getName());
                $stagingGroup->save();

                $masterWebsite = $website->getMasterWebsite();
                $stagingWebsite = $website->getStagingWebsite();
                if ($stagingWebsite && ($realStoreGroup->getId() == $masterWebsite->getDefaultGroupId()) ) {
                    $stagingWebsite->setData('default_group_id', $stagingGroup->getId());
                    $stagingWebsite->save();
                }

                $store->setGroupId($stagingGroup->getId());

                $createdStoreGroups[$realStore->getGroupId()] = $stagingGroup->getId();
            }
        }

        return $this;
    }
}
