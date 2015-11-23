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
class OnTap_Merchandiser_Helper_Data extends Mage_Core_Helper_Abstract
{
    const ID_EMPTY = 'empty';
    const DEFAULT_COLUMN_COUNT = 4;
    const MAX_COLUMN_COUNT = 5;
    const MIN_COLUMN_COUNT = 1;
    const DEFAULT_IMAGE_COUNT = 4;
    const PLACEHOLDER_PATH = 'merchandiser/images/placeholder.jpg';

    /**
     * getMinStockThreshold
     *
     * @return int
     */
    public function getMinStockThreshold()
    {
        $minStock = Mage::getStoreConfig('catalog/merchandiser/min_stock_threshold');
        return is_numeric($minStock) && $minStock > 0 ? $minStock : 0;
    }

    /**
     * getImageUrl
     *
     * @param Mage_Catalog_Model_Product $product
     * @param object $imageFile
     * @param int $size
     * @return string or null
     * @throws Exception
     */
    public function getImageUrl($product, $imageFile=null, $size=135)
    {
        try {
            return Mage::helper('catalog/image')
                ->init($product, 'small_image', $imageFile)
                ->resize($size)
                ->__toString();
        } catch(Exception $e) {
            $imagePath = self::PLACEHOLDER_PATH;
            $scheme = array('_theme' => 'default', '_package' => 'default');
            $baseDir = Mage::getDesign()->getSkinBaseDir($scheme) . DS;
            if (!file_exists($baseDir . $imagePath)) {
                throw new Exception(Mage::helper('merchandiser')->__('Image file was not found.'));
            }
            return Mage::getDesign()->getSkinBaseUrl($scheme) . $imagePath;
        }
    }

    /**
     * isCurrentCategoryRuled
     *
     * @param string $categoryId
     * @return array
     */
    public function isCurrentCategoryRuled($categoryId)
    {
        return Mage::getModel('merchandiser/merchandiser')->getCategoryValues($categoryId, 'ruled_only');
    }

    /**
     * getSkuIdDisplay
     *
     * @param string $sku
     * @return string
     */
    public function getSkuIdDisplay($sku)
    {
        return str_replace(" ", "_", $sku);
    }

    /**
     * getNotVisibleIds
     *
     * @return array
     */
    public function getNotVisibleIds()
    {
        return array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
        );
    }

    /**
     * afterSaveCategory
     *
     * @param mixed $category
     * @return void
     */
    public function afterSaveCategory($category)
    {
        Mage::dispatchEvent('catalog_category_save_commit_after', array(
            'category' => $category,
        ));
        $this->clearCategoryCache($category->getId());
    }

    /**
     * clearCategoryCache
     *
     * @param mixed $categoryId
     * @return void
     */
    public function clearCategoryCache($categoryId)
    {
        Mage::getModel('merchandiser/merchandiser')
            ->clearEntityCache(Mage::getModel('catalog/category'), array($categoryId));
    }

    /**
     * getSearchActionUrl
     *
     * @return string
     */
    public function getSearchActionUrl()
    {
        $url = Mage::getModel('core/url')->getUrl('merchandiser/search/result', array(
            '_secure'       => Mage::app()->getStore()->isFrontUrlSecure(),
            '_nosid'        => 1,
            ));
        return $url;
    }

    /**
     * isEnabledProduct
     *
     * @param Mage_Catalog_Model_Abstract $product
     * @return boolean
     */
    public function isEnabledProduct(Mage_Catalog_Model_Abstract $product)
    {
        return (Mage_Catalog_Model_Product_Status::STATUS_ENABLED == $product->getStatus());
    }

    /**
     * getEmptyId
     *
     * @return string
     */
    public function getEmptyId()
    {
        return self::ID_EMPTY;
    }

    /**
     * getProductinfoUrl
     *
     * @return string
     */
    public function getProductinfoUrl()
    {
        return Mage::helper("adminhtml")->getUrl('merchandiser/adminhtml/getproductinfo', array(
            '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure(),
        ));
    }

    /**
     * getColumnCount
     *
     * @param mixed $_columnCount
     * @return int
     */
    public function getColumnCount($_columnCount)
    {
        if (!$_columnCount) {
            $_columnCount = intval(Mage::getStoreConfig('catalog/merchandiser/column_count'));
        }

        if ($_columnCount < self::MIN_COLUMN_COUNT) {
            $_columnCount = self::DEFAULT_COLUMN_COUNT;
        }

        if ($_columnCount > self::MAX_COLUMN_COUNT) {
            $_columnCount = self::MAX_COLUMN_COUNT;
        }

        return $_columnCount;
    }

    /**
     * getAttributeCodes
     *
     * @return array
     */
    public function getAttributeCodes()
    {
        $attrstring = str_replace(' ', '', Mage::getStoreConfig('catalog/merchandiser/attribute_codes'));
        return explode(',', $attrstring);
    }

    /**
     * getAttributeCodesCount
     *
     * @return int
     */
    public function getAttributeCodesCount()
    {
        return count($this->getAttributeCodes());
    }

    /**
     * getMoreImageCount
     *
     * @return int
     */
    public function getMoreImageCount()
    {
        if (Mage::getStoreConfig('catalog/merchandiser/max_images_thumbnail')) {
            return Mage::getStoreConfig('catalog/merchandiser/max_images_thumbnail');
        }
        return self::DEFAULT_IMAGE_COUNT;
    }

    /**
     * getShowExtraImages
     *
     * @return bool
     */
    public function getShowExtraImages()
    {
        return Mage::getStoreConfig('catalog/merchandiser/show_extra_images');
    }

    /**
     * getShowCreationDate
     *
     * @return bool
     */
    public function getShowCreationDate()
    {
        return Mage::getStoreConfig('catalog/merchandiser/show_creation_date');
    }

    /**
     * getAjaxPageLoad
     *
     * @return int
     */
    public function getAjaxPageLoad()
    {
        return Mage::getStoreConfig('catalog/merchandiser/ajax_page_load');
    }

    /**
     * getStockQty
     *
     * @param mixed $product
     * @return int
     */
    public function getStockQty($product)
    {
        $qty = 0;
        switch ($product->getTypeId()) {
            case 'simple':
                if ($this->isEnabledProduct($product)) {
                    $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
                }
                break;
            case 'grouped':
                $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
                foreach ($associatedProducts as $associatedProduct) {
                    $qty += $this->getStockQty($associatedProduct);
                }
                break;
            case 'configurable':
                $associatedProducts = Mage::getModel('catalog/product_type_configurable')
                    ->getUsedProducts(null, $product);
                foreach ($associatedProducts as $associatedProduct) {
                    $qty += $this->getStockQty($associatedProduct);
                }
                break;
            default:
                $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
                $qty = $qty ? $qty : 0;
                break;
        }

        return $qty;
    }

    /**
     * getStockQtyHtml
     *
     * @param mixed $product
     * @return string
     */
    public function getStockQtyHtml($product)
    {
        $qty = (int)$this->getStockQty($product);
        return $this->__('Stock: ').$qty;
    }

    /**
     * getBundlePrice
     *
     * @param mixed $productId
     * @return string
     */
    public function getBundlePrice($productId)
    {
        $bundledProduct = new Mage_Catalog_Model_Product();
        $bundledProduct->load($productId);

        $bundledProductType = $bundledProduct->getTypeInstance(true);
        $dbSelectionCollection = $bundledProductType->getSelectionsCollection(
            $bundledProductType->getOptionsIds($bundledProduct),
            $bundledProduct
        );

        $bundledPrices = array();
        foreach ($dbSelectionCollection as $option) {
            if ($option->getPrice() != "0.0000") {
                $bundledPrices[] = $option->getPrice();
            }
        }
        sort($bundledPrices);

        $minPrice = $bundledPrices[0];
        $maxPriceTmp = array_slice($bundledPrices, -1, 1, false);
        $maxPrice = $maxPriceTmp[0];

        return '<div class="price-box">
                    <p class="price-from">
                        <span class="price-label">From:</span>
                        <span class="price">'.round($minPrice, 2).'</span>
                    </p>
                    <p class="price-to">
                        <span class="price-label">To:</span>
                        <span class="price">'.round($maxPrice, 2).'</span>
                    </p>
                </div>';
    }

    /**
     * getExtensionVersion
     *
     * @return string
     */
    public function getExtensionVersion()
    {
        return (string) Mage::getConfig()->getNode()->modules->OnTap_Merchandiser->version;
    }

    /**
     * getConfigAction
     *
     * @return array
     */
    public function getConfigAction()
    {
        $xmlActions = Mage::getStoreConfig('catalog/merchandiser/actions');
        $actions = array();
        foreach ($xmlActions as $xmlKey =>$xmlAction) {
            $action = array();
            $action['name'] = (string)$xmlAction['name'];
            $action['sorting_function'] = (string)$xmlAction['sorting_function'];
            $actions[$xmlKey] = $action;
        }
        return $actions;
    }

    /**
     * isHideInvisibleProducts
     *
     * @return bool
     */
    public function isHideInvisibleProducts()
    {
        return Mage::getStoreConfig('catalog/merchandiser/hide_invisible');
    }

    /**
     * isHideDisabledProducts
     *
     * @return bool
     */
    public function isHideDisabledProducts()
    {
        return Mage::getStoreConfig('catalog/merchandiser/hide_disabled');
    }

    /**
     * isHideProductPositionField
     *
     * @return bool
     */
    public function isHideProductPositionField()
    {
        return Mage::getStoreConfig('catalog/merchandiser/hide_product_position_field');
    }

    /**
     * smartFilter
     *
     * @param bool $curCategory (default: false)
     * @param mixed $categoryValues
     * @return array
     */
    public function smartFilter($curCategory = false, $categoryValues)
    {
        if ($categoryValues == '') {
            return array();
        }

        $queryParams = Mage::app()->getRequest()->getParams();

        if (isset($queryParams['cat']) && $queryParams['cat'] >0) {
            $curCategory = $queryParams['cat'];
        }

        if (!$curCategory) {
            $curCategory = mage::registry('current_category');
        } elseif (is_numeric($curCategory)) {
            $curCategory = Mage::getModel('catalog/category')->load($curCategory);
        }

        if (is_object($curCategory)) {
            if (strlen(trim($categoryValues))>=1 || $categoryValues) {
                $allIds = array();
                $attributeConditions = unserialize($categoryValues);
                $condition = "";

                foreach ($attributeConditions as $attribute) {
                    $productCollection = Mage::getResourceModel('catalog/product_collection');
                    $curType = "=";
                    $attributeFilterArray = array();

                    if ($attribute['attribute'] == "category_id") {
                        $productCollection
                            ->joinField('category_id', 'catalog/category_product',
                                'category_id', 'product_id=entity_id', null, 'inner');
                        $categoryFilterCondition = array();
                        $missingCategories = array();
                        foreach (explode(",", $attribute['value']) as $categoryId) {
                            $categoryObject = Mage::getModel('catalog/category')->load($categoryId);
                            if (!$categoryObject || !$categoryObject->getId()) {
                                $missingCategories[] = $categoryId;
                            }
                            $categoryFilterCondition[] = array('finset' => $categoryId);
                        }
                        if (count($missingCategories) > 0) {
                            $pluralTitle = count($missingCategories) > 1
                                ? $this->__("categories")
                                : $this->__("category");
                            $categoryIdsStr = implode(', ', $missingCategories);
                            $message = $this->__("The %s '%s' that you are trying to clone, does not exist.",
                                $pluralTitle,
                                $categoryIdsStr
                            );
                            Mage::getSingleton('core/session')->addNotice($message);
                        }
                        if (sizeof($categoryFilterCondition) > 0) {
                            $productCollection->addAttributeToFilter('category_id', $categoryFilterCondition);
                        } else {
                            continue;
                        }
                    } elseif ($attribute['attribute'] == 'created_at' || $attribute['attribute'] == 'updated_at') {
                        $allowedOperatorsDate = array(
                            '>' => 'lt',
                            '<' => 'gt',
                            '>=' => 'lteq',
                            '<=' => 'gteq'
                        );
                        $ranger = substr($attribute['value'], 0, 2);
                        $rangerToStrip = 2;
                        $rightRanger = substr($ranger, 1);

                        if (is_numeric($rightRanger)) {
                            $ranger = substr($attribute['value'], 0, 1);
                            $rangerToStrip = 1;
                        }

                        if (!is_numeric($ranger)) {
                            if (!array_key_exists($ranger, $allowedOperatorsDate)) {
                                $ranger = '<';
                            }
                            $curType = $allowedOperatorsDate[$ranger];
                            $attribute['value'] = substr($attribute['value'], $rangerToStrip);
                        }

                        $dateValue = date('Y-m-d', strtotime('-'.$attribute['value'].' days'));

                        if ($curType == "=") {
                            $dateStart = date('Y-m-d 00:00:00', strtotime($dateValue));
                            $dateEnd = date('Y-m-d 23:59:59', strtotime($dateValue));
                            $attributeFilterArray = array('from'  => $dateStart,'to'    => $dateEnd);
                        } else {
                            $attributeFilterArray = array($curType => $dateValue);
                        }

                        $productCollection->addAttributeToFilter($attribute['attribute'], $attributeFilterArray);
                    } else {
                        $attributeModel = Mage::getModel('catalog/resource_eav_attribute')
                            ->load($attribute['attribute']);
                        $attributeCode = $attributeModel->getAttributeCode();
                        $allowedOperators = array(
                            '>' => 'gt',
                            '<' => 'lt',
                            '!' => 'neq',
                            '>=' => 'gteq',
                            '<=' => 'lteq',
                            '=' => 'eq'
                        );

                        switch (strtolower($attribute['value'])) {
                            case 'yes':
                            case 'true':
                                $attribute['value'] = 1;
                                break;
                            case 'no':
                            case 'false':
                                $attribute['value'] = '0';
                                break;
                        }

                        if (strpos($attribute['value'], '*') !== false) {
                            $attribute['value'] = str_replace('*', '%', $attribute['value']);
                            $attributeFilterArray = array('like'=>$attribute['value']);
                        }

                        if ($attributeModel->getFrontendInput() == "select") {
                            if (substr($attribute['value'], 0, 1) == '!') {
                                $curType = '!=';
                                $attribute['value'] = substr($attribute['value'], 1);
                            }
                            $populateOptions = $attributeModel->getSource()->getAllOptions(true);
                            foreach ($populateOptions as $option) {
                                if ($option['label'] == $attribute['value']) {
                                    $attribute['value'] = $option['value'];
                                }
                            }
                            if ($curType == "!=") {
                                $attributeFilterArray = array('neq'=>$attribute['value']);
                            }
                        }

                        if ($attributeModel->getFrontendInput() == "multiselect") {
                            if (substr($attribute['value'], 0, 1) == '!') {
                                $curType = '!=';
                                $attribute['value'] = substr($attribute['value'], 1);
                            }
                            $populateOptions = $attributeModel->getSource()->getAllOptions(true);
                            foreach ($populateOptions as $option) {
                                if ($option['label'] == $attribute['value']) {
                                    $attribute['value'] = $option['value'];
                                }
                            }
                            $attributeFilterArray = array('finset' => $attribute['value']);
                        }

                        if ($attributeModel->getFrontendInput() == "price") {
                            $ranger = substr($attribute['value'], 0, 2);
                            $rangerToStrip = 2;
                            $rightRanger = substr($ranger, 1);

                            if (is_numeric($rightRanger)) {
                                $ranger = substr($attribute['value'], 0, 1);
                                $rangerToStrip = 1;
                            }

                            if (!is_numeric($ranger)) {
                                if (!array_key_exists($ranger, $allowedOperators)) {
                                    $ranger = '=';
                                }
                                $curType = $allowedOperators[$ranger];
                                $attribute['value'] = substr($attribute['value'], $rangerToStrip);
                            }

                            $attributeFilterArray = array($curType => $attribute['value']);
                        }

                        if (sizeof($attributeFilterArray) < 1) {
                            $attributeFilterArray = array('eq' => $attribute['value']);
                        }

                        $productCollection->addAttributeToSelect($attributeCode, 'left');

                        if ($attributeModel->getFrontendInput() == "multiselect" && $curType == "!=") {
                            $notFindInSetSQL = Mage::getModel('merchandiser/resource_mysql4_product_collection')
                                ->filterNotFindInSet($attributeCode, array('finset' => $attribute['value']));
                            $productCollection->getSelect()
                                ->where($notFindInSetSQL, null, Varien_Db_Select::TYPE_CONDITION);
                        } else {
                            $productCollection->addAttributeToFilter($attributeCode, $attributeFilterArray);
                        }
                    }
                    $currentCollectionIDs = $productCollection->getAllIds();
                    if ($condition == "AND") {
                        $allIds = array_intersect($allIds, $currentCollectionIDs);
                    } else {
                        $allIds = array_merge($allIds, $currentCollectionIDs);
                    }
                    $condition = $attribute['link'];
                }

                $allIds = array_unique($allIds);
                return $allIds;
            }
        }
    }

    /**
     * rebuildOnProductSave
     *
     * @return bool
     */
    public function rebuildOnProductSave()
    {
        return Mage::getStoreConfig('catalog/merchandiser/rebuild_on_productsave');
    }

    /**
     * rebuildOnCategorySave
     *
     * @return bool
     */
    public function rebuildOnCategorySave()
    {
        return Mage::getStoreConfig('catalog/merchandiser/rebuild_on_categorysave');
    }

    /**
     * rebuildOnCron
     *
     * @return bool
     */
    public function rebuildOnCron()
    {
        return Mage::getStoreConfig('catalog/merchandiser/rebuild_on_cron');
    }

    /**
     * newProductsHandler
     *
     * @return string
     */
    public function newProductsHandler()
    {
        return Mage::getStoreConfig('catalog/merchandiser/new_products_handler');
    }

    /**
     * getColorAttribute function.
     *
     * @return string
     */
    public function getColorAttribute()
    {
        return Mage::getStoreConfig('catalog/merchandiser/color_attribute');
    }

    /**
     * getColorAttributeOrder function.
     *
     * @return string
     */
    public function getColorAttributeOrder()
    {
        return Mage::getStoreConfig('catalog/merchandiser/color_order');
    }
}
