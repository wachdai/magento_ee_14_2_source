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
class OnTap_Merchandiser_Block_Adminhtml_Catalog_Product_List extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * _construct
     */
    public function _construct()
    {
        parent::_construct();
        $this->addPriceBlockType('simple', 'catalog/product_price', 'merchandiser/new/category/price.phtml');
        $this->addPriceBlockType('grouped', 'catalog/product_price', 'merchandiser/new/category/price-grouped.phtml');
        $this->addPriceBlockType(
            'configurable',
            'catalog/product_price',
            'merchandiser/new/category/price-configurable.phtml'
        );
    }

    /**
     * getMediaImagesByProductId
     *
     * @param int $productId
     * @return array
     */
    public function getMediaImagesByProductId($productId)
    {
        return Mage::getModel('catalog/product')->load($productId)->getMediaGalleryImages();
    }

    /**
     * getCurrency
     *
     * @return object
     */
    public function getCurrency()
    {
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        return Mage::getModel('directory/currency')->load($currencyCode);
    }

    /**
     * getUserPriceAttributesPerProduct
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getUserPriceAttributesPerProduct($product)
    {
        $currency = $this->getCurrency();

        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
            ->addFieldToFilter('frontend_input', 'price')
            ->addFieldToFilter('is_user_defined', 1);

        $resultAttributes = array();
        foreach ($attributes as $attribute) {
            $resourceAttribute = $product->getResource()->getAttribute($attribute->getAttributeCode());
            $attributeLabel = $resourceAttribute->getFrontendLabel();
            $attributeValue = $resourceAttribute->getFrontend()->getValue($product);

            if ($attributeValue == "") {
                continue;
            }
            $resultAttributes[] = array(
                'label' => $attributeLabel,
                'value' => $currency->formatPrecision($attributeValue, 2),
            );
        }
        return $resultAttributes;
    }

    /**
     * getAttributesPerProduct
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getAttributesPerProduct($product)
    {
        $attributeCodes = Mage::helper('merchandiser')->getAttributeCodes();
        $currency = $this->getCurrency();

        if (is_null($attributeCodes)) {
            return array();
        }

        $attributes = array();

        foreach ($attributeCodes as $attributeCode) {
            if (strcmp($attributeCode, '') == 0) {
                continue;
            }

            $eavAttribute = Mage::getModel('catalog/resource_eav_attribute')
                ->loadByCode('catalog_product', $attributeCode);

            if ($eavAttribute->getId() == null) {
                continue;
            }

            $resource = $product->getResource()->getAttribute($attributeCode);
            $label = $resource->getFrontendLabel();
            $value = $resource->getFrontend()->getValue($product);

            if ($value) {
                if ($eavAttribute->getFrontendInput() == 'price') {
                    $value = $currency->formatPrecision($value, 2);
                }
                $attributes[] = array(
                   'label' => $label,
                   'value' => $value,
                );
            }
        }
        return $attributes;
    }

    /**
     * getProductClasses
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getProductClasses($product)
    {
        $attrCodeCount = Mage::helper('merchandiser')->getAttributeCodesCount();
        $productClasses = array(
            "attrs-{$attrCodeCount}"
        );

        if (!Mage::helper('merchandiser')->isEnabledProduct($product)) {
            $productClasses[] = 'disabled';
        }

        if (in_array($product->getVisibility(), Mage::helper('merchandiser')->getNotVisibleIds())) {
            $productClasses[] = 'invisible';
        }

        return implode(' ', $productClasses);
    }

    /**
     * getWebClassesFromProduct
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getWebClassesFromProduct($product)
    {
        $websites = $product->getWebsiteIds();
        $websiteClasses = '';
        foreach ($websites as $websiteId) {
            $websiteClasses .= "website-" . $websiteId . " ";
        }
        return $websiteClasses;
    }

    /**
     * getCurrentProduct
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getCurrentProduct()
    {
        $productId = $this->getPid();
        return Mage::getModel('catalog/product')->load($productId);
    }
}
