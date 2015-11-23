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
 * @category    OnTap
 * @package     OnTap_Merchandiser
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class OnTap_Merchandiser_Model_Adminhtml_Observer
{
    /**
     * Setup the tab in Manage Categories
     *
     * @param Varien_Event_Observer $observer
     * @return Varien_Event_Observer
     */
    public function adminhtmlCatalogCategoryTabs(Varien_Event_Observer $observer)
    {
        try {
            $adminTabs=$observer->getEvent()->getTabs();
            $adminTabBlock=$adminTabs->getLayout()
                ->createBlock('merchandiser/adminhtml_catalog_category_tab_smartmerch', 'category.smartmerch.tab');
            $adminTabs->addTab('smartmerch', array('label' => Mage::helper('catalog')
                ->__('Visual Merchandiser'), 'content' => $adminTabBlock->toHtml()));
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * reindexCron
     *
     * @return void
     */
    public function reindexCron()
    {
        if (!Mage::helper('merchandiser')->rebuildOnCron()) {
            return $this;
        }
        $resourceModel = Mage::getResourceModel('merchandiser/merchandiser');

        $vmBuildAttributeCodes = $resourceModel->getVmBuildRows();
        $vmBuildAttributeCodes = array_unique($vmBuildAttributeCodes);
        $vmBuildAttributeCodes[] = 'updated_at';

        $resourceModel->reindexCategoryValuesIndexCron($vmBuildAttributeCodes);

        $resourceModel->clearVmBuildTable();
    }

    /**
     * categorySaveAfter
     *
     * @param mixed $observer
     * @return void
     */
    public function categorySaveAfter($observer)
    {
        $merchandiserResourceModel = Mage::getResourceModel('merchandiser/merchandiser');
        $category = $observer->getDataObject();
        $post = Mage::app()->getRequest()->getParams();

        if (!isset($post['merchandiser'])) {
            return $this;
        }

        $catId = $category->getId();
        $iCounter = 1;
        $insertData = array();

        $categoryValues = $merchandiserResourceModel->getCategoryValues($catId);
        $condition = 'category_id='. $catId;
        $merchandiserResourceModel->removeCategoryValues($condition);

        $productPositions = $category->getData('posted_products');
        $positionsArray = $productPositions;
        asort($productPositions);
        $productPositions = array_keys($productPositions);
        if ($post['merchandiser']['ruled_only'] == 1) {
            $productPositions = array();
        }

        $insertValues = array();
        $attributeCodes = array();
        if (isset($post['smartmerch_attributes'])) {
            foreach ($post['smartmerch_attributes'] as $key => $value) {
                if (isset($value['attribute']) && trim($value['attribute']) != '') {
                    if ($attribute = Mage::getModel('catalog/resource_eav_attribute')->load($value['attribute'])) {
                        if ($attribute->getId()) {
                            $attributeCodes[] = $attribute->getAttributeCode();
                        }
                    }
                    if (!is_numeric($value['attribute'])) {
                        $attributeCodes[] = $value['attribute'];
                    }
                }
                if (!is_array($value)) {
                    unset($post['smartmerch_attributes'][$key]);
                    continue;
                }
                if (array_key_exists('value', $value) && strlen(trim($value['value'])) == 0) {
                    unset($post['smartmerch_attributes'][$key]);
                    continue;
                }
            }
            if (count($post['smartmerch_attributes']) > 0) {
                $insertValues['smart_attributes'] = serialize($post['smartmerch_attributes']);
            } else {
                $insertValues['smart_attributes'] = '';
            }
        }

        if ($insertValues['smart_attributes'] == "" && $post['merchandiser']['heroproducts'] == ""
            && $post['merchandiser']['automatic_sort'] == "none") {
                return $this;
        }

        $insertValues['ruled_only']     =   $post['merchandiser']['ruled_only'];
        $insertValues['heroproducts']   =   $post['merchandiser']['heroproducts'];
        $insertValues['automatic_sort'] =   $post['merchandiser']['automatic_sort'];
        $insertValues['category_id']    =   $catId;
        $insertValues['attribute_codes']=   implode(",", array_unique($attributeCodes));

        if ($insertValues['smart_attributes'] == "") {
            $post['merchandiser']['ruled_only'] = 0;
        }

        if ($post['merchandiser']['ruled_only'] == 1) {
            $productPositions = array();
        }

        $allocatedProducts = array();
        if (Mage::helper('merchandiser')->rebuildOnCategorySave()) {

            $merchandiserResourceModel->clearCategoryProducts($catId);
            $heroProducts = implode(',',array_unique(explode(',', $post['merchandiser']['heroproducts'])));
            $postedHeroProduct =  array_map('trim', explode(",", $heroProducts));
            $existHeroProducts = array_map('trim', explode(",", $categoryValues['heroproducts']));
            $removedSKUs = array_diff($existHeroProducts, $postedHeroProduct);
            $productObject = Mage::getModel('catalog/product');

            if (sizeof($removedSKUs) > 0) {
                foreach ($removedSKUs as $removedSku) {
                    if ($productId = $productObject->getIdBySku(trim($removedSku))) {
                        $allocatedProducts[] = $productId;
                    }
                }
            }

            foreach (explode(",", $heroProducts) as $heroSKU) {
                if ($heroSKU != '' && $productId = $productObject->getIdBySku(trim($heroSKU))) {
                    if ($productId > 0) {
                        $allocatedProducts[] = $productId;
                        unset($positionsArray[$productId]);
                        $insertData[] = array(
                            'category_id' => $catId,
                            'product_id' => $productId,
                            'position' => $iCounter);
                        $iCounter++;
                    }
                }
            }

            $addTo = Mage::helper('merchandiser')->newProductsHandler(); // 1= TOP , 2 = BOTTOM
            $addTo = ($addTo < 1) ? 1 : $addTo;

            $productPositions = array_diff($productPositions, $allocatedProducts);
            $ruledProductIds = Mage::helper('merchandiser')->smartFilter($category, $insertValues['smart_attributes']);

            $ruledProductCount = $iCounter;
            if (sizeof($ruledProductIds) > 0) {

                $normalProductCount = sizeof($positionsArray) > 0 ? max($positionsArray) : 0;
                $differenceFactor = $iCounter - $normalProductCount;
                if ($differenceFactor <= 0) {
                    $differenceFactor = 1;
                }

                if ($addTo == 2 && $post['merchandiser']['ruled_only'] == 0) {
                     $ruledProductCount = $differenceFactor + $normalProductCount;
                }
                foreach ($ruledProductIds as $productId) {
                    if (!in_array($productId, $allocatedProducts) && !in_array($productId, $productPositions)) {
                        $allocatedProducts[] = $productId;
                        if ($addTo == 2) {
                            unset($positionsArray[$productId]);
                        }
                        $insertData[] = array(
                            'category_id' => $catId,
                            'product_id' => $productId,
                            'position' => $ruledProductCount);
                        $ruledProductCount++;
                    }
                }
            }
            if ($addTo == 1) {
                $iCounter = $ruledProductCount;
            }
            if ($post['merchandiser']['ruled_only'] == 0) {
                if (sizeof($productPositions) > 0) {
                    $incrementFactor = $iCounter - min($positionsArray);
                    if ($incrementFactor<0) {
                        $incrementFactor = 0;
                    }
                    foreach ($productPositions as $productId ) {
                        $allocatedProducts[] = $productId;
                        $currentPosition = ($positionsArray[$productId] > 0) ? $positionsArray[$productId] : 0;
                        $currentPosition += $incrementFactor;
                        $insertData[] = array(
                            'category_id' => $catId,
                            'product_id' => $productId,
                            'position' => $currentPosition
                        );
                    }
                    $iCounter = $currentPosition;
                }
            }
        }

        if ($insertValues['automatic_sort']==null || $insertValues['automatic_sort']=='') {
            $insertValues['automatic_sort'] = 'none';
        }
        $merchandiserResourceModel->insertCategoryValues($insertValues);

        if (sizeof($insertData)>0) {
            $merchandiserResourceModel->insertMultipleProductsToCategory($insertData);
            $merchandiserResourceModel->applySortAction($catId);
        }

        $merchandiserResourceModel->applySortAction($catId);
        Mage::helper('merchandiser')->clearCategoryCache($catId);
    }

    /**
     * categoryDeleteAfter
     *
     * @param mixed $observer
     * @return object
     */
    public function categoryDeleteAfter($observer)
    {
        if (!Mage::helper('merchandiser')->rebuildOnCategorySave()) {
            return $this;
        }
        $merchandiserResourceModel = Mage::getResourceModel('merchandiser/merchandiser');
        $category = $observer->getDataObject();
        $coreResource = Mage::getSingleton('core/resource');
        $writeAdapter = $coreResource->getConnection('core_write');
        $condition = array($writeAdapter->quoteInto('category_id=?', $category->getId()));
        $merchandiserResourceModel->removeCategoryValues($condition);
        return $this;
    }

    /**
     * productPrepareSave
     *
     * @param mixed $observer
     * @return void
     */
    public function productPrepareSave($observer)
    {
        if (!Mage::helper('merchandiser')->rebuildOnProductSave()) {
            return $this;
        }

        $merchandiserResourceModel = Mage::getResourceModel('merchandiser/merchandiser');
        $product = $observer->getProduct();
        $originData = $product->getOrigData();
        $data = $product->getData();

        unset($data['stock_item']);

        $data = array_map(array($this,'check'), $data);

        if (is_array($originData)) {
            unset($originData['stock_item']);
            $originData = array_map(array($this, 'check'), $originData);
            $difference = array_diff($originData, $data);
        } else {
            $difference = $data;
        }
        $changeAttributes = array_keys($difference);
        $insertData = array();

        foreach ($changeAttributes as $attributeCode) {
            if (Mage::getModel('catalog/resource_eav_attribute')->load($attributeCode, 'attribute_code')) {
                $categoryValues = $merchandiserResourceModel->getVmBuildRows($attributeCode);
                if (!$categoryValues || sizeof($categoryValues) < 1) {
                    $insertData[] = array('attribute_code' => $attributeCode);
                }
            }
        }

        if (sizeof($insertData) > 0) {
            foreach ($insertData as $iData) {
                $merchandiserResourceModel->insertVmBuildRows($iData);
            }
        }
    }

    /**
     * check
     *
     * @param mixed $value
     * @return string
     */
    public function check($value)
    {
        if (is_array($value)) {
            return 0;
        }
        if (is_object($value)) {
            return $value;
        }
        if (preg_match("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/", $value)) {
            $value = date("n/j/Y", strtotime($value));
        }
        if (preg_match('/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/', $value)) {
            $value = number_format($value, 2);
        }
        return $value;
    }
}
