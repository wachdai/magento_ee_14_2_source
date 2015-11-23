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
 * @package     Enterprise_CatalogPermissions
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Adminhtml observer
 *
 * @category   Enterprise
 * @package    Enterprise_CatalogPermissions
 */
class Enterprise_CatalogPermissions_Model_Adminhtml_Observer
{
    /**
     * Value when all websites or customer groups selected
     */
    const FORM_SELECT_ALL_VALUES = -1;

    /**
     * Category index queue
     *
     * @var array
     */
    protected $_indexQueue = array();

    /**
     * Product index queue
     *
     * @deprecated after 1.12.0.2
     *
     * @var array
     */
    protected $_indexProductQueue = array();

    /**
     * Save category permissions on category after save event
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Adminhtml_Observer
     */
    public function saveCategoryPermissions(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_catalogpermissions')->isEnabled()) {
            return $this;
        }

        $category = $observer->getEvent()->getCategory();
        /* @var $category Mage_Catalog_Model_Category */
        if ($category->hasData('permissions') && is_array($category->getData('permissions'))
            && Mage::getSingleton('admin/session')->isAllowed('catalog/enterprise_catalogpermissions')) {
            foreach ($category->getData('permissions') as $data) {
                $permission = Mage::getModel('enterprise_catalogpermissions/permission');
                if (!empty($data['id'])) {
                    $permission->load($data['id']);
                }

                if (!empty($data['_deleted'])) {
                    if ($permission->getId()) {
                        $permission->delete();
                    }
                    continue;
                }

                if ($data['website_id'] == self::FORM_SELECT_ALL_VALUES) {
                    $data['website_id'] = null;
                }

                if ($data['customer_group_id'] == self::FORM_SELECT_ALL_VALUES) {
                    $data['customer_group_id'] = null;
                }

                $permission->addData($data);
                $categoryViewPermission = $permission->getGrantCatalogCategoryView();
                if (Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY == $categoryViewPermission) {
                    $permission->setGrantCatalogProductPrice(
                        Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY
                    );
                }

                $productPricePermission = $permission->getGrantCatalogProductPrice();
                if (Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY == $productPricePermission) {
                    $permission->setGrantCheckoutItems(Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY);
                }
                $permission->setCategoryId($category->getId());
                $permission->save();
            }
            $this->_indexQueue[] = $category->getPath();
        }
        return $this;
    }

    /**
     * Reindex category permissions on category move event
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Adminhtml_Observer
     */
    public function reindexCategoryPermissionOnMove(Varien_Event_Observer $observer)
    {
        $category = Mage::getModel('catalog/category')
            ->load($observer->getEvent()->getCategoryId());
        $this->_indexQueue[] = $category->getPath();
        return $this;
    }

    /**
     * Reindex permissions in queue on postdipatch
     *
     * @param Varien_Event_Observer $observer
     * @return  Enterprise_CatalogPermissions_Model_Adminhtml_Observer
     */
    public function reindexPermissions(Varien_Event_Observer $observer)
    {
        if (!empty($this->_indexQueue)) {
            /** @var $indexer Enterprise_Catalogpermissions_Model_Permission_Index */
            $indexer = Mage::getModel('enterprise_catalogpermissions/permission_index');
            foreach ($this->_indexQueue as $item) {
                $indexer->reindex($item);
            }
            $this->_indexQueue = array();

            Mage::dispatchEvent('clean_cache_by_tags', array('tags' => array(
                Mage_Catalog_Model_Category::CACHE_TAG
            )));
        }

        return $this;
    }

    /**
     * Refresh category related cache on catalog permissions config save
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Adminhtml_Observer
     * @deprecated after 1.13.0.0
     */
    public function cleanCacheOnConfigChange(Varien_Event_Observer $observer)
    {
        Mage::dispatchEvent('clean_cache_by_tags', array('tags' => array(
            Mage_Catalog_Model_Category::CACHE_TAG
        )));
        Mage::getModel('enterprise_catalogpermissions/permission_index')->reindex();
        return $this;
    }

    /**
     * Rebuild index
     *
     * @param Varien_Event_Observer $observer
     * @return  Enterprise_CatalogPermissions_Model_Adminhtml_Observer
     */
    public function reindex(Varien_Event_Observer $observer)
    {
        $this->_indexQueue[] = '1';
        return $this;
    }

    /**
     * Add permission tab on category edit page
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Adminhtml_Observer
     */
    public function addCategoryPermissionTab(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_catalogpermissions')->isEnabled()) {
            return $this;
        }
        if (!Mage::getSingleton('admin/session')->isAllowed('catalog/enterprise_catalogpermissions')) {
            return $this;
        }

        $tabs = $observer->getEvent()->getTabs();
        /* @var $tabs Mage_Adminhtml_Block_Catalog_Category_Tabs */

        //if (Mage::helper('enterprise_catalogpermissions')->isAllowedCategory($tabs->getCategory())) {
            $tabs->addTab(
                'permissions',
                'enterprise_catalogpermissions/adminhtml_catalog_category_tab_permissions'
            );
        //}

        return $this;
    }

    /**
     * Apply categories and products permissions after reindex category products
     *
     * @param Varien_Event_Observer $observer
     */
    public function applyPermissionsAfterReindex(Varien_Event_Observer $observer)
    {
        Mage::getModel('enterprise_catalogpermissions/permission_index')->reindex();
    }

    /**
     * Check permissions availability for current category
     *
     * @deprecated after 1.12.0.2
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_CatalogPermissions_Model_Adminhtml_Observer
     */
    public function checkCategoryPermissions(Varien_Event_Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        /* @var $category Mage_Catalog_Model_Category */
        if (!Mage::helper('enterprise_catalogpermissions')->isAllowedCategory($category)
            && $category->hasData('permissions')
        ) {
            $category->unsetData('permissions');
        }

        return $this;
    }

    /**
     * Rebuild index for products
     *
     * @deprecated after 1.12.0.2
     *
     * @return  Enterprise_CatalogPermissions_Model_Adminhtml_Observer
     */
    public function reindexProducts()
    {
        $this->_indexProductQueue[] = null;
        return $this;
    }

    /**
     * Rebuild index after product assigned websites
     *
     * @deprecated after 1.12.0.2
     *
     * @param   Varien_Event_Observer $observer
     * @return  Enterprise_CatalogPermissions_Model_Adminhtml_Observer
     */
    public function reindexAfterProductAssignedWebsite(Varien_Event_Observer $observer)
    {
        $productIds = $observer->getEvent()->getProducts();
        $this->_indexProductQueue = array_merge($this->_indexProductQueue, $productIds);
        return $this;
    }

    /**
     * Save product permission index
     *
     * @deprecated after 1.12.0.2
     *
     * @param   Varien_Event_Observer $observer
     * @return  Enterprise_CatalogPermissions_Model_Adminhtml_Observer
     */
    public function saveProductPermissionIndex(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $this->_indexProductQueue[] = $product->getId();
        return $this;
    }
}
