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
 * Adminhtml permissions row block
 *
 * @category   Enterprise
 * @package    Enterprise_CatalogPermissions
 */
class Enterprise_CatalogPermissions_Block_Adminhtml_Catalog_Category_Tab_Permissions_Row
    extends Mage_Adminhtml_Block_Catalog_Category_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('enterprise/catalogpermissions/catalog/category/tab/permissions/row.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('delete_button', $this->getLayout()->createBlock('adminhtml/widget_button')
            ->addData(array(
                //'label' => $this->helper('enterprise_catalogpermissions')->__('Remove Permission'),
                'class' => 'delete' . ($this->isReadonly() ? ' disabled' : ''),
                'disabled' => $this->isReadonly(),
                'type'  => 'button',
                'id'    => '{{html_id}}_delete_button'
            ))
        );

        return parent::_prepareLayout();
    }

    /**
     * Check edit by websites
     *
     * @return boolean
     */
    public function canEditWebsites()
    {
        return !Mage::app()->isSingleStoreMode();
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

    public function getDefaultWebsiteId()
    {
        return Mage::app()->getStore(true)->getWebsiteId();
    }

    /**
     * Retrieve list of permission grants
     *
     * @return array
     */
    public function getGrants()
    {
        return array(
            'grant_catalog_category_view' => $this->helper('enterprise_catalogpermissions')->__('Browsing Category'),
            'grant_catalog_product_price' => $this->helper('enterprise_catalogpermissions')->__('Display Product Prices'),
            'grant_checkout_items' => $this->helper('enterprise_catalogpermissions')->__('Add to Cart')
        );
    }

    /**
     * Retrieve field class name
     *
     * @param string $fieldId
     * @return string
     */
    public function getFieldClassName($fieldId)
    {
        return strtr($fieldId, '_', '-') . '-value';
    }

    /**
     * Retrieve websites collection
     *
     * @return Mage_Core_Model_Mysql4_Website_Collection
     */
    public function getWebsiteCollection()
    {
        if (!$this->hasData('website_collection')) {
            $collection = Mage::getModel('core/website')->getCollection();
            $this->setData('website_collection', $collection);
        }

        return $this->getData('website_collection');
    }

    /**
     * Retrieve customer group collection
     *
     * @return Mage_Customer_Model_Entity_Group_Collection
     */
    public function getCustomerGroupCollection()
    {
        if (!$this->hasData('customer_group_collection')) {
            $collection = Mage::getModel('customer/group')->getCollection();
            $this->setData('customer_group_collection', $collection);
        }

        return $this->getData('customer_group_collection');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }
}
