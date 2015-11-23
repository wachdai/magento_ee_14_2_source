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

class Enterprise_Staging_Model_Staging_Mapper_Website extends Enterprise_Staging_Model_Staging_Mapper_Abstract
{
    protected $_createMapData   = array();

    protected $_mergeMapData    = array();

    protected $_rollbackMapData = array();

    /**
     * set create map data
     *
     * @param array $mapData
     */
    public function setCreateMapData($mapData)
    {
        $this->_createMapData = $mapData;

        $websites = !empty($mapData['websites']) ? $mapData['websites'] : array();
        if (!empty($websites)) {
            foreach ($websites as $masterWebsiteId => $websiteMap) {
                $websites[$masterWebsiteId] = $this->_addCreateWebsiteMap($websiteMap);
            }
            $this->setData('websites', $websites);
        }

        $stagingItems = !empty($mapData['staging_items']) ? $mapData['staging_items'] : array();
        $this->addStagingItemsMap($stagingItems);

        return $this;
    }

    protected function _addCreateWebsiteMap($websiteMap)
    {
        $stores = !empty($websiteMap['stores']) ? $websiteMap['stores'] : array();

        $websiteMap = new Varien_Object($websiteMap);

        $_storesMap = array();
        foreach ($stores as $masterStoreId => $storeMap) {
            if (isset($storeMap['use'])) {
                $_storesMap[$masterStoreId] = $this->getStoreWrapper($storeMap);
            }
        }
        $websiteMap->setData('stores', $_storesMap);

        return $websiteMap;
    }

    public function getStoreWrapper($data)
    {
        if (!($data instanceof Varien_Object)) {
            return new Varien_Object($data);
        } else {
            return $data;
        }
    }

    public function getWebsite($websiteId)
    {
        $websites = $this->getData('websites');
        return isset($websites[$websiteId]) ? $websites[$websiteId] : false;
    }

    public function getCreateMapData()
    {
        return $this->_createMapData;
    }

    /**
     * Set websites, stores, staging items retrieved from map data
     *
     * @param array $mapData
     * @return Enterprise_Staging_Model_Staging_Mapper_Website
     */
    public function setMergeMapData($mapData)
    {
        $this->_mergeMapData = $mapData;

        if (isset($mapData['backup'])) {
            $this->setIsBackup((bool)$mapData['backup']);
        }

        $websitesMap = !empty($mapData['websites']) ? $mapData['websites'] : array();
        $this->addWebsitesMap($websitesMap);

        $storesMap = !empty($mapData['stores']) ? $mapData['stores'] : array();
        $this->addStoresMap($storesMap);

        $stagingItems = !empty($mapData['staging_items']) ? $mapData['staging_items'] : array();

        $this->addStagingItemsMap($stagingItems);

        return $this;
    }

    public function addWebsitesMap(array $websitesMap)
    {
        $_websitesMap = array();

        $fromWebsitesData   = !empty($websitesMap['from'])   ? $websitesMap['from']   : array();
        $toWebsitesData     = !empty($websitesMap['to'])     ? $websitesMap['to']     : array();
        foreach ($fromWebsitesData as $_idx => $stagingWebsiteId) {
            if (!empty($stagingWebsiteId) && !empty($toWebsitesData[$_idx])) {
                $_websitesMap[$stagingWebsiteId][] = $toWebsitesData[$_idx];
            }
        }
        $this->setData('websites', $_websitesMap);

        return $this;
    }

    public function addStoresMap(array $storesMap)
    {
        $_storesMap = array();

        foreach ($storesMap as &$storeMap) {
            $fromStoresData   = !empty($storeMap['from']) ? $storeMap['from'] : array();
            $toStoresData     = !empty($storeMap['to'])   ? $storeMap['to']   : array();
            foreach ($fromStoresData as $_idx => $stagingStoreId) {
                if (!empty($stagingStoreId) && !empty($toStoresData[$_idx])) {
                    $_storesMap[$stagingStoreId][] = $toStoresData[$_idx];
                }
            }
        }

        $this->setData('stores', $_storesMap);

        return $this;
    }

    public function addStagingItemsMap(array $stagingItems)
    {
        $_stagingItems = array();

        foreach ($stagingItems as $stagingItemCode => $stagingItemInfo) {
            $stagingItem = Mage::getSingleton('enterprise_staging/staging_config')->getStagingItem($stagingItemCode);
            if ($stagingItem) {
                $_stagingItems[$stagingItemCode] = $stagingItem;
            }
        }

        $this->setData('staging_items', $_stagingItems);

        return $this;
    }

    public function hasStagingItems()
    {
        $items = $this->getData('staging_items');

        return (is_array($items) && count($items) > 0);
    }

    public function hasStagingItem($itemCode)
    {
        $items = $this->getData('staging_items');

        foreach ($items as $item) {
            if ($itemCode == $item->getName()) {
                return true;
            }
        }
        return false;
    }

    public function getMergeMapData()
    {
        return $this->_mergeMapData;
    }

    /**
     * set rollback map data
     *
     * @param array $mapData
     */
    public function setRollbackMapData($mapData)
    {
        $this->_rollbackMapData = $mapData;

        $websitesMap = !empty($mapData['websites']) ? $mapData['websites'] : array();
        if (!empty($websitesMap)) {
            $this->addWebsitesMap($websitesMap);
        }

        $storesMap = !empty($mapData['stores']) ? $mapData['stores'] : array();
        if (!empty($storesMap)) {
            $this->addStoresMap($storesMap);
        }

        $stagingItems = !empty($mapData['staging_items']) ? $mapData['staging_items'] : array();
        $this->addStagingItemsMap($stagingItems);

        return $this;
    }

    /**
     * setialize main map data
     *
     * @return string
     */
    public function serialize($attributes = array(), $valueSeparator='=', $fieldSeparator=' ', $quote='"')
    {
        $resArray = array();

        $resArray["_createMapData"]     = $this->_createMapData;
        $resArray["_mergeMapData"]      = $this->_mergeMapData;
        $resArray["_rollbackMapData"]   = $this->_rollbackMapData;

        return serialize($resArray);
    }

    /**
     * unserialize map array and init mapper
     *
     * @param string $serializedData
     */
    public function unserialize($serializedData)
    {
        $unserializedArray = unserialize($serializedData);
        if ($unserializedArray) {
            if ( !empty($unserializedArray["_createMapData"])) {
                $this->setCreateMapData($unserializedArray["_createMapData"]);
            }
            if ( !empty($unserializedArray["_mergeMapData"])) {
                $this->setMergeMapData($unserializedArray["_mergeMapData"]);
            }
            if ( !empty($unserializedArray["_rollbackMapData"])) {
                $this->setRollbackMapData($unserializedArray["_rollbackMapData"]);
            }
        }
        return $this;
    }

    /**
     * Convenient getter of websites for megre and create
     *
     * @return array
     */
    public function getWebsiteObjects()
    {
        $objects = $this->getData('website_objects');
        if (is_null($objects)) {
            $objects = array();

            foreach ($this->getWebsites() as $k => $v) {
                // website varien object created for create staging
                if ($v instanceof Varien_Object) {
                    $objects[$k] = $v;
                } else { // merge, backup, rollback
                    $website    = new Varien_Object();
                    $stores     = array();

                    $storeMaps = $this->getStores();
                    if (!empty($storeMaps)) {
                        foreach ($storeMaps as $sourceStoreId => $targetStoreIds) {
                            foreach ($targetStoreIds as $targetStoreId) {
                                $stores[] = new Varien_Object(array(
                                    'master_store_id'   => $targetStoreId,
                                    'staging_store_id'  => $sourceStoreId
                                ));
                            }
                        }
                    } else {
                        foreach ($v as $targetWebsiteId) {
                            // fix for flat resource (if store mapping is not defined)
                            $sourceWebsiteStores = Mage::app()->getWebsite($k)->getStores();
                            $targetWebsiteStores = Mage::app()->getWebsite($targetWebsiteId)->getStores();

                            foreach ($targetWebsiteStores as $targetStore) {
                                foreach ($sourceWebsiteStores as $sourceStore) {
                                    if ($targetStore->getName() == $sourceStore->getName()) {
                                        $stores[] = new Varien_Object(array(
                                            'master_store_id'   => $targetStore->getId(),
                                            'staging_store_id'  => $sourceStore->getId()
                                        ));
                                    }
                                }
                            }
                        }
                    }

                    $website->setData('stores', $stores);
                    $objects[$k] = $website;
                }
            }

            $this->setData('website_objects', $objects);
        }
        return $objects;
    }
}
