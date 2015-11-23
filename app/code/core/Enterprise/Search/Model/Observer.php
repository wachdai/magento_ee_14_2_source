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
 * @package     Enterprise_Search
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

 /**
 * Enterprise search model observer
 *
 * @category   Enterprise
 * @package    Enterprise_Search
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Search_Model_Observer
{
    /**
     * Add search weight field to attribute edit form (only for quick search)
     * @see Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Main
     *
     * @param Varien_Event_Observer $observer
     */
    public function eavAttributeEditFormInit(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_search')->isThirdPartyEngineAvailable()) {
            return;
        }

        $form      = $observer->getEvent()->getForm();
        $attribute = $observer->getEvent()->getAttribute();
        $fieldset  = $form->getElement('front_fieldset');

        $fieldset->addField('search_weight', 'select', array(
            'name'        => 'search_weight',
            'label'       => Mage::helper('catalog')->__('Search Weight'),
            'values'      => Mage::getModel('enterprise_search/source_weight')->getOptions(),
        ), 'is_searchable');
        /**
         * Disable default search fields
         */
        $attributeCode = $attribute->getAttributeCode();

        if ($attributeCode == 'name') {
            $form->getElement('is_searchable')->setDisabled(1);
        }
    }

    /**
     * Save search query relations after save search query
     *
     * @param Varien_Event_Observer $observer
     */
    public function searchQueryEditFormAfterSave(Varien_Event_Observer $observer)
    {
        $searchQuryModel = $observer->getEvent()->getDataObject();
        $queryId         = $searchQuryModel->getId();
        $relatedQueries  = $searchQuryModel->getSelectedQueriesGrid();

        if (strlen($relatedQueries) == 0) {
            $relatedQueries = array();
        } else {
            $relatedQueries = explode('&', $relatedQueries);
        }

        Mage::getResourceModel('enterprise_search/recommendations')
            ->saveRelatedQueries($queryId, $relatedQueries);
    }

    /**
     * Invalidate catalog search index after creating of new customer group or changing tax class of existing,
     * because there are all combinations of customer groups and websites per price stored at search engine index
     * and there will be no document's price field for customers that belong to new group or data will be not actual.
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerGroupSaveAfter(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_search')->isThirdPartyEngineAvailable()) {
            return;
        }

        $object = $observer->getEvent()->getDataObject();
        if ($object->isObjectNew() || $object->getTaxClassId() != $object->getOrigData('tax_class_id')) {
            $this->_invalidateCatalogSearchMview();
        }
    }

    /**
     * Invaidate the materialized view for CatalogSearch indexer
     *
     * @return \Enterprise_Search_Model_Observer
     */
    protected function _invalidateCatalogSearchMview()
    {
        /* @var $client Enterprise_Mview_Model_Client */
        $client = Mage::getModel('enterprise_mview/client');
        $client->init('catalogsearch_fulltext');

        /* @var $metaData Enterprise_Mview_Model_MetaData */
        $metaData = $client->getMetadata();
        $metaData->setInvalidStatus();
        $metaData->save();
        return $this;
    }

    /**
     * Hold commit at indexation start if needed
     *
     * @param Varien_Event_Observer $observer
     */
    public function holdCommit(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_search')->isThirdPartyEngineAvailable()) {
            return;
        }

        $engine = Mage::helper('catalogsearch')->getEngine();
        if (!$engine->holdCommit()) {
            return;
        }

        /*
         * Index needs to be optimized if all products were affected
         */
        $productIds = $observer->getEvent()->getProductIds();
        if (is_null($productIds)) {
            $engine->setIndexNeedsOptimization();
        }
    }

    /**
     * Apply changes in search engine index.
     * Make index optimization if documents were added to index.
     * Allow commit if it was held.
     *
     * @param Varien_Event_Observer $observer
     */
    public function applyIndexChanges(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_search')->isThirdPartyEngineAvailable()) {
            return;
        }

        $engine = Mage::helper('catalogsearch')->getEngine();
        if (!$engine->allowCommit()) {
            return;
        }

        if ($engine->getIndexNeedsOptimization()) {
            $engine->optimizeIndex();
        } else {
            $engine->commitChanges();
        }

        /**
         * Cleaning MAXPRICE cache
         */
        $cacheTag = Mage::getSingleton('enterprise_search/catalog_layer_filter_price')->getCacheTag();
        Mage::dispatchEvent('clean_cache_by_tags', array('tags' => array(
            $cacheTag
        )));
    }

    /**
     * Store searchable attributes at adapter to avoid new collection load there
     *
     * @param Varien_Event_Observer $observer
     */
    public function storeSearchableAttributes(Varien_Event_Observer $observer)
    {
        $engine     = $observer->getEvent()->getEngine();
        $attributes = $observer->getEvent()->getAttributes();
        if (!$engine || !$attributes || !Mage::helper('enterprise_search')->isThirdPartyEngineAvailable()) {
            return;
        }

        foreach ($attributes as $attribute) {
            if (!$attribute->usesSource()) {
                continue;
            }

            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($attribute->getAttributeId())
                ->setPositionOrder(Varien_Db_Select::SQL_ASC, true)
                ->load();

            $optionsOrder = array();
            foreach ($optionCollection as $option) {
                $optionsOrder[] = $option->getOptionId();
            }
            $optionsOrder = array_flip($optionsOrder);

            $attribute->setOptionsOrder($optionsOrder);
        }

        $engine->storeSearchableAttributes($attributes);
    }

    /**
     * Save store ids for website or store group before deleting
     * because lazy load for this property is used and this info is unavailable after deletion
     *
     * @param Varien_Event_Observer $observer
     */
    public function saveStoreIdsBeforeScopeDelete(Varien_Event_Observer $observer)
    {
        $object = $observer->getEvent()->getDataObject();
        $object->getStoreIds();
    }

    /**
     * Clear index data for deleted stores
     *
     * @param Varien_Event_Observer $observer
     */
    public function clearIndexForStores(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_search')->isThirdPartyEngineAvailable()) {
            return;
        }

        $object = $observer->getEvent()->getDataObject();
        if ($object instanceof Mage_Core_Model_Website
            || $object instanceof Mage_Core_Model_Store_Group
        ) {
            $storeIds = $object->getStoreIds();
        } elseif ($object instanceof Mage_Core_Model_Store) {
            $storeIds = $object->getId();
        } else {
            $storeIds = array();
        }

        if (!empty($storeIds)) {
            $engine = Mage::helper('catalogsearch')->getEngine();
            $engine->cleanIndex($storeIds);
        }
    }

    /**
     * Reset search engine if it is enabled for catalog navigation
     *
     * @param Varien_Event_Observer $observer
     */
    public function resetCurrentCatalogLayer(Varien_Event_Observer $observer)
    {
        if (Mage::helper('enterprise_search')->getIsEngineAvailableForNavigation()) {
            Mage::register('current_layer', Mage::getSingleton('enterprise_search/catalog_layer'));
        }
    }

    /**
     * Reset search engine if it is enabled for search navigation
     *
     * @param Varien_Event_Observer $observer
     */
    public function resetCurrentSearchLayer(Varien_Event_Observer $observer)
    {
        if (Mage::helper('enterprise_search')->getIsEngineAvailableForNavigation(false)) {
            Mage::register('current_layer', Mage::getSingleton('enterprise_search/search_layer'));
        }
    }

    /**
     * Reindex data after price reindex
     *
     * @deprecated since version 1.13.2
     *
     * @param Varien_Event_Observer $observer
     */
    public function runFulltextReindexAfterPriceReindex(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_search')->isThirdPartyEngineAvailable()) {
            return;
        }

        /* @var Enterprise_Search_Model_Indexer_Indexer $indexer */
        $indexer = Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext');
        if (empty($indexer)) {
            return;
        }

        if ('process' == strtolower(Mage::app()->getRequest()->getControllerName())) {
            $indexer->reindexAll();
        } else {
            $indexer->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }
   }

    /**
     * Invalidate CatalogSearch Mview after a catalog product price full reindex
     *
     * @param Varien_Event_Observer $observer
     */
    public function invalidateCatalogSearchMview(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_search')->isThirdPartyEngineAvailable()) {
            return;
        }

        $this->_invalidateCatalogSearchMview();
    }


    /**
     * Retrieve Fulltext Search instance
     *
     * @return Mage_CatalogSearch_Model_Fulltext
     */
    protected function _getIndexer()
    {
        return Mage::getSingleton('catalogsearch/fulltext');
    }

    /**
     * Reindex data after catalog category/product partial reindex
     *
     * @deprecated since version 1.13.2
     * @param Varien_Event_Observer $observer
     */
    public function rebuiltIndex(Varien_Event_Observer $observer)
    {
        $this->_getIndexer()->rebuildIndex(null, $observer->getEvent()->getProductIds())->resetSearchResults();
    }

    /**
     * Reindex affected products when a category is saved
     *
     * @param Varien_Event_Observer $observer
     * @return \Enterprise_Search_Model_Observer
     */
    public function processCategorySaveEvent(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_catalogsearch')->isLiveFulltextReindexEnabled()) {
            return;
        }

        /** @var $category Mage_Catalog_Model_Category */
        $category = $observer->getEvent()->getCategory();
        $productIds = $category->getAffectedProductIds();
        if (empty($productIds)) {
            return $this;
        }

        /* @var $client Enterprise_Mview_Model_Client */
        $client = Mage::getModel('enterprise_mview/client');
        $client->init('catalogsearch_fulltext');

        $client->execute('enterprise_catalogsearch/index_action_fulltext_refresh_row', array(
            'value' => $productIds,
        ));
    }
}
