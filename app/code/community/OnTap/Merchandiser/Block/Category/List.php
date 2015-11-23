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
class OnTap_Merchandiser_Block_Category_List extends Mage_Core_Block_Template
{
    const MIN_HEIGHT = 300;
    const MIN_HEIGHT_PER_ITEM = 15;

    /**
     * getHeroBoxHtml
     *
     * @return string or false
     */
    public function getHeroBoxHtml()
    {
        $model = Mage::getModel('merchandiser/merchandiser');
        $heroProducts = $model->getCategoryValues($this->getCategoryId(), 'heroproducts');
        if (trim($heroProducts) == "") {
            return false;
        }

        $productObject = Mage::getModel('catalog/product');
        $iCounter = 0;
        $html = array();
        foreach (explode(",", $heroProducts) as $heroSku) {
            $iCounter++;
            if ($productId = $productObject->getIdBySku(trim($heroSku))) {
                $productBox =  $this->getLayout()
                    ->createBlock('merchandiser/adminhtml_catalog_product_list')
                    ->setTemplate('merchandiser/new/category/heroproductbox.phtml');

                $productBox->setPid($productId);
                $productBox->setCurrentPosition($iCounter);

                $html[] = $productBox->toHtml();
            }
        }
        return count($html) > 0 ? $html : false;
    }

    /**
     * getProductBoxHeight
     *
     * @return int
     */
    public function getProductBoxHeight()
    {
        $attrCodeCount = Mage::helper('merchandiser')->getAttributeCodesCount();
        return self::MIN_HEIGHT + ($attrCodeCount * self::MIN_HEIGHT_PER_ITEM);
    }

    /**
     * getAjaxHtml
     *
     * @return string
     */
    public function getAjaxHtml()
    {
        $currentPage = $this->getRequest()->getParam('current_page');
        $pageLimit = $this->getRequest()->getParam('extra_products');

        $productCollection = $this->getProductCollection();
        $productCollection->setPage($currentPage, $pageLimit);

        $currentPosition = ((int)$currentPage-1) * (int)$pageLimit + 1;

        $html = "";

        if (0 < $productCollection->count()) {
            foreach ($productCollection as $_product) {
                $productBox =  $this->getLayout()
                    ->createBlock('merchandiser/adminhtml_catalog_product_list')
                    ->setTemplate('merchandiser/new/category/productbox.phtml');

                $productBox->setPid($_product->getId());
                $productBox->setCurrentPosition($currentPosition);
                $html .= $productBox->toHtml();
            }
        } else {
            $html = Mage::helper('merchandiser')->__('false');
        }

        return $html;
    }

    /**
     * getCategoryProductCollection
     *
     * @param int $catId
     * @param int $storeId (default: null)
     * @return Varien_Data_Collection
     */
    public function getCategoryProductCollection($catId, $storeId=null)
    {
        if (is_numeric($catId)) {
            $collection = Mage::getSingleton('merchandiser/search')
                ->addCategoryFilter($catId)
                ->getProductCollection()
                ->setStoreId($storeId);
        } else {
            $collection = $this->_getProductCollection();
        }
        return $collection;
    }

    /**
     * getCategory
     *
     * @return array
     */
    public function getCategory()
    {
        if (!$this->getData('category')) {
            if ($this->getCategoryId()) {
                if ($category = Mage::getModel('catalog/category')->load($this->getCategoryId())) {
                    $this->setData('category', $category);
                }
            }
        }
        return $this->getData('category');
    }

    /**
     * getCategoryId
     *
     * @return int or null
     */
    public function getCategoryId()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        return is_numeric($categoryId) ? (int)$categoryId : null;
    }

    /**
     * getProductCollection
     *
     * @return Varien_Data_Collection
     */
    public function getProductCollection()
    {
        $products = Mage::getModel('catalog/category')->load($this->getCategoryId())
            ->getProductCollection()
            ->addAttributeToSelect('*');

        $heroProducts = Mage::getModel('merchandiser/merchandiser')
            ->getCategoryValues($this->getCategory()->getId(), 'heroproducts');
        if ($heroProducts != '') {
            $products->addFieldToFilter('sku', array('nin' => array_map('trim', explode(",", $heroProducts))));
        }
            $products->getSelect()->order('cat_pro.position ASC');

        if (Mage::helper('merchandiser')->isHideInvisibleProducts()) {
            $products->addAttributeToFilter('visibility', array('or' => array(4,2)));
        }

        if (Mage::helper('merchandiser')->isHideDisabledProducts()) {
            $products->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        }
        return $products;
    }
}
