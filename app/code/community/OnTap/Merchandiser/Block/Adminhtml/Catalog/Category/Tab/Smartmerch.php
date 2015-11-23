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
class OnTap_Merchandiser_Block_Adminhtml_Catalog_Category_Tab_Smartmerch extends Mage_Core_Block_Template
{
    /**
     * _construct
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('catalog_category_smartmerch');
        $this->setTemplate('merchandiser/smartmerch/tab.phtml');
    }

    /**
     * getCategoryValues
     *
     * @return array
     */
    public function getCurrentCategoryValues()
    {
        $categoryValues = Mage::getResourceModel('merchandiser/merchandiser')
            ->fetchCategoriesValuesByCategoryId($this->getCategory()->getId());
        return count($categoryValues) ? array_shift($categoryValues) : null;
    }

    /**
     * getCategory
     *
     * @return object
     */
    public function getCategory()
    {
        return Mage::registry('category');
    }

    /**
     * getSmartCategoryAttributes
     *
     * @param array $categoryValues
     * @return array
     */
    public function getSmartCategoryAttributes($categoryValues)
    {
        try {
            $smartCategoryAttributes = unserialize($categoryValues['smart_attributes']);
        } catch(Exception $e) {
            $smartCategoryAttributes = array();
        }

        if (is_array($smartCategoryAttributes)) {
            foreach ($smartCategoryAttributes as $key => $row) {
                if (!is_array($row)) {
                    unset($smartCategoryAttributes[$key]);
                }
            }
        }

        return $smartCategoryAttributes;
    }
}
