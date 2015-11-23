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
 * Adminhtml permission tab on category page
 *
 * @category   Enterprise
 * @package    Enterprise_CatalogPermissions
 */
class Enterprise_CatalogPermissions_Block_Adminhtml_Catalog_Category_Tab_Permissions
    extends Mage_Adminhtml_Block_Catalog_Category_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('enterprise/catalogpermissions/catalog/category/tab/permissions.phtml');
    }

    /**
     * Prepare layout
     *
     * @return Enterprise_CatalogPermissions_Block_Adminhtml_Catalog_Category_Tab_Permissions
     */
    protected function _prepareLayout()
    {
        $this->setChild('row', $this->getLayout()->createBlock(
            'enterprise_catalogpermissions/adminhtml_catalog_category_tab_permissions_row'
        ));

        $this->setChild('add_button', $this->getLayout()->createBlock('adminhtml/widget_button')
            ->addData(array(
                'label' => $this->helper('enterprise_catalogpermissions')->__('New Permission'),
                'class' => 'add' . ($this->isReadonly() ? ' disabled' : ''),
                'type'  => 'button',
                'disabled' => $this->isReadonly()
            ))
        );

        return parent::_prepareLayout();
    }

    /**
     * Retrieve block config as JSON
     *
     * @return string
     */
    public function getConfigJson()
    {
        $config = array(
            'row' => $this->getChildHtml('row'),
            'duplicate_message' => $this->helper('enterprise_catalogpermissions')->__('A permission with the same scope already exists.'),
            'permissions'  => array()
        );

        if ($this->getCategoryId()) {
            foreach ($this->getPermissionCollection() as $permission) {
                $config['permissions']['permission' . $permission->getId()] = $permission->getData();
            }
        }

        $config['single_mode']  = Mage::app()->isSingleStoreMode();
        $config['website_id']   = Mage::app()->getStore(true)->getWebsiteId();
        $config['parent_vals']  = $this->getParentPermissions();

        $config['use_parent_allow'] = Mage::helper('enterprise_catalogpermissions')->__('(Allow)');
        $config['use_parent_deny'] = Mage::helper('enterprise_catalogpermissions')->__('(Deny)');
        //$config['use_parent_config'] = Mage::helper('enterprise_catalogpermissions')->__('(Config)');
        $config['use_parent_config'] = '';

        $additionalConfig = $this->getAdditionConfigData();
        if (is_array($additionalConfig)) {
            $config = array_merge($additionalConfig, $config);
        }

        return Mage::helper('core')->jsonEncode($config);
    }

    /**
     * Retrieve permission collection
     *
     * @return Enterprise_CatalogPermissions_Model_Mysql4_Permission_Collection
     */
    public function getPermissionCollection()
    {
        if (!$this->hasData('permission_collection')) {
            $collection = Mage::getModel('enterprise_catalogpermissions/permission')
                ->getCollection()
                ->addFieldToFilter('category_id', $this->getCategoryId())
                ->setOrder('permission_id', 'asc');
            $this->setData('permisssion_collection', $collection);
        }

        return $this->getData('permisssion_collection');
    }

    /**
     * Retrieve Use Parent permissions per website and customer group
     *
     * @return array
     */
    public function getParentPermissions()
    {
        $categoryId = null;
        if ($this->getCategoryId()) {
            $categoryId = $this->getCategory()->getParentId();
        }
        // parent category
        else if ($this->getRequest()->getParam('parent')) {
            $categoryId = $this->getRequest()->getParam('parent');
        }

        $permissions = array();
        if ($categoryId) {
            $index  = Mage::getModel('enterprise_catalogpermissions/permission_index')
                ->getIndexForCategory($categoryId, null, null);
            foreach ($index as $row) {
                $permissionKey = (isset($row['website_id']) ? $row['website_id'] : 'default')
                    . '_'
                    . (isset($row['customer_group_id']) ? $row['customer_group_id'] : 'default');
                $permissions[$permissionKey] = array(
                    'category'  => $row['grant_catalog_category_view'],
                    'product'   => $row['grant_catalog_product_price'],
                    'checkout'  => $row['grant_checkout_items']
                );
            }
        }

        $websites = Mage::app()->getWebsites(false);
        $groups   = Mage::getModel('customer/group')->getCollection()->getAllIds();

        /* @var $helper Enterprise_CatalogPermissions_Helper_Data */
        $helper   = Mage::helper('enterprise_catalogpermissions');

        $parent = (string)Enterprise_CatalogPermissions_Model_Permission::PERMISSION_PARENT;
        $allow  = (string)Enterprise_CatalogPermissions_Model_Permission::PERMISSION_ALLOW;
        $deny   = (string)Enterprise_CatalogPermissions_Model_Permission::PERMISSION_DENY;

        foreach ($groups as $groupId) {
            foreach ($websites as $website) {
                /* @var $website Mage_Core_Model_Website */
                $websiteId = $website->getId();

                $store = $website->getDefaultStore();
                $category = $helper->isAllowedCategoryView($store, $groupId);
                $product  = $helper->isAllowedProductPrice($store, $groupId);
                $checkout = $helper->isAllowedCheckoutItems($store, $groupId);

                $permissionKey = $websiteId . '_' . $groupId;
                if (!isset($permissions[$permissionKey])) {

                    if (isset($permissions[$websiteId . '_default'])) {
                        $permissions[$permissionKey] = $permissions[$websiteId . '_default'];
                    } elseif(isset($permissions['default_' . $groupId])) {
                        $permissions[$permissionKey] = $permissions['default_' . $groupId];
                    } elseif(isset($permissions['default_default'])) {
                        $permissions[$permissionKey] = $permissions['default_default'];
                    } else {
                        $permissions[$permissionKey] = array(
                            'category'  => $category ? $allow : $deny,
                            'product'   => $product ? $allow : $deny,
                            'checkout'  => $checkout ? $allow : $deny
                        );
                    }
                } else {
                    // validate and rewrite parent values for exists data
                    $data = $permissions[$permissionKey];
                    $permissions[$permissionKey] = array(
                        'category'  => $data['category'] == $parent ? ($category ? $allow : $deny) : $data['category'],
                        'product'   => $data['product'] == $parent ? ($checkout ? $allow : $deny) : $data['product'],
                        'checkout'  => $data['checkout'] == $parent ? ($product ? $allow : $deny) : $data['checkout'],
                    );
                }
            }
        }

        return $permissions;
    }

    /**
     * Retrieve tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->helper('enterprise_catalogpermissions')->__('Category Permissions');
    }

    /**
     * Retrieve tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->helper('enterprise_catalogpermissions')->__('Category Permissions');
    }

    /**
     * Tab visibility
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $canShow = $this->getCanShowTab();
        if (is_null($canShow)) {
            $canShow = Mage::getSingleton('admin/session')->isAllowed('catalog/enterprise_catalogpermissions');
        }
        return $canShow;
    }

    /**
     * Tab visibility
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Retrieve add button html
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Check is block readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getCategory()->getPermissionsReadonly();
    }
}
